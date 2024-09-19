<?php



namespace FapiMember\Repository;

use DateTimeImmutable;
use FapiMember\Model\Enums\Format;
use FapiMember\Model\MembershipChange;
use FapiMember\Utils\DateTimeHelper;
use wpdb;

class MemberActivityRepository extends Repository
{
	private wpdb $wpdb;
	private string $tableName;

	public function __construct()
	{
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		global $wpdb;

		$this->wpdb = $wpdb;
		$this->tableName = $this->wpdb->prefix . 'fm_member_activity';
	}

	public function tableExists(): bool
	{
		return $this->wpdb->get_var("SHOW TABLES LIKE '$this->tableName'") == $this->tableName;
	}

	public function createTableIfNeeded(): void
	{
    	$charset_collate = $this->wpdb->get_charset_collate();

		$sql = "
			CREATE TABLE IF NOT EXISTS $this->tableName (
				id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				user_id BIGINT(20) UNSIGNED NOT NULL,
				timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY (id),
				INDEX idx_user_id (user_id),
				INDEX idx_timestamp (timestamp)
			);
		) $charset_collate;";

    	dbDelta($sql);
	}

	public function addActivity(int $userId): void
	{
		$this->wpdb->insert(
			$this->tableName,
			[
				'user_id' => $userId,
				'timestamp' => DateTimeHelper::getNow()->format(Format::DATE_TIME_BASIC),
			],
		);
	}

	/** @return array<MembershipChange>*/
	public function findLastActivity(int $userId): array
	{
		$query = $this->wpdb->prepare("
			SELECT * FROM $this->tableName
			WHERE user_id = %d
			ORDER BY timestamp DESC, id DESC
			LIMIT 1;
		", $userId);

		$results = $this->wpdb->get_results($query, ARRAY_A);

		return $results;
	}

	public function getOneForPeriod(int $userId, DateTimeImmutable $dateFrom, DateTimeImmutable $dateTo)
	{
		$query = $this->wpdb->prepare("
			SELECT * FROM $this->tableName
			WHERE user_id = %d
			AND timestamp >= %s
			AND timestamp <= %s
			GROUP BY user_id
			;
		",
			[
				$userId,
				$dateFrom->format(Format::DATE_TIME_BASIC),
				$dateTo->format(Format::DATE_TIME_BASIC),
			],
		);

		$results = $this->wpdb->get_results($query, ARRAY_A);

		return $results;
	}

	public function getAllForPeriod(DateTimeImmutable $dateFrom, DateTimeImmutable $dateTo)
	{
		$query = $this->wpdb->prepare("
			SELECT * FROM $this->tableName
			WHERE timestamp >= %s
			AND timestamp <= %s
			GROUP BY user_id
			;
		",
			[
				$dateFrom->format(Format::DATE_TIME_BASIC),
				$dateTo->format(Format::DATE_TIME_BASIC),
			],
		);

		$results = $this->wpdb->get_results($query, ARRAY_A);

		return $results;
	}

	/** @return array<MembershipChange>*/
	private function toEntities(array|null $results, bool $allowMultiple = false): array
	{
		if ($results === null) {
			return [];
		}

		$entities = [];

		foreach ($results as $result) {
			$change = new MembershipChange($result);

			if ($allowMultiple) {
				$entities[] = $change;
				continue;
			}
			$entities[$change->getLevelId() . '-' . $change->getUserId()] = $change;
		}

		return $entities;
	}

	public function getAll()
	{
		$query = $this->wpdb->prepare("
			SELECT * FROM $this->tableName;
		");

		$result = $this->wpdb->get_results($query, ARRAY_A);

		return $result;
	}

	public function dropTable()
	{
		$sql = "DROP TABLE IF EXISTS $this->tableName";

		$this->wpdb->query($sql);
	}

}
