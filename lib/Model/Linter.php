<?php

namespace Phpactor\Extension\LanguageServerPhpstan\Model;

use LanguageServerProtocol\Diagnostic;

class Linter
{
    /**
     * @return array<Diagnostic>
     */
    public function lint(string $string): array
    {
        return [];
    }
}
