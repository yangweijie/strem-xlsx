<?php
declare(strict_types=1);
 
namespace StreamXlsx\Factory;
 
use StreamXlsx\Contract\OutputHandlerInterface;
use StreamXlsx\Enum\OutputMode;
use StreamXlsx\Handler\DownloadHandler;
use StreamXlsx\Handler\RawHandler;
use StreamXlsx\Handler\StoreHandler;
use StreamXlsx\Handler\StreamHandler;
 
final class OutputHandlerFactory
{
    public function make(string $mode): OutputHandlerInterface
    {
        switch ($mode) {
            case OutputMode::DOWNLOAD:
                return new DownloadHandler();
            case OutputMode::STORE:
                return new StoreHandler();
            case OutputMode::STREAM:
                return new StreamHandler();
            case OutputMode::RAW:
                return new RawHandler();
            default:
                throw new \InvalidArgumentException(sprintf('Unknown output mode "%s".', $mode));
        }
    }
}
