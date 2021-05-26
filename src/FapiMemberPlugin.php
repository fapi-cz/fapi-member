<?php


class FapiMemberPlugin {
	private $errorBasket = [];
	private $fapiLevels = null;
	private $fapiSanitization = null;
	private $fapiUserUtils = null;
	private $fapiMembershipLoader = null;
	private $fapiApi = null;

	const OPTION_KEY_SETTINGS = 'fapiSettings';
	const OPTION_KEY_API_USER = 'fapiMemberApiEmail';
	const OPTION_KEY_API_KEY = 'fapiMemberApiKey';
	const OPTION_KEY_API_CHECKED = 'fapiMemberApiChecked';
	const OPTION_KEY_IS_DEVELOPMENT = 'fapiIsDevelopment';
	const REQUIRED_CAPABILITY = 'manage_options';
	const DF = 'Y-m-d\TH:i:s';

	public function __construct() {
		$this->addHooks();
	}

	public static function isDevelopment() {
		$s = (int)get_option(self::OPTION_KEY_IS_DEVELOPMENT, 0);

		return ( $s === 1 );
	}

	public function levels() {
		if ( $this->fapiLevels === null ) {
			$this->fapiLevels = new FapiLevels();
		}

		return $this->fapiLevels;
	}

	public function sanitization() {
		if ( $this->fapiSanitization === null ) {
			$this->fapiSanitization = new FapiSanitization( $this->levels() );
		}

		return $this->fapiSanitization;
	}

	public function userUtils() {
		if ( $this->fapiUserUtils === null ) {
			$this->fapiUserUtils = new FapiUserUtils();
		}

		return $this->fapiUserUtils;
	}

	public function fapiMembershipLoader() {
		if ( $this->fapiMembershipLoader === null ) {
			$this->fapiMembershipLoader = new FapiMembershipLoader( $this->levels() );
		}

		return $this->fapiMembershipLoader;
	}

	public function fapiApi() {
		if ( $this->fapiApi === null ) {
			$apiUser       = get_option( self::OPTION_KEY_API_USER, null );
			$apiKey        = get_option( self::OPTION_KEY_API_KEY, null );
			$this->fapiApi = new FapiApi( $apiUser, $apiKey );
		}

		return $this->fapiApi;
	}

	public function addHooks() {
		add_action( 'admin_menu', [ $this, 'addAdminMenu' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'addScripts' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'addPublicScripts' ] );
		add_action( 'admin_init', [ $this, 'registerSettings' ] );

		add_action( 'init', [ $this, 'registerLevelsTaxonomy' ] );
        add_action( 'init', [ $this, 'registerRoles' ] );
		add_action( 'init', [ $this, 'addShortcodes' ] );
		add_action( 'rest_api_init', [ $this, 'addRestEndpoints' ] );
		// check if page in fapi level
		add_action( 'template_redirect', [ $this, 'checkPage' ] );
		// level selection in front-end
		add_action( 'init', [ $this, 'checkIfLevelSelection' ] );

		//user profile
		add_action( 'edit_user_profile', [ $this, 'addUserProfileForm' ] );

		// admin form handling
		add_action( 'admin_post_fapi_member_api_credentials_submit', [ $this, 'handleApiCredentialsSubmit' ] );
		add_action( 'admin_post_fapi_member_new_section', [ $this, 'handleNewSection' ] );
		add_action( 'admin_post_fapi_member_new_level', [ $this, 'handleNewLevel' ] );
		add_action( 'admin_post_fapi_member_remove_level', [ $this, 'handleRemoveLevel' ] );
		add_action( 'admin_post_fapi_member_edit_level', [ $this, 'handleEditLevel' ] );
		add_action( 'admin_post_fapi_member_order_level', [ $this, 'handleOrderLevel' ] );
		add_action( 'admin_post_fapi_member_add_pages', [ $this, 'handleAddPages' ] );
		add_action( 'admin_post_fapi_member_remove_pages', [ $this, 'handleRemovePages' ] );
		add_action( 'admin_post_fapi_member_edit_email', [ $this, 'handleEditEmail' ] );
		add_action( 'admin_post_fapi_member_set_other_page', [ $this, 'handleSetOtherPage' ] );
		add_action( 'admin_post_fapi_member_set_settings', [ $this, 'handleSetSettings' ] );
		// user profile save
		add_action( 'edit_user_profile_update', [ $this, 'handleUserProfileSave' ] );

		add_image_size( 'level-selection', 300, 164, true );
		add_filter( 'login_redirect', [ $this, 'loginRedirect' ], 10, 3 );
        add_filter( 'show_admin_bar' , [ $this, 'hideAdminBar' ]);
	}

	public function hideAdminBar($original) {
        $user = wp_get_current_user();
        if (in_array( 'member', (array) $user->roles )) {
            return false;
        }
        return $original;
    }

	public function showError( $type, $message ) {
		add_action( 'admin_notices',
			function ( $e ) {
				printf( '<div class="notice notice-%s is-dismissible"><p>%s</p></div>', $e[0], $e[1] );
			} );
	}

	public function registerRoles() {
        if (get_role('member') === null) {
            add_role( 'member', 'Člen', get_role( 'subscriber' )->capabilities );
        }
    }

	public function registerStyles() {
		wp_register_style(
			'fapi-member-admin',
			plugins_url( 'fapi-member/media/fapi-member.css' )
		);
		wp_register_style(
			'fapi-member-user-profile',
			plugins_url( 'fapi-member/media/fapi-user-profile.css' )
		);
		wp_register_style(
			'fapi-member-admin-font',
			plugins_url( 'fapi-member/media/font/stylesheet.css' )
		);
		wp_register_style(
			'fapi-member-swal-css',
			plugins_url( 'fapi-member/media/dist/sweetalert2.min.css' )
		);
		wp_register_style(
			'fapi-member-public-style',
			plugins_url( 'fapi-member/media/fapi-member-public.css' )
		);
	}

	public function registerPublicStyles() {
		wp_register_style(
			'fapi-member-public-style',
			plugins_url( 'fapi-member/media/fapi-member-public.css' )
		);
	}

	public function registerScripts() {
		wp_register_script(
			'fapi-member-swal',
			plugins_url( 'fapi-member/media/dist/sweetalert2.js' )
		);
		wp_register_script(
			'fapi-member-swal-promise-polyfill',
			plugins_url( 'fapi-member/media/dist/polyfill.min.js' )
		);
		if ( self::isDevelopment() ) {
			wp_register_script(
				'fapi-member-main',
				plugins_url( 'fapi-member/media/dist/fapi.dev.js' )
			);
		} else {
			wp_register_script(
				'fapi-member-main',
				plugins_url( 'fapi-member/media/dist/fapi.dist.js' )
			);
		}
	}

	public function registerLevelsTaxonomy() {
		$this->levels()->registerTaxonomy();
	}

	public function addShortcodes() {
		add_shortcode( 'fapi-member-login', [ $this, 'shortcodeLogin' ] );
		add_shortcode( 'fapi-member-user', [ $this, 'shortcodeUser' ] );
	}

	public function shortcodeLogin() {
		return FapiMemberTools::shortcodeLoginForm();
	}

	public function shortcodeUser() {
		return FapiMemberTools::shortcodeUser();
	}

	public function addRestEndpoints() {
		register_rest_route(
			'fapi/v1',
			'/sections',
			[
				'methods'  => 'GET',
				'callback' => [ $this, 'handleApiSections' ],
			]
		);
		register_rest_route(
			'fapi/v1',
			'/callback',
			[
				'methods'  => 'POST',
				'callback' => [ $this, 'handleApiCallback' ],
			]
		);
	}

	public function handleApiSections() {
		$t        = $this->levels()->loadAsTermEnvelopes();
		$t        = array_map( function ( $oneEnvelope ) {
		    $one = $oneEnvelope->getTerm();
			return [
				'id'     => $one->term_id,
				'parent' => $one->parent,
				'name'   => $one->name
			];
		},
			$t );
		$sections = array_reduce( $t,
			function ( $carry, $one ) use ( $t ) {
				if ( $one['parent'] === 0 ) {
					$children      = array_values(
						array_filter( $t,
							function ( $i ) use ( $one ) {
								return ( $i['parent'] === $one['id'] );
							} )
					);
					$children      = array_map( function ( $j ) {
						unset( $j['parent'] );

						return $j;
					},
						$children );
					$one['levels'] = $children;
					unset( $one['parent'] );
					$carry[] = $one;
				}

				return $carry;
			},
			                      [] );

		return new WP_REST_Response( $sections );
	}

	public function handleApiCallback( WP_REST_Request $request ) {
		$get  = $request->get_params();
		$body = $request->get_body();
		// extract invoice/order id and preform request to find: email, level id, days...
		// temp
		/*
		$get = [
			'level' => ['12'],
			'days' => '30',
		];
		$body = 'id=187034262&time=1614239639&security=9edbc14e1907b61af468217f60d2406d160c4fdf';
		*/
		$d = [];
		parse_str( $body, $d );

		if ( isset( $d['voucher'] ) ) {
			$voucherId = $d['voucher'];
			$voucher   = $this->fapiApi()->getVoucher( $voucherId );
			$voucherItemTemplateCode = $voucher['item_template_code'];
			$itemTemplate = $this->fapiApi()->getItemTemplate( $voucherItemTemplateCode );
			if ( $voucher === false ) {
				$this->callbackError( sprintf( 'Error getting voucher: %s', $this->fapiApi()->lastError ) );
				return false;
			}
			$itemTemplate = ($itemTemplate === false) ? [] : $itemTemplate;
            if ( !$this->fapiApi()->isVoucherSecurityValid($voucher, $itemTemplate, $d['time'], $d['security']) ) {
                $this->callbackError( sprintf( 'Invoice security is not valid.' ) );
                return false;
            }
			if ( ! isset( $voucher['status'] ) || $voucher['status'] !== 'applied' ) {
				$this->callbackError( sprintf( 'Voucher status is not applied.' ) );
				return false;
			}
			if ( ! isset( $voucher['applicant'] ) || ( $voucher['applicant'] === null ) || ! isset( $voucher['applicant']['email'] ) ) {
				$this->callbackError( sprintf( 'Cannot find applicant email in API response.' ) );
				return false;
			}
			$email = $voucher['applicant']['email'];
		} else {
			$invoiceId = $d['id'];
			$invoice   = $this->fapiApi()->getInvoice( $invoiceId );
			if ( $invoice === false ) {
				$this->callbackError( sprintf( 'Error getting invoice: %s', $this->fapiApi()->lastError ) );
				return false;
			}
			if ( !$this->fapiApi()->isInvoiceSecurityValid($invoice, $d['time'], $d['security']) ) {
                $this->callbackError( sprintf( 'Invoice security is not valid.' ) );
                return false;
            }
			if ( isset($invoice['parent']) && $invoice['parent'] !== null ) {
                $this->callbackError( sprintf( 'Invoice parent is set and not null.' ) );
				return false;
			}
			if ( ! isset( $invoice['customer'] ) || ! isset( $invoice['customer']['email'] ) ) {
				$this->callbackError( sprintf( 'Cannot find customer email in API response.' ) );
				return false;
			}
			$email = $invoice['customer']['email'];
		}

		if ( ! isset( $get['level'] ) ) {
			$this->callbackError( sprintf( 'Level parameter missing in get params.' ) );
			return false;
		}


		$props       = [];
		$userCreated = $this->userUtils()->createUser( $email, $props );

		// create or prolong membership
		if ( ! is_array( $get['level'] ) ) {
			$levelIds = [ (int) [ $get['level'] ] ];
		} else {
			$levelIds = array_map( 'intval', $get['level'] );
		}

		$existingLevels = $this->levels()->allIds();
		foreach ( $levelIds as $oneLevelid ) {
			if ( ! in_array( $oneLevelid, $existingLevels ) ) {
				$this->callbackError( sprintf( 'Section or level with ID %s, does not exist.', $oneLevelid ) );
				return false;
			}
		}

		if ( ! isset( $get['days'] ) ) {
			$days        = false;
			$isUnlimited = true;
		} else {
			$days        = (int) $get['days'];
			$isUnlimited = false;
		}

		// add parents where needed
		$added = [];
		for ( $i = 0; $i < count( $levelIds ); $i ++ ) {
			$l = $this->levels()->loadById( $levelIds[ $i ] );
			if ( $l->parent !== 0 && ! in_array( $l->parent, $levelIds ) ) {
				$added[] = $l->parent;
			}
		}
		$levelIds = array_values( array_merge( $levelIds, $added ) );
        $send1ASectionIds = [];
		foreach ( $levelIds as $id ) {
			$mainLevel = $this->levels()->loadById( $id );
			if ( ! $mainLevel ) {
				continue;
			}

			$this->createOrProlongMembership( $email, $id, $days, $isUnlimited, $props );
			$this->enhanceProps( $props );

			// send emails
			// 1. Pokud je uživatel úplně nový, pošli REG email
			if ( isset( $props['new_user'] ) && $props['new_user'] ) {
				$this->sendEmail( $email, FapiLevels::EMAIL_TYPE_AFTER_REGISTRATION, $id, $props );
				continue;
			}
			// 1a: Pokud uživatel získal úroveň v sekci, kterou ještě neměl, pošli “afterRegistration” email
            if (
                ( isset( $props['membership_level_added'] ) && $props['membership_level_added'] )
                &&
                ( isset( $props['membership_level_added_is_section'] ) && $props['membership_level_added_is_section'] === false )
                &&
                ( !isset( $props['did_user_had_this_parent_before'] ) || $props['did_user_had_this_parent_before'] === false )
            ) {
                $l = $this->levels()->loadById( $props['membership_level_added_level'] );
                $send1ASectionIds[] = $l->parent;
                $this->sendEmail( $email, FapiLevels::EMAIL_TYPE_AFTER_REGISTRATION, $id, $props );
                continue;
            }
			// 2. Pokud uživatel získal sekci, kterou ještě neměl  a v rámci tohoto callbacku
            //    z fapi se neposlal email dle 1a pro danou sekci pošli REG email
			if (
				( isset( $props['membership_level_added'] ) && $props['membership_level_added'] )
				&&
				( isset( $props['membership_level_added_is_section'] ) && $props['membership_level_added_is_section'] === true )
				&&
				( ! isset( $props['did_user_had_this_level_before'] ) || $props['did_user_had_this_level_before'] === false )
                &&
                !in_array($props['membership_level_added'], $send1ASectionIds)
			) {

				$this->sendEmail( $email, FapiLevels::EMAIL_TYPE_AFTER_REGISTRATION, $id, $props );
				continue;
			}
			// 3. Pokud uživatel získal úroveň v sekci co už měl, pošli Při přidání
			if (
			( isset( $props['membership_level_added'] ) && $props['membership_level_added'] )
			) {
				$l = $this->levels()->loadById( $props['membership_level_added_level'] );
				if ( $l->parent !== 0 ) {
					$sectionLevel = $this->levels()->loadById( $l->parent );
					if ( $this->fapiMembershipLoader()->didUserHadLevelMembershipBefore( $props['user_id'],
					                                                                     $sectionLevel->term_id ) ) {
						$this->sendEmail( $email, FapiLevels::EMAIL_TYPE_AFTER_ADDING, $id, $props );
						continue;
					}
				}
			}
			// 4. Pokud uživatel koupil sekce nebo úroveň kterou již měl, pokud nebyla neomezená
			if (
			( isset( $props['membership_prolonged'] ) && $props['membership_prolonged'] )
			) {
				$this->sendEmail( $email, FapiLevels::EMAIL_TYPE_AFTER_MEMBERSHIP_PROLONGED, $id, $props );
				continue;
			}
		}
		return '';
	}

	protected function callbackError( $message ) {
		http_response_code( 400 );
		echo json_encode( [ 'error' => $message ] );
		exit;
	}

	protected function enhanceProps( &$props ) {
		if ( isset( $props['membership_level_added_level'] ) ) {
			$props['membership_level_added_level_name'] = $this->levels()->loadById( $props['membership_level_added_level'] )->name;
		}
		if ( isset( $props['membership_child_level_added_level'] ) ) {
			$props['membership_child_level_added_level_name'] = $this->levels()->loadById( $props['membership_child_level_added_level'] )->name;
		}
		if ( isset( $props['membership_prolonged_level'] ) ) {
			$props['membership_prolonged_level_name'] = $this->levels()->loadById( $props['membership_prolonged_level'] )->name;
		}
		$props['login_link']     = sprintf( '<a href="%s">zde</a>', $this->getLoginUrl() );
		$props['login_link_url'] = $this->getLoginUrl();
	}

	protected function getLoginUrl() {
		$setLoginPageId = $this->getSetting( 'login_page_id' );
		if ( $setLoginPageId === null ) {
			return wp_login_url();
		} else {
			return get_permalink( $setLoginPageId );
		}
	}

	public function handleApiCredentialsSubmit() {
		$this->verifyNonceAndCapability( 'api_credentials_submit' );

		$apiEmail = $this->sanitization()->loadPostValue( self::OPTION_KEY_API_USER,
		                                                  [ $this->sanitization(), FapiSanitization::ANY_STRING ] );
		$apiKey   = $this->sanitization()->loadPostValue( self::OPTION_KEY_API_KEY,
		                                                  [ $this->sanitization(), FapiSanitization::ANY_STRING ] );

		if ( $apiKey === null || $apiEmail === null ) {
			$this->redirect( 'connection', 'apiFormEmpty' );
		}

		update_option( self::OPTION_KEY_API_USER, $apiEmail );
		update_option( self::OPTION_KEY_API_KEY, $apiKey );

		$credentialsOk = $this->fapiApi()->checkCredentials();
		if ( $credentialsOk ) {
			update_option( self::OPTION_KEY_API_CHECKED, true );
			$this->redirect( 'connection', 'apiFormSuccess' );
		} else {
			update_option( self::OPTION_KEY_API_CHECKED, false );
			$this->redirect( 'connection', 'apiFormError' );
		}
	}

	public function handleUserProfileSave( $userId ) {
		if ( empty( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'update-user_' . $userId ) ) {
			return;
		}
		if ( ! current_user_can( self::REQUIRED_CAPABILITY ) ) {
			return false;
		}

		$data = $this->sanitizeLevels( $_POST['Levels'] );

		$memberships = [];
		$levelEvelopes      = $this->levels()->loadAsTermEnvelopes();
		$levels      = array_reduce( $levelEvelopes,
			function ( $carry, $one ) {
				$carry[ $one->getTerm()->term_id ] = $one->getTerm();

				return $carry;
			},
			                         [] );

		foreach ( $data as $id => $inputs ) {
			if ( isset( $inputs['check'] ) && $inputs['check'] === 'on' ) {
				if (
					isset( $inputs['registrationDate'] )
					&&
					isset( $inputs['registrationTime'] )
					&&
					( isset( $inputs['membershipUntil'] ) || ( isset( $inputs['isUnlimited'] ) && $inputs['isUnlimited'] === 'on' ) )
				) {
					$reg = \DateTime::createFromFormat( 'Y-m-d\TH:i',
					                                    $inputs['registrationDate'] . 'T' . $inputs['registrationTime'] );
					if ( isset( $inputs['membershipUntil'] ) && $inputs['membershipUntil'] !== '' ) {
						$until = \DateTime::createFromFormat( 'Y-m-d\TH:i:s',
						                                      $inputs['membershipUntil'] . 'T23:59:59' );
					} else {
						$until = null;
					}
					if ( isset( $inputs['isUnlimited'] ) && $inputs['isUnlimited'] === 'on' ) {
						$isUnlimited = true;
					} else {
						$isUnlimited = false;
					}

					$memberships[] = new FapiMembership( $id, $reg, $until, $isUnlimited );
				}
			}
		}
		$this->fapiMembershipLoader()->saveForUser( $userId, $memberships );
	}

	protected function sanitizeLevels( $levels ) {
		if ( ! is_array( $levels ) ) {
			wp_die( 'Unknown input structure.' );
		}
		$levels = array_filter( $levels,
			function ( $one ) {
				return ( isset( $one['check'] ) && $one['check'] === 'on' );
			} );
		$levels = array_filter( $levels,
			function ( $one ) {
				return ( isset( $one['registrationDate'] ) && isset( $one['registrationTime'] ) && isset( $one['membershipUntil'] ) );
			} );
		$levels = array_map( function ( $one ) {
			$n                     = [];
			$n['registrationDate'] = $this->sanitizeDate( $one['registrationDate'] );
			$n['membershipUntil']  = $this->sanitizeDate( $one['membershipUntil'] );
			$n['registrationTime'] = $this->sanitizeTime( $one['registrationTime'] );

			return $one;
		},
			$levels );

		return $levels;
	}

	protected function sanitizeDate( $dateStr ) {
		$f = 'Y-m-d';
		$d = \DateTime::createFromFormat( $f, $dateStr );
		if ( $d === false ) {
			return null;
		}

		return $d->format( $f );
	}

	protected function sanitizeTime( $timeStr ) {
		// expects 07:00 HH:MM
		if ( strpos( $timeStr, ':' ) < 1 ) {
			return null;
		}
		$parts = explode( ':', $timeStr );
		if ( count( $parts ) !== 2 ) {
			return null;
		}
		if ( ! is_numeric( $parts[0] ) || ! is_numeric( $parts[1] ) ) {
			return null;
		}
		$h = (int) $parts[0];
		$m = (int) $parts[1];
		if ( $h < 0 || $h > 23 || $m < 0 || $m > 59 ) {
			return null;
		}

		return $timeStr;
	}

	protected function verifyNonceAndCapability( $hook ) {
		$nonce = sprintf( 'fapi_member_%s_nonce', $hook );
		if (
			! isset( $_POST[ $nonce ] )
			||
			! wp_verify_nonce( $_POST[ $nonce ], $nonce )
		) {
			wp_die( 'Zabezpečení formuláře neumožnilo zpracování, zkuste obnovit stránku a odeslat znovu.' );
		}
		if ( ! current_user_can( self::REQUIRED_CAPABILITY ) ) {
			wp_die( 'Nemáte potřebná oprvánění.' );
		}
	}

	public function handleNewSection() {
		$this->verifyNonceAndCapability( 'new_section' );

		$name = $this->sanitization()->loadPostValue( 'fapiMemberSectionName',
		                                              [ $this->sanitization(), FapiSanitization::ANY_STRING ] );

		if ( $name === null ) {
			$this->redirect( 'settingsSectionNew', 'sectionNameEmpty' );
		}

		$this->levels()->insert( $name );

		$this->redirect( 'settingsSectionNew' );
	}

	public function handleNewLevel() {
		$this->verifyNonceAndCapability( 'new_level' );

		$name     = $this->sanitization()->loadPostValue( 'fapiMemberLevelName',
		                                                  [ $this->sanitization(), FapiSanitization::ANY_STRING ] );
		$parentId = $this->sanitization()->loadPostValue( 'fapiMemberLevelParent',
		                                                  [ $this->sanitization(), FapiSanitization::VALID_LEVEL_ID ] );

		if ( $name === null || $parentId === null ) {
			$this->redirect( 'settingsLevelNew', 'levelNameOrParentEmpty' );
		}

		$parent = $this->levels()->loadById( $parentId );
		if ( $parent === null ) {
			$this->redirect( 'settingsLevelNew', 'sectionNotFound' );
		}

		// check parent
		$this->levels()->insert( $name, $parentId );

		$this->redirect( 'settingsLevelNew' );
	}

	public function handleAddPages() {
		$this->verifyNonceAndCapability( 'add_pages' );

		$levelId = $this->sanitization()->loadPostValue( 'level_id',
		                                                 [ $this->sanitization(), FapiSanitization::VALID_LEVEL_ID ] );
		$toAdd   = $this->sanitization()->loadPostValue( 'toAdd',
		                                                 [ $this->sanitization(), FapiSanitization::VALID_PAGE_IDS ] );

		if ( $levelId === null || $toAdd === null ) {
			$this->redirect( 'settingsContentAdd', 'levelIdOrToAddEmpty' );
		}

		$parent = $this->levels()->loadById( $levelId );
		if ( $parent === null ) {
			$this->redirect( 'settingsContentAdd', 'sectionNotFound' );
		}

		// check parent
		$old = get_term_meta( $parent->term_id, 'fapi_pages', true );

		$old = ( empty( $old ) ) ? null : json_decode( $old );

		$all = ( $old === null ) ? $toAdd : array_merge( $old, $toAdd );
		$all = array_values( array_unique( $all ) );
		$all = array_map( 'intval', $all );
		update_term_meta( $parent->term_id, 'fapi_pages', json_encode( $all ) );

		$this->redirect( 'settingsContentRemove', null, [ 'level' => $levelId ] );
	}

	public function handleRemovePages() {
		$this->verifyNonceAndCapability( 'remove_pages' );

		$levelId  = $this->sanitization()->loadPostValue( 'level_id',
		                                                  [ $this->sanitization(), FapiSanitization::VALID_LEVEL_ID ] );
		$selection = $this->sanitization()->loadPostValue( 'selection',
		                                                  [ $this->sanitization(), FapiSanitization::VALID_PAGE_IDS ] );

		if ( $levelId === null || $selection === null ) {
			$this->redirect( 'settingsContentRemove', 'levelIdOrToAddEmpty' );
		}

		$parent = $this->levels()->loadById( $levelId );
		if ( $parent === null ) {
			$this->redirect( 'settingsContentRemove', 'sectionNotFound' );
		}

		$selection = array_map( 'intval', $selection );

		update_term_meta( $parent->term_id, 'fapi_pages', json_encode( $selection ) );

		$this->redirect( 'settingsContentAdd', null, [ 'level' => $levelId ] );
	}

	public function handleRemoveLevel() {
		$this->verifyNonceAndCapability( 'remove_level' );

		$id = $this->sanitization()->loadPostValue( 'level_id',
		                                            [ $this->sanitization(), FapiSanitization::VALID_LEVEL_ID ] );

		if ( $id === null ) {
			$this->redirect( 'settingsSectionNew' );
		}

		$this->levels()->remove( $id );

		$this->redirect( 'settingsLevelNew', 'removeLevelSuccessful' );
	}

	public function handleEditLevel() {
		$this->verifyNonceAndCapability( 'edit_level' );

		$id   = $this->sanitization()->loadPostValue( 'level_id',
		                                              [ $this->sanitization(), FapiSanitization::VALID_LEVEL_ID ] );
		$name = $this->sanitization()->loadPostValue( 'name', [ $this->sanitization(), FapiSanitization::ANY_STRING ] );

		if ( $id === null || $name === null ) {
			$this->redirect( 'settingsSectionNew', 'editLevelNoName' );
		}

		$this->levels()->update( $id, $name );

		$this->redirect( 'settingsLevelNew', 'editLevelSuccessful' );
	}

    public function handleOrderLevel() {
        $this->verifyNonceAndCapability( 'order_level' );

        $id   = $this->sanitization()->loadPostValue( 'id',
            [ $this->sanitization(), FapiSanitization::VALID_LEVEL_ID ] );
        $direction = $this->sanitization()->loadPostValue( 'direction', [ $this->sanitization(), FapiSanitization::VALID_DIRECTION ] );

        if ( $id === null || $direction === null ) {
            $this->redirect( 'settingsSectionNew', 'editLevelNoName' );
        }

        $this->levels()->order( $id, $direction );

        $this->redirect( 'settingsLevelNew', 'editLevelSuccessful' );
    }

	public function handleEditEmail() {
		$this->verifyNonceAndCapability( 'edit_email' );

		$levelId     = $this->sanitization()->loadPostValue( 'level_id',
		                                                     [
			                                                     $this->sanitization(),
			                                                     FapiSanitization::VALID_LEVEL_ID
		                                                     ] );
		$emailType   = $this->sanitization()->loadPostValue( 'email_type',
		                                                     [
			                                                     $this->sanitization(),
			                                                     FapiSanitization::VALID_EMAIL_TYPE
		                                                     ] );
		$mailSubject = $this->sanitization()->loadPostValue( 'mail_subject',
		                                                     [ $this->sanitization(), FapiSanitization::ANY_STRING ] );
		$mailBody    = $this->sanitization()->loadPostValue( 'mail_body',
		                                                     [ $this->sanitization(), FapiSanitization::ANY_STRING ] );

		if ( $mailSubject === null || $mailBody === null ) {
			// remove mail template
			delete_term_meta(
				$levelId,
				$this->levels()->constructEmailTemplateKey( $emailType )
			);
			$this->redirect( 'settingsEmails', 'editMailsRemoved', [ 'level' => $levelId ] );
		}

		update_term_meta(
			$levelId,
			$this->levels()->constructEmailTemplateKey( $emailType ),
			[ 's' => $mailSubject, 'b' => $mailBody ]
		);

		$this->redirect( 'settingsEmails', 'editMailsUpdated', [ 'level' => $levelId ] );
	}

	public function handleSetOtherPage() {
		$this->verifyNonceAndCapability( 'set_other_page' );

		$levelId  = $this->sanitization()->loadPostValue( 'level_id',
		                                                  [ $this->sanitization(), FapiSanitization::VALID_LEVEL_ID ] );
		$pageType = $this->sanitization()->loadPostValue( 'page_type',
		                                                  [
			                                                  $this->sanitization(),
			                                                  FapiSanitization::VALID_OTHER_PAGE_TYPE
		                                                  ] );
		$page     = $this->sanitization()->loadPostValue( 'page',
		                                                  [ $this->sanitization(), FapiSanitization::VALID_PAGE_ID ] );

		if ( $page === null ) {
			// remove mail template
			delete_term_meta( $levelId, $this->levels()->constructOtherPageKey( $pageType ) );
			$this->redirect( 'settingsPages', 'editOtherPagesRemoved', [ 'level' => $levelId ] );
		}

		update_term_meta( $levelId, $this->levels()->constructOtherPageKey( $pageType ), $page );

		$this->redirect( 'settingsPages', 'editOtherPagesUpdated', [ 'level' => $levelId ] );
	}

	public function handleSetSettings() {
		$this->verifyNonceAndCapability( 'set_settings' );

		$currentSettings = get_option( self::OPTION_KEY_SETTINGS );

		$loginPageId = $this->sanitization()->loadPostValue( 'login_page_id',
		                                                     [
			                                                     $this->sanitization(),
			                                                     FapiSanitization::VALID_PAGE_ID
		                                                     ] );

		if ( $loginPageId === null ) {
			unset( $currentSettings['login_page_id'] );
			update_option( self::OPTION_KEY_SETTINGS, $currentSettings );
			$this->redirect( 'settingsSettings', 'settingsSettingsUpdated' );
		}
		$page = get_post( $loginPageId );
		if ( $page === null ) {
			$this->redirect( 'settingsSettings', 'settingsSettingsNoValidPage' );
		}

		$currentSettings['login_page_id'] = $loginPageId;
		update_option( self::OPTION_KEY_SETTINGS, $currentSettings );
		$this->redirect( 'settingsSettings', 'settingsSettingsUpdated' );
	}

	public function registerSettings() {
		register_setting( 'options',
		                  'fapiMemberApiEmail',
		                  [
			                  'type'         => 'string',
			                  'description'  => 'Fapi Member - API e-mail',
			                  'show_in_rest' => false,
			                  'default'      => null,
		                  ] );
		register_setting( 'options',
		                  'fapiMemberApiKey',
		                  [
			                  'type'         => 'string',
			                  'description'  => 'Fapi Member - API key',
			                  'show_in_rest' => false,
			                  'default'      => null,
		                  ] );
	}

	public function addScripts() {
		$this->registerStyles();
		$this->registerScripts();
		global $pagenow;
		if ( $pagenow === 'admin.php' || $pagenow === 'options-general.php' ) {
			wp_enqueue_style( 'fapi-member-admin-font' );
			wp_enqueue_style( 'fapi-member-admin' );
			wp_enqueue_style( 'fapi-member-swal-css' );
			wp_enqueue_script( 'fapi-member-swal' );
			wp_enqueue_script( 'fapi-member-swal-promise-polyfill' );
			wp_enqueue_script( 'fapi-member-main' );
		}
		if ( $pagenow === 'user-edit.php' ) {
			wp_enqueue_style( 'fapi-member-user-profile' );
			wp_enqueue_script( 'fapi-member-main' );
		}
	}

	public function addPublicScripts() {
		$this->registerPublicStyles();
		wp_enqueue_style( 'fapi-member-public-style' );
		if ( defined( 'FAPI_SHOWING_LEVEL_SELECTON' ) ) {
			wp_register_style(
				'fapi-member-public-levelselection-font',
				'https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap'
			);
			wp_enqueue_style( 'fapi-member-public-levelselection-font' );
		}
	}

	public function addAdminMenu() {
		add_menu_page(
			'FAPI Member',
			'FAPI Member',
			self::REQUIRED_CAPABILITY,
			'fapi-member-options',
			[ $this, 'constructAdminMenu' ],
			sprintf( 'data:image/svg+xml;base64,%s',
			         base64_encode( file_get_contents( __DIR__ . '/../_sources/F_fapi2.svg' ) ) ),
			81
		);
	}

	public function addUserProfileForm( WP_User $user ) {
		$levels = $this->levels()->loadAsTermEnvelopes();

		$memberships = $this->fapiMembershipLoader()->loadForUser( $user->ID );
		$memberships = array_reduce( $memberships,
			function ( $carry, $one ) {
				$carry[ $one->level ] = $one;

				return $carry;
			},
			                         [] );
		$o[]         = '<h2>Členské sekce</h2>';


		foreach ( $levels as $lvl ) {
			if ( $lvl->getTerm()->parent === 0 ) {
				$o[] = $this->tUserProfileOneSection( $lvl->getTerm(), $levels, $memberships );
			}
		}

		echo join( '', $o );
	}

	public function constructAdminMenu() {
		if ( ! current_user_can( self::REQUIRED_CAPABILITY ) ) {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}

		$subpage = $this->findSubpage();

		if ( method_exists( $this, sprintf( 'show%s', ucfirst( $subpage ) ) ) ) {
			call_user_func( [ $this, sprintf( 'show%s', ucfirst( $subpage ) ) ] );
		}
	}

	public function findSubpage() {
		$subpage = ( isset( $_GET['subpage'] ) ) ? $this->sanitizeSubpage( $_GET['subpage'] ) : null;
		if ( ! $subpage ) {
			return 'index';
		}

		return $subpage;
	}

	protected function sanitizeSubpage( $subpage ) {
		if ( ! is_string( $subpage ) || strlen( $subpage ) < 1 ) {
			return null;
		}
		if ( ! method_exists( $this, sprintf( 'show%s', ucfirst( $subpage ) ) ) ) {
			return null;
		}

		return $subpage;
	}

	protected function showIndex() {
		if ( ! $this->areApiCredentialsSet() ) {
			$this->showTemplate( 'connection' );
		}
		$this->showTemplate( 'index' );
	}

	protected function showSettingsSectionNew() {
		$this->showTemplate( 'settingsSectionNew' );
	}

	protected function showSettingsLevelNew() {
		$this->showTemplate( 'settingsLevelNew' );
	}

	protected function showSettingsContentSelect() {
		$this->showTemplate( 'settingsContentSelect' );
	}

	protected function showSettingsContentRemove() {
		$this->showTemplate( 'settingsContentRemove' );
	}

	protected function showSettingsContentAdd() {
		$this->showTemplate( 'settingsContentAdd' );
	}

	protected function showConnection() {
		$this->showTemplate( 'connection' );
	}

	protected function showSettingsEmails() {
		$this->showTemplate( 'settingsEmails' );
	}

	protected function showSettingsElements() {
		$this->showTemplate( 'settingsElements' );
	}

	protected function showSettingsSettings() {
		$this->showTemplate( 'settingsSettings' );
	}

	protected function showSettingsPages() {
		$this->showTemplate( 'settingsPages' );
	}

    protected function showTest() {
	    if (!self::isDevelopment()) {
	        wp_die('This path is only allowed in development.');
        }
        $this->showTemplate( 'test' );
    }

	protected function showTemplate( $name ) {
		$areApiCredentialsSet = $this->areApiCredentialsSet();
		$subpage              = $this->findSubpage();

		$path = sprintf( '%s/../templates/%s.php', __DIR__, $name );
		if ( file_exists( $path ) ) {
			include $path;
		}
	}

	protected function redirect( $subpage, $e = null, $other = [] ) {
		$tail = '';
		foreach ( $other as $key => $value ) {
			$tail .= sprintf( '&%s=%s', $key, urlencode( $value ) );
		}
		if ( $e === null ) {
			wp_redirect( admin_url( sprintf( '/admin.php?page=fapi-member-options&subpage=%s%s', $subpage, $tail ) ) );
		} else {
			wp_redirect( admin_url( sprintf( '/admin.php?page=fapi-member-options&subpage=%s&e=%s%s',
			                                 $subpage,
			                                 $e,
			                                 $tail ) ) );
		}
		exit;
	}

	public function areApiCredentialsSet() {
		return get_option( self::OPTION_KEY_API_CHECKED, false );
	}

	public function recheckApiCredentials() {
		return $this->fapiApi()->checkCredentials();
	}

	/**
	 * @param WP_Term $level
	 * @param WP_Term[] $levels
	 * @param FapiMembership[] $memberships
	 *
	 * @return string
	 */
	private function tUserProfileOneSection( WP_Term $level, $levels, $memberships ) {
		$lower     = array_filter( $levels,
			function ( $one ) use ( $level ) {
				return $one->getTerm()->parent === $level->term_id;
			} );
		$lowerHtml = [];
		foreach ( $lower as $envelope ) {
		    $l = $envelope->getTerm();
			$checked = ( isset( $memberships[ $l->term_id ] ) ) ? 'checked' : '';
			if ( isset( $memberships[ $l->term_id ] ) && $memberships[ $l->term_id ]->registered !== null &&  $memberships[ $l->term_id ]->registered !== false) {
				$reg     = $memberships[ $l->term_id ]->registered;
				$regDate = sprintf( 'value="%s"', $reg->format( 'Y-m-d' ) );
				$regTime = sprintf( 'value="%s"', $reg->format( 'H:i' ) );
			} else {
				$regDate = '';
				$regTime = '';
			}
			if ( isset( $memberships[ $l->term_id ]->until ) && $memberships[ $l->term_id ]->until !== null ) {
				$untilDate = sprintf( 'value="%s"', $memberships[ $l->term_id ]->until->format( 'Y-m-d' ) );
			} else {
				$untilDate = '';
			}

			$lowerHtml[] = sprintf(
				'
                    <div class="oneLevel">
                        <input class="check" type="checkbox" name="Levels[%s][check]" id="Levels[%s][check]" %s>
                        <label class="levelName"  for="Levels[%s][check]">%s</label>
                        <label class="registrationDate" for="Levels[%s][registrationDate]">Datum registrace</label>
                        <input class="registrationDateInput" type="date" name="Levels[%s][registrationDate]" %s>
                        <label class="registrationTime" for="Levels[%s][registrationTime]">Čas registrace</label>
                        <input class="registrationTimeInput" type="time" name="Levels[%s][registrationTime]" %s>
                        <label class="membershipUntil" data-for="Levels[%s][membershipUntil]" for="Levels[%s][membershipUntil]">Členství do</label>
                        <input class="membershipUntilInput" type="date" name="Levels[%s][membershipUntil]" %s>
                        <label class="isUnlimited" for="Levels[%s][isUnlimited]">Bez expirace</label>
                        <input class="isUnlimitedInput" type="checkbox" name="Levels[%s][isUnlimited]" %s>
                    </div>
                    ',
				$l->term_id,
				$l->term_id,
				$checked,
				$l->term_id,
				$l->name,
				$l->term_id,
				$l->term_id,
				$regDate,
				$l->term_id,
				$l->term_id,
				$regTime,
				$l->term_id,
				$l->term_id,
				$l->term_id,
				$untilDate,
				$l->term_id,
				$l->term_id,
				( isset( $memberships[ $l->term_id ]->isUnlimited ) && $memberships[ $l->term_id ]->isUnlimited ) ? 'checked' : ''
			);
		}

		$checked     = ( isset( $memberships[ $level->term_id ] ) ) ? 'checked' : '';
		$isUnlimited = ( isset( $memberships[ $level->term_id ] ) && $memberships[ $level->term_id ]->isUnlimited ) ? 'checked' : '';
		if ( isset( $memberships[ $level->term_id ]->registered ) && $memberships[ $level->term_id ]->registered !== null ) {
			$reg     = $memberships[ $level->term_id ]->registered;
			$regDate = sprintf( 'value="%s"', $reg->format( 'Y-m-d' ) );
			$regTime = sprintf( 'value="%s"', $reg->format( 'H:i' ) );
		} else {
			$regDate = '';
			$regTime = '';
		}
		if ( isset( $memberships[ $level->term_id ]->until ) && $memberships[ $level->term_id ]->until !== null ) {
			$untilDate = sprintf( 'value="%s"', $memberships[ $level->term_id ]->until->format( 'Y-m-d' ) );
		} else {
			$untilDate = '';
		}

		return '
        <table class="wp-list-table widefat fixed striped fapiMembership">
            <thead>
            <tr>
                <td id="cb" class="manage-column column-cb check-column">
                    <label class="screen-reader-text" for="Levels[' . $level->term_id . '][check]">Vybrat</label>
                    <input id="Levels[' . $level->term_id . '][check]" name="Levels[' . $level->term_id . '][check]" type="checkbox" ' . $checked . '>
                </td>
                <th scope="col" id="title" class="manage-column column-title column-primary">
                    <span>' . $level->name . '</span>
                </th>
                <th scope="col" class="manage-column fields">
                    <span class="a">Datum registrace</span>
                    <span class="b">
                    <input type="date" name="Levels[' . $level->term_id . '][registrationDate]" ' . $regDate . '>
                    </span>
                </th>
                <th scope="col" class="manage-column fields">
                    <span class="a">Čas registrace</span>
                    <span class="b">
                    <input type="time" name="Levels[' . $level->term_id . '][registrationTime]" ' . $regTime . '>
                    </span>
                </th>
                <th scope="col" class="manage-column fields">
                    <span class="a" data-for="Levels[' . $level->term_id . '][membershipUntil]">Členství do</span>
                    <span class="b">
                    <input type="date" name="Levels[' . $level->term_id . '][membershipUntil]" ' . $untilDate . '>
                    </span>
                </th>
                <th scope="col" class="manage-column fields">
                    <span class="a">Bez expirace</span>
                    <span class="b">
                    <input class="isUnlimitedInput" type="checkbox" name="Levels[' . $level->term_id . '][isUnlimited]" ' . $isUnlimited . '>
                    </span>
                </th>
            </thead>
        
            <tbody id="the-list">
                <tr><td colspan="6">
                    ' . join( '', $lowerHtml ) . '
                </td></tr>
            </tbody>
        </table>
        ';
	}

	public function getSetting( $key ) {
		$o = get_option( self::OPTION_KEY_SETTINGS );
		if ( $o === false ) {
			$o = [];
		}

		return ( isset( $o[ $key ] ) ) ? $o[ $key ] : null;
	}

	public function getAllMemberships() {
		// it looks utterly inefficient, but users meta should be loaded with get_users to cache
		$users       = get_users( [ 'fields' => [ 'ID' ] ] );
		$memberships = [];
		foreach ( $users as $user ) {
			$memberships[ $user->ID ] = $this->fapiMembershipLoader()->loadForUser( $user->ID );
		}

		return $memberships;
	}

	public function checkPage() {
		global $wp_query;
		if ( ! isset( $wp_query->post ) || ! ( $wp_query->post instanceof WP_Post ) || $wp_query->post->post_type !== 'page' ) {
			return;
		}
		$pageId            = $wp_query->post->ID;
		$levelsToPages     = $this->levels()->levelsToPages();
		$levelsForThisPage = [];
		foreach ( $levelsToPages as $levelId => $pageIds ) {
			if ( in_array( $pageId, $pageIds ) ) {
				$levelsForThisPage[] = $levelId;
			}
		}
		if ( count( $levelsForThisPage ) === 0 ) {
			// page is not in any level, not protecting
			return;
		}

		// page is protected for users with membership

		if ( ! is_user_logged_in() ) {
			// user not logged in
			// we do not know what level to choose, choosing first
			$firstLevel = $levelsForThisPage[0];
			$this->redirectToNoAccessPage( $firstLevel );
		}

		// user is logged in
		if ( current_user_can( self::REQUIRED_CAPABILITY ) ) {
			// admins can access anything
			return;
		}

		$memberships = $this->fapiMembershipLoader()->loadForUser( get_current_user_id() );

		// Does user have membership for any level that page is in
		foreach ( $memberships as $m ) {
			if ( in_array( $m->level, $levelsForThisPage ) ) {
				return true;
			}
		}

		// no, he does not
		$firstLevel = $levelsForThisPage[0];
		$this->redirectToNoAccessPage( $firstLevel );
	}

	protected function redirectToNoAccessPage( $levelId ) {
		$otherPages     = $this->levels()->loadOtherPagesForLevel( $levelId, true );
		$noAccessPageId = ( isset( $otherPages['noAccess'] ) ) ? $otherPages['noAccess'] : null;
		if ( $noAccessPageId ) {
			wp_redirect( get_permalink( $noAccessPageId ) );
			exit;
		} else {
			wp_redirect( home_url() );
			exit;
		}
	}

	public function checkIfLevelSelection() {
		$isFapiLevelSelection = ( isset( $_GET['fapi-level-selection'] ) && intval( $_GET['fapi-level-selection'] ) === 1 ) ? true : false;
		if ( ! $isFapiLevelSelection ) {
			return true;
		}
		$this->showLevelSelectionPage();
	}

	protected function showLevelSelectionPage() {
		$mem   = $this->fapiMembershipLoader()->loadForUser( get_current_user_id() );
		$pages = array_map( function ( $m ) {
			$p = $this->levels()->loadOtherPagesForLevel( $m->level, true );

			return ( isset( $p['afterLogin'] ) ) ? $p['afterLogin'] : null;
		},
			$mem );
		$pages = array_unique( array_filter( $pages ) );
		if ( count( $pages ) === 0 ) {
			// no afterLogin page set anywhere
			return;
		}
		if ( count( $pages ) === 1 ) {
			// exactly one afterLogin page
			$f    = array_shift( $pages );
			$page = get_post( $f );
			wp_redirect( get_permalink( $page ) );
			exit;
		}
		define( 'FAPI_SHOWING_LEVEL_SELECTON', 1 );
		include( __DIR__ . '/../templates/levelSelection.php' );
		exit;
	}

	/**
	 *  this is not nice implementation :/
	 * it will append `fapi-level-selection=1` to every after login redirect
	 * for users without memberships id doesn't do anything
	 * for users with memberships it shows list of afterLogin pages from level config
	 * or directly redirect if there is only one
	 *
	 * @see FapiMemberPlugin::showLevelSelectionPage()
	 */
	public function loginRedirect( $redirectTo, $request, $user ) {
		if ( ( strpos( $request, '?' ) !== false ) ) {
			if ( ( strpos( $request, 'fapi-level-selection' ) !== false ) ) {
				return $request;
			}

			return $request . '&fapi-level-selection=1';
		} else {
			return $request . '?fapi-level-selection=1';
		}
	}

	protected function createOrProlongMembership( $email, $levelId, $days, $isUnlimited, &$props ) {
		$user = get_user_by( 'email', $email );
		if ( $user === false ) {
			return;
		}
		$fm            = new FapiMembershipLoader( $this->levels() );
		$memberships   = $fm->loadForUser( $user->ID );
		$membershipKey = null;
		foreach ( $memberships as $k => $m ) {
			/** @var FapiMembership $m */
			if ( $m->level === $levelId ) {
				$membershipKey = $k;
				break;
			}
		}

		if ( $membershipKey !== null ) {
			// level is there, we are prolonging
			/** @var FapiMembership $levelMembership */
			$levelMembership = $memberships[ $membershipKey ];
			if ( ! $levelMembership->isUnlimited ) {
				$props['membership_prolonged']       = true;
				$props['membership_prolonged_level'] = $levelId;
				$wasUnlimitedBefore                  = false;
			} else {
				$wasUnlimitedBefore = true;
			}
			if ( $isUnlimited || $levelMembership->isUnlimited ) {
				$levelMembership->isUnlimited = true;
				if ( ! $wasUnlimitedBefore ) {
					$props['membership_prolonged_to_unlimited'] = true;
				}
			} else {
				$levelMembership->until              = $levelMembership->until->modify( sprintf( '+ %s days', $days ) );
				$props['membership_prolonged_days']  = $days;
				$props['membership_prolonged_until'] = $levelMembership->until;
			}
			$this->fapiMembershipLoader()->saveMembershipToHistory( $user->ID, $levelMembership );
			$this->fapiMembershipLoader()->saveForUser( $user->ID, $memberships );
		} else {
			// new level membership
			$props['membership_level_added']       = true;
			$props['membership_level_added_level'] = $levelId;
			$levelTerm                             = $this->levels()->loadById( $levelId );
			if ( $levelTerm->parent === 0 ) {
				$props['membership_level_added_is_section'] = true;
			} else {
				$props['membership_level_added_is_section'] = false;
			}
			$props['did_user_had_this_level_before'] = $this->fapiMembershipLoader()->didUserHadLevelMembershipBefore(
				$user->ID,
				$levelId
			);
			if ($levelTerm->parent === 0) {
                $props['did_user_had_this_parent_before'] = null;
            } else {
                $props['did_user_had_this_parent_before'] = $this->fapiMembershipLoader()->didUserHadLevelMembershipBefore(
                    $user->ID,
                    $levelTerm->parent
                );
            }


			$registered = new DateTime();
			if ( $isUnlimited ) {
				$props['membership_level_added_unlimited'] = true;
				$until                                     = null;
			} else {
				$until = new DateTime();
				$until->modify( sprintf( '+ %s days', $days ) );
				$props['membership_level_added_until'] = $until;
				$props['membership_level_added_days']  = $days;
			}
			$new           = new FapiMembership( $levelId, $registered, $until, $isUnlimited );
			$memberships[] = $new;
			$this->fapiMembershipLoader()->saveMembershipToHistory( $user->ID, $new );
			$this->fapiMembershipLoader()->saveForUser( $user->ID, $memberships );
		}

		return true;
	}

	protected function sendEmail( $email, $type, $levelId, $props ) {
		$emails = $this->levels()->loadEmailTemplatesForLevel( $levelId, true );
		if ( count( $emails ) === 0 ) {
			// No emails defined
			return false;
		}
		if ( ! isset( $emails[ $type ] ) ) {
			// No emails of this type defined
			return false;
		}

		$subject = $emails[ $type ]['s'];
		$body    = $emails[ $type ]['b'];
		$subject = $this->applyEmailShortcodes( $subject, $props );
		$body    = $this->applyEmailShortcodes( $body, $props );

		return wp_mail( $email, $subject, $body );
	}

	protected function applyEmailShortcodes( $text, $props ) {
		$map = [
			'%%SEKCE%%'              => function ( $props ) {
                if (isset($props['membership_level_added_is_section']) && $props['membership_level_added_is_section'] === true) {
                    if (isset($props['membership_prolonged_level_name'])) {
                        return $props['membership_prolonged_level_name'];
                    } elseif (isset($props['membership_level_added_level_name'])) {
                        return $props['membership_level_added_level_name'];
                    } else {
                        return '';
                    }
                } else {
                    return '';
                }
			},
			'%%UROVEN%%'             => function ( $props ) {
		        if (isset($props['membership_level_added_is_section']) && $props['membership_level_added_is_section'] === true) {
                    if ( isset( $props['membership_child_level_added_level_name'] ) ) {
                        return $props['membership_child_level_added_level_name'];
                    } else {
                        return '';
                    }
                } else {
                    if ( isset( $props['membership_prolonged_level_name'] ) ) {
                        return $props['membership_prolonged_level_name'];
                    } elseif ( isset( $props['membership_level_added_level_name'] ) ) {
                        return $props['membership_level_added_level_name'];
                    } else {
                        return '';
                    }
                }
			},
			'%%DNI%%'                => function ( $props ) {
				if ( isset( $props['membership_prolonged_days'] ) ) {
					return $props['membership_prolonged_days'];
				} elseif ( isset( $props['membership_level_added_days'] ) ) {
					return $props['membership_level_added_days'];
				} elseif ( isset( $props['membership_prolonged_to_unlimited'] ) || isset( $props['membership_level_added_unlimited'] ) ) {
					return 'neomezeně';
				} else {
					return '';
				}
			},
			'%%CLENSTVI_DO%%'        => function ( $props ) {
				if ( isset( $props['membership_prolonged_until'] ) ) {
					return $props['membership_prolonged_until']->format( 'j. n. Y H:i' );
				} elseif ( isset( $props['membership_level_added_until'] ) ) {
					return $props['membership_level_added_until']->format( 'j. n. Y H:i' );
				} elseif ( isset( $props['membership_prolonged_to_unlimited'] ) || isset( $props['membership_level_added_unlimited'] ) ) {
					return 'neomezené';
				} else {
					return '';
				}
			},
			'%%PRIHLASENI_ODKAZ%%'   => function ( $props ) {
				return $props['login_link_url'];
			},
			'%%PRIHLASOVACI_JMENO%%' => function ( $props ) {
				if ( isset( $props['login'] ) ) {
					return $props['login'];
				} else {
					return '';
				}
			},
			'%%HESLO%%'              => function ( $props ) {
				if ( isset( $props['password'] ) ) {
					return $props['password'];
				} else {
					return '';
				}
			}
		];

		foreach ( $map as $key => $func ) {
			$text = str_replace( $key, $func( $props ), $text );
		}

		return $text;
	}
}