<?php
declare(strict_types=1);
 
namespace StreamXlsx\Contract;
 
interface ConfigRepositoryInterface
{
    /**
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get(string $key, $default = null);
}
