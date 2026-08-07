<?php
declare(strict_types=1);
 
namespace StreamXlsx\Dto;
 
final class HeaderCell
{
    /** @var string */
    public $label;
    /** @var int */
    public $colspan;
    /** @var int */
    public $rowspan;
    /** @var HeaderCell[] */
    public $children;
 
    /**
     * @param HeaderCell[] $children
     */
    public function __construct(string $label, int $colspan = 1, int $rowspan = 1, array $children = [])
    {
        $this->label    = $label;
        $this->colspan  = $colspan;
        $this->rowspan  = $rowspan;
        $this->children = $children;
    }
}
