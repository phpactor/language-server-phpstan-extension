<?php

namespace Phpactor\Extension\LanguageServerPhpstan\Model;

use Amp\Promise;
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
     * @return Promise<array<Diagnostic>>
     */
    public function lint(string $string): Promise
    {
        return $this->process->analyse($string);
    }
}
