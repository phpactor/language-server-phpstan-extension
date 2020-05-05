<?php

namespace Phpactor\Extension\LanguageServerPhpstan\Model;

use Amp\Promise;
use LanguageServerProtocol\Diagnostic;
use LanguageServerProtocol\VersionedTextDocumentIdentifier;

interface Linter
{
    /**
     * @return Promise<array<Diagnostic>>
     */
    public function lint(string $url, string $text): Promise;
}
