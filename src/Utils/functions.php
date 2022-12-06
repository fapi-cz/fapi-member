<?php

namespace FapiMember\Utils;

/**
 * @param int|array<int> $sectionOrLevel Section or level ID or list of IDs
 * @return bool Return TRUE if user is in provided section
 */
function isInSection( $sectionOrLevel ) {
	if ( ! is_array( $sectionOrLevel ) ) {
		$sectionOrLevel = array( $sectionOrLevel );
	}

	return DisplayHelper::shouldContentBeRendered( '1', $sectionOrLevel );
}

/**
 * @param int|array<int> $sectionOrLevel Section or level ID or list of IDs
 * @return bool Return TRUE if user is NOT in provided section
 */
function isNotInSection( $sectionOrLevel ) {
	if ( ! is_array( $sectionOrLevel ) ) {
		$sectionOrLevel = array( $sectionOrLevel );
	}

	return DisplayHelper::shouldContentBeRendered( '0', $sectionOrLevel );
}

/**
 * @param string              $hasSectionOrLevel Allowed values '1' | '0' . '1' - return TRUE if user is in provided section or level. '0' - return TRUE if user is NOT in provided section or level. '' or any value - return TRUE.
 * @param string|array<mixed> $fapiSectionAndLevels List of section or level user is being in or must not be in.
 * @return bool
 */
function shouldContentBeRendered( $hasSectionOrLevel, $fapiSectionAndLevels ) {
	return DisplayHelper::shouldContentBeRendered( $hasSectionOrLevel, $fapiSectionAndLevels );
}
