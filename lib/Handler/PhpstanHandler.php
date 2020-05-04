<?php

namespace Phpactor\Extension\LanguageServerPhpstan\Handler;

use Amp\CancellationToken;
use Amp\Delayed;
use Amp\Promise;
use Amp\Success;
use Phpactor\Extension\LanguageServerPhpstan\Model\Linter;
use Phpactor\Extension\LanguageServerPhpstan\Model\PhpstanProcess;
use Phpactor\LanguageServer\Core\Handler\ServiceProvider;
use Phpactor\LanguageServer\Core\Rpc\NotificationMessage;
use Phpactor\LanguageServer\Core\Server\Transmitter\MessageTransmitter;
use Phpactor\LanguageServer\Event\TextDocumentUpdated;
use Phpactor\LanguageServer\Handler\System\ServiceHandler;
use Psr\EventDispatcher\ListenerProviderInterface;

class PhpstanHandler implements ServiceProvider, ListenerProviderInterface
{
    /**
     * @var MessageTransmitter
     */
    private $transmitter;

    /**
     * @var Linter
     */
    private $linter;

    /**
     * @var array<TextDocumentUpdated>
     */
    private $queue = [];

    public function __construct(Linter $linter)
    {
        $this->linter = $linter;
    }

    /**
     * @return array<string,string>
     */
    public function methods(): array
    {
        return [];
    }

    /**
     * @return array<string>
     */
    public function services(): array
    {
        return [
            'phpstan'
        ];
    }

    /**
     * @return Promise<bool>
     */
    public function phpstan(MessageTransmitter $transmitter, CancellationToken $token): Promise
    {
        return \Amp\call(function () use ($transmitter, $token) {
            while (true) {
                if (null === $textDocument = array_shift($this->queue)) {
                    yield new Delayed(100);
                    continue;
                }

                $diagnostics = yield $this->linter->lint($textDocument->identifier()->uri, $textDocument->updatedText());


                $transmitter->transmit(new NotificationMessage(
                    'textDocument/publishDiagnostics',
                    [
                        'uri' => $textDocument->identifier()->uri,
                        'version' => $textDocument->identifier()->version,
                        'diagnostics' => $diagnostics
                    ]
                ));
            }
        });
    }

    /**
     * @return array<callable>
     */
    public function getListenersForEvent(object $event): iterable
    {
        if (!$event instanceof TextDocumentUpdated) {
            return [];
        }

        return [
            [$this, 'lint']
        ];
    }

    public function lint(TextDocumentUpdated $textDocument): void
    {
        $this->queue[] = $textDocument;
    }
}
