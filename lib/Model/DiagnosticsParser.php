<?php

namespace Phpactor\Extension\LanguageServerPhpstan\Model;

use Phpactor\LanguageServerProtocol\DiagnosticSeverity;
use Phpactor\LanguageServerProtocol\Position;
use Phpactor\LanguageServerProtocol\Range;
use Phpactor\LanguageServerProtocol\Diagnostic;
use Phpactor\TextDocument\Util\LineColRangeForLine;
use RuntimeException;

class DiagnosticsParser
{
    /**
     * @return array<Diagnostic>
     */
    public function parse(string $jsonString): array
    {
        $decoded = $this->decodeJson($jsonString);
        $diagnostics = [];

        foreach ($decoded['files'] ?? [] as $uri => $fileDiagnostics) {
            $text = \file_get_contents($uri);
            
            foreach ($fileDiagnostics['messages'] as $message) {
                $lineNo = (int)$message['line'] - 1;
                $lineNo = (int)$lineNo > 0 ? $lineNo : 0;
                
                $start = 0;
                $end = 100;
                if($text !== false) {
                    $range = (new LineColRangeForLine())->rangeFromLine($text, $lineNo + 1);
                    $start = $range->start()->col() - 1;
                    $end = $range->end()->col();
                }
                 
                $diagnostics[] = Diagnostic::fromArray([
                     'message' => $message['message'],
                     'range' => new Range(new Position($lineNo, $start), new Position($lineNo, $end)),
                     'severity' => DiagnosticSeverity::ERROR,
                     'source' => 'phpstan'
                 ]);
            }
        }
 
        return $diagnostics;
    }

    /**
     * @return array<mixed>
     */
    private function decodeJson(string $jsonString): array
    {
        $decoded = json_decode($jsonString, true);

        if (null === $decoded) {
            throw new RuntimeException(sprintf(
                'Could not decode expected PHPStan JSON string "%s"',
                $jsonString
            ));
        }

        return $decoded;
    }
}
