<?php

declare (strict_types=1);
namespace FapiMember\Library\SmartEmailing\Types\Helpers;

use FapiMember\Library\SmartEmailing\Types\ToArrayInterface;
use FapiMember\Library\SmartEmailing\Types\ToStringInterface;
abstract class ArrayHelpers
{
    /**
     * @param array<\SmartEmailing\Types\ToArrayInterface> $arrayableCollection
     * @return array<mixed>
     */
    final public static function collectionItemsToArray(array $arrayableCollection): array
    {
        $toArrayCallback = static fn(ToArrayInterface $toArray): array => $toArray->toArray();
        return \array_map($toArrayCallback, $arrayableCollection);
    }
    /**
     * @param array<\SmartEmailing\Types\ToStringInterface> $stringableCollection
     * @return array<string>
     */
    final public static function stringExtractableCollectionToArray(array $stringableCollection): array
    {
        $toArrayCallback = static fn(ToStringInterface $toString): string => (string) $toString;
        return \array_map($toArrayCallback, $stringableCollection);
    }
}
