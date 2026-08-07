<?php
declare(strict_types=1);
 
namespace StreamXlsx\Dto;
 
final class RowStyleOptions
{
    /** @var float|null */
    public $rowHeight;
    /** @var string|null */
    public $alternateColor;
 
    public function __construct(?float $rowHeight = null, ?string $alternateColor = null)
    {
        $this->rowHeight       = $rowHeight;
        $this->alternateColor  = $alternateColor;
    }
 
    public function with(array $overrides): self
    {
        return new self(
            $overrides['rowHeight']      ?? $this->rowHeight,
            $overrides['alternateColor'] ?? $this->alternateColor
        );
    }
}
