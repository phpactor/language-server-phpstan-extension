<?php

namespace Phpactor\Extension\LanguageServerPhpstan\Handler;

use Phpactor\LanguageServer\Core\Handler\ServiceProvider;
use Phpactor\LanguageServer\Handler\System\ServiceHandler;

class PhpstanHandler implements ServiceProvider
{
    /**
     * {@inheritDoc}
     */
    public function methods(): array
    {
        return [];
    }

    /**
     * {@inheritDoc}
     */
    public function services(): array
    {
        return [
            'phpstan'
        ];
    }

    public function phpstan(MessageTransmitter $transmitter, CancellationToken $cancel): Promise
    {
        return \Amp\call(function () use ($transmitter, $cancel) {
            return true;
        });
    }
}
