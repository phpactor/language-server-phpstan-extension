<?php

namespace Phpactor\Extension\LanguageServerPhpstan\Model;

use Phpactor\LanguageServerProtocol\DiagnosticSeverity;
use Phpactor\LanguageServerProtocol\Position;
use Phpactor\LanguageServerProtocol\Range;
use Phpactor\LanguageServerProtocol\Diagnostic;
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
            $lines = file($uri);
            if ($lines === false) {
                $lines = [];
            }
                 
            foreach ($fileDiagnostics['messages'] as $message) {
                $lineNo = (int)$message['line'] - 1;
                $lineNo = (int)$lineNo > 0 ? $lineNo : 0;
                $line = $lines[$lineNo] ?? "";
                 
                $matches = [];
                $offset = (\preg_match("/^\\s+/", $line, $matches) === 1) ? mb_strlen($matches[0]) : 0;
                 
                $diagnostics[] = Diagnostic::fromArray([
                     'message' => $message['message'],
                     'range' => new Range(new Position($lineNo, $offset), new Position($lineNo, mb_strlen($line))),
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
