<?php
declare(strict_types=1);
 
namespace StreamXlsx\Handler;
 
use StreamXlsx\Contract\OutputHandlerInterface;
use StreamXlsx\Dto\ExportResult;
 
final class RawHandler implements OutputHandlerInterface
{
    /**
     * @param ExportResult $result
     * @param mixed $target
     * @return mixed
     */
    public function handle(ExportResult $result, $target = null)
    {
        $content = file_get_contents($result->filePath);
        unlink($result->filePath);
 
        return $content;
    }
}
