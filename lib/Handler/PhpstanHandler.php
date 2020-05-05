<?php

namespace Phpactor\Extension\LanguageServerPhpstan\Handler;

use Amp\CancellationToken;
use Amp\CancelledException;
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
     * @var ?array{string,string,?int}
     */
    private $fileToAnalyse = null;

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
                try {
                    $token->throwIfRequested();
                } catch (CancelledException $cancelled) {
                    return;
                }

                if (null === $textDocument = $this->fileToAnalyse) {
                    yield new Delayed(10);
                    continue;
                }

                [$uri, $updatedText, $version] = $this->fileToAnalyse;

                $diagnostics = yield $this->linter->lint($uri, $updatedText);
                $this->fileToAnalyse = null;

                $transmitter->transmit(new NotificationMessage(
                    'textDocument/publishDiagnostics',
                    [
                        'uri' => $uri,
                        'version' => $version,
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
            [$this, 'lintUpdated']
        ];
    }

    public function lintUpdated(TextDocumentUpdated $textDocument): void
    {
        $this->fileToAnalyse = [$textDocument->identifier()->uri, $textDocument->updatedText(), $textDocument->identifier()->version];
    }
}
