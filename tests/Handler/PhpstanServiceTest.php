<?php

namespace Phpactor\Extension\LanguageServerPhpstan\Tests\Handler;

use Amp\Delayed;
use Amp\PHPUnit\AsyncTestCase;
use Generator;
use Phpactor\Extension\LanguageServerPhpstan\Handler\PhpstanService;
use Phpactor\Extension\LanguageServerPhpstan\Model\Linter\TestLinter;
use Phpactor\Extension\LanguageServerPhpstan\Tests\Util\DiagnosticBuilder;
use Phpactor\LanguageServer\Core\Server\Transmitter\TestMessageTransmitter;
use Phpactor\LanguageServer\Core\Service\ServiceManager;
use Phpactor\LanguageServer\Core\Service\ServiceProviders;
use Phpactor\LanguageServer\Event\TextDocumentSaved;
use Phpactor\LanguageServer\Event\TextDocumentUpdated;
use Phpactor\LanguageServer\Test\HandlerTester;
use Phpactor\LanguageServer\Test\ProtocolFactory;
use Psr\Log\NullLogger;

class PhpstanServiceTest extends AsyncTestCase
{
    /**
     * @var HandlerTester
     */
    private $tester;

    /**
     * @var PhpstanService
     */
    private $serviceProvider;

    /**
     * @var ServiceManager
     */
    private $serviceManager;

    /**
     * @var TestMessageTransmitter
     */
    private $transmitter;

    protected function setUp(): void
    {
        parent::setUp();

        $this->transmitter = new TestMessageTransmitter();
        $this->serviceProvider = new PhpstanService($this->transmitter, $this->createTestLinter());
        $this->serviceManager = new ServiceManager(new ServiceProviders($this->serviceProvider), new NullLogger());
        $this->serviceManager->start('phpstan');
    }

    /**
     * @return Generator<mixed>
     */
    public function testHandleSingle(): Generator
    {
        $updated = new TextDocumentUpdated(ProtocolFactory::versionedTextDocumentIdentifier('file://path', 12), 'asd');
        $this->serviceProvider->lintUpdated($updated);

        yield new Delayed(10);

        $message = $this->transmitter->shift();

        self::assertNotNull($message);
        $this->serviceManager->stop('phpstan');
    }

    /**
     * @return Generator<mixed>
     */
    public function testHandleMany(): Generator
    {
        $updated = new TextDocumentUpdated(ProtocolFactory::versionedTextDocumentIdentifier('file://path', 12), 'asd');
        $this->serviceProvider->lintUpdated($updated);

        yield new Delayed(10);

        $updated = new TextDocumentUpdated(ProtocolFactory::versionedTextDocumentIdentifier('file://path', 12), 'asd');
        $this->serviceProvider->lintUpdated($updated);

        yield new Delayed(10);

        self::assertNotNull($this->transmitter->shift(), 'has message');

        $this->serviceManager->stop('phpstan');
    }

    /**
     * @return Generator<mixed>
     */
    public function testHandleManyFast(): Generator
    {
        $updated = new TextDocumentUpdated(ProtocolFactory::versionedTextDocumentIdentifier('file://path', 12), 'asd');
        $this->serviceProvider->lintUpdated($updated);
        $this->serviceProvider->lintUpdated($updated);
        $this->serviceProvider->lintUpdated($updated);
        $this->serviceProvider->lintUpdated($updated);
        $this->serviceProvider->lintUpdated($updated);

        yield new Delayed(100);

        $messages = [];
        while ($message = $this->transmitter->shift()) {
            $messages[] = $message;
        }

        $this->serviceManager->stop('phpstan');

        self::assertCount(2, $messages);
    }

    /**
     * @return Generator<mixed>
     */
    public function testHandleSaved(): Generator
    {
        $saved = new TextDocumentSaved(ProtocolFactory::textDocumentIdentifier('id'));
        $this->serviceProvider->lintSaved($saved);

        yield new Delayed(10);

        $message = $this->transmitter->shift();

        self::assertNotNull($message);
        $this->serviceManager->stop('phpstan');
    }


    private function createTestLinter(): TestLinter
    {
        return new TestLinter([
            DiagnosticBuilder::create()->build(),
        ], 10);
    }
}
