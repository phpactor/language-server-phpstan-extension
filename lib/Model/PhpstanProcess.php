<?php

namespace Phpactor\Extension\LanguageServerPhpstan\Model;

use Amp\Process\Process;
use Amp\Promise;
use function Amp\ByteStream\buffer;
use LanguageServerProtocol\Diagnostic;
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
     * @return Promise<array<Diagnostic>>
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

            $start = microtime(true);
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

            $this->logger->debug(sprintf(
                'Phpstan completed in %s: %s in %s',
                number_format(microtime(true) - $start, 4),
                $process->getCommand(),
                $process->getWorkingDirectory(),
            ));

            return $this->parser->parse($stdout);
        });
    }
}
