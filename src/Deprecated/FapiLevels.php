<?php

namespace FapiMember\Deprecated;

use FapiMember\Utils\EmailHelper;
use FapiMember\Utils\PostTypeHelper;
use stdClass;
use WP_Error;
use WP_Term;

/** @deprecated  */
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

	/**
	 * @param int $id
	 * @return WP_Term|null
	 */
	public function loadParentById( $id ) {
		$termEnvelopes = $this->loadAsTermEnvelopes();
		$child = $this->loadById($id);

		foreach ( $termEnvelopes as $term ) {
			$term = $term->getTerm();
			if ( $term->term_id === $child->parent ) {
				return $term;
			}
		}

		return null;
	}

	/**
	 * @param int $id
	 * @return array<WP_Term>|null
	 */
	public function loadByParentId( $id ) {
		$termEnvelopes = $this->loadAsTermEnvelopes();
		$terms = [];

		foreach ( $termEnvelopes as $term ) {
			$term = $term->getTerm();

			if ( $term->parent !== (int) $id ) {
				continue;
			}

			$terms[] = $term;
		}

		return $terms;
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
	public function loadEmailTemplatesForLevel($levelId, $useCascade = false) {
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
		$meta = array();
		$parentMeta = array();

		if ( $useCascade ) {
			$term = $this->loadById( $levelId );

			if ( $term->parent !== 0 ) {
				$parentMeta = $this->loadOtherPagesForLevel( $term->parent );
			}
		}

		foreach (self::$pageTypes as $type) {
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
	 * @param int $id
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

			if (!is_array($terms)) {
				$this->levelEnvelopes = [];
			} else {
				$this->levelEnvelopes = $this->termsToEnvelopes( $terms );
			}
		}

		return $this->levelEnvelopes;
	}

	/**
	 * @param WP_Term[] $terms
	 * @return FapiTermEnvelope[]
	 */
	protected function termsToEnvelopes( $terms ) {
		$ordering = (array) get_option( self::OPTION_KEY_LEVELS_ORDER, ( new stdClass() ) );

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
			EmailHelper::FAPI_EMAILS[ $emailKind ][ self::EMAIL_TYPE_AFTER_REGISTRATION ]
		);

		update_term_meta(
			$termId,
			$this->constructEmailTemplateKey( self::EMAIL_TYPE_AFTER_MEMBERSHIP_PROLONGED ),
			EmailHelper::FAPI_EMAILS[ $emailKind ][ self::EMAIL_TYPE_AFTER_MEMBERSHIP_PROLONGED ]
		);

		update_term_meta(
			$termId,
			$this->constructEmailTemplateKey( self::EMAIL_TYPE_AFTER_ADDING ),
			EmailHelper::FAPI_EMAILS[ $emailKind ][ self::EMAIL_TYPE_AFTER_ADDING ]
		);
	}

}
