<?php
declare(strict_types=1);
 
namespace StreamXlsx\Dto;
 
use StreamXlsx\Enum\LogoPosition;
 
final class LogoOptions
{
    /** @var string */
    public $source;
    /** @var int */
    public $width;
    /** @var int */
    public $height;
    /** @var string */
    public $position;
    /** @var int */
    public $marginTop;
 
    public function __construct(
        string $source,
        int $width = 120,
        int $height = 80,
        string $position = LogoPosition::LEFT,
        int $marginTop = 5
    ) {
        $this->source    = $source;
        $this->width     = $width;
        $this->height    = $height;
        $this->position  = $position;
        $this->marginTop = $marginTop;
    }
 
    public function with(array $overrides): self
    {
        return new self(
            $overrides['source']    ?? $this->source,
            $overrides['width']     ?? $this->width,
            $overrides['height']    ?? $this->height,
            $overrides['position']  ?? $this->position,
            $overrides['marginTop'] ?? $this->marginTop
        );
    }
}
