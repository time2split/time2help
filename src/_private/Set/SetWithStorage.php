<?php

declare(strict_types=1);

namespace Time2Split\Help\_private\Set;

use Time2Split\Help\Set;

/**
 * @internal
 */
abstract class SetWithStorage extends Set implements \IteratorAggregate
{
    /**
     * @var bool[]|(\Traversable<mixed,bool>&\Countable) $storage
     */
    protected array|(\Traversable&\Countable) $storage;

    protected function __construct(mixed $storage)
    {
        $this->storage = $storage;
    }

    public function offsetGet(mixed $offset): bool
    {
        return $this->storage[$offset] ?? false;
    }

    public function count(): int
    {
        return \count($this->storage);
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        if (!\is_bool($value))
            throw new \InvalidArgumentException('Must be a boolean');

        if ($value)
            $this->storage[$offset] = true;
        else
            unset($this->storage[$offset]);
    }

    public function getIterator(): \Traversable
    {
        return $this->storage;
    }
}
