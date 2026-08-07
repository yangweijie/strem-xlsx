<?php
declare(strict_types=1);
 
namespace StreamXlsx\Dto;
 
final class SheetDefinition
{
    /** @var string */
    public $name;
    /** @var string|null */
    public $title;
    /** @var string|null */
    public $subtitle;
    /** @var string|null */
    public $description;
    /** @var LogoOptions|null */
    public $logo;
    /** @var HeaderDefinition|null */
    public $header;
    /** @var mixed */
    public $rows;
    /** @var StyleOptions|null */
    public $headerStyle;
    /** @var RowStyleOptions|null */
    public $rowStyle;
    /** @var StyleOptions|null */
    public $bodyStyle;
    /** @var FreezeOptions|null */
    public $freeze;
    /** @var bool */
    public $filter;
    /** @var bool */
    public $autoWidth;
    /** @var array<string,float> */
    public $columnWidths;
    /** @var string[] */
    public $merges;
    /** @var array<string,string> */
    public $columnFormats;
    /** @var array<int,array{cell:string,path:string,width:?int,height:?int}> */
    public $images;
    /** @var \Closure|null */
    public $styleCallback;
    /** @var string[] */
    public $borders;
 
    /**
     * @param array<string,float> $columnWidths
     * @param string[] $merges
     * @param array<string,string> $columnFormats
     * @param array<int,array{cell:string,path:string,width:?int,height:?int}> $images
     * @param string[] $borders
     */
    public function __construct(
        string $name,
        ?string $title = null,
        ?string $subtitle = null,
        ?string $description = null,
        ?LogoOptions $logo = null,
        ?HeaderDefinition $header = null,
        $rows = null,
        ?StyleOptions $headerStyle = null,
        ?RowStyleOptions $rowStyle = null,
        ?StyleOptions $bodyStyle = null,
        ?FreezeOptions $freeze = null,
        bool $filter = false,
        bool $autoWidth = false,
        array $columnWidths = [],
        array $merges = [],
        array $columnFormats = [],
        array $images = [],
        ?\Closure $styleCallback = null,
        array $borders = []
    ) {
        $this->name           = $name;
        $this->title          = $title;
        $this->subtitle       = $subtitle;
        $this->description    = $description;
        $this->logo           = $logo;
        $this->header         = $header;
        $this->rows           = $rows;
        $this->headerStyle    = $headerStyle;
        $this->rowStyle       = $rowStyle;
        $this->bodyStyle      = $bodyStyle;
        $this->freeze         = $freeze;
        $this->filter         = $filter;
        $this->autoWidth      = $autoWidth;
        $this->columnWidths   = $columnWidths;
        $this->merges         = $merges;
        $this->columnFormats  = $columnFormats;
        $this->images         = $images;
        $this->styleCallback  = $styleCallback;
        $this->borders        = $borders;
    }
}
