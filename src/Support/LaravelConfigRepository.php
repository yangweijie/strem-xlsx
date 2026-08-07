<?php
declare(strict_types=1);
 
namespace StreamXlsx\Support;
 
use StreamXlsx\Contract\ConfigRepositoryInterface;
 
final class LaravelConfigRepository implements ConfigRepositoryInterface
{
    /** @var string */
    private $prefix;
 
    public function __construct(string $prefix = 'stream-xlsx')
    {
        $this->prefix = $prefix;
    }
 
    /**
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        return config("{$this->prefix}.{$key}", $default);
    }
}
