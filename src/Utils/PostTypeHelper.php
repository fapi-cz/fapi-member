<?php

namespace FapiMember\Utils;

class PostTypeHelper
{
	public static array $excludedPostTypePrefixes = ['jet-', 'elementor_', 'elemental_'];

	/**
	 * @return array<string>
	 */
	public static function getSupportedPostTypes(bool $cptOnly = false): array
	{
		$supportedPostTypes = [];
		$excludedPostTypes = $cptOnly ? ['attachment', 'page', 'post'] : ['attachment'];
		$supportedPostTypesObjects = get_post_types(['public' => true], 'objects');

		foreach ($supportedPostTypesObjects as $obj) {
			$name = $obj->name;

			if (in_array($name, $excludedPostTypes, true)) {
				continue;
			}

			foreach (self::$excludedPostTypePrefixes as $prefix) {
				if (str_starts_with($name, $prefix)) {
					continue 2;
				}
			}

			$supportedPostTypes[] = $name;
		}

		sort($supportedPostTypes);

		return $supportedPostTypes;
	}

}
