<?php

namespace Phpactor\Extension\LanguageServerPhpstan\Handler;

use Amp\CancellationToken;
use Amp\CancelledException;
use Amp\Deferred;
use Amp\Delayed;
use Amp\Promise;
use Amp\Success;
use Phpactor\Extension\LanguageServerPhpstan\Model\FileToLint;
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
     * @var int
     */
    private $pollTime;

    /**
     * @var Deferred<FileToLint>
     */
    private $deferred;

    public function __construct(Linter $linter, int $pollTime = 100)
    {
        $this->linter = $linter;
        $this->pollTime = $pollTime;
        $this->deferred = new Deferred();
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

                $fileToLint = yield $this->deferred->promise();

                // reset deferred
                $this->deferred = new Deferred();

                assert($fileToLint instanceof FileToLint);
                $diagnostics = yield $this->linter->lint($fileToLint->uri(), $fileToLint->contents());

                $transmitter->transmit(new NotificationMessage(
                    'textDocument/publishDiagnostics',
                    [
                        'uri' => $fileToLint->uri(),
                        'version' => $fileToLint->version(),
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
        $this->deferred->resolve(new FileToLint(
            $textDocument->identifier()->uri,
            $textDocument->updatedText(),
            $textDocument->identifier()->version
        ));
    }
}
