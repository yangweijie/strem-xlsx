<?php
declare(strict_types=1);
 
namespace StreamXlsx\Builder;
 
use StreamXlsx\Dto\FreezeOptions;
use StreamXlsx\Dto\LogoOptions;
use StreamXlsx\Dto\RowStyleOptions;
use StreamXlsx\Dto\SheetDefinition;
use StreamXlsx\Dto\StyleOptions;
use StreamXlsx\Enum\BorderScope;
use StreamXlsx\Enum\FreezeMode;
use StreamXlsx\Enum\HorizontalAlignment;
use StreamXlsx\Enum\LogoPosition;
use StreamXlsx\Exception\InvalidConfigurationException;
 
/**
 * Fluent, per-sheet configuration builder. Accumulates plain mutable state
 * and produces an immutable SheetDefinition via build().
 */
final class SheetBuilder
{
    /** @var string */
    private $name;
    /** @var string|null */
    private $title = null;
    /** @var string|null */
    private $subtitle = null;
    /** @var string|null */
    private $description = null;
    /** @var LogoOptions|null */
    private $logo = null;
    /** @var array|null */
    private $rawHeaders = null;
    /** @var mixed */
    private $rows = null;
    /** @var StyleOptions */
    private $headerStyle;
    /** @var RowStyleOptions */
    private $rowStyle;
    /** @var StyleOptions */
    private $bodyStyle;
    /** @var FreezeOptions|null */
    private $freeze = null;
    /** @var bool */
    private $filter = false;
    /** @var bool */
    private $autoWidth = false;
    /** @var array<string,float> */
    private $columnWidths = [];
    /** @var string[] */
    private $merges = [];
    /** @var array<string,string> */
    private $columnFormats = [];
    /** @var array<int,array{cell:string,path:string,width:?int,height:?int}> */
    private $images = [];
    /** @var string[] */
    private $borders = [];
    /** @var \Closure|null */
    private $styleCallback = null;
 
    public function __construct(string $name = 'Sheet1')
    {
        $this->name = $name;
        $this->headerStyle = StyleOptions::defaultHeader();
        $this->rowStyle = new RowStyleOptions();
        $this->bodyStyle = new StyleOptions();
    }
 
    public function setTitle(string $title): self { $this->title = $title; return $this; }
    public function setSubtitle(string $subtitle): self { $this->subtitle = $subtitle; return $this; }
    public function setDescription(string $description): self { $this->description = $description; return $this; }
 
    public function withLogo(string $source): self { $this->logo = new LogoOptions($source); return $this; }
 
    public function logoWidth(int $width): self
    {
        $this->logo = ($this->logo ?? new LogoOptions(''))->with(['width' => $width]);
        return $this;
    }
 
    public function logoHeight(int $height): self
    {
        $this->logo = ($this->logo ?? new LogoOptions(''))->with(['height' => $height]);
        return $this;
    }
 
    public function logoPosition(string $position): self
    {
        $this->logo = ($this->logo ?? new LogoOptions(''))->with(['position' => LogoPosition::from($position)]);
        return $this;
    }
 
    public function logoMarginTop(int $margin): self
    {
        $this->logo = ($this->logo ?? new LogoOptions(''))->with(['marginTop' => $margin]);
        return $this;
    }
 
    public function headerColor(string $hex): self
    {
        $this->headerStyle = $this->headerStyle->with([
            'backgroundColor' => $hex,
            'fontColor' => $this->headerStyle->fontColor ?? '#FFFFFF',
            'bold' => true,
            'verticalCenter' => true,
        ]);
        return $this;
    }
 
    public function headerFontSize(int $size): self
    {
        $this->headerStyle = $this->headerStyle->with(['fontSize' => $size]);
        return $this;
    }
 
    public function headerFontColor(string $hex): self
    {
        $this->headerStyle = $this->headerStyle->with(['fontColor' => $hex]);
        return $this;
    }
 
    public function headerBold(bool $bold = true): self
    {
        $this->headerStyle = $this->headerStyle->with(['bold' => $bold]);
        return $this;
    }
 
    public function headerItalic(bool $italic = true): self
    {
        $this->headerStyle = $this->headerStyle->with(['italic' => $italic]);
        return $this;
    }
 
    public function rowHeight(float $height): self
    {
        $this->rowStyle = $this->rowStyle->with(['rowHeight' => $height]);
        return $this;
    }
 
    public function font(string $family): self
    {
        $this->bodyStyle = $this->bodyStyle->with(['fontFamily' => $family]);
        return $this;
    }
 
    public function fontSize(int $size): self
    {
        $this->bodyStyle = $this->bodyStyle->with(['fontSize' => $size]);
        return $this;
    }
 
    public function alternateColor(string $hex): self
    {
        $this->rowStyle = $this->rowStyle->with(['alternateColor' => $hex]);
        return $this;
    }
 
    public function wrapText(bool $wrap = true): self
    {
        $this->bodyStyle = $this->bodyStyle->with(['wrapText' => $wrap]);
        return $this;
    }
 
    public function alignment(string $horizontal): self
    {
        $this->bodyStyle = $this->bodyStyle->with(['alignment' => HorizontalAlignment::from($horizontal)]);
        return $this;
    }
 
    public function alignCenter(): self { return $this->alignment('center'); }
    public function alignLeft(): self { return $this->alignment('left'); }
    public function alignRight(): self { return $this->alignment('right'); }
 
    public function freeze(string $cell): self
    {
        $this->freeze = new FreezeOptions(FreezeMode::CELL, $cell);
        return $this;
    }
 
    public function freezeHeader(): self
    {
        $this->freeze = new FreezeOptions(FreezeMode::HEADER);
        return $this;
    }
 
    public function freezeColumn(string $column): self
    {
        $this->freeze = new FreezeOptions(FreezeMode::COLUMN, $column);
        return $this;
    }
 
    public function freezeRow(int $row): self
    {
        $this->freeze = new FreezeOptions(FreezeMode::ROW, $row);
        return $this;
    }
 
    public function filter(bool $enabled = true): self { $this->filter = $enabled; return $this; }
    public function autoWidth(bool $enabled = true): self { $this->autoWidth = $enabled; return $this; }
 
    /**
     * @param array<string,float> $widths
     */
    public function columnWidth(array $widths): self
    {
        $this->columnWidths = array_merge($this->columnWidths, $widths);
        return $this;
    }
 
    public function merge(string $range): self
    {
        $this->merges[] = $range;
        return $this;
    }
 
    public function border(): self { return $this->borderAll(); }
 
    public function borderHeader(): self
    {
        $this->borders[] = BorderScope::HEADER;
        return $this;
    }
 
    public function borderBody(): self
    {
        $this->borders[] = BorderScope::BODY;
        return $this;
    }
 
    public function borderAll(): self
    {
        $this->borders[] = BorderScope::ALL;
        return $this;
    }
 
    public function currency(string ...$columns): self { return $this->applyFormat('currency', $columns); }
    public function date(string ...$columns): self { return $this->applyFormat('date', $columns); }
    public function datetime(string ...$columns): self { return $this->applyFormat('datetime', $columns); }
    public function percentage(string ...$columns): self { return $this->applyFormat('percentage', $columns); }
    public function number(string ...$columns): self { return $this->applyFormat('number', $columns); }
 
    /**
     * @param array<string,string> $formats
     */
    public function columnFormat(array $formats): self
    {
        $this->columnFormats = array_merge($this->columnFormats, $formats);
        return $this;
    }
 
    public function image(string $cell, string $path, ?int $width = null, ?int $height = null): self
    {
        $this->images[] = ['cell' => $cell, 'path' => $path, 'width' => $width, 'height' => $height];
        return $this;
    }
 
    public function headers(array $headers): self
    {
        $this->rawHeaders = $headers;
        return $this;
    }
 
    /**
     * @param mixed $data
     */
    public function rows($data): self
    {
        $this->rows = $data;
        return $this;
    }
 
    public function style(\Closure $callback): self
    {
        $this->styleCallback = $callback;
        return $this;
    }
 
    public function build(): SheetDefinition
    {
        $header = null;
        if ($this->rawHeaders !== null) {
            $header = (new HeaderBuilder())->build($this->rawHeaders);
        }
 
        return new SheetDefinition(
            $this->name,
            $this->title,
            $this->subtitle,
            $this->description,
            $this->logo,
            $header,
            $this->rows,
            $this->headerStyle,
            $this->rowStyle,
            $this->bodyStyle,
            $this->freeze,
            $this->filter,
            $this->autoWidth,
            $this->columnWidths,
            $this->merges,
            $this->columnFormats,
            $this->images,
            $this->styleCallback,
            $this->borders
        );
    }
 
    private function applyFormat(string $format, array $columns): self
    {
        if ($columns === []) {
            throw new InvalidConfigurationException(
                sprintf('->%s() requires at least one column letter, e.g. ->%s(\'G\').', $format, $format)
            );
        }
        foreach ($columns as $column) {
            $this->columnFormats[$column] = $format;
        }
        return $this;
    }
}
