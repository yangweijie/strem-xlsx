<?php
declare(strict_types=1);
 
namespace StreamXlsx\Enum;
 
use StreamXlsx\Exception\UnsupportedExportFormatException;
 
final class ExportFormat
{
    public const XLSX = 'xlsx';
    public const CSV  = 'csv';
 
    private const VALUES = [self::XLSX, self::CSV];
 
    public static function fromExtension(string $filename): string
    {
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
 
        if (!in_array($extension, self::VALUES, true)) {
            throw new UnsupportedExportFormatException(
                sprintf('Unsupported export format "%s".', $extension)
            );
        }
 
        return $extension;
    }
}
