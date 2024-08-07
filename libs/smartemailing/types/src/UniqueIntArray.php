<?php

declare (strict_types=1);
namespace FapiMember\Library\SmartEmailing\Types;

use FapiMember\Library\SmartEmailing\Types\Comparable\ArrayComparableTrait;
use FapiMember\Library\SmartEmailing\Types\Comparable\ComparableInterface;
use FapiMember\Library\SmartEmailing\Types\ExtractableTraits\ArrayExtractableTrait;
/**
 * @implements \IteratorAggregate<int>
 */
final class UniqueIntArray implements \Countable, \IteratorAggregate, ToArrayInterface, ComparableInterface
{
    use ArrayExtractableTrait;
    use UniqueArrayFeatures;
    use ArrayComparableTrait;
    /**
     * @var array<bool>
     */
    private array $valuesPresenceMap;
    /**
     * @param array<mixed> $data
     */
    private function __construct(array $data = [])
    {
        $this->valuesPresenceMap = [];
        foreach ($data as $value) {
            try {
                $this->add(IntType::from($value));
            } catch (InvalidTypeException $e) {
                throw InvalidTypeException::typeError('all members of array to be int', $value);
            }
        }
    }
    /**
     * @param array<mixed> $data
     */
    public static function extractNotEmpty(array $data, string $key): self
    {
        $self = self::extract($data, $key);
        if ($self->isEmpty()) {
            throw InvalidTypeException::cannotBeEmptyError($key);
        }
        return $self;
    }
    /**
     * @param array<\SmartEmailing\Types\UniqueIntArray> $uniqueIntArrays
     */
    public static function intersect(array $uniqueIntArrays): UniqueIntArray
    {
        if (\count($uniqueIntArrays) === 1) {
            return \reset($uniqueIntArrays);
        }
        $plainIntArrays = [];
        foreach ($uniqueIntArrays as $uniqueIntArray) {
            $plainIntArrays[] = $uniqueIntArray->valuesPresenceMap;
        }
        $result = \array_intersect_key(...$plainIntArrays);
        $output = new UniqueIntArray([]);
        $output->valuesPresenceMap = $result;
        return $output;
    }
    /**
     * @param array<\SmartEmailing\Types\UniqueIntArray> $uniqueIntArrays
     */
    public static function union(array $uniqueIntArrays): UniqueIntArray
    {
        $result = [];
        foreach ($uniqueIntArrays as $uniqueIntArray) {
            foreach ($uniqueIntArray->valuesPresenceMap as $key => $true) {
                $result[$key] = $true;
            }
        }
        $output = new UniqueIntArray([]);
        $output->valuesPresenceMap = $result;
        return $output;
    }
    /**
     * @return \Traversable<int>
     */
    public function getIterator(): \Traversable
    {
        return new \RecursiveArrayIterator($this->getValues());
    }
    public function count(): int
    {
        return \count($this->valuesPresenceMap);
    }
    /**
     * @return array<int>
     */
    public function getValues(): array
    {
        return \array_keys($this->valuesPresenceMap);
    }
    /**
     * @return array<int>
     */
    public function toArray(): array
    {
        return $this->getValues();
    }
    public function add(int $id): bool
    {
        if (!isset($this->valuesPresenceMap[$id])) {
            $this->valuesPresenceMap[$id] = \true;
            return \true;
        }
        return \false;
    }
    public function remove(int $id): void
    {
        unset($this->valuesPresenceMap[$id]);
    }
    public function contains(int $id): bool
    {
        return isset($this->valuesPresenceMap[$id]);
    }
    public function isEmpty(): bool
    {
        return $this->valuesPresenceMap === [];
    }
    public function orderASC(): void
    {
        \ksort($this->valuesPresenceMap);
    }
}
