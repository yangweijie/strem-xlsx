<?php
declare(strict_types=1);
 
namespace StreamXlsx\Dto;
 
final class ExportResult
{
    /** @var string */
    public $filePath;
    /** @var string */
    public $filename;
    /** @var string */
    public $mimeType;
 
    public function __construct(string $filePath, string $filename, string $mimeType)
    {
        $this->filePath = $filePath;
        $this->filename = $filename;
        $this->mimeType = $mimeType;
    }
}
