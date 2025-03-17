<?php

namespace Vista\Custom;

use Iterator;

/**
 * Interface CollectionContract<T>
 */
interface CollectionContract extends Iterator
{
    public function toArray(): array;

    public function init(array $items = []): self;

    public function push(mixed $item): self;

    public function pop(): mixed;

    public function get(?int $key = null): mixed;

    public function first(): mixed;

    public function last(): mixed;

    public function count(): int;

    public function contains(mixed $value): bool;

    public function merge(array $items): self;

    public function pluck(mixed $key): self;

    public function sort(callable $func): self;

    public function each(callable $func): self;

    public function map(callable $func): self;

    public function filter(callable $func): self;

    public function find(mixed $search): mixed;

    public function exists(mixed $item): bool;

    public function reduce(callable $func, $initial): mixed;

    public function clone(): self;

    public function current(): mixed;

    public function next(): void;

    public function key(): int|string|null;

    public function valid(): bool;

    public function rewind(): void;
}
