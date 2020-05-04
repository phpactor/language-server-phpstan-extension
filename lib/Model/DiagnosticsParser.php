<?php

namespace Phpactor\Extension\LanguageServerPhpstan\Model;

class DiagnosticsParser
{
    /**
     * @return array<Diagnostic>
     */
    public function parse(string $jsonString): array
    {
        return [];
    }
}
