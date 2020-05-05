<?php

namespace Phpactor\Extension\LanguageServerPhpstan\Model;

use LanguageServerProtocol\Diagnostic;
use LanguageServerProtocol\DiagnosticSeverity;
use LanguageServerProtocol\Position;
use LanguageServerProtocol\Range;

class DiagnosticsParser
{
    /**
     * @return array<Diagnostic>
     */
    public function parse(string $jsonString): array
    {
        $decoded = $this->decodeJson($jsonString);
        $diagnostics = [];

        foreach ($decoded['files'] ?? [] as $fileDiagnostics) {
            foreach ($fileDiagnostics['messages'] as $message) {
                $lineNo = (int)$message['line'] - 1;
                $lineNo = (int)$lineNo > 0 ? $lineNo : 0;

                $diagnostics[] = new Diagnostic(
                    $message['message'],
                    new Range(new Position($lineNo, 1), new Position($lineNo, 100)),
                    null,
                    DiagnosticSeverity::ERROR,
                    'phpstan'
                );
            }
        }

        return $diagnostics;
    }

    /**
     * @return array<mixed>
     */
    private function decodeJson(string $jsonString): array
    {
        return json_decode($jsonString, true, JSON_THROW_ON_ERROR);
    }
}
