<?php
declare(strict_types=1);
 
namespace StreamXlsx\Handler;
 
use StreamXlsx\Contract\OutputHandlerInterface;
use StreamXlsx\Dto\ExportResult;
use StreamXlsx\Exception\FileWriteException;
 
final class StoreHandler implements OutputHandlerInterface
{
    /**
     * @param ExportResult $result
     * @param mixed $target
     * @return mixed
     */
    public function handle(ExportResult $result, $target = null)
    {
        if (!is_string($target) || $target === '') {
            throw new FileWriteException('Store destination path is required.');
        }
 
        $directory = dirname($target);
        if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
            throw new FileWriteException(sprintf('Failed to create directory "%s".', $directory));
        }
 
        if (!rename($result->filePath, $target)) {
            throw new FileWriteException(sprintf('Failed to store file at "%s".', $target));
        }
 
        return $target;
    }
}
