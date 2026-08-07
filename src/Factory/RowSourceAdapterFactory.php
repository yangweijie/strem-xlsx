<?php
declare(strict_types=1);
 
namespace StreamXlsx\Factory;
 
use StreamXlsx\Exception\InvalidRowSourceException;
 
/**
 * Normalizes array, Generator, Iterator, Laravel Collection, LazyCollection
 * and Cursor sources into a single plain iterable.
 */
final class RowSourceAdapterFactory
{
    /**
     * @param mixed $source
     * @return iterable
     */
    public function toIterable($source): iterable
    {
        if (is_array($source)) {
            return $source;
        }
        if ($source instanceof \Traversable) {
            return $source;
        }
        throw new InvalidRowSourceException(
            sprintf('Unsupported row source type "%s".', get_debug_type($source))
        );
    }
}
