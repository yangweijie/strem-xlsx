<?php
declare(strict_types=1);
 
namespace StreamXlsx\Handler;
 
use StreamXlsx\Contract\OutputHandlerInterface;
use StreamXlsx\Dto\ExportResult;
use StreamXlsx\Support\LaravelEnvironment;
 
final class DownloadHandler implements OutputHandlerInterface
{
    /**
     * @param ExportResult $result
     * @param mixed $target
     * @return mixed
     */
    public function handle(ExportResult $result, $target = null)
    {
        if (LaravelEnvironment::isBooted()
            && class_exists(\Symfony\Component\HttpFoundation\BinaryFileResponse::class)) {
            return response()->download($result->filePath, $result->filename, [
                'Content-Type' => $result->mimeType,
            ])->deleteFileAfterSend(true);
        }
 
        header('Content-Type: ' . $result->mimeType);
        header('Content-Disposition: attachment; filename="' . $result->filename . '"');
        header('Content-Length: ' . filesize($result->filePath));
 
        readfile($result->filePath);
        unlink($result->filePath);
 
        return null;
    }
}
