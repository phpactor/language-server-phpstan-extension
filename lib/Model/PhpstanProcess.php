<?php

namespace Phpactor\Extension\LanguageServerPhpstan\Model;

use Amp\Process\Process;
use Amp\Promise;
use function Amp\ByteStream\buffer;
use Phpactor\Extension\LanguageServerPhpstan\Model\Excepteion\PhpstanProcessError;

class PhpstanProcess
{
    /**
     * @var string
     */
    private $phpstanPath;

    public function __construct()
    {
        $this->phpstanPath = __DIR__ . '/../../vendor/bin/phpstan';
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
            ]);
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

            die($diagnostics);
        });
    }
}
