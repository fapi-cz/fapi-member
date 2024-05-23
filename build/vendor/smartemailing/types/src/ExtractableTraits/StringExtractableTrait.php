<?php

declare (strict_types=1);
namespace FapiMember\Library\SmartEmailing\Types\ExtractableTraits;

use FapiMember\Library\SmartEmailing\Types\StringType;
trait StringExtractableTrait
{
    use ExtractableTrait;
    abstract public function __construct(string $value);
    /**
     * @param string|mixed|array<mixed> $data
     * @return static
     */
    final public static function from($data)
    {
        if ($data instanceof self) {
            return $data;
        }
        $data = StringType::from($data);
        return new static($data);
    }
}
