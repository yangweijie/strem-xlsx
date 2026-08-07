<?php
declare(strict_types=1);
 
namespace StreamXlsx\Contract;
 
use StreamXlsx\Dto\ExportResult;
 
interface OutputHandlerInterface
{
    /**
     * @param ExportResult $result
     * @param mixed $target
     * @return mixed
     */
    public function handle(ExportResult $result, $target = null);
}
