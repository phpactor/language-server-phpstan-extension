<?php

namespace Phpactor\Extension\LanguageServerPhpstan;

use Phpactor\Container\Container;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\Extension;
use Phpactor\Extension\LanguageServerPhpstan\Handler\PhpstanHandler;
use Phpactor\Extension\LanguageServerPhpstan\Model\Linter;
use Phpactor\Extension\LanguageServerPhpstan\Model\PhpstanProcess;
use Phpactor\Extension\LanguageServer\LanguageServerExtension;
use Phpactor\Extension\Logger\LoggingExtension;
use Phpactor\FilePathResolverExtension\FilePathResolverExtension;
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

        $container->register(Linter::class, function (Container $container) {
            return new Linter($container->get(PhpstanProcess::class));
        });

        $container->register(PhpstanProcess::class, function (Container $container) {
            $root = $container->get(FilePathResolverExtension::SERVICE_FILE_PATH_RESOLVER)->resolve('%project_root%');
            return new PhpstanProcess(
                $root,
                $container->get(LoggingExtension::SERVICE_LOGGER)
            );
        });
    }

    /**
     * {@inheritDoc}
     */
    public function configure(Resolver $schema)
    {
    }
}
