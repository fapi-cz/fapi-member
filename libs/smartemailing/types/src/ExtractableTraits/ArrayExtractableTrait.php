<?php

declare (strict_types=1);
namespace FapiMember\Library\SmartEmailing\Types\ExtractableTraits;

use FapiMember\Library\SmartEmailing\Types\Arrays;
trait ArrayExtractableTrait
{
    use ExtractableTrait;
    /**
     * @param array<mixed> $data
     */
    abstract public function __construct(array $data);
    /**
     * @param string|mixed|array<mixed> $data
     * @return static
     */
    final public static function from($data)
    {
        if ($data instanceof self) {
            return $data;
        }
        $data = Arrays::from($data);
        return new static($data);
    }
}
