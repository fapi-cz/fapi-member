<?php


class FapiLevels {
	const TAXONOMY = 'fapi_levels';
	const OPTION_KEY_LEVELS_ORDER = 'fapi_levels_order';

	const EMAIL_TYPE_AFTER_REGISTRATION = 'afterRegistration';
	const EMAIL_TYPE_AFTER_MEMBERSHIP_PROLONGED = 'afterMembershipProlonged';
	const EMAIL_TYPE_AFTER_ADDING = 'afterAdding';

	public static $emailTypes = [
		self::EMAIL_TYPE_AFTER_REGISTRATION,
		self::EMAIL_TYPE_AFTER_MEMBERSHIP_PROLONGED,
		self::EMAIL_TYPE_AFTER_ADDING,
	];

	public static $pageTypes = [
		'afterLogin',
		'noAccess',
		'login',
	];

	private $levels = null;
	private $levelEnvelopes = null;
	private $levelsToPages = null;

	public function registerTaxonomy() {
		register_taxonomy( self::TAXONOMY,
		                   'page',
		                   [
			                   'public'       => false,
			                   'hierarchical' => true,
			                   'show_ui'      => false,
			                   'show_in_rest' => false,
		                   ] );
	}

    /**
     * @deprecated
     * @see FapiLevels::loadAsTermEnvelopes()
     */
	public function loadAsTerms() {
		if ( $this->levels === null ) {
			$this->levels = get_terms(
				[
					'taxonomy'   => self::TAXONOMY,
					'hide_empty' => false,
					'orderby'    => 'ID'
				]
			);
		}

		return $this->levels;
	}

    public function loadAsTermEnvelopes() {
        if ( $this->levelEnvelopes === null ) {
            $terms = get_terms(
                [
                    'taxonomy'   => self::TAXONOMY,
                    'hide_empty' => false,
                    'orderby'    => 'ID'
                ]
            );
            $this->levelEnvelopes = $this->termsToEnvelopes($terms);
        }

        return $this->levelEnvelopes;
    }

    /**
     * @param WP_Term[] $terms
     * @return FapiTermEnvelope[]
     */
    protected function termsToEnvelopes($terms)
    {
        $ordering = get_option( self::OPTION_KEY_LEVELS_ORDER, [] );
        $envelopes = array_map(function($term) use ($ordering) {
            $o = (isset($ordering[$term->term_id])) ? $ordering[$term->term_id] : 1245;
            return new FapiTermEnvelope($term, $o);
        }, $terms);
        usort($envelopes, function($a, $b) {
            return $a->getOrder() - $b->getOrder();
        });
        return $envelopes;
    }

	public function allIds() {
		$terms = $this->loadAsTerms();

		return array_reduce( $terms,
			function ( $carry, $one ) {
				/** @var WP_Term $one */
				$carry[] = $one->term_id;

				return $carry;
			},
			                 [] );
	}

	public function loadById( $id ) {
		if ( $this->levels === null ) {
			return get_term_by( 'ID', $id, self::TAXONOMY );
		}
		$f = array_filter( $this->levels,
			function ( WP_Term $one ) use ( $id ) {
				return $one->term_id === (int) $id;
			} );
		if ( count( $f ) >= 1 ) {
			return array_values( $f )[0];
		}

		return null;
	}

	public function levelsToPages() {
		if ( $this->levelsToPages === null ) {
			$levels = array_map( function ( $one ) {
				return [
					'term_id' => $one->term_id,
					'name'    => $one->name,
				];
			},
				$this->loadAsTerms() );

			$this->levelsToPages = array_reduce( $levels,
				function ( $carry, $lvl ) {
					$pages                    = get_term_meta( $lvl['term_id'], 'fapi_pages', true );
					$carry[ $lvl['term_id'] ] = ( empty( $pages ) ) ? [] : array_values( json_decode( $pages ) );

					return $carry;
				},
				                                 [] );
		}

		return $this->levelsToPages;
	}

	public function constructEmailTemplateKey( $type ) {
		return sprintf( 'fapi_email_%s', $type );
	}

	public function constructOtherPageKey( $type ) {
		return sprintf( 'fapi_page_%s', $type );
	}

	public function loadEmailTemplatesForLevel( $levelId, $useCascade = false ) {
		$meta = [];
		foreach ( self::$emailTypes as $type ) {
			$template = get_term_meta( $levelId, $this->constructEmailTemplateKey( $type ), true );
			if ( ! empty( $template ) ) {
				$meta[ $type ] = $template;
			}
		}
		if ( $useCascade && count( $meta ) !== count( self::$emailTypes ) ) {
			$level        = $this->loadById( $levelId );
			$parent       = ( $level->parent === 0 ) ? null : $this->loadById( $level->parent );
			$parentEmails = ( $parent === null ) ? [] : $this->loadEmailTemplatesForLevel( $parent->term_id, false );
			foreach ( self::$emailTypes as $type ) {
				if ( ! isset( $meta[ $type ] ) && isset( $parentEmails[ $type ] ) ) {
					$meta[ $type ] = $parentEmails[ $type ];
				}
			}
		}

		return $meta;
	}

	public function loadOtherPagesForLevel( $levelId, $useCascade = false ) {
		$meta       = [];
		$parentMeta = [];
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
			} else {
				if ( $useCascade ) {
					$meta[ $type ] = ( isset( $parentMeta[ $type ] ) ) ? $parentMeta[ $type ] : null;
				}
			}
		}

		return $meta;
	}

	public function insert( $name, $parent = null ) {
		if ( $parent === null ) {
			wp_insert_term( $name, self::TAXONOMY );
		} else {
			wp_insert_term( $name, self::TAXONOMY, [ 'parent' => $parent ] );
		}
	}

	public function remove( $id ) {
		wp_delete_term( $id, self::TAXONOMY );
	}

	public function update( $id, $name ) {
		wp_update_term( $id, self::TAXONOMY, [ 'name' => $name ] );
	}
}