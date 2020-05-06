<?php

namespace Phpactor\Extension\LanguageServerPhpstan\Handler;

use Amp\CancellationToken;
use Amp\CancelledException;
use Amp\Deferred;
use Amp\Promise;
use Phpactor\Extension\LanguageServerPhpstan\Model\FileToLint;
use Phpactor\Extension\LanguageServerPhpstan\Model\Linter;
use Phpactor\LanguageServer\Core\Handler\ServiceProvider;
use Phpactor\LanguageServer\Core\Rpc\NotificationMessage;
use Phpactor\LanguageServer\Core\Server\Transmitter\MessageTransmitter;
use Phpactor\LanguageServer\Event\TextDocumentSaved;
use Phpactor\LanguageServer\Event\TextDocumentUpdated;
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

    /**
     * @var bool
     */
    private $linting = false;

    /**
     * @var ?FileToLint
     */
    private $next;

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

                // if another update came in while doing the previous lint use
                // use that.
                if ($this->next) {
                    $fileToLint = $this->next;
                    $this->next = null;
                } else {
                    $fileToLint = yield $this->deferred->promise();
                }

                $this->deferred = new Deferred();

                // after we have reset deferred, we can safely set linting to
                // `false` and let another resolve happen
                $this->linting = false;

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
        if ($event instanceof TextDocumentUpdated) {
            return [
                [$this, 'lintUpdated']
            ];
        }

        if ($event instanceof TextDocumentSaved) {
            return [
                [$this, 'lintSaved']
            ];
        }


        return [];
    }

    public function lintUpdated(TextDocumentUpdated $textDocument): void
    {
        $fileToLint = new FileToLint(
            $textDocument->identifier()->uri,
            $textDocument->updatedText(),
            $textDocument->identifier()->version
        );

        // if we are already linting then store whatever comes afterwards in
        // next, overwriting the redundant update
        if ($this->linting === true) {
            $this->next = $fileToLint;
            return;
        }

        // resolving the promise will start PHPStan
        $this->linting = true;
        $this->deferred->resolve($fileToLint);
    }

    public function lintSaved(TextDocumentSaved $textDocument): void
    {
        $fileToLint = new FileToLint(
            $textDocument->identifier()->uri
        );

        // if we are already linting then store whatever comes afterwards in
        // next, overwriting the redundant update
        if ($this->linting === true) {
            $this->next = $fileToLint;
            return;
        }

        // resolving the promise will start PHPStan
        $this->linting = true;
        $this->deferred->resolve($fileToLint);
    }
}
