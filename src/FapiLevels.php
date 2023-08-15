<?php

namespace FapiMember;

use FapiMember\Utils\PostTypeHelper;
use stdClass;
use WP_Error;
use WP_Term;

final class FapiLevels {


	const TAXONOMY                = 'fapi_levels';
	const OPTION_KEY_LEVELS_ORDER = 'fapi_levels_order';

	const EMAIL_TYPE_AFTER_REGISTRATION         = 'afterRegistration';
	const EMAIL_TYPE_AFTER_MEMBERSHIP_PROLONGED = 'afterMembershipProlonged';
	const EMAIL_TYPE_AFTER_ADDING               = 'afterAdding';

	public static $emailTypes
		= array(
			self::EMAIL_TYPE_AFTER_REGISTRATION,
			self::EMAIL_TYPE_AFTER_MEMBERSHIP_PROLONGED,
			self::EMAIL_TYPE_AFTER_ADDING,
		);

	public static $pageTypes
		= array(
			'afterLogin',
			'noAccess',
			'login',
		);

	private $levels = null;

	private $levelEnvelopes = null;

	private $levelsToPages = null;

	public function registerTaxonomy() {
		register_taxonomy(
			self::TAXONOMY,
			PostTypeHelper::getSupportedPostTypes(),
			array(
				'public'       => false,
				'hierarchical' => true,
				'show_ui'      => false,
				'show_in_rest' => false,
			)
		);
	}

	/**
	 * @return int[]
	 */
	public function allIds() {
		$termEnvelopes = $this->loadAsTermEnvelopes();
		$out           = array();

		foreach ( $termEnvelopes as $term ) {
			$out[] = (int) $term->getTerm()->term_id;
		}

		return $out;
	}

	/**
	 * @param int[] $ids
	 * @return WP_Term[]
	 */
	public function loadByIds( $ids ) {
		$levels = array();

		foreach ( $ids as $id ) {
			$levels[] = $this->loadById( $id );
		}

		return $levels;
	}

	/**
	 * @param int $id
	 * @return WP_Term|null
	 */
	public function loadById( $id ) {
		if ( $this->levels === null ) {
			return get_term_by( 'ID', $id, self::TAXONOMY );
		}

		foreach ( $this->levels as $level ) {
			if ( $level->term_id !== (int) $id ) {
				continue;
			}

			return $level;
		}

		return null;
	}

	public function pageIdsForLevel( $levelTerm ) {
		$lvlToPages = $this->levelsToPages();
		$levelId    = $levelTerm->term_id;

		return ( isset( $lvlToPages[ $levelId ] ) ) ? $lvlToPages[ $levelId ] : array();
	}

	/**
	 * @return array
	 */
	public function levelsToPages() {
		if ( $this->levelsToPages === null ) {
			$this->levelsToPages = array();

			foreach ( $this->loadAsTermEnvelopes() as $term ) {
				$levelId = $term->getTerm()->term_id;
				$pages   = get_term_meta( $levelId, 'fapi_pages', true );

				$this->levelsToPages[ $levelId ] = ( empty( $pages ) ) ? array() : array_values( json_decode( $pages, true ) );
			}
		}

		return $this->levelsToPages;
	}

	/**
	 * @param int  $levelId
	 * @param bool $useCascade
	 * @return array
	 */
	public function loadEmailTemplatesForLevel( $levelId, $useCascade = false ) {
		$meta = array();

		foreach ( self::$emailTypes as $type ) {
			$template = get_term_meta( $levelId, $this->constructEmailTemplateKey( $type ), true );

			if ( ! empty( $template ) ) {
				$meta[ $type ] = $template;
			}
		}

		if ( $useCascade && count( $meta ) !== count( self::$emailTypes ) ) {
			$level        = $this->loadById( $levelId );
			$parent       = $level->parent === 0 ? null : $this->loadById( $level->parent );
			$parentEmails = $parent === null ? array() : $this->loadEmailTemplatesForLevel( $parent->term_id, false );

			foreach ( self::$emailTypes as $type ) {
				if ( ! isset( $meta[ $type ] ) && isset( $parentEmails[ $type ] ) ) {
					$meta[ $type ] = $parentEmails[ $type ];
				}
			}
		}

		return $meta;
	}

	/**
	 * @param string $type
	 * @return string
	 */
	public function constructEmailTemplateKey( $type ) {
		return sprintf( 'fapi_email_%s', $type );
	}

	/**
	 * @param int  $levelId
	 * @param bool $useCascade
	 * @return array
	 */
	public function loadOtherPagesForLevel( $levelId, $useCascade = false ) {
		$meta       = array();
		$parentMeta = array();

		if ( $useCascade ) {
			$term = $this->loadById( $levelId );

			if ( $term->parent !== 0 ) {
				$parentMeta = $this->loadOtherPagesForLevel( $term->parent );
			}
		}

		foreach ( self::$pageTypes as $type ) {
			$pageId = get_term_meta( $levelId, $this->constructOtherPageKey( $type ), true );

			if ( ! empty( $pageId ) ) {
				$meta[ $type ] = (int) $pageId;
			} elseif ( $useCascade ) {
				$meta[ $type ] = ( isset( $parentMeta[ $type ] ) ) ? $parentMeta[ $type ] : null;
			}
		}

		return $meta;
	}

	public function constructOtherPageKey( $type ) {
		return sprintf( 'fapi_page_%s', $type );
	}

	/**
	 * @param string   $name
	 * @param int|null $parent
	 * @return array<mixed>|WP_Error
	 */
	public function insert( $name, $parent = null ) {
		if ( $parent === null ) {
			$section = wp_insert_term( $name, self::TAXONOMY );

			$this->createDefaultSectionEmails( $section['term_id'], 'section' );

			return $section;
		}

		$level = wp_insert_term( $name, self::TAXONOMY, array( 'parent' => $parent ) );

		$this->createDefaultSectionEmails( $level['term_id'], 'level' );

		return $level;
	}

	/**
	 * @param int $id
	 */
	public function remove( $id ) {
		wp_delete_term( $id, self::TAXONOMY );
	}

	/**
	 * @param int    $id
	 * @param string $name
	 */
	public function update( $id, $name ) {
		wp_update_term( $id, self::TAXONOMY, array( 'name' => $name ) );
	}

	/**
	 * @param int    $id
	 * @param string $direction
	 * @return bool
	 */
	public function order( $id, $direction ) {
		$envelopes = $this->loadAsTermEnvelopes();
		$modified  = array_filter(
			$envelopes,
			static function ( $envelope ) use ( $id ) {
				return ( $envelope->getTerm()->term_id === $id );
			}
		);

		if ( count( $modified ) !== 1 ) {
			return false;
		}

		$modified            = array_shift( $modified );
		$parentId            = $modified->getTerm()->parent;
		$sameParentEnvelopes = array_filter(
			$envelopes,
			static function ( $envelope ) use ( $parentId ) {
				return ( $envelope->getTerm()->parent === $parentId );
			}
		);

		$currentPosition = -1;
		$lastPosition    = null;

		foreach ( $sameParentEnvelopes as $envelope ) {
			$currentPosition++;

			if ( $envelope->getTerm()->term_id === $modified->getTerm()->term_id ) {
				$lastPosition = $currentPosition;
			}
		}

		if ( $direction === 'up' ) {
			$newPosition = max( 0, ( $lastPosition - 1 ) );
		} else {
			$newPosition = min( ( count( $sameParentEnvelopes ) - 1 ), ( $lastPosition + 1 ) );
		}

		$newOrder = array();
		$siblings = array_filter(
			$sameParentEnvelopes,
			static function ( $envelope ) use ( $modified ) {
				return $envelope->getTerm()->term_id !== $modified->getTerm()->term_id;
			}
		);

		$curr = 0;

		foreach ( $siblings as $siblingEnvelope ) {
			if ( $newPosition === $curr ) {
				$newOrder[] = (string) $modified->getTerm()->term_id;
			}
			$newOrder[] = (string) $siblingEnvelope->getTerm()->term_id;
			$curr++;
		}

		if ( $newPosition === $curr ) {
			$newOrder[] = (string) $modified->getTerm()->term_id;
		}

		$orderingPatch = new stdClass();

		foreach ( $newOrder as $order => $orderId ) {
			$orderingPatch->{$orderId} = $order;
		}

		$oldOrdering = get_option( self::OPTION_KEY_LEVELS_ORDER, ( new stdClass() ) );
		$ordering    = $this->mergeOrderings( $oldOrdering, $orderingPatch );

		update_option( self::OPTION_KEY_LEVELS_ORDER, $ordering );

		return true;
	}

	/**
	 * @return FapiTermEnvelope[]
	 */
	public function loadAsTermEnvelopes() {
		if ( $this->levelEnvelopes === null ) {
			$terms = get_terms(
				array(
					'taxonomy'   => self::TAXONOMY,
					'hide_empty' => false,
					'orderby'    => 'ID',
				)
			);

			$this->levelEnvelopes = $this->termsToEnvelopes( $terms );
		}

		return $this->levelEnvelopes;
	}

	/**
	 * @param WP_Term[] $terms
	 * @return FapiTermEnvelope[]
	 */
	protected function termsToEnvelopes( $terms ) {
		$ordering = get_option( self::OPTION_KEY_LEVELS_ORDER, ( new stdClass() ) );

		$envelopes = array_map(
			static function ( $term ) use ( $ordering ) {
				$o = ( isset( $ordering->{$term->term_id} ) ) ? $ordering->{$term->term_id} : 1245;

				return new FapiTermEnvelope( $term, $o );
			},
			$terms
		);

		usort(
			$envelopes,
			static function ( $a, $b ) {
				if ( $a->getOrder() === $b->getOrder() ) {
					return (int) $a->getTerm()->term_id - (int) $b->getTerm()->term_id;
				}

				return $a->getOrder() - $b->getOrder();
			}
		);

		return $envelopes;
	}

	/**
	 * @param stdClass $old
	 * @param stdClass $patch
	 * @return stdClass
	 */
	protected function mergeOrderings( $old, $patch ) {
		$new = clone $old;

		foreach ( get_object_vars( $patch ) as $key => $val ) {
			$new->{$key} = $val;
		}

		return $new;
	}

	/**
	 * @param int    $termId
	 * @param string $emailKind
	 * @return void
	 */
	private function createDefaultSectionEmails( $termId, $emailKind ) {
		update_term_meta(
			$termId,
			$this->constructEmailTemplateKey( self::EMAIL_TYPE_AFTER_REGISTRATION ),
			EmailTemplatesProvider::FAPI_EMAILS[ $emailKind ][ self::EMAIL_TYPE_AFTER_REGISTRATION ]
		);

		update_term_meta(
			$termId,
			$this->constructEmailTemplateKey( self::EMAIL_TYPE_AFTER_MEMBERSHIP_PROLONGED ),
			EmailTemplatesProvider::FAPI_EMAILS[ $emailKind ][ self::EMAIL_TYPE_AFTER_MEMBERSHIP_PROLONGED ]
		);

		update_term_meta(
			$termId,
			$this->constructEmailTemplateKey( self::EMAIL_TYPE_AFTER_ADDING ),
			EmailTemplatesProvider::FAPI_EMAILS[ $emailKind ][ self::EMAIL_TYPE_AFTER_ADDING ]
		);
	}

	/**
	 * @param int  $levelId
	 * @param bool $loadPrevious If true, returns the previous sibling's ID. Otherwise returns the next sibling's ID.
	 * @return int|null
	 */
	public function getSiblingOfLevel( $levelId, $loadPrevious = false ) {

		$loadPrevious = $loadPrevious === true ? 1 : -1;
		$parentId     = get_term( $levelId )->parent;

		if ( empty( $parentId ) ) {

			return null;

		}

		$envelopes = $this->loadAsTermEnvelopes();

		$filteredLevels = array_filter(
			$envelopes,
			function ( $obj ) use ( $parentId ) {
				return $obj->getTerm()->parent === $parentId;
			}
		);

		usort(
			$filteredLevels,
			function ( $a, $b ) {
				return $a->getOrder() - $b->getOrder();
			}
		);

		foreach ( $filteredLevels as $arrIndex => $level ) {
			if ( $level->getTerm()->term_id === $levelId ) {

				if ( $arrIndex + $loadPrevious < 0 || $arrIndex + $loadPrevious > ( count( $filteredLevels ) - 1 ) ) {
					return null;
				} else {
					return $filteredLevels[ $arrIndex + $loadPrevious ]->getTerm()->term_id;
				}
			}
		}

	}

	/**
	 * @param int $postId
	 * @return array<int>
	 */
	public function getLevelsForPostId( $postId ) {
		$levelsAndPages = $this->levelsToPages();
		$levelsForPost  = array();

		foreach ( $levelsAndPages as $levelId => $postIds ) {
			if ( in_array( $postId, $postIds ) ) {
				$levelsForPost[] = $levelId;
			}
		}

		return $levelsForPost;

	}

}
