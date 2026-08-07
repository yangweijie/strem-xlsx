<?php
declare(strict_types=1);
 
namespace StreamXlsx\Dto;
 
use StreamXlsx\Enum\ExportFormat;
use StreamXlsx\Enum\OutputMode;
 
final class ExportOptions
{
    /** @var string */
    public $format;
    /** @var string */
    public $mode;
    /** @var string */
    public $filename;
    /** @var string|null */
    public $path;
 
    public function __construct(string $format, string $mode, string $filename, ?string $path = null)
    {
        $this->format   = $format;
        $this->mode     = $mode;
        $this->filename = $filename;
        $this->path     = $path;
    }
 
    public static function forFilename(string $filename, string $mode): self
    {
        return new self(ExportFormat::fromExtension($filename), $mode, $filename);
    }
}
