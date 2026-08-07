<?php
declare(strict_types=1);
 
namespace StreamXlsx\Dto;
 
final class HeaderDefinition
{
    /** @var HeaderCell[] */
    public $cells;
    /** @var int */
    public $rowCount;
    /** @var int */
    public $columnCount;
 
    /**
     * @param HeaderCell[] $cells
     */
    public function __construct(array $cells, int $rowCount = 1, int $columnCount = 0)
    {
        $this->cells       = $cells;
        $this->rowCount    = $rowCount;
        $this->columnCount = $columnCount;
    }
}
