<?php
declare(strict_types=1);
 
namespace StreamXlsx\Support;
 
use StreamXlsx\Contract\ConfigRepositoryInterface;
 
final class ArrayConfigRepository implements ConfigRepositoryInterface
{
    /** @var array */
    private $items;
 
    public function __construct(array $items = [])
    {
        $this->items = $items;
    }
 
    /**
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        return $this->items[$key] ?? $default;
    }
}
