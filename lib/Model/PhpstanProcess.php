<?php

namespace Phpactor\Extension\LanguageServerPhpstan\Model;

use Amp\Process\Process;
use Amp\Promise;
use function Amp\ByteStream\buffer;
use Phpactor\Extension\LanguageServerPhpstan\Model\Excepteion\PhpstanProcessError;
use Psr\Log\LoggerInterface;

class PhpstanProcess
{
    /**
     * @var string
     */
    private $phpstanPath;

    /**
     * @var DiagnosticsParser
     */
    private $parser;

    /**
     * @var string
     */
    private $cwd;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(string $cwd, LoggerInterface $logger, DiagnosticsParser $parser = null)
    {
        $this->phpstanPath = __DIR__ . '/../../vendor/bin/phpstan';
        $this->parser = $parser ?: new DiagnosticsParser();
        $this->cwd = $cwd;
        $this->logger = $logger;
    }

    /**
     * @return Promise<array>
     */
    public function analyse(string $filename): Promise
    {
        return \Amp\call(function () use ($filename) {
            $process = new Process([
                $this->phpstanPath,
                'analyse',
                '--no-progress',
                '--error-format=json',
                $filename
            ], $this->cwd);

            $this->logger->info(sprintf(
                'Phpstan: %s in %s',
                $process->getCommand(),
                $process->getWorkingDirectory()
            ));

            $pid = yield $process->start();
            $stdout = yield buffer($process->getStdout());
            $stderr = yield buffer($process->getStderr());

            $exitCode = yield $process->join();

            if ($exitCode > 1) {
                throw new PhpstanProcessError(sprintf(
                    'Phpstan exited with code "%s": %s',
                    $exitCode,
                    $stderr
                ));
            }

            return $this->parser->parse($stdout);
        });
    }
}
