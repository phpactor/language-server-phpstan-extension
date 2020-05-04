<?php

namespace Phpactor\Extension\LanguageServerPhpstan;

use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\Extension;
use Phpactor\Extension\LanguageServerPhpstan\Handler\PhpstanHandler;
use Phpactor\MapResolver\Resolver;

class LanguageServerPhpstanExtension implements Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(ContainerBuilder $container)
    {
        $container->register(PhpstanHandler::class, function () {
            return new PhpstanHandler();
        });
    }

    /**
     * {@inheritDoc}
     */
    public function configure(Resolver $schema)
    {
    }
}
