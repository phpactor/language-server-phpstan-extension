<?php

namespace Phpactor\Extension\LanguageServerPhpstan\Model;

use Amp\Promise;
use LanguageServerProtocol\Diagnostic;
use LanguageServerProtocol\VersionedTextDocumentIdentifier;

class Linter
{
    /**
     * @var PhpstanProcess
     */
    private $process;

    public function __construct(?PhpstanProcess $process = null)
    {
        $this->process = $process ?: new PhpstanProcess();
    }

    public function lint(string $url, string $text): Promise
    {
        return \Amp\call(function () use ($url, $text) {
            $name = tempnam(sys_get_temp_dir(), 'phpstanls');
            file_put_contents($name, $text);
            $diagnostics = yield $this->process->analyse($name);
            unlink($name);

            return $diagnostics;
        });
    }
}
