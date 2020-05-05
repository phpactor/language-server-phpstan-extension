<?php

namespace Phpactor\Extension\LanguageServerPhpstan\Model\Linter;

use Amp\Promise;
use LanguageServerProtocol\Diagnostic;
use LanguageServerProtocol\VersionedTextDocumentIdentifier;
use Phpactor\Extension\LanguageServerPhpstan\Model\Linter;
use Phpactor\Extension\LanguageServerPhpstan\Model\PhpstanProcess;
use function Safe\tempnam;
use function Safe\file_put_contents;

class PhpstanLinter implements Linter
{
    /**
     * @var PhpstanProcess
     */
    private $process;

    public function __construct(PhpstanProcess $process)
    {
        $this->process = $process;
    }

    public function lint(string $url, string $text): Promise
    {
        /** @phpstan-ignore-next-line */
        return \Amp\call(function () use ($url, $text) {
            $name = tempnam(sys_get_temp_dir(), 'phpstanls');
            file_put_contents($name, $text);
            $diagnostics = yield $this->process->analyse($name);
            unlink($name);

            return $diagnostics;
        });
    }
}
