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
    /**
     * @var HandlerTester
     */
    private $tester;

    /**
     * @var PhpstanHandler
     */
    private $handler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->handler = new PhpstanHandler($this->createTestLinter());
        $this->tester = new HandlerTester($this->handler);

        $this->tester->serviceManager()->start('phpstan');
    }

    /**
     * @return Generator<mixed>
     */
    public function testHandleSingle(): Generator
    {
        $updated = new TextDocumentUpdated(new VersionedTextDocumentIdentifier('file://path', 12), 'asd');
        $this->handler->lintUpdated($updated);

        yield new Delayed(10);

        $message = $this->tester->transmitter()->shift();

        self::assertNotNull($message);
        $this->tester->serviceManager()->stop('phpstan');
    }

    /**
     * @return Generator<mixed>
     */
    public function testHandleMany(): Generator
    {
        $updated = new TextDocumentUpdated(new VersionedTextDocumentIdentifier('file://path', 12), 'asd');
        $this->handler->lintUpdated($updated);

        yield new Delayed(10);

        $updated = new TextDocumentUpdated(new VersionedTextDocumentIdentifier('file://path', 12), 'asd');
        $this->handler->lintUpdated($updated);

        yield new Delayed(10);

        self::assertNotNull($this->tester->transmitter()->shift(), 'has message');

        $this->tester->serviceManager()->stop('phpstan');
    }

    private function createTestLinter(): TestLinter
    {
        return new TestLinter([
            DiagnosticBuilder::create()->build(),
        ], 10);
    }
}
