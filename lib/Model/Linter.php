<?php

namespace Phpactor\Extension\LanguageServerPhpstan\Model;

use LanguageServerProtocol\Diagnostic;

class Linter
{
    /**
     * @var PhpstanProcess
     */
    private $process;

    public function __construct(PhpstanProcess $process = null)
    {
        $this->process = $process ?: new PhpstanProcess();
    }

    /**
     * @return array<Diagnostic>
     */
    public function lint(string $string): array
    {
        return [];
    }
}
