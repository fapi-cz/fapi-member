<?php declare(strict_types=1);

namespace FapiMember\Utils;

use DateInterval;
use DatePeriod;
use DateTime;
use DateTimeImmutable;
use FapiMember\Model\Enums\Format;

class DateTimeHelper
{

	public static function getNow(): DateTimeImmutable
	{
		$nowLocal = new DateTimeImmutable('now', wp_timezone());
		return new DateTimeImmutable($nowLocal->format(Format::DATE_TIME));
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

	public static function getDaysDifference(DateTimeImmutable $from, DateTimeImmutable $to): int
	{
		$interval = $from->diff($to);

		return (int) $interval->format('%r%a');
	}

	public static function calculateGraphPeriods(
		DateTimeImmutable $dateFrom,
		DateTimeImmutable $dateTo,
		$maxDataPoints = 61,
	): array
	{
		if ($dateTo > DateTimeHelper::getNow()) {
			$dateTo = DateTimeHelper::getNow();
		}

		$dateFrom = $dateFrom->setTime(0, 0, 0);
		$dateTo = $dateTo->setTime(23, 59, 59);

		$periods = [];
		$totalDays = $dateTo->diff($dateFrom)->days;

		if ($totalDays <= $maxDataPoints) {
			$interval = new DateInterval('P1D');
		} elseif ($totalDays <= $maxDataPoints * 3) {
			$interval = new DateInterval('P3D');
		} elseif ($totalDays <= $maxDataPoints * 7) {
			$interval = new DateInterval('P1W');
		} elseif ($totalDays <= $maxDataPoints * 30) {
			$interval = new DateInterval('P1M');
		} elseif ($totalDays <= $maxDataPoints * 365) {
			$interval = new DateInterval('P1Y');
		} else {
			$yearsPerPeriod = ceil($totalDays / 365 / $maxDataPoints);
			$interval = new DateInterval("P{$yearsPerPeriod}Y");
		}

		$period = new DatePeriod($dateFrom, $interval, $dateTo);

		foreach ($period as $startDate) {
			$endDate = $startDate->add($interval)->sub(new DateInterval('P1D'));

			if ($endDate > $dateTo) {
				$endDate = $dateTo;
			}

			$periods[] = [
				'date_from' => $startDate->setTime(0, 0, 0),
				'date_to' => $endDate->setTime(23, 59, 59),
			];
		}

		return $periods;
	}

}
