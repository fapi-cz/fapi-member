<?php



namespace FapiMember\Repository;

use DateTimeImmutable;
use FapiMember\Model\Enums\Format;
use FapiMember\Model\MembershipChange;
use wpdb;

class MembershipChangeRepository extends Repository
{
	private wpdb $wpdb;
	private string $tableName;

	public function __construct()
	{
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		global $wpdb;

		$this->wpdb = $wpdb;
		$this->tableName = $this->wpdb->prefix . 'fm_membership_changes';
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
				level_id BIGINT(20) UNSIGNED NOT NULL,
				timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
				registered TIMESTAMP NULL,
				until TIMESTAMP NULL,
				type VARCHAR(100) NOT NULL,
				PRIMARY KEY (id),
				INDEX idx_user_id (user_id),
				INDEX idx_level_id (level_id),
				INDEX idx_timestamp (timestamp)
			);
		) $charset_collate;";

    	dbDelta($sql);
	}

	public function addChange(MembershipChange $membershipChange): void
	{
		$this->wpdb->insert(
			$this->tableName,
			$membershipChange->toArray(),
		);
	}

	/** @return array<MembershipChange>*/
	public function getForUser(int $userId): array
	{
		$query = $this->wpdb->prepare("
			SELECT * FROM $this->tableName
			WHERE user_id = %d
			ORDER BY timestamp DESC, id DESC
		", $userId);

		$results = $this->wpdb->get_results($query, ARRAY_A);

		return $this->toEntities($results, true);
	}

	/** @return array<MembershipChange> */
	public function getForLevels(array $levelIds): array
	{
		$ids = implode(',', array_map('intval', array_column($levelIds, 'id')));

		$query = $this->wpdb->prepare("
			SELECT * FROM $this->tableName
			WHERE level_id IN ($ids)
			ORDER BY timestamp DESC, id DESC
		");

		$results = $this->wpdb->get_results($query, ARRAY_A);

		return $this->toEntities($results, true);
	}

	public function findLastChange(int $userId, int $levelId, DateTimeImmutable $date): MembershipChange|null
	{
		$query = $this->wpdb->prepare("
			SELECT * FROM $this->tableName
			WHERE user_id = %d
			AND level_id = %d
			AND timestamp <= %s
			ORDER BY timestamp DESC, id DESC
			LIMIT 1
		", $userId, $levelId, $date->format(Format::DATE_TIME));

		$result = $this->wpdb->get_row($query, ARRAY_A);

		if ($result === null) {
			return null;
		}

		return new MembershipChange($result);
	}

	/** @return array<MembershipChange> */
	public function getLastChangesForLevels(array $levelIds, DateTimeImmutable $timestamp): array
	{
		$levelIdsPlaceholder = implode(',', array_fill(0, count($levelIds), '%d'));

		$filterByLevelsString = $levelIds === []
			? ''
			:'AND level_id IN (' . $levelIdsPlaceholder . ')';

		$queryParams = array_merge([$timestamp->format(Format::DATE_TIME_BASIC)], $levelIds);

		$query = $this->wpdb->prepare("
			SELECT t1.*
			FROM $this->tableName t1
			JOIN (
				SELECT user_id, level_id, MAX(timestamp) as max_timestamp
				FROM $this->tableName
				WHERE timestamp <= %s
				" . $filterByLevelsString . "
				GROUP BY user_id, level_id
			) t2
			ON t1.user_id = t2.user_id
			AND t1.level_id = t2.level_id
			AND t1.timestamp = t2.max_timestamp
			ORDER BY t1.user_id, t1.level_id, t1.id DESC;
		",
		$queryParams
		);

		$results = $this->wpdb->get_results($query, ARRAY_A);

		return $this->toEntities($results);
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
