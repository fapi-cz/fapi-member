<?php declare(strict_types=1);

namespace FapiMember\Utils;

use DateTime;
use DateTimeImmutable;
use FapiMember\Model\Enums\Format;

class DateTimeHelper
{

	public static function getNow(): DateTimeImmutable
	{
		return new DateTimeImmutable('now', wp_timezone());
	}

	public static function getNowTimestamp(): int
	{
		$now = self::getNow();

		return strtotime($now->format(Format::DATE_TIME));
	}

	public static function createOrNull(string|null $dateTimeString, string $format): DateTimeImmutable|null
	{
		if ($dateTimeString === null) {
			return null;
		}

		$dateTime = DateTimeImmutable::createFromFormat(
			$format,
			$dateTimeString,
			wp_timezone(),
		);

		return $dateTime !== false ? $dateTime : null;
	}

	public static function getNextFullHour(): DateTime
	{
		$dateTime = new DateTime();
		$dateTime->setTimestamp(time());
		$dateTime->modify('+1 hour');
		$dateTime->setTime((int)$dateTime->format('H'), 0);

		return $dateTime;
	}

}
