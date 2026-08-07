<?php
declare(strict_types=1);
 
namespace StreamXlsx\Dto;
 
final class SpreadsheetDefinition
{
    /** @var SheetDefinition[] */
    public $sheets;
 
    /**
     * @param SheetDefinition[] $sheets
     */
    public function __construct(array $sheets = [])
    {
        $this->sheets = $sheets;
    }
}
