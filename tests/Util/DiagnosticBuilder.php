<?php

namespace Phpactor\Extension\LanguageServerPhpstan\Tests\Util;

use LanguageServerProtocol\Diagnostic;
use LanguageServerProtocol\DiagnosticSeverity;
use LanguageServerProtocol\Position;
use LanguageServerProtocol\Range;

final class DiagnosticBuilder
{
    public static function create(): self
    {
        return new self();
    }

    public function build(): Diagnostic
    {
        return new Diagnostic('Undefined variable: $barfoo', new Range(
            new Position(1, 1),
            new Position(1, 1)
        ), null, DiagnosticSeverity::ERROR, 'phpstan');
    }
}
