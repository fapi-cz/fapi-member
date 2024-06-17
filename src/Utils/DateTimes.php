<?php

declare(strict_types = 1);

namespace FapiMember\Utils;

use FapiMember\Library\SmartEmailing\Types\DateTimeFormatter;
use FapiMember\Library\SmartEmailing\Types\ExtractableTypeInterface;
use FapiMember\Library\SmartEmailing\Types\Helpers\ExtractableHelpers;
use FapiMember\Library\SmartEmailing\Types\InvalidTypeException;
use FapiMember\Model\Enums\Format;

abstract class DateTimes implements ExtractableTypeInterface
{

	final public static function from(
		mixed $value
	): \DateTime {
		if ($value instanceof \DateTime) {
			return $value;
		}

		if ($value instanceof \DateTimeImmutable) {
			$value = DateTimeFormatter::format($value, Format::DATE_TIME);
		}

		if
			(\is_string($value) &&
			(\preg_match('#^\d\d\d\d-\d\d-\d\d \d\d:\d\d:\d\d(\.\d+)?\z#', $value, $matches) ||
			\preg_match('#^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\z#', $value, $matches))
		) {
			if (\count($matches) > 1) {
				$value = \substr($value, 0, \strlen($value) - \strlen($matches[1]));
			}

			$date = \DateTime::createFromFormat(Format::DATE_TIME, $value);

			if ($date instanceof \DateTime && DateTimeFormatter::format($date, Format::DATE_TIME) === $value) {
				return $date;
			}
		}

		throw new InvalidTypeException(
			'Value ' . $value . ' must be string in ' . Format::DATE_TIME . ' format'
		);
	}

    /**
     * @param array<mixed>|\ArrayAccess<mixed, mixed> $data
     * @throws \SmartEmailing\Types\InvalidTypeException
     */
    final public static function extract(
        $data,
        string $key
    ): \DateTime {
        $value = ExtractableHelpers::extractValue($data, $key);

        try {
            return self::from($value);
        } catch (InvalidTypeException $exception) {
            throw $exception->wrap($key);
        }
    }

    /**
     * @param array<mixed>|\ArrayAccess<mixed, mixed> $data
     */
    final public static function extractOrNull(
        $data,
        string $key,
        bool $nullIfInvalid = false
    ): ?\DateTime {
        $value = ExtractableHelpers::extractValueOrNull($data, $key);

        if ($value === null) {
            return null;
        }

        try {
            return self::fromOrNull($value, $nullIfInvalid);
        } catch (InvalidTypeException $exception) {
            throw $exception->wrap($key);
        }
    }

    /**
     * @param array<mixed> $data
     * @throws \SmartEmailing\Types\InvalidTypeException
     * @deprecated Use Dates::extract
     */
    final public static function extractDate(
        array &$data,
        string $key
    ): \DateTime {
        return Dates::extract($data, $key);
    }

    /**
     * @param array<mixed> $data
     * @deprecated Use Dates::extractDateOrNull
     */
    final public static function extractDateOrNull(
        array &$data,
        string $key
    ): ?\DateTime {
        return Dates::extractOrNull($data, $key);
    }

	public static function fromOrNull(
		mixed $value,
		bool $nullIfInvalid = false
	): ?\DateTime {
		if ($value === null) {
			return null;
		}

		try {
			return self::from($value);
		} catch (InvalidTypeException $e) {
			if ($nullIfInvalid) {
				return null;
			}

			throw $e;
		}
	}

}
