<?php

namespace Phpactor\Extension\LanguageServerPhpstan\Model;

use Phpactor\FilePathResolver\PathResolver;

class PhpstanFinder
{
    /**
     * @var array
     */
    private $paths;

    /**
     * @var PathResolver
     */
    private $pathResolver;

    public function __construct(PathResolver $pathResolver, array $paths)
    {
        $this->paths = $paths;
        $this->pathResolver = $pathResolver;
    }

    public function resolve(): string
    {
    }
}
