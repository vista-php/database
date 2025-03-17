<?php

namespace Tests\Unit\Custom;

use PHPUnit\Framework\TestCase;
use Traversable;
use Vista\Custom\Collection;

class CollectionTest extends TestCase
{
    public function testToArrayReturnsTheEntireArray(): void
    {
        $collection = new Collection([1, 2, 3]);
        $this->assertIsArray(actual: $collection->toArray());
    }

    public function testToArrayReturnsEmptyArrayForEmptyCollection(): void
    {
        $collection = new Collection();
        $this->assertEmpty(actual: $collection->toArray());
    }

    public function testInitSetsInitialArrayWithIndexedArray(): void
    {
        $collection = new Collection();
        $collection->init([1, 2, 3]);
        $this->assertEquals(
            expected: [1, 2, 3],
            actual: $collection->toArray()
        );
    }

    public function testInitSetsInitialArrayWithAssociativeArray(): void
    {
        $collection = new Collection();
        $collection->init(['a' => 1, 'b' => 2, 'c' => 3]);
        $this->assertEquals(
            expected: [1, 2, 3],
            actual: $collection->toArray()
        );

        $this->assertEquals(
            expected: [3, 4, 5],
            actual: $collection->init([3, 4, 5])->toArray()
        );
    }

    public function testPushAddsItemToTheEndOfCollection(): void
    {
        $collection = new Collection();

        $collection->push(item: 1);
        $this->assertEquals(
            expected: [1],
            actual: $collection->toArray()
        );

        $collection->push(item: 2);
        $this->assertEquals(
            expected: [1, 2],
            actual: $collection->toArray()
        );

        $this->assertEquals(
            expected: [1, 2, 3],
            actual: $collection->push(3)->toArray()
        );
    }

    public function testPopReturnsFirstItemAndRemovesItFromCollection(): void
    {
        $collection = new Collection([1, 2, 3]);

        $this->assertEquals(
            expected: 3,
            actual: $collection->pop()
        );
        $this->assertEquals(
            expected: 2,
            actual: $collection->pop()
        );
        $this->assertEquals(
            expected: 1,
            actual: $collection->pop()
        );
        $this->assertTrue(
            condition: $collection->pop() === null
        );
    }

    public function testGetReturnsProperItemWithProvidedIndex(): void
    {
        $collection = new Collection([1, 2, 3]);

        $this->assertEquals(
            expected: 1,
            actual: $collection->get(0)
        );
        $this->assertEquals(
            expected: 2,
            actual: $collection->get(1)
        );
        $this->assertEquals(
            expected: 3,
            actual: $collection->get(2)
        );
        $this->assertNull(actual: $collection->get(3));
    }

    public function testReturnsCurrentInstanceOfCollection()
    {
        $collection = new Collection([1, 2, 3]);

        $this->assertInstanceOf(
            expected: Collection::class,
            actual: $collection
        );
        $this->assertEquals(
            expected: [1, 2, 3],
            actual: $collection->toArray()
        );
    }

    public function testFirstReturnNullWhenEmpty(): void
    {
        $collection = new Collection();
        $this->assertNull(actual: $collection->first());
    }

    public function testFirstReturnsFirstElementWhenOneElementInCollection(): void
    {
        $collection = new Collection([1]);
        $this->assertEquals(
            expected: 1,
            actual: $collection->first()
        );
    }

    public function testFirstReturnsFirstElement(): void
    {
        $collection = new Collection([1, 2, 3]);
        $this->assertEquals(
            expected: 1,
            actual: $collection->first()
        );
    }

    public function testFirstDoesntAlterOriginalCollection(): void
    {
        $collection = new Collection([1, 2, 3]);

        $collection->first();

        $this->assertEquals(
            expected: [1, 2, 3],
            actual: $collection->toArray()
        );
    }

    public function testLastReturnNullWhenEmpty(): void
    {
        $collection = new Collection();
        $this->assertNull(actual: $collection->last());
    }

    public function testLastReturnsLastElementWhenOneElementInCollection(): void
    {
        $collection = new Collection([1]);
        $this->assertEquals(
            expected: 1,
            actual: $collection->last()
        );
    }

    public function testLastReturnsLastElement(): void
    {
        $collection = new Collection([1, 2, 3]);
        $this->assertEquals(
            expected: 3,
            actual: $collection->last()
        );
    }

    public function testLastDoesntAlterOriginalCollection(): void
    {
        $collection = new Collection([1, 2, 3]);

        $collection->last();

        $this->assertEquals(
            expected: [1, 2, 3],
            actual: $collection->toArray()
        );
    }

    public function testCount(): void
    {
        $collection = new Collection();
        $this->assertEquals(
            expected: 0,
            actual: $collection->count()
        );

        $collection = new Collection([1, 2, 3]);
        $this->assertEquals(
            expected: 3,
            actual: $collection->count()
        );
    }

    public function testContains(): void
    {
        $collection = new Collection();
        $this->assertFalse(condition: $collection->contains(1));
        $this->assertFalse(condition: $collection->contains(null));

        $collection = new Collection([1]);
        $this->assertTrue(condition: $collection->contains(value: 1));
        $this->assertFalse(condition: $collection->contains(value: 2));
        $this->assertFalse(condition: $collection->contains(value: null));

        $collection = new Collection([1, 2, 3]);
        $this->assertTrue(condition: $collection->contains(value: 1));
        $this->assertTrue(condition: $collection->contains(value: 2));
        $this->assertTrue(condition: $collection->contains(value: 3));
        $this->assertFalse(condition: $collection->contains(value: 4));
        $this->assertFalse(condition: $collection->contains(value: null));
    }

    public function testMergeWithEmptyArray(): void
    {
        $collection = new Collection();

        $merged = $collection->merge([]);
        $this->assertInstanceOf(
            expected: Collection::class,
            actual: $merged
        );
        $this->assertEquals(
            expected: [],
            actual: $merged->toArray()
        );

        $collection = new Collection([1, 2, 3]);
        $merged = $collection->merge([]);

        $this->assertInstanceOf(
            expected: Collection::class,
            actual: $merged
        );
        $this->assertEquals(
            expected: [1, 2, 3],
            actual: $merged->toArray()
        );
    }

    public function testMergeWithNonEmptyArray(): void
    {
        $collection = new Collection();
        $merged = $collection->merge([4, 5]);

        $this->assertInstanceOf(
            expected: Collection::class,
            actual: $merged
        );
        $this->assertEquals(
            expected: [4, 5],
            actual: $merged->toArray()
        );

        $collection = new Collection([1, 2, 3]);
        $merged = $collection->merge([4, 5]);

        $this->assertInstanceOf(
            expected: Collection::class,
            actual: $merged
        );
        $this->assertEquals(
            expected: [1, 2, 3, 4, 5],
            actual: $merged->toArray()
        );
    }

    public function testMergeWithOverlappingArray(): void
    {
        $collection = new Collection([1, 2, 3]);
        $merged = $collection->merge([2, 3, 4]);

        $this->assertInstanceOf(
            expected: Collection::class,
            actual: $merged
        );
        $this->assertEquals(
            expected: [1, 2, 3, 2, 3, 4],
            actual: $merged->toArray()
        );
    }

    public function testMergeDoesNotAffectOriginalCollection(): void
    {
        $collection = new Collection([1, 2, 3]);
        $this->assertEquals(
            expected: [1, 2, 3, 4, 5, 6],
            actual: $collection->merge([4, 5, 6])->toArray()
        );
        $this->assertEquals(
            expected: [1, 2, 3],
            actual: $collection->toArray()
        );
    }

    public function testPluckWithArrayItems(): void
    {
        $collection = new Collection([
            ['name' => 'Alice', 'age' => 30],
            ['name' => 'Bob', 'age' => 25],
        ]);

        $pluckedCollection = $collection->pluck(key: 'name');

        $this->assertInstanceOf(
            expected: Collection::class,
            actual: $pluckedCollection
        );
        $this->assertEquals(
            expected: ['Alice', 'Bob'],
            actual: $pluckedCollection->toArray()
        );
    }

    public function testPluckWithObjectItems(): void
    {
        $collection = new Collection([
            (object) ['name' => 'Alice', 'age' => 30],
            (object) ['name' => 'Bob', 'age' => 25],
        ]);

        $pluckedCollection = $collection->pluck(key: 'name');

        $this->assertInstanceOf(
            expected: Collection::class,
            actual: $pluckedCollection
        );
        $this->assertEquals(
            expected: ['Alice', 'Bob'],
            actual: $pluckedCollection->toArray()
        );
    }

    public function testPluckWithMixedItems(): void
    {
        $collection = new Collection([
            ['name' => 'Alice', 'age' => 30],
            (object) ['name' => 'Bob', 'age' => 25],
        ]);

        $pluckedCollection = $collection->pluck(key: 'name');

        $this->assertInstanceOf(
            expected: Collection::class,
            actual: $pluckedCollection
        );
        $this->assertEquals(
            expected: ['Alice', 'Bob'],
            actual: $pluckedCollection->toArray()
        );
    }

    public function testPluckWithNonExistentKey(): void
    {
        $collection = new Collection([
            ['name' => 'Alice', 'age' => 30],
            (object) ['name' => 'Bob', 'age' => 25],
        ]);

        $pluckedCollection = $collection->pluck('undefined');

        $this->assertInstanceOf(
            expected: Collection::class,
            actual: $pluckedCollection
        );
        $this->assertEquals(
            expected: [null, null],
            actual: $pluckedCollection->toArray()
        );
    }

    public function testPluckDoesNotAlterOriginalCollection(): void
    {
        $collection = new Collection([
            ['name' => 'Alice', 'age' => 30],
            (object) ['name' => 'Bob', 'age' => 25],
        ]);

        $collection->pluck('name');

        $this->assertEquals(
            expected: [
                ['name' => 'Alice', 'age' => 30],
                (object) ['name' => 'Bob', 'age' => 25],
            ],
            actual: $collection->toArray()
        );
    }

    public function testSortAscending(): void
    {
        $collection = new Collection([3, 1, 2]);
        $sortedCollection = $collection->sort(
            fn ($a, $b): int => $a <=> $b
        );

        $this->assertInstanceOf(
            expected: Collection::class,
            actual: $sortedCollection
        );
        $this->assertEquals(
            expected: [1, 2, 3],
            actual: $sortedCollection->toArray()
        );
    }

    public function testSortDescending(): void
    {
        $collection = new Collection([3, 1, 2]);
        $sortedCollection = $collection->sort(
            fn ($a, $b): int => $b <=> $a
        );

        $this->assertInstanceOf(
            expected: Collection::class,
            actual: $sortedCollection
        );
        $this->assertEquals(
            expected: [3, 2, 1],
            actual: $sortedCollection->toArray()
        );
    }

    public function testSortWithCustomFunction(): void
    {
        $collection = new Collection(['apple', 'pineapple', 'cherry']);
        $sortedCollection = $collection->sort(fn ($a, $b): int => strlen($a) <=> strlen($b));

        $this->assertInstanceOf(
            expected: Collection::class,
            actual: $sortedCollection
        );
        $this->assertEquals(
            expected: ['apple', 'cherry', 'pineapple'],
            actual: $sortedCollection->toArray()
        );
    }

    public function testSortDoesNotAlterOriginalCollection(): void
    {
        $collection = new Collection(['apple', 'pineapple', 'cherry']);

        $collection->sort(fn ($a, $b): int => strlen($a) <=> strlen($b));

        $this->assertEquals(
            expected: ['apple', 'pineapple', 'cherry'],
            actual: $collection->toArray()
        );
    }

    public function testToString()
    {
        $collection = new Collection();
        $this->assertEquals(
            expected: '',
            actual: $collection
        );

        $collection = new Collection([1, 2, 3]);
        $this->assertEquals(
            expected: '1, 2, 3',
            actual: $collection
        );

        $collection = new Collection([
            1,
            [1, 2, 3],
            [2, 3, 4],
            ['a' => 1, 'b' => 2],
            (object) ['a' => 3, 'b' => 4],
        ]);
        $this->assertEquals(
            expected: '1, [1, 2, 3], [2, 3, 4], [1, 2], [3, 4]',
            actual: $collection
        );
    }

    public function testEachAppliesFunctionToAllItems(): void
    {
        $collection = new Collection([1, 2, 3]);
        $collection->each(fn ($item): int => $item * 2);

        $this->assertEquals(
            expected: [2, 4, 6],
            actual: $collection->toArray()
        );
    }

    public function testEachReturnsSelf(): void
    {
        $collection = new Collection([1, 2, 3]);
        $map = $collection->each(fn ($item): int => $item);

        $this->assertInstanceOf(
            expected: Collection::class,
            actual: $map
        );
        $this->assertSame(
            expected: [1, 2, 3],
            actual: $collection->toArray()
        );
    }

    public function testEachWithEmptyCollection(): void
    {
        $collection = new Collection();
        $collection->each(fn ($item): int => $item * 2);

        $this->assertEquals(
            expected: [],
            actual: $collection->toArray()
        );
    }

    public function testMapAppliesFunctionToAllItems(): void
    {
        $collection = new Collection([1, 2, 3]);
        $map = $collection->map(fn ($item): int => $item * 2);

        $this->assertEquals(
            expected: [2, 4, 6],
            actual: $map->toArray()
        );
    }

    public function testMapReturnsNewCollection(): void
    {
        $collection = new Collection([1, 2, 3]);
        $map = $collection->map(fn ($item): int => 2 * $item);

        $this->assertInstanceOf(
            expected: Collection::class,
            actual: $map
        );
        $this->assertNotEquals(
            expected: $map,
            actual: $collection->toArray()
        );
    }

    public function testMapWithEmptyCollection(): void
    {
        $collection = new Collection();
        $map = $collection->map(fn ($item): int => $item * 2);

        $this->assertEquals(
            expected: [],
            actual: $map->toArray()
        );
    }

    public function testFilterReturnsNewCollectionWithItemsSatisfyingCondition(): void
    {
        $collection = new Collection([1, 2, 3]);
        $filtered = $collection->filter(fn ($item): bool => $item < 3);

        $this->assertEquals(
            expected: [1, 2],
            actual: $filtered->toArray()
        );
    }

    public function testFilterReturnsEmptyCollectionIfNoItemSatisfiesCondition(): void
    {
        $collection = new Collection([1, 2, 3]);
        $filtered = $collection->filter(fn ($item): bool => $item > 3);

        $this->assertEquals(
            expected: [],
            actual: $filtered->toArray()
        );
    }

    public function testFilterReturnsEmptyCollectionIfEmptyCollection(): void
    {
        $collection = new Collection();
        $filtered = $collection->filter(fn ($item): bool => true);

        $this->assertEquals(
            expected: [],
            actual: $filtered->toArray()
        );
    }

    public function testFilterDoesNotAffectOriginalCollection(): void
    {
        $collection = new Collection([1, 2, 3]);
        $collection->filter(fn ($item): bool => $item < 3);

        $this->assertEquals(
            expected: [1, 2, 3],
            actual: $collection->toArray()
        );
    }

    public function testFindReturnsFirstItemSatisfyingCondition(): void
    {
        $collection = new Collection([1, 2, 3]);
        $found = $collection->find(fn ($item): bool => $item > 1);

        $this->assertEquals(
            expected: 2,
            actual: $found
        );
    }

    public function testFindReturnsFirstItemEqualTo(): void
    {
        $collection = new Collection([1, 2, 3]);
        $found = $collection->find(search: 2);

        $this->assertEquals(
            expected: 2,
            actual: $found
        );
    }

    public function testFindReturnsNullIfNoItemSatisfiesCondition(): void
    {
        $collection = new Collection([1, 2, 3]);
        $found = $collection->find(fn ($item): bool => $item > 4);

        $this->assertEquals(
            expected: null,
            actual: $found
        );
    }

    public function testFindReturnsNullIfCollectionIsEmpty(): void
    {
        $collection = new Collection();
        $found = $collection->find(fn ($item): bool => true);

        $this->assertEquals(
            expected: null,
            actual: $found
        );
    }

    public function testFindDoesNotAffectOriginalCollection(): void
    {
        $collection = new Collection([1, 2, 3]);
        $collection->find(fn ($item): bool => $item < 3);

        $this->assertEquals(
            expected: [1, 2, 3],
            actual: $collection->toArray()
        );
    }

    public function existsReturnsTrueIfItemExists(): void
    {
        $collection = new Collection([1, 2, 3]);

        $this->assertTrue(condition: $collection->exists(item: 2));
    }

    public function existsReturnsFalseIfItemDoesNotExist(): void
    {
        $collection = new Collection([1, 2, 3]);

        $this->assertFalse(condition: $collection->exists(item: 5));
    }

    public function testReduceSum(): void
    {
        $collection = new Collection([1, 2, 3, 4, 5]);
        $result = $collection->reduce(fn ($carry, $item) => $carry + $item, 0);

        $this->assertEquals(
            expected: 15,
            actual: $result
        );
    }

    public function testReduceProduct(): void
    {
        $collection = new Collection([1, 2, 3, 4, 5]);
        $result = $collection->reduce(fn ($carry, $item) => $carry * $item, 1);

        $this->assertEquals(
            expected: 120,
            actual: $result
        );
    }

    public function testReduceConcatenateStrings(): void
    {
        $collection = new Collection(['a', 'b', 'c']);
        $result = $collection->reduce(fn ($carry, $item) => $carry . $item, '');

        $this->assertEquals(
            expected: 'abc',
            actual: $result
        );
    }

    public function testReduceWithEmptyCollection(): void
    {
        $collection = new Collection();
        $result = $collection->reduce(fn ($carry, $item) => $carry + $item, 0);

        $this->assertEquals(
            expected: 0,
            actual: $result
        );
    }

    public function testCloneCreatesNewInstance(): void
    {
        $collection = new Collection([1, 2, 3]);
        $clonedCollection = $collection->clone();

        $this->assertInstanceOf(
            expected: Collection::class,
            actual: $clonedCollection
        );
        $this->assertNotSame(
            expected: $collection,
            actual: $clonedCollection
        );
    }

    public function testCloneHasSameItems(): void
    {
        $collection = new Collection([1, 2, 3]);
        $clonedCollection = $collection->clone();

        $this->assertEquals($collection->toArray(), $clonedCollection->toArray());
    }

    public function testCloneModificationsDoNotAffectOriginal(): void
    {
        $collection = new Collection([1, 2, 3]);
        $clonedCollection = $collection->clone();

        $clonedItems = $clonedCollection->toArray();
        $clonedItems[] = 4;

        $this->assertNotEquals(
            expected: $collection->toArray(),
            actual: $clonedItems
        );
    }

    public function testCurrentReturnsFirstElement(): void
    {
        $collection = new Collection([1, 2, 3]);
        $this->assertEquals(
            expected: 1,
            actual: $collection->current()
        );
    }

    public function testNextAdvancesPointer(): void
    {
        $collection = new Collection([1, 2, 3]);
        $collection->next();
        $this->assertEquals(
            expected: 2,
            actual: $collection->current()
        );
    }

    public function testKeyReturnsCurrentKey(): void
    {
        $collection = new Collection([1, 2, 3]);
        $this->assertEquals(
            expected: 0,
            actual: $collection->key()
        );

        $collection->next();
        $this->assertEquals(
            expected: 1,
            actual: $collection->key()
        );
    }

    public function testValidReturnsTrueIfCurrentElementExists(): void
    {
        $collection = new Collection([1, 2, 3]);
        $this->assertTrue(condition: $collection->valid());
    }

    public function testValidReturnsFalseIfCurrentElementDoesNotExist(): void
    {
        $collection = new Collection([1, 2, 3]);
        $collection->next();
        $collection->next();
        $collection->next();
        $this->assertFalse(condition: $collection->valid());
    }

    public function testRewindResetsPointer(): void
    {
        $collection = new Collection([1, 2, 3]);
        $collection->next();
        $collection->next();
        $collection->rewind();
        $this->assertEquals(
            expected: 1,
            actual: $collection->current()
        );
    }

    public function testImplementsIterator(): void
    {
        $collection = new Collection([1, 2, 3]);
        $this->assertInstanceOf(
            expected: Traversable::class,
            actual: $collection
        );
    }
}
