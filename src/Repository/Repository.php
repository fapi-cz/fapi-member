<?php declare(strict_types=1);

namespace FapiMember\Repository;

use WP_Term;

abstract class Repository
{
	protected string|null $key = null;

	protected function getTermsBy(array $params): array
	{
		$query = array(
			'taxonomy' => $this->key,
			'hide_empty' => false,
			'orderby' => 'ID',
		);

		$query = array_merge($query, $params);

		return get_terms($query);
	}

	protected function getTermById(int $id): WP_Term|null
	{
		$term = get_term_by('ID', $id, $this->key);

		if ($term === false) {
			$term = null;
		}

		return $term;
	}

	protected function getTermMeta(int $levelId, string $key, bool $single = true): mixed
	{
		return get_term_meta($levelId, $key, $single);
	}

	protected function updateTermMeta(int $levelId, string $key, mixed $value): void
	{
		update_term_meta($levelId, $key, $value);
	}

	protected function deleteTermMeta(int $levelId, string $key): void
	{
		delete_term_meta($levelId, $key);
	}

	protected function getUserMeta(int $userId, bool $single = true): array
	{
		$meta = get_user_meta($userId, $this->key, $single);

		if ($meta === '' || $meta === false) {
			$meta = [];
		}

		return $meta;
	}

	protected function updateUserMeta(int $userId, $meta): void
	{
		update_user_meta($userId, $this->key, $meta);
	}

}
