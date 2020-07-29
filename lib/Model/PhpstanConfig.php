<?php

namespace Phpactor\Extension\LanguageServerPhpstan\Model;

final class PhpstanConfig
{
    /**
     * @var string
     */
    private $phpstanBin;

    /**
     * @var int|null
     */
    private $level;

    public function __construct(string $phpstanBin, ?int $level)
    {
        $this->phpstanBin = $phpstanBin;
        $this->level = $level;
    }

    public function level(): ?int
    {
        return $this->level;
    }

    public function phpstanBin(): string
    {
        return $this->phpstanBin;
    }
}
