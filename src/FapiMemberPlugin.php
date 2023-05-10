<?php

namespace FapiMember;

use DateTimeImmutable;
use DateTimeInterface;
use FapiMember\Email\EmailShortCodesReplacer;
use FapiMember\Utils\DisplayHelper;
use FapiMember\Utils\PostTypeHelper;
use FapiMember\Utils\Random;
use FapiMember\Utils\SecurityValidator;
use WP_Error;
use WP_Post;
use WP_REST_Request;
use WP_Term;
use WP_User;
use function add_meta_box;
use function array_key_exists;
use function array_map;
use function array_merge;
use function array_unique;
use function array_values;
use function get_option;
use function get_post;
use function get_site_url;
use function get_term_meta;
use function implode;
use function in_array;
use function is_array;
use function is_email;
use function json_decode;
use function json_encode;
use function parse_str;
use function plugins_url;
use function register_rest_route;
use function rtrim;
use function sprintf;
use function update_option;
use function update_term_meta;
use function wp_enqueue_script;
use function wp_register_script;
use function wp_send_json;
use function wp_send_json_error;
use function wp_send_json_success;
use const FAPI_MEMBER_PLUGIN_VERSION;

final class FapiMemberPlugin {


	const OPTION_KEY_SETTINGS = 'fapiSettings';

	const OPTION_KEY_API_USER = 'fapiMemberApiEmail';

	const OPTION_KEY_API_KEY = 'fapiMemberApiKey';

	const OPTION_KEY_API_CREDENTIALS = 'fapiMemberApiCredentials';

	const OPTION_KEY_API_URL = 'fapiMemberApiUrl';

	const OPTION_KEY_TOKEN = 'fapiMemberApiToken';

	const OPTION_KEY_API_CHECKED = 'fapiMemberApiChecked';

	const OPTION_KEY_IS_DEVELOPMENT = 'fapiIsDevelopment';

	const REQUIRED_CAPABILITY = 'manage_options';

	const DATE_TIME_FORMAT = 'Y-m-d\TH:i:s';

	const FAPI_MEMBER_SECTIONS = 'fapi_member_sections';

	const FAPI_MEMBER_PLUGIN_VERSION_KEY = 'fapi_member_plugin_version';

	/** @var FapiLevels|null */
	private $fapiLevels = null;

	/** @var FapiSanitization|null */
	private $fapiSanitization = null;

	/** @var FapiUserUtils|null */
	private $fapiUserUtils = null;

	/** @var FapiMembershipLoader|null */
	private $fapiMembershipLoader = null;

	/** @var FapiApi|null */
	private $fapiApi = null;

	/** @var FapiClients|null */
	private $fapiClients = null;

	public function __construct() {
		$this->addHooks();
		$token = get_option( self::OPTION_KEY_TOKEN, '' );

		if ( ! $token ) {
			update_option( self::OPTION_KEY_TOKEN, Random::generate( 20, 'A-Za-z' ) );
		}
	}

	public function addHooks() {
		add_action( 'admin_menu', array( $this, 'addAdminMenu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'addScripts' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'addPublicScripts' ) );
		add_action( 'admin_init', array( $this, 'registerSettings' ) );

		add_action( 'init', array( $this, 'registerLevelsTaxonomy' ) );
		add_action( 'init', array( $this, 'registerRoles' ) );
		add_action( 'init', array( $this, 'addShortcodes' ) );
		add_action( 'rest_api_init', array( $this, 'addRestEndpoints' ) );

		// adds meta boxed to setting page/post side bar
		add_action( 'add_meta_boxes', array( $this, 'addMetaBoxes' ) );

		// saves related post to sections or levels
		add_action( 'save_post', array( $this, 'savePostMetadata' ) );

		// check if page in fapi level
		add_action( 'template_redirect', array( $this, 'checkPage' ) );

		// level selection in front-end
		add_action( 'init', array( $this, 'checkIfLevelSelection' ) );

		// user profile
		add_action( 'edit_user_profile', array( $this, 'addUserProfileForm' ) );

		// admin form handling
		add_action( 'admin_post_fapi_member_api_credentials_submit', array( $this, 'handleApiCredentialsSubmit' ) );
		add_action( 'admin_post_fapi_member_new_section', array( $this, 'handleNewSection' ) );
		add_action( 'admin_post_fapi_member_new_level', array( $this, 'handleNewLevel' ) );
		add_action( 'admin_post_fapi_member_remove_level', array( $this, 'handleRemoveLevel' ) );
		add_action( 'admin_post_fapi_member_edit_level', array( $this, 'handleEditLevel' ) );
		add_action( 'admin_post_fapi_member_order_level', array( $this, 'handleOrderLevel' ) );
		add_action( 'admin_post_fapi_member_add_pages', array( $this, 'handleAddPages' ) );
		add_action( 'admin_post_fapi_member_remove_pages', array( $this, 'handleRemovePages' ) );
		add_action( 'admin_post_fapi_member_edit_email', array( $this, 'handleEditEmail' ) );
		add_action( 'admin_post_fapi_member_set_other_page', array( $this, 'handleSetOtherPage' ) );
		add_action( 'admin_post_fapi_member_set_settings', array( $this, 'handleSetSettings' ) );

		// user profile save
		add_action( 'edit_user_profile_update', array( $this, 'handleUserProfileSave' ) );

		add_image_size( 'level-selection', 300, 164, true );
		add_filter( 'login_redirect', array( $this, 'loginRedirect' ), 5, 3 );
		add_filter( 'show_admin_bar', array( $this, 'hideAdminBar' ) );

		// filters block to render by section and levels provided
		add_filter(
			'render_block',
			function ( $blockContent, $block ) {
				if ( ! isset( $block['attrs']['hasSectionOrLevel'] ) ) {
					return $blockContent;
				}

				if ( ! isset( $block['attrs']['fapiSectionAndLevels'] ) ) {
					return $blockContent;
				}

				if ( DisplayHelper::shouldContentBeRendered( (string) $block['attrs']['hasSectionOrLevel'], $block['attrs']['fapiSectionAndLevels'] ) ) {
					return $blockContent;
				}

				return '';
			},
			15,
			2
		);

		// Hacks and fixes
		// WPS hide login plugin
		add_filter( 'whl_logged_in_redirect', array( $this, 'loggedInRedirect' ), 1 );
	}

	public function hideAdminBar( $original ) {
		$user = wp_get_current_user();

		if ( in_array( 'member', (array) $user->roles, true ) ) {
			return false;
		}

		return $original;
	}

	public function showError( $type, $message ) {
		add_action(
			'admin_notices',
			function ( $e ) {
				printf( '<div class="notice notice-%s is-dismissible"><p>%s</p></div>', $e[0], $e[1] );
			}
		);
	}

	public function registerRoles() {
		if ( get_role( 'member' ) === null ) {
			add_role( 'member', __( 'Člen', 'fapi-member' ), get_role( 'subscriber' )->capabilities );
		}
	}

	public function registerLevelsTaxonomy() {
		$this->levels()->registerTaxonomy();
	}

	public function levels() {
		if ( $this->fapiLevels === null ) {
			$this->fapiLevels = new FapiLevels();
		}

		return $this->fapiLevels;
	}

	public function addShortcodes() {
		add_shortcode( 'fapi-member-login', array( FapiMemberTools::class, 'shortcodeLoginForm' ) );
		add_shortcode( 'fapi-member-user', array( FapiMemberTools::class, 'shortcodeUser' ) );
		add_shortcode( 'fapi-member-user-section-expiration', array( FapiMemberTools::class, 'shortcodeSectionExpirationDate' ) );
	}

	public function addRestEndpoints() {
		register_rest_route(
			'fapi/v1',
			'/sections',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'handleApiSections' ),
				'permission_callback' => function () {
					return true;
				},
			)
		);
		register_rest_route(
			'fapi/v1',
			'/sections-simple',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'handleApiSectionsSimple' ),
				'permission_callback' => function () {
					return true;
				},
			)
		);
		register_rest_route(
			'fapi/v1',
			'/callback',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'handleApiCallback' ),
				'permission_callback' => function () {
					return true;
				},
			)
		);
		register_rest_route(
			'fapi/v1',
			'/check-connection',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'handleApiCheckConnectionCallback' ),
				'permission_callback' => function () {
					return true;
				},
			)
		);
		register_rest_route(
			'fapi/v1',
			'/list-forms',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'handleApiListFormsCallback' ),
				'permission_callback' => function () {
					return true;
				},
			)
		);
	}

	/**
	 * @return void
	 */
	public function addMetaBoxes() {
		$screens = PostTypeHelper::getSupportedPostTypes();

		add_meta_box(
			'fapi_member_meta_box_id',
			'FAPI Member',
			function ( WP_Post $post ) {
				echo '<p>' . __( 'Přiřazené sekce a úrovně', 'fapi-member' ) . '</p>';

				$envelopes         = $this->levels()->loadAsTermEnvelopes();
				$levelsToPages     = $this->levels()->levelsToPages();
				$levelsForThisPage = array();

				foreach ( $levelsToPages as $levelId => $pageIds ) {
					if ( in_array( $post->ID, $pageIds, true ) ) {
						$levelsForThisPage[] = $levelId;
					}
				}

				echo '<input name="' . self::FAPI_MEMBER_SECTIONS . '[]" checked="checked" type="checkbox" value="-1" style="display: none !important;">';

				foreach ( $envelopes as $envelope ) {
					$term = $envelope->getTerm();

					if ( $term->parent === 0 ) {
						echo '<p>';
						echo self::renderCheckbox( $term, $levelsForThisPage );

						foreach ( $envelopes as $underEnvelope ) {
							$underTerm = $underEnvelope->getTerm();

							if ( $underTerm->parent === $term->term_id ) {
								echo '<span style="margin: 15px;"></span>' . self::renderCheckbox( $underTerm, $levelsForThisPage );
							}
						}
						echo '</p>';
					}
				}
			},
			$screens,
			'side'
		);
	}

	private static function renderCheckbox( WP_Term $term, array $levelsForThisPage ) {
		$isAssigned = in_array( $term->term_id, $levelsForThisPage, true );

		return '<input name="' . self::FAPI_MEMBER_SECTIONS . '[]" ' . ( $isAssigned ? 'checked="checked"' : '' ) . 'type="checkbox" value="' . $term->term_id . '">' . $term->name . '<br>';
	}

	public function savePostMetadata( $postId ) {
		if ( ! array_key_exists( self::FAPI_MEMBER_SECTIONS, $_POST ) ) {
			return;
		}

		$levelAndSectionIds = $this->sanitization()->loadPostValue(
			self::FAPI_MEMBER_SECTIONS,
			array( $this->sanitization(), FapiSanitization::INT_LIST )
		);
		$levelAndSectionIds = $this->sanitization()->validLevelIds( $levelAndSectionIds );

		$allLevels = $this->levels()->allIds();

		foreach ( $allLevels as $levelId ) {
			$posts = get_term_meta( $levelId, 'fapi_pages', true );
			$posts = ( empty( $posts ) ) ? array() : json_decode( $posts, true );

			if ( in_array( $levelId, $levelAndSectionIds, true ) ) {
				$posts[] = (int) $postId;
			} else {
				foreach ( $posts as $key => $levelPostId ) {
					if ( $levelPostId !== $postId ) {
						continue;
					}

					unset( $posts[ $key ] );
				}
			}

			$posts = array_values( array_unique( $posts ) );

			update_term_meta( $levelId, 'fapi_pages', json_encode( $posts ) );
		}
	}

	public function sanitization() {
		if ( $this->fapiSanitization === null ) {
			$this->fapiSanitization = new FapiSanitization( $this->levels() );
		}

		return $this->fapiSanitization;
	}

	/**
	 * @return never
	 */
	public function handleApiSections() {
		$termEnvelopes   = $this->levels()->loadAsTermEnvelopes();
		$sections        = array();
		$levelsBySection = array();

		foreach ( $termEnvelopes as $termEnvelope ) {
			$term = $termEnvelope->getTerm();

			if ( $term->parent === 0 ) {
				$sections[] = array(
					'id'   => $term->term_id,
					'name' => $term->name,
				);

				continue;
			}

			if ( ! isset( $levelsBySection[ $term->parent ] ) ) {
				$levelsBySection[ $term->parent ] = array();
			}

			$levelsBySection[ $term->parent ][] = array(
				'id'   => $term->term_id,
				'name' => $term->name,
			);
		}

		foreach ( $sections as $key => $section ) {
			$sections[ $key ]['levels'] = array();

			if ( ! isset( $levelsBySection[ $section['id'] ] ) ) {
				continue;
			}

			$sections[ $key ]['levels'] = $levelsBySection[ $section['id'] ];
		}

		wp_send_json( $sections );
	}

	/**
	 * @return never
	 */
	public function handleApiSectionsSimple( WP_REST_Request $request ) {
		$params        = $request->get_query_params();
		$termEnvelopes = $this->levels()->loadAsTermEnvelopes();
		$sections      = array();
		$limit         = null;

		if ( isset( $params['limit'] ) && is_numeric( $params['limit'] ) ) {
			$limit = (int) $params['limit'];
		}

		$iterator = 0;

		foreach ( $termEnvelopes as $termEnvelope ) {
			$term = $termEnvelope->getTerm();

			if ( $iterator === $limit ) {
				break;
			}

			$iterator++;

			if ( $term->parent === 0 ) {
				$sections[] = array(
					'id'   => $term->term_id,
					'name' => $term->name,
				);

				continue;
			}

			/** @var WP_Term $parentTerm */
			$parentTerm = $this->levels()->loadById( $term->parent );

			$sections[] = array(
				'id'   => $term->term_id,
				'name' => $parentTerm->name . ' - ' . $term->name,
			);
		}

		wp_send_json( $sections );
	}

	/**
	 * @return never
	 */
	public function handleApiCallback( WP_REST_Request $request ) {
		$params = $request->get_params();
		$body   = $request->get_body();
		$data   = array();
		parse_str( $body, $data );

		if ( ! isset( $params['level'] ) ) {
			$this->callbackError( 'Level parameter missing in get params.' );
		}

		if ( is_array( $params['level'] ) ) {
			$levelIds = array();

			foreach ( $params['level'] as $level ) {
				$levelIds[] = (int) $level;
			}
		} else {
			$levelIds = array( (int) array( $params['level'] ) );
		}

		$existingLevels = $this->levels()->allIds();

		foreach ( $levelIds as $oneLevelId ) {
			if ( ! in_array( $oneLevelId, $existingLevels, true ) ) {
				$this->callbackError( sprintf( 'Section or level with ID %s, does not exist.', $oneLevelId ) );
			}
		}

		if ( isset( $data['voucher'] ) ) {
			$userData = $this->getEmailFromValidVoucher( $data );
		} elseif ( isset( $data['id'] ) ) {
			$userData = $this->getEmailFromPaidInvoice( $data );
		} elseif ( isset( $data['token'] ) ) {
			$userData = $this->getEmailFromBodyWithValidToken( $data );
		} else {
			$this->callbackError( 'Invalid notification received. Missing voucher, id or token.' );
		}

		if ( ! is_email( $userData['email'] ) ) {
			$this->callbackError( 'Invalid email provided. Email given: ' . $userData['email'] );
		}

		$props = array();

		if ( isset( $params['days'] ) ) {
			$days = (int) $params['days'];
		} else {
			$days = false;
		}

		$isUnlimited = $days === false;

		$user = $this->userUtils()->getOrCreateUser( $userData, $props );

		if ( $user instanceof WP_Error ) {
			$this->callbackError( 'Failed to create user. Last errors: ' . json_encode( $user->get_error_messages() ) );
		}

		$historicalMemberships = $this->fapiMembershipLoader()->loadMembershipsHistory( $user->ID );

		foreach ( $levelIds as $levelId ) {
			$level = $this->levels()->loadById( $levelId );

			if ( $level === null ) {
				continue;
			}

			$this->createOrProlongMembership( $user, $levelId, $days, $isUnlimited, $props );
			$this->enhanceProps( $props );
		}

		$this->fapiMembershipLoader()->extendMembershipsToParents( $user->ID );
		$wasUserCreatedNow = isset( $props['new_user'] ) && $props['new_user'];

		$levels       = $this->levels()->loadByIds( $levelIds );
		$emailsToSend = $this->findEmailsToSend( $user, $levels, $wasUserCreatedNow, $this->fapiMembershipLoader(), $historicalMemberships );

		foreach ( $emailsToSend as $emailToSend ) {
			list($type, $level) = $emailToSend;

			$this->sendEmail( $user->user_email, $type, $level->term_id, $props );
		}

		wp_send_json_success( array( self::FAPI_MEMBER_PLUGIN_VERSION_KEY => FAPI_MEMBER_PLUGIN_VERSION ) );

		die;
	}

	/**
	 * @param string $message
	 * @return never
	 */
	protected function callbackError( $message ) {
		wp_send_json_error(
			array(
				'error'                              => $message,
				self::FAPI_MEMBER_PLUGIN_VERSION_KEY => FAPI_MEMBER_PLUGIN_VERSION,
			),
			400
		);

		die;
	}

	/**
	 * @param array<mixed> $data
	 * @return array<mixed>
	 */
	private function getEmailFromValidVoucher( array $data ) {
		$voucherId               = $data['voucher'];
		$voucher                 = $this->getFapiClients()->getVoucher( $voucherId );
		$voucherItemTemplateCode = $voucher['item_template_code'];
		$itemTemplate            = $this->getFapiClients()->getItemTemplate( $voucherItemTemplateCode );

		if ( $voucher === false ) {
			$this->callbackError( sprintf( 'Error getting voucher: %s', $this->getFapiClients()->getLastErrors() ) );
		}

		$itemTemplate = ( $itemTemplate === false ) ? array() : $itemTemplate;

		if ( ! self::isDevelopment() && ! SecurityValidator::isVoucherSecurityValid( $voucher, $itemTemplate, $data['time'], $data['security'] ) ) {
			$this->callbackError( 'Invoice security is not valid.' );
		}

		if ( ! isset( $voucher['status'] ) || $voucher['status'] !== 'applied' ) {
			$this->callbackError( 'Voucher status is not applied.' );
		}

		if ( ! isset( $voucher['applicant']['email'] ) ) {
			$this->callbackError( 'Cannot find applicant email in API response.' );
		}

		return array( 'email' => $voucher['applicant']['email'] );
	}

	/**
	 * @deprecated use /FapiMember/FapiClients
	 * @return FapiApi
	 */
	public function fapiApi() {
		if ( $this->fapiApi === null ) {
			$apiUser = get_option( self::OPTION_KEY_API_USER, null );
			$apiKey  = get_option( self::OPTION_KEY_API_KEY, null );
			$apiUrl  = get_option( self::OPTION_KEY_API_URL, 'https://api.fapi.cz/' );

			$this->fapiApi = new FapiApi( $apiUser, $apiKey, $apiUrl );
		}

		return $this->fapiApi;
	}

	/**
	 * @return FapiClients
	 */
	public function getFapiClients() {
		if ( $this->fapiClients === null ) {
			$apiUser = get_option( self::OPTION_KEY_API_USER, null );
			$apiKey  = get_option( self::OPTION_KEY_API_KEY, null );
			$apiUrl  = get_option( self::OPTION_KEY_API_URL, 'https://api.fapi.cz/' );
			// $fapiCredentials = get_option( self::OPTION_KEY_API_CREDENTIALS, null );

			// if ( $fapiCredentials === null ) {
				$fapiCredentials = array(
					array(
						'username' => $apiUser,
						'token'    => $apiKey,
					),
				);
				// } else {
				// $fapiCredentials = json_decode( $fapiCredentials, true );
				// }

				$fapiClients = array();

				foreach ( $fapiCredentials as $fapiCredential ) {
					$fapiClients[] = new FapiApi( $fapiCredential['username'], $fapiCredential['token'], $apiUrl );
				}

				$this->fapiClients = new FapiClients( $fapiClients );
		}

		return $this->fapiClients;
	}

	public static function isDevelopment() {
		$s = (int) get_option( self::OPTION_KEY_IS_DEVELOPMENT, 0 );

		return ( $s === 1 );
	}

	/**
	 * @param array<mixed> $data
	 * @return array<mixed>
	 */
	private function getEmailFromPaidInvoice( array $data ) {
		$invoice = $this->getFapiClients()->getInvoice( $data['id'] );

		if ( $invoice === false ) {
			$this->callbackError( sprintf( 'Error getting invoice: %s', $this->getFapiClients()->getLastErrors() ) );
		}

		if ( ! self::isDevelopment() && ! SecurityValidator::isInvoiceSecurityValid( $invoice, $data['time'], $data['security'] ) ) {
			$this->callbackError( 'Invoice security is not valid.' );
		}

		if ( isset( $invoice['parent'] ) ) {
			$this->callbackError( 'Invoice parent is set and not null.' );
		}

		if ( ! isset( $invoice['customer']['email'] ) ) {
			$this->callbackError( 'Cannot find customer email in API response.' );
		}

		return array(
			'email'      => $invoice['customer']['email'],
			'first_name' => isset( $invoice['customer']['first_name'] ) ? $invoice['customer']['first_name'] : null,
			'last_name'  => isset( $invoice['customer']['last_name'] ) ? $invoice['customer']['last_name'] : null,
		);
	}

	/**
	 * @param array<mixed> $data
	 * @return array<mixed>
	 */
	private function getEmailFromBodyWithValidToken( array $data ) {
		$token = get_option( self::OPTION_KEY_TOKEN, null );

		if ( $data['token'] !== $token ) {
			$this->callbackError( 'Invalid token provided. Check token correctness.' );
		}

		if ( ! isset( $data['email'] ) ) {
			$this->callbackError( 'Parameter email is missing.' );
		}

		return array(
			'email'      => $data['email'],
			'first_name' => isset( $data['first_name'] ) ? $data['first_name'] : null,
			'last_name'  => isset( $data['last_name'] ) ? $data['last_name'] : null,
		);
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

	protected function createOrProlongMembership( $user, $levelId, $days, $isUnlimited, &$props ) {
		$fapiMembershipLoader = $this->fapiMembershipLoader();
		$memberships          = $fapiMembershipLoader->loadForUser( $user->ID );
		$membershipKey        = null;

		foreach ( $memberships as $k => $m ) {
			if ( $m->level === $levelId ) {
				$membershipKey = $k;
				break;
			}
		}

		if ( $membershipKey !== null ) {
			// level is there, we are prolonging
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

			$levelTerm = $this->levels()->loadById( $levelId );

			if ( $levelTerm->parent === 0 ) {
				$props['membership_prolonged_is_section'] = true;
			} else {
				$props['membership_prolonged_is_section'] = false;
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

			$registered = new DateTimeImmutable( 'now', wp_timezone() );

			if ( $isUnlimited ) {
				$props['membership_level_added_unlimited'] = true;
				$until                                     = null;
			} else {
				$until                                 = new DateTimeImmutable( 'now', wp_timezone() );
				$until                                 = $until->modify( sprintf( '+ %s days', $days ) );
				$props['membership_level_added_until'] = $until;
				$props['membership_level_added_days']  = $days;
			}

			$new           = new FapiMembership( $levelId, $registered, $until, $isUnlimited );
			$memberships[] = $new;
			$this->fapiMembershipLoader()->saveMembershipToHistory( $user->ID, $new );
			$this->fapiMembershipLoader()->saveForUser( $user->ID, $memberships );
		}

		$this->fapiMembershipLoader()->extendMembershipsToParents( $user->ID );

		return true;
	}

	/**
	 * @param array<mixed> $props
	 */
	protected function enhanceProps( array &$props ) {
		if ( isset( $props['membership_level_added_level'] ) ) {
			$props['membership_level_added_level_name'] = $this->levels()->loadById( $props['membership_level_added_level'] )->name;
		}

		if ( isset( $props['membership_prolonged_level'] ) ) {
			$props['membership_prolonged_level_name'] = $this->levels()->loadById( $props['membership_prolonged_level'] )->name;
		}

		if ( isset( $props['membership_level_added_level'] ) ) {
			$props['login_link']     = sprintf( '<a href="%s">zde</a>', $this->getLoginUrl( $props['membership_level_added_level'] ) );
			$props['login_link_url'] = $this->getLoginUrl( $props['membership_level_added_level'] );
		} else {
			$props['login_link']     = sprintf( '<a href="%s">zde</a>', $this->getLoginUrl() );
			$props['login_link_url'] = $this->getLoginUrl();
		}
	}

	/**
	 * @param int|null $level
	 * @return false|string|WP_Error
	 */
	protected function getLoginUrl( $level = null ) {
		if ( $level ) {
			$otherPages  = $this->levels()->loadOtherPagesForLevel( $level, true );
			$loginPageId = ( isset( $otherPages['login'] ) ) ? $otherPages['login'] : null;

			if ( $loginPageId ) {
				return get_permalink( $loginPageId );
			}
		}

		$setLoginPageId = $this->getSetting( 'login_page_id' );

		if ( $setLoginPageId === null ) {
			return wp_login_url();
		}

		return get_permalink( $setLoginPageId );
	}

	/**
	 * @param string $key
	 * @return mixed|null
	 */
	public function getSetting( $key ) {
		$options = get_option( self::OPTION_KEY_SETTINGS );

		if ( $options === false ) {
			$options = array();
		}

		return ( isset( $options[ $key ] ) ) ? $options[ $key ] : null;
	}

	/**
	 * @param WP_Term[]        $levels
	 * @param bool             $wasUserCreated
	 * @param FapiMembership[] $historicalMemberships
	 * @return array
	 */
	public function findEmailsToSend( WP_User $user, array $levels, $wasUserCreated, FapiMembershipLoader $fapiMembershipLoader, $historicalMemberships ) {
		$toSend = array();

		foreach ( $levels as $level ) {
			if ( $wasUserCreated === true ) {
				$toSend[] = array( FapiLevels::EMAIL_TYPE_AFTER_REGISTRATION, $level );

				return $toSend;
			}

			if ( $historicalMemberships === array() ) {
				$toSend[] = array( FapiLevels::EMAIL_TYPE_AFTER_REGISTRATION, $level );

				return $toSend;
			}

			$didUserHasThisIdBefore = $fapiMembershipLoader->didUserHadLevelMembershipBefore( $historicalMemberships, $level->term_id );

			if ( ! $didUserHasThisIdBefore ) {
				$toSend[] = array( FapiLevels::EMAIL_TYPE_AFTER_ADDING, $level );

				continue;
			}

			$toSend[] = array( FapiLevels::EMAIL_TYPE_AFTER_MEMBERSHIP_PROLONGED, $level );
		}

		return $toSend;
	}

	/**
	 * @param string       $email
	 * @param string       $type
	 * @param int          $levelId
	 * @param array<mixed> $props
	 * @return bool|mixed|void
	 */
	protected function sendEmail( $email, $type, $levelId, $props ) {
		$emails = $this->levels()->loadEmailTemplatesForLevel( $levelId, true );

		if ( ! isset( $emails[ $type ] ) ) {
			return false;
		}

		$subject = $emails[ $type ]['s'];
		$body    = $emails[ $type ]['b'];
		$subject = EmailShortCodesReplacer::replace( $subject, $props );
		$body    = EmailShortCodesReplacer::replace( $body, $props );

		return wp_mail( $email, $subject, $body );
	}

	public function handleApiCheckConnectionCallback( WP_REST_Request $request ) {
		$body = $request->get_body();
		$data = array();
		parse_str( $body, $data );

		$token = get_option( self::OPTION_KEY_TOKEN );

		if ( ! isset( $data['token'] ) ) {
			$this->callbackError( 'Missing token.' );
		}

		if ( $token !== $data['token'] ) {
			$this->callbackError( 'Invalid token provided. Check token correctness.' );
		}

		wp_send_json_success();
	}

	public function handleApiListFormsCallback( WP_REST_Request $request ) {

		$forms = $this->getFapiClients()->listForms();
		$out   = array();

		foreach ( $forms as $form ) {
			$out[] = array(
				'label' => $form['name'],
				'value' => $form['path'],
			);
		}

		wp_send_json( $out );
	}

	public function handleApiCredentialsSubmit() {
		$this->verifyNonceAndCapability( 'api_credentials_submit' );

		$apiEmail = $this->sanitization()->loadPostValue(
			self::OPTION_KEY_API_USER,
			array( $this->sanitization(), FapiSanitization::ANY_STRING )
		);
		$apiKey   = $this->sanitization()->loadPostValue(
			self::OPTION_KEY_API_KEY,
			array( $this->sanitization(), FapiSanitization::ANY_STRING )
		);

		if ( $apiKey === null || $apiEmail === null ) {
			$this->redirect( 'connection', 'apiFormEmpty' );
		}

		update_option( self::OPTION_KEY_API_USER, $apiEmail );
		update_option( self::OPTION_KEY_API_KEY, $apiKey );
		update_option(
			self::OPTION_KEY_API_CREDENTIALS,
			json_encode(
				array(
					array(
						'username' => $apiEmail,
						'token'    => $apiKey,
					),
				)
			)
		);

		$credentialsOk = $this->getFapiClients()->checkCredentials();
		update_option( self::OPTION_KEY_API_CHECKED, $credentialsOk );

		$webUrl = rtrim( get_site_url(), '/' ) . '/';
		foreach ( $this->getFapiClients()->getFapiApis() as $fapiApi ) {
			$connection = $fapiApi->findConnection( $webUrl );

			if ( $connection === null ) {
				$fapiApi->createConnection( $webUrl );
			}
		}

		if ( $credentialsOk ) {
			$this->redirect( 'connection', 'apiFormSuccess' );
		} else {
			$this->redirect( 'connection', 'apiFormError' );
		}
	}

	protected function verifyNonceAndCapability( $hook ) {
		$nonce = sprintf( 'fapi_member_%s_nonce', $hook );

		if ( ! isset( $_POST[ $nonce ] )
			|| ! wp_verify_nonce( $_POST[ $nonce ], $nonce )
		) {
			wp_die( __( 'Zabezpečení formuláře neumožnilo zpracování, zkuste obnovit stránku a odeslat znovu.', 'fapi-member' ) );
		}
		if ( ! current_user_can( self::REQUIRED_CAPABILITY ) ) {
			wp_die( __( 'Nemáte potřebná oprvánění.', 'fapi-member' ) );
		}
	}

	protected function redirect( $subpage, $e = null, $other = array() ) {
		$tail = '';
		foreach ( $other as $key => $value ) {
			$tail .= sprintf( '&%s=%s', $key, urlencode( $value ) );
		}
		if ( $e === null ) {
			wp_redirect( admin_url( sprintf( '/admin.php?page=fapi-member-options&subpage=%s%s', $subpage, $tail ) ) );
		} else {
			wp_redirect(
				admin_url(
					sprintf(
						'/admin.php?page=fapi-member-options&subpage=%s&e=%s%s',
						$subpage,
						$e,
						$tail
					)
				)
			);
		}
		exit;
	}

	public function handleUserProfileSave( $userId ) {
		if ( empty( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'update-user_' . $userId ) ) {
			return false;
		}

		if ( ! current_user_can( self::REQUIRED_CAPABILITY ) ) {
			return false;
		}

		$data = $this->sanitizeLevels( $_POST['Levels'] );

		$memberships    = array();
		$levelEnvelopes = $this->levels()->loadAsTermEnvelopes();
		$levels         = array_reduce(
			$levelEnvelopes,
			static function ( $carry, $one ) {
				$carry[ $one->getTerm()->term_id ] = $one->getTerm();

				return $carry;
			},
			array()
		);

		foreach ( $data as $id => $inputs ) {
			if ( isset( $inputs['check'] ) && $inputs['check'] === 'on' ) {
				if ( isset( $inputs['registrationDate'] )
					&& ( isset( $inputs['membershipUntil'] ) || ( isset( $inputs['isUnlimited'] ) && $inputs['isUnlimited'] === 'on' ) )
				) {
					$registered = DateTimeImmutable::createFromFormat(
						'Y-m-d\TH:i',
						$inputs['registrationDate'] . 'T' . $inputs['registrationTime'],
						wp_timezone()
					);

					if ( $registered === false ) {
						$registered = new DateTimeImmutable( 'now', wp_timezone() );
					}

					if ( isset( $inputs['membershipUntil'] ) && $inputs['membershipUntil'] !== '' ) {
						$until = DateTimeImmutable::createFromFormat(
							'Y-m-d\TH:i:s',
							$inputs['membershipUntil'] . 'T23:59:59',
							wp_timezone()
						);
					} else {
						$until = null;
					}

					if ( isset( $inputs['isUnlimited'] ) && $inputs['isUnlimited'] === 'on' ) {
						$isUnlimited = true;
					} else {
						$isUnlimited = false;
					}

					$memberships[] = new FapiMembership( $id, $registered, $until, $isUnlimited );
				}
			}
		}

		$this->fapiMembershipLoader()->saveForUser( $userId, $memberships );

		foreach ( $memberships as $oneMembership ) {
			$this->fapiMembershipLoader()->saveMembershipToHistory( $userId, $oneMembership );
		}

		$this->fapiMembershipLoader()->extendMembershipsToParents( $userId );

		return true;
	}

	protected function sanitizeLevels( $levels ) {
		if ( ! is_array( $levels ) ) {
			wp_die( 'Unknown input structure.' );
		}

		$levels = array_filter(
			$levels,
			static function ( $one ) {
				return ( isset( $one['check'] ) && $one['check'] === 'on' );
			}
		);
		$levels = array_filter(
			$levels,
			static function ( $one ) {
				return ( isset( $one['registrationDate'] ) && isset( $one['registrationTime'] ) && isset( $one['membershipUntil'] ) );
			}
		);
		$levels = array_map(
			function ( $one ) {
				$n                     = array();
				$n['registrationDate'] = $this->sanitizeDate( $one['registrationDate'] );
				$n['membershipUntil']  = $this->sanitizeDate( $one['membershipUntil'] );
				$n['registrationTime'] = $this->sanitizeTime( $one['registrationTime'] );

				return $one;
			},
			$levels
		);

		return $levels;
	}

	protected function sanitizeDate( $dateStr ) {
		$f = 'Y-m-d';
		$d = DateTimeImmutable::createFromFormat( $f, $dateStr, wp_timezone() );
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

	public function handleNewSection() {
		$this->verifyNonceAndCapability( 'new_section' );

		$name = $this->sanitization()->loadPostValue(
			'fapiMemberSectionName',
			array( $this->sanitization(), FapiSanitization::ANY_STRING )
		);

		if ( $name === null ) {
			$this->redirect( 'settingsSectionNew', 'sectionNameEmpty' );
		}

		$this->levels()->insert( $name );

		$this->redirect( 'settingsSectionNew' );
	}

	public function handleNewLevel() {
		$this->verifyNonceAndCapability( 'new_level' );

		$name     = $this->sanitization()->loadPostValue(
			'fapiMemberLevelName',
			array( $this->sanitization(), FapiSanitization::ANY_STRING )
		);
		$parentId = $this->sanitization()->loadPostValue(
			'fapiMemberLevelParent',
			array( $this->sanitization(), FapiSanitization::VALID_LEVEL_ID )
		);

		if ( $name === null || $parentId === null ) {
			$this->redirect( 'settingsLevelNew', 'levelNameOrParentEmpty' );
		}

		$parent = $this->levels()->loadById( $parentId );

		if ( $parent === null ) {
			$this->redirect( 'settingsLevelNew', 'sectionNotFound' );
		}

		$this->levels()->insert( $name, $parentId );

		$this->redirect( 'settingsLevelNew' );
	}

	public function handleAddPages() {
		$this->verifyNonceAndCapability( 'add_pages' );

		$levelId = $this->sanitization()->loadPostValue(
			'level_id',
			array( $this->sanitization(), FapiSanitization::VALID_LEVEL_ID )
		);
		$toAdd   = $this->sanitization()->loadPostValue(
			'toAdd',
			array( $this->sanitization(), FapiSanitization::VALID_PAGE_IDS )
		);

		if ( $levelId === null || $toAdd === null ) {
			$this->redirect( 'settingsContentAdd', 'levelIdOrToAddEmpty' );
		}

		$parent = $this->levels()->loadById( $levelId );

		if ( $parent === null ) {
			$this->redirect( 'settingsContentAdd', 'sectionNotFound' );
		}

		// check parent
		$old = get_term_meta( $parent->term_id, 'fapi_pages', true );

		$old = ( empty( $old ) ) ? null : json_decode( $old, true );

		$all = ( $old === null ) ? $toAdd : array_merge( $old, $toAdd );
		$all = array_values( array_unique( $all ) );
		$all = array_map( 'intval', $all );
		update_term_meta( $parent->term_id, 'fapi_pages', json_encode( $all ) );

		$this->redirect( 'settingsContentRemove', null, array( 'level' => $levelId ) );
	}

	public function handleRemovePages() {
		$this->verifyNonceAndCapability( 'remove_pages' );

		$levelId = $this->sanitization()->loadPostValue(
			'level_id',
			array( $this->sanitization(), FapiSanitization::VALID_LEVEL_ID )
		);

		$selection = $this->sanitization()->loadPostValue(
			'selection',
			array( $this->sanitization(), FapiSanitization::VALID_PAGE_IDS )
		);

		if ( ! $selection ) {
			$selection = array();
		}

		$cpt_selection = $this->sanitization()->loadPostValue(
			'cpt_selection',
			array( $this->sanitization(), FapiSanitization::STR_LIST ),
			array()
		);

		if ( $levelId === null /*|| $selection === null*/ ) {
			$this->redirect( 'settingsContentRemove', 'levelIdOrToAddEmpty' );
		}

		$parent = $this->levels()->loadById( $levelId );
		if ( $parent === null ) {
			$this->redirect( 'settingsContentRemove', 'sectionNotFound' );
		}

		$selection = array_map( 'intval', $selection );

		update_term_meta( $parent->term_id, 'fapi_pages', json_encode( $selection ) );

		$cpt_selection = array_map( 'strval', $cpt_selection );

		$all_stored_post_types             = get_option( 'fapi_member_post_types', array() );
		$all_stored_post_types[ $levelId ] = $cpt_selection;
		update_option( 'fapi_member_post_types', $all_stored_post_types );

		$this->redirect( 'settingsContentAdd', null, array( 'level' => $levelId ) );
	}

	public function handleRemoveLevel() {
		$this->verifyNonceAndCapability( 'remove_level' );

		$id = $this->sanitization()->loadPostValue(
			'level_id',
			array( $this->sanitization(), FapiSanitization::VALID_LEVEL_ID )
		);

		if ( $id === null ) {
			$this->redirect( 'settingsSectionNew' );
		}

		$this->levels()->remove( $id );

		$this->redirect( 'settingsLevelNew', 'removeLevelSuccessful' );
	}

	public function handleEditLevel() {
		 $this->verifyNonceAndCapability( 'edit_level' );

		$id   = $this->sanitization()->loadPostValue(
			'level_id',
			array( $this->sanitization(), FapiSanitization::VALID_LEVEL_ID )
		);
		$name = $this->sanitization()->loadPostValue( 'name', array( $this->sanitization(), FapiSanitization::ANY_STRING ) );

		if ( $id === null || $name === null ) {
			$this->redirect( 'settingsSectionNew', 'editLevelNoName' );
		}

		$this->levels()->update( $id, $name );

		$this->redirect( 'settingsLevelNew', 'editLevelSuccessful' );
	}

	public function handleOrderLevel() {
		$this->verifyNonceAndCapability( 'order_level' );

		$id        = $this->sanitization()->loadPostValue(
			'id',
			array( $this->sanitization(), FapiSanitization::VALID_LEVEL_ID )
		);
		$direction = $this->sanitization()->loadPostValue( 'direction', array( $this->sanitization(), FapiSanitization::VALID_DIRECTION ) );

		if ( $id === null || $direction === null ) {
			$this->redirect( 'settingsSectionNew', 'editLevelNoName' );
		}

		$this->levels()->order( $id, $direction );

		$this->redirect( 'settingsLevelNew', 'editLevelSuccessful' );
	}

	public function handleEditEmail() {
		 $this->verifyNonceAndCapability( 'edit_email' );

		$levelId     = $this->sanitization()->loadPostValue(
			'level_id',
			array(
				$this->sanitization(),
				FapiSanitization::VALID_LEVEL_ID,
			)
		);
		$emailType   = $this->sanitization()->loadPostValue(
			'email_type',
			array(
				$this->sanitization(),
				FapiSanitization::VALID_EMAIL_TYPE,
			)
		);
		$mailSubject = $this->sanitization()->loadPostValue(
			'mail_subject',
			array( $this->sanitization(), FapiSanitization::ANY_STRING )
		);
		$mailBody    = $this->sanitization()->loadPostValue(
			'mail_body',
			array( $this->sanitization(), FapiSanitization::ANY_STRING )
		);

		if ( $mailSubject === null || $mailBody === null ) {
			// remove mail template
			delete_term_meta(
				$levelId,
				$this->levels()->constructEmailTemplateKey( $emailType )
			);
			$this->redirect( 'settingsEmails', 'editMailsRemoved', array( 'level' => $levelId ) );
		}

		update_term_meta(
			$levelId,
			$this->levels()->constructEmailTemplateKey( $emailType ),
			array(
				's' => $mailSubject,
				'b' => $mailBody,
			)
		);

		$this->redirect( 'settingsEmails', 'editMailsUpdated', array( 'level' => $levelId ) );
	}

	public function handleSetOtherPage() {
		$this->verifyNonceAndCapability( 'set_other_page' );

		$levelId  = $this->sanitization()->loadPostValue(
			'level_id',
			array( $this->sanitization(), FapiSanitization::VALID_LEVEL_ID )
		);
		$pageType = $this->sanitization()->loadPostValue(
			'page_type',
			array(
				$this->sanitization(),
				FapiSanitization::VALID_OTHER_PAGE_TYPE,
			)
		);
		$page     = $this->sanitization()->loadPostValue(
			'page',
			array( $this->sanitization(), FapiSanitization::VALID_PAGE_ID )
		);

		if ( $page === null ) {
			// remove mail template
			delete_term_meta( $levelId, $this->levels()->constructOtherPageKey( $pageType ) );
			$this->redirect( 'settingsPages', 'editOtherPagesRemoved', array( 'level' => $levelId ) );
		}

		update_term_meta( $levelId, $this->levels()->constructOtherPageKey( $pageType ), $page );

		$this->redirect( 'settingsPages', 'editOtherPagesUpdated', array( 'level' => $levelId ) );
	}

	public function handleSetSettings() {
		$this->verifyNonceAndCapability( 'set_settings' );

		$currentSettings = get_option( self::OPTION_KEY_SETTINGS );

		$loginPageId = $this->sanitization()->loadPostValue(
			'login_page_id',
			array(
				$this->sanitization(),
				FapiSanitization::VALID_PAGE_ID,
			)
		);

		$dashboardPageId = $this->sanitization()->loadPostValue(
			'dashboard_page_id',
			array(
				$this->sanitization(),
				FapiSanitization::VALID_PAGE_ID,
			)
		);

		if ( $loginPageId === null ) {
			unset( $currentSettings['login_page_id'] );
		} else {
			$page = get_post( $loginPageId );

			if ( $page === null ) {
				$this->redirect( 'settingsSettings', 'settingsSettingsNoValidPage' );
			}

			$currentSettings['login_page_id'] = $loginPageId;
		}

		if ( $dashboardPageId === null ) {
			unset( $currentSettings['dashboard_page_id'] );
		} else {
			$page = get_post( $dashboardPageId );

			if ( $page === null ) {
				$this->redirect( 'settingsSettings', 'settingsSettingsNoValidPage' );
			}

			$currentSettings['dashboard_page_id'] = $dashboardPageId;
		}

		update_option( self::OPTION_KEY_SETTINGS, $currentSettings );

		$this->redirect( 'settingsSettings', 'settingsSettingsUpdated' );
	}

	public function registerSettings() {
		register_setting(
			'options',
			'fapiMemberApiEmail',
			array(
				'type'         => 'string',
				'description'  => __( 'Fapi Member - API e-mail', 'fapi-member' ),
				'show_in_rest' => false,
				'default'      => null,
			)
		);
		register_setting(
			'options',
			'fapiMemberApiKey',
			array(
				'type'         => 'string',
				'description'  => __( 'Fapi Member - API key', 'fapi-member' ),
				'show_in_rest' => false,
				'default'      => null,
			)
		);
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
			wp_enqueue_script( 'fapi-member-clipboard' );
			wp_enqueue_script( 'fapi-member-main' );
		}
		if ( $pagenow === 'user-edit.php' ) {
			wp_enqueue_style( 'fapi-member-user-profile' );
			wp_enqueue_script( 'fapi-member-main' );
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

	public function registerScripts() {
		wp_register_script(
			'fapi-member-swal',
			plugins_url( 'fapi-member/media/dist/sweetalert2.js' )
		);

		wp_register_script(
			'fapi-member-swal-promise-polyfill',
			plugins_url( 'fapi-member/media/dist/polyfill.min.js' )
		);

		wp_register_script(
			'fapi-member-clipboard',
			plugins_url( 'fapi-member/media/dist/clipboard.min.js' )
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

	public function addPublicScripts() {
		$this->registerPublicStyles();

		wp_enqueue_style( 'fapi-member-public-style' );

		if ( defined( 'FAPI_SHOWING_LEVEL_SELECTION' ) ) {
			wp_register_style(
				'fapi-member-public-levelselection-font',
				'https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap'
			);
			wp_enqueue_style( 'fapi-member-public-levelselection-font' );
		}
	}

	public function registerPublicStyles() {
		wp_register_style(
			'fapi-member-public-style',
			plugins_url( 'fapi-member/media/fapi-member-public.css' )
		);
	}

	public function addAdminMenu() {
		add_menu_page(
			'FAPI Member',
			'FAPI Member',
			self::REQUIRED_CAPABILITY,
			'fapi-member-options',
			array( $this, 'constructAdminMenu' ),
			sprintf(
				'data:image/svg+xml;base64,%s',
				base64_encode( file_get_contents( __DIR__ . '/../_sources/F_fapi2.svg' ) )
			),
			81
		);
	}

	public function addUserProfileForm( WP_User $user ) {
		$levels = $this->levels()->loadAsTermEnvelopes();

		$memberships = $this->fapiMembershipLoader()->loadForUser( $user->ID );
		$memberships = array_reduce(
			$memberships,
			static function ( $carry, $one ) {
				$carry[ $one->level ] = $one;

				return $carry;
			},
			array()
		);
		$o[]         = '<h2>' . __( 'Členské sekce', 'fapi-member' ) . '</h2>';

		foreach ( $levels as $lvl ) {
			if ( $lvl->getTerm()->parent === 0 ) {
				$o[] = $this->tUserProfileOneSection( $lvl->getTerm(), $levels, $memberships );
			}
		}

		echo implode( '', $o );
	}

	/**
	 * @param WP_Term          $level
	 * @param WP_Term[]        $levels
	 * @param FapiMembership[] $memberships
	 * @return string
	 */
	private function tUserProfileOneSection( WP_Term $level, $levels, $memberships ) {
		$lower     = array_filter(
			$levels,
			static function ( $one ) use ( $level ) {
				return $one->getTerm()->parent === $level->term_id;
			}
		);
		$lowerHtml = array();
		foreach ( $lower as $envelope ) {
			$l       = $envelope->getTerm();
			$checked = ( isset( $memberships[ $l->term_id ] ) ) ? 'checked' : '';
			if ( isset( $memberships[ $l->term_id ] ) && $memberships[ $l->term_id ]->registered !== null && $memberships[ $l->term_id ]->registered !== false ) {
				$reg     = $memberships[ $l->term_id ]->registered;
				$regDate = sprintf( 'value="%s"', $reg->format( 'Y-m-d' ) );
				$regTime = sprintf( 'value="%s"', $reg->format( 'H:i' ) );
			} else {
				$regDate = '';
				$regTime = 'value="00:00"';
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
                        <label class="registrationDate" for="Levels[%s][registrationDate]">' . __( 'Datum registrace', 'fapi-member' ) . '</label>
                        <input class="registrationDateInput" type="date" name="Levels[%s][registrationDate]" %s>
                        <label class="registrationTime" for="Levels[%s][registrationTime]">' . __( 'Čas registrace', 'fapi-member' ) . '</label>
                        <input class="registrationTimeInput" type="time" name="Levels[%s][registrationTime]" %s>
                        <label class="membershipUntil" data-for="Levels[%s][membershipUntil]" for="Levels[%s][membershipUntil]">' . __( 'Členství do', 'fapi-member' ) . '</label>
                        <input class="membershipUntilInput" type="date" name="Levels[%s][membershipUntil]" %s>
                        <label class="isUnlimited" for="Levels[%s][isUnlimited]">' . __( 'Bez expirace', 'fapi-member' ) . '</label>
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
		if ( isset( $memberships[ $level->term_id ]->registered ) && is_a( $memberships[ $level->term_id ]->registered, DateTimeInterface::class ) ) {
			$reg     = $memberships[ $level->term_id ]->registered;
			$regDate = sprintf( 'value="%s"', $reg->format( 'Y-m-d' ) );
			$regTime = sprintf( 'value="%s"', $reg->format( 'H:i' ) );
		} else {
			$regDate = '';
			$regTime = 'value="00:00"';
		}
		if ( isset( $memberships[ $level->term_id ]->until ) && is_a( $memberships[ $level->term_id ]->until, DateTimeInterface::class ) ) {
			$untilDate = sprintf( 'value="%s"', $memberships[ $level->term_id ]->until->format( 'Y-m-d' ) );
		} else {
			$untilDate = '';
		}

		return '
        <table class="wp-list-table widefat fixed striped fapiMembership">
            <thead>
            <tr>
                <td id="cb" class="manage-column column-cb check-column">
                    <label class="screen-reader-text" for="Levels[' . $level->term_id . '][check]">' . __( 'Vybrat', 'fapi-member' ) . '</label>
                    <input id="Levels[' . $level->term_id . '][check]" name="Levels[' . $level->term_id . '][check]" type="checkbox" ' . $checked . '>
                </td>
                <th scope="col" id="title" class="manage-column column-title column-primary">
                    <span>' . $level->name . '</span>
                </th>
                <th scope="col" class="manage-column fields">
                    <span class="a">' . __( 'Datum registrace', 'fapi-member' ) . '</span>
                    <span class="b">
                    <input type="date" name="Levels[' . $level->term_id . '][registrationDate]" ' . $regDate . '>
                    </span>
                </th>
                <th scope="col" class="manage-column fields">
                    <span class="a">' . __( 'Čas registrace', 'fapi-member' ) . '</span>
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
                    <span class="a">' . __( 'Bez expirace', 'fapi-member' ) . '</span>
                    <span class="b">
                    <input class="isUnlimitedInput" type="checkbox" name="Levels[' . $level->term_id . '][isUnlimited]" ' . $isUnlimited . '>
                    </span>
                </th>
            </thead>
        
            <tbody id="the-list">
                <tr><td colspan="6">
                    ' . implode( '', $lowerHtml ) . '
                </td></tr>
            </tbody>
        </table>
        ';
	}

	public function constructAdminMenu() {
		if ( ! current_user_can( self::REQUIRED_CAPABILITY ) ) {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}

		$subpage = $this->findSubpage();

		if ( method_exists( $this, sprintf( 'show%s', ucfirst( $subpage ) ) ) ) {
			$this->{sprintf( 'show%s', ucfirst( $subpage ) )}();
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
		if ( ! is_string( $subpage ) || $subpage === '' ) {
			return null;
		}
		if ( ! method_exists( $this, sprintf( 'show%s', ucfirst( $subpage ) ) ) ) {
			return null;
		}

		return $subpage;
	}

	/**
	 * @return bool
	 */
	public function recheckApiCredentials() {
		return $this->getFapiClients()->checkCredentials();
	}

	public function getAllMemberships() {
		// it looks utterly inefficient, but users meta should be loaded with get_users to cache
		$users       = get_users( array( 'fields' => array( 'ID' ) ) );
		$memberships = array();

		foreach ( $users as $user ) {
			$memberships[ $user->ID ] = $this->fapiMembershipLoader()->loadForUser( $user->ID );
		}

		return $memberships;
	}

	public function checkPage() {
		global $wp_query;

		if ( ! isset( $wp_query->post ) ||
			! ( $wp_query->post instanceof WP_Post ) ||
			! in_array( $wp_query->post->post_type, PostTypeHelper::getSupportedPostTypes(), true )
		) {
			return true;
		}

		$pageId            = $wp_query->post->ID;
		$levelsToPages     = $this->levels()->levelsToPages();
		$levelsForThisPage = array();

		$post_type             = $wp_query->post->post_type;
		$all_stored_post_types = get_option( 'fapi_member_post_types', array() );

		foreach ( $all_stored_post_types as $levelId => $post_types ) {
			if ( is_string( $post_types ) ) {
				$post_types = array( $post_types );
			}

			if ( in_array( $post_type, $post_types, true ) ) {
				$levelsForThisPage[] = $levelId;
			}
		}

		foreach ( $levelsToPages as $levelId => $pageIds ) {
			if ( in_array( $pageId, $pageIds, true ) ) {
				$levelsForThisPage[] = $levelId;
			}
		}

		$levelsForThisPage = array_unique( $levelsForThisPage );

		if ( count( $levelsForThisPage ) === 0 ) {
			// page is not in any level, not protecting
			return true;
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
			return true;
		}

		$memberships = $this->fapiMembershipLoader()->loadForUser( get_current_user_id() );

		// Does user have membership for any level that page is in
		foreach ( $memberships as $membership ) {
			if ( in_array( $membership->level, $levelsForThisPage, true ) ) {
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
		}

		wp_redirect( home_url() );

		exit;
	}

	public function checkIfLevelSelection() {
		$isFapiLevelSelection = ( isset( $_GET['fapi-level-selection'] ) && (int) $_GET['fapi-level-selection'] === 1 );

		if ( ! $isFapiLevelSelection ) {
			return true;
		}

		$this->showLevelSelectionPage();
	}

	/**
	 * @deprecated
	 * @return never
	 */
	protected function showLevelSelectionPage() {
		$memberships = $this->fapiMembershipLoader()->loadForUser( get_current_user_id() );

		$pages = array_map(
			function ( $m ) {
				$p = $this->levels()->loadOtherPagesForLevel( $m->level, true );

				return isset( $p['afterLogin'] ) ? $p['afterLogin'] : null;
			},
			$memberships
		);

		$dashboardPageId     = $this->getSetting( 'dashboard_page_id' );
		$page                = get_post( $dashboardPageId );
		$defaultDashboardUrl = null;

		if ( $page !== null ) {
			$defaultDashboardUrl = get_permalink( $page );
		}

		$pages = array_unique( array_filter( $pages ) );

		if ( count( $pages ) === 0 ) {

			if ( $defaultDashboardUrl !== null ) {
				wp_redirect( $defaultDashboardUrl );

				exit;
			}

			// no afterLogin page set anywhere
			wp_redirect( get_site_url() );

			exit;
		}

		if ( count( $pages ) === 1 ) {
			// exactly one afterLogin page
			$f    = array_shift( $pages );
			$page = get_post( $f );
			wp_redirect( get_permalink( $page ) );

			exit;
		}

		if ( $defaultDashboardUrl !== null ) {
			wp_redirect( $defaultDashboardUrl );

			exit;
		}

		define( 'FAPI_SHOWING_LEVEL_SELECTION', 1 );
		include __DIR__ . '/../templates/levelSelection.php';

		exit;
	}

	/**
	 * this is not nice implementation :/
	 * it will append `fapi-level-selection=1` to every after login redirect
	 * for users without memberships id doesn't do anything
	 * for users with memberships it shows list of afterLogin pages from level config
	 * or directly redirect if there is only one
	 *
	 * @see FapiMemberPlugin::showLevelSelectionPage()
	 * @param WP_User|WP_Error $user
	 */
	public function loginRedirect( $redirectTo, $request, $user ) {
		if ( $user instanceof WP_Error ) {
			return $redirectTo;
		}

		if ( current_user_can( 'manage_options' ) ) {
			return $redirectTo;
		}

		$memberships = $this->fapiMembershipLoader()->loadForUser( $user->ID );

		$pages = array_map(
			function ( $m ) {
				$p = $this->levels()->loadOtherPagesForLevel( $m->level, true );

				return isset( $p['afterLogin'] ) ? $p['afterLogin'] : null;
			},
			$memberships
		);

		$dashboardPageId     = $this->getSetting( 'dashboard_page_id' );
		$page                = get_post( $dashboardPageId );
		$defaultDashboardUrl = null;

		if ( $page !== null ) {
			$defaultDashboardUrl = get_permalink( $page );
		}

		$pages = array_unique( array_filter( $pages ) );

		if ( count( $pages ) === 0 ) {

			if ( $defaultDashboardUrl !== null ) {
				return $defaultDashboardUrl;
			}

			// no afterLogin page set anywhere
			return get_site_url();
		}

		if ( count( $pages ) === 1 ) {
			// exactly one afterLogin page
			$f    = array_shift( $pages );
			$page = get_post( $f );

			return get_permalink( $page );
		}

		if ( $defaultDashboardUrl !== null ) {
			return $defaultDashboardUrl;
		}

		if ( ( strpos( $request, '?' ) !== false ) ) {
			if ( ( strpos( $request, 'fapi-level-selection' ) !== false ) ) {
				return $request;
			}

			return $request . '&fapi-level-selection=1';
		}

		return $request . '?fapi-level-selection=1';
	}

	protected function showIndex() {
		if ( ! $this->areApiCredentialsSet() ) {
			$this->showTemplate( 'connection' );
		}

		$this->showTemplate( 'index' );
	}

	public function areApiCredentialsSet() {
		return get_option( self::OPTION_KEY_API_CHECKED, false );
	}

	protected function showTemplate( $name ) {
		$areApiCredentialsSet = $this->areApiCredentialsSet();
		$subpage              = $this->findSubpage();

		$path = sprintf( '%s/../templates/%s.php', __DIR__, $name );

		if ( file_exists( $path ) ) {
			include $path;
		}
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

	protected function showMemberList() {
		$this->showTemplate( 'memberList' );
	}

	protected function showTest() {
		if ( ! self::isDevelopment() ) {
			wp_die( 'This path is only allowed in development.' );
		}

		$this->showTemplate( 'test' );
	}

	/**
	 * @description Because of WPS hide login plugin
	 *
	 * @return string
	 */
	public function loggedInRedirect() {
		if ( ! is_user_logged_in() ) {
			return '';
		}

		$user = wp_get_current_user();

		if ( $user === null || $user instanceof WP_Error ) {
			return '';
		}

		return $this->loginRedirect( '', '', $user );
	}

}
