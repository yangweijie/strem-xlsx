<?php
declare(strict_types=1);
 
namespace StreamXlsx\Handler;
 
use StreamXlsx\Contract\OutputHandlerInterface;
use StreamXlsx\Dto\ExportResult;
use StreamXlsx\Support\LaravelEnvironment;
 
final class StreamHandler implements OutputHandlerInterface
{
    /**
     * @param ExportResult $result
     * @param mixed $target
     * @return mixed
     */
    public function handle(ExportResult $result, $target = null)
    {
        if (LaravelEnvironment::isBooted()) {
            $content = file_get_contents($result->filePath);
            unlink($result->filePath);
 
            return response($content, 200, [
                'Content-Type' => $result->mimeType,
                'Content-Disposition' => 'inline; filename="' . $result->filename . '"',
            ]);
        }
 
        header('Content-Type: ' . $result->mimeType);
        header('Content-Disposition: inline; filename="' . $result->filename . '"');
 
        readfile($result->filePath);
        unlink($result->filePath);
 
        return null;
    }
}
