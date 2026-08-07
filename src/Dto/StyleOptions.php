<?php
declare(strict_types=1);
 
namespace StreamXlsx\Dto;
 
final class StyleOptions
{
    /** @var string|null */
    public $fontFamily;
    /** @var int|null */
    public $fontSize;
    /** @var string|null */
    public $fontColor;
    /** @var bool */
    public $bold;
    /** @var bool */
    public $italic;
    /** @var string|null */
    public $backgroundColor;
    /** @var string|null */
    public $alignment;
    /** @var bool */
    public $verticalCenter;
    /** @var bool */
    public $wrapText;
 
    public function __construct(
        ?string $fontFamily = null,
        ?int $fontSize = null,
        ?string $fontColor = null,
        bool $bold = false,
        bool $italic = false,
        ?string $backgroundColor = null,
        ?string $alignment = null,
        bool $verticalCenter = false,
        bool $wrapText = false
    ) {
        $this->fontFamily      = $fontFamily;
        $this->fontSize        = $fontSize;
        $this->fontColor       = $fontColor;
        $this->bold            = $bold;
        $this->italic          = $italic;
        $this->backgroundColor = $backgroundColor;
        $this->alignment       = $alignment;
        $this->verticalCenter  = $verticalCenter;
        $this->wrapText        = $wrapText;
    }
 
    public function with(array $overrides): self
    {
        return new self(
            $overrides['fontFamily']      ?? $this->fontFamily,
            $overrides['fontSize']        ?? $this->fontSize,
            $overrides['fontColor']       ?? $this->fontColor,
            $overrides['bold']            ?? $this->bold,
            $overrides['italic']          ?? $this->italic,
            $overrides['backgroundColor'] ?? $this->backgroundColor,
            $overrides['alignment']       ?? $this->alignment,
            $overrides['verticalCenter']  ?? $this->verticalCenter,
            $overrides['wrapText']        ?? $this->wrapText
        );
    }
}
