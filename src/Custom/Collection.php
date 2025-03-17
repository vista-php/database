<?php

namespace Vista\Custom;

/**
 * Collection class
 *
 * This interface provides a flexible and convenient way to work with arrays of items.
 * It includes various methods for manipulating and interacting with the collection,
 * such as adding, removing, and transforming items.
 *
 * Key Features:
 * - Initialize the collection with an array of items.
 * - Convert the collection to an array.
 * - Add and remove items from the collection.
 * - Retrieve items by key, or get the first or last item.
 * - Count the number of items in the collection.
 * - Check if the collection contains a specific value.
 * - Merge the collection with another array of items.
 * - Pluck values from the collection based on a key.
 * - Sort the collection using a custom comparison function.
 * - Iterate over the collection and apply a function to each item.
 * - Map the collection to a new collection by applying a function to each item.
 * - Filter the collection based on a custom function.
 * - Find an item in the collection using a custom search function or value.
 * - Check if an item exists in the collection.
 * - Reduce the collection to a single value using a custom function.
 * - Clone the collection to create a new instance with the same items.
 *
 * Example Usage:
 * <code>
 * $collection = new Collection([1, 2, 3]);
 * $collection->push(4)->sort(fn($a, $b) => $a <=> $b);
 * echo $collection; // Outputs: 1, 2, 3, 4
 * </code>
 *
 * @class Collection<T>
 */
class Collection implements CollectionContract
{
    private array $items;

    public function __construct(array $items = [])
    {
        $this->init($items);
    }

    public function toArray(): array
    {
        return $this->items;
    }

    public function init(array $items = []): self
    {
        $this->items = [];
        foreach ($items as $item) {
            $this->push($item);
        }

        return $this;
    }

    public function push(mixed $item): self
    {
        $this->items[] = $item;

        return $this;
    }

    public function pop(): mixed
    {
        return array_pop($this->items);
    }

    public function get(?int $key = null): mixed
    {
        return $key === null ? $this : $this->items[$key] ?? null;
    }

    public function first(): mixed
    {
        return reset($this->items) ?: null;
    }

    public function last(): mixed
    {
        return end($this->items) ?: null;
    }

    public function count(): int
    {
        return count($this->items);
    }

    public function contains(mixed $value): bool
    {
        return in_array($value, $this->items);
    }

    public function merge(array $items): self
    {
        $merged = array_merge($this->items, $items);

        return new self($merged);
    }

    public function pluck(mixed $key): self
    {
        $plucked = array_map(
            function($item) use ($key) {
                if (!is_array($item) && $item === $key) {
                    return $item;
                }
                if (is_array($item) && isset($item[$key])) {
                    return $item[$key];
                }
                if (is_object($item) && isset($item->$key)) {
                    return $item->$key;
                }

                return null;
            },
            $this->items
        );

        return new self($plucked);
    }

    public function sort(callable $func): self
    {
        $items = $this->items;
        usort($items, $func);

        return new self($items);
    }

    public function __toString(): string
    {
        $str = '';
        $count = count($this->items);
        for ($i = 0; $i < $count; $i++) {
            if (is_array($this->items[$i])) {
                $str .= '[' . new self($this->items[$i]) . ']';
            } elseif (is_object($this->items[$i])) {
                $str .= '[' . new self((array) $this->items[$i]) . ']';
            } else {
                $str .= $this->items[$i];
            }

            if ($count > 1 && $i < $count - 1) {
                $str .= ', ';
            }
        }

        return $str;
    }

    public function each(callable $func): self
    {
        foreach ($this->items as &$item) {
            $item = $func($item);
        }

        return $this;
    }

    public function map(callable $func): self
    {
        $collection = new self($this->items);

        foreach ($collection->items as &$item) {
            $item = $func($item);
        }

        return $collection;
    }

    public function filter(callable $func): self
    {
        $filtered = array_filter($this->items, $func);

        return new self($filtered);
    }

    public function find(mixed $search): mixed
    {
        foreach ($this->items as $item) {
            if (is_callable($search) ? $search($item) : $search === $item) {
                return $item;
            }
        }

        return null;
    }

    public function exists(mixed $item): bool
    {
        return $this->find($item) !== null;
    }

    public function reduce(callable $func, $initial): mixed
    {
        return array_reduce($this->items, $func, $initial);
    }

    public function clone(): self
    {
        return clone $this;
    }

    public function current(): mixed
    {
        return current($this->items);
    }

    public function next(): void
    {
        next($this->items);
    }

    public function key(): int|string|null
    {
        return key($this->items);
    }

    public function valid(): bool
    {
        return key($this->items) !== null;
    }

    public function rewind(): void
    {
        reset($this->items);
    }
}
