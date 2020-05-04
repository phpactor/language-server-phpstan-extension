<?php

namespace Phpactor\Extension\LanguageServerPhpstan;

use Phpactor\Container\Container;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\Extension;
use Phpactor\Extension\LanguageServerPhpstan\Handler\PhpstanHandler;
use Phpactor\Extension\LanguageServerPhpstan\Model\Linter;
use Phpactor\Extension\LanguageServer\LanguageServerExtension;
use Phpactor\MapResolver\Resolver;

class LanguageServerPhpstanExtension implements Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(ContainerBuilder $container)
    {
        $container->register(PhpstanHandler::class, function (Container $container) {
            return new PhpstanHandler($container->get(Linter::class));
        }, [
            LanguageServerExtension::TAG_LISTENER_PROVIDER => [],
            LanguageServerExtension::TAG_SESSION_HANDLER => [],
        ]);

        $container->register(Linter::class, function () {
            return new Linter();
        });
    }

    /**
     * {@inheritDoc}
     */
    public function configure(Resolver $schema)
    {
    }
}
