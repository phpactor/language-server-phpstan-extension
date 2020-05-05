<?php

namespace Phpactor\Extension\LanguageServerPhpstan\Tests\Handler;

use Amp\Delayed;
use Amp\PHPUnit\AsyncTestCase;
use Generator;
use LanguageServerProtocol\VersionedTextDocumentIdentifier;
use PHPUnit\Framework\TestCase;
use Phpactor\Extension\LanguageServerPhpstan\Handler\PhpstanHandler;
use Phpactor\Extension\LanguageServerPhpstan\Model\Linter;
use Phpactor\Extension\LanguageServerPhpstan\Model\Linter\TestLinter;
use Phpactor\Extension\LanguageServerPhpstan\Tests\Util\DiagnosticBuilder;
use Phpactor\LanguageServer\Event\TextDocumentUpdated;
use Phpactor\LanguageServer\Test\HandlerTester;

class PhpstanHandlerTest extends AsyncTestCase
{
    public function testHandler(): Generator
    {
        $linter = new TestLinter([
            DiagnosticBuilder::create()->build(),
        ], 10);

        $handler = new PhpstanHandler($linter);
        $tester = new HandlerTester($handler);

        $tester->serviceManager()->start('phpstan');

        yield new Delayed(10);

        $updated = new TextDocumentUpdated(new VersionedTextDocumentIdentifier('file://path', 12), 'asd');
        $handler->lintUpdated($updated);

        yield new Delayed(100);

        $message = $tester->transmitter()->shift();
        self::assertNotNull($message);

        $tester->serviceManager()->stop('phpstan');
    }
}
