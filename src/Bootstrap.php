<?php declare(strict_types=1);

namespace FapiMember;

use FapiMember\Api\V1\RequestHandler;
use FapiMember\Api\V2\ApiController;
use FapiMember\Container\Container;
use FapiMember\Divi\FapiMemberDivi;
use FapiMember\Mioweb\FapiMemberMioweb;
use FapiMember\Model\Enums\Keys\OptionKey;
use FapiMember\Model\Enums\Types\RequestMethodType;
use FapiMember\Model\Enums\UserPermission;
use FapiMember\Service\ApiService;
use FapiMember\Service\ElementService;
use FapiMember\Service\RedirectService;
use FapiMember\Utils\DisplayHelper;
use FapiMember\Utils\PostTypeHelper;
use FapiMember\Utils\Random;
use FapiMember\Utils\ShortcodeSubstitutor;

final class Bootstrap
{
	private FapiMemberPlugin $fapiMemberPlugin;
	private FapiMemberDivi $fapiMemberDivi;
	private FapiMemberMioweb $fapiMemberMioweb;
	private ApiService $apiService;
	private ElementService $elementService;
	private RedirectService $redirectService;
	private RequestHandler $requestHandler;
	private ShortcodeSubstitutor $shortcodeSubstitutor;
	private ApiController $apiController;

	public function __construct(FapiMemberPlugin $fapiMemberPlugin)
	{
		$this->fapiMemberPlugin = $fapiMemberPlugin;
		Container::set(FapiMemberPlugin::class, $fapiMemberPlugin);

		$this->apiService = Container::get(ApiService::class);
		$this->elementService = Container::get(ElementService::class);
		$this->redirectService = Container::get(RedirectService::class);
		$this->requestHandler = Container::get(RequestHandler::class);
		$this->shortcodeSubstitutor = Container::get(ShortcodeSubstitutor::class);
		$this->apiController = Container::get(ApiController::class);
		$this->fapiMemberDivi = Container::get(FapiMemberDivi::class);
		$this->fapiMemberMioweb = Container::get(FapiMemberMioweb::class);
	}

	public function initialize(): void
	{
		$this->addHooks();
		$this->generateTokenIfNeeded();
		$this->migrateCredentialsIfNeeded();

		update_option(OptionKey::FAPI_MEMBER_VERSION, FAPI_MEMBER_PLUGIN_VERSION);
	}

	private function generateTokenIfNeeded(): void
	{
		$token = get_option(OptionKey::TOKEN, '');

		if (!$token) {
			update_option(OptionKey::TOKEN, Random::generate(20, 'A-Za-z'));
		}
	}

	public function migrateCredentialsIfNeeded(): void
	{
		$oldVersion = get_option(OptionKey::FAPI_MEMBER_VERSION, '');

		if (!empty($oldVersion)){
			return;
		}

		$fapiCredentials = get_option(OptionKey::API_CREDENTIALS, null);

		if ((!empty($fapiCredentials))) {
			return;
		}

		$apiUser = get_option(OptionKey::API_USER, null);
		$apiKey  = get_option(OptionKey::API_KEY, null );

		if (empty($apiKey) || empty($apiUser)) {
			return;
		}

		update_option(
			OptionKey::API_CREDENTIALS,
			json_encode(
				array(
					array(
						'username' => $apiUser,
						'token' => $apiKey,
					),
				)
			)
		);

		$credentialsOk = $this->apiService->checkCredentials();
		update_option( OptionKey::API_CHECKED, $credentialsOk );

		if (!$credentialsOk) {
			update_option(OptionKey::API_CREDENTIALS, '0');
		}
	}

	private function addHooks(): void
	{
		$this->addInitHooks();
		$this->addAdminHooks();

		add_action('wp_enqueue_scripts', array($this, 'addPublicScripts'));

		add_action('rest_api_init', array($this, 'addRestEndpoints'));

		// adds meta boxed to setting page/post side bar
		add_action('add_meta_boxes', array($this->elementService, 'addMetaBoxes'));
		add_action('save_post', array($this->elementService, 'savePostMetadata'));

		// check if page in fapi level
		add_action('template_redirect', array($this->redirectService, 'checkPageForRedirects'));

		// user profile
		add_action('edit_user_profile', array($this->elementService, 'addUserMenuPage'));

		add_action('wp_enqueue_scripts', array($this, 'addPublicScripts'));

		$this->addMiowebHooks();
		$this->addDiviHooks();

		add_image_size('level-selection', 300, 164, true );
		add_filter('login_redirect', array($this->fapiMemberPlugin, 'loginRedirect'), 5, 3 );
		add_filter('show_admin_bar', array($this->elementService, 'hideAdminBar'));

		// filters block to render by section and levels provided
		add_filter(
			'render_block',
			function ( $blockContent, $block ) {
				if (!isset( $block['attrs']['hasSectionOrLevel'])) {
					return $blockContent;
				}

				if (!isset( $block['attrs']['fapiSectionAndLevels'])) {
					return $blockContent;
				}

				if (DisplayHelper::shouldContentBeRendered(
						(string) $block['attrs']['hasSectionOrLevel'],
						$block['attrs']['fapiSectionAndLevels']
				)) {
					return $blockContent;
				}

				return '';
			},
			15,
			2
		);

		// WPS hide login plugin
		add_filter('whl_logged_in_redirect', array($this->redirectService, 'loggedInRedirect'), 1);
		add_filter('whl_logged_in_redirect', array($this->redirectService, 'loggedInRedirect'), 1);
	}

	private function addInitHooks(): void
	{
		add_action('init', array($this, 'registerLevelsTaxonomy'));
		add_action('init', array($this, 'registerRoles'));
		add_action('init', array($this, 'addShortcodes'));
		add_action('init', array($this->fapiMemberPlugin, 'checkTimedLevelUnlock'));
	}

	private function addAdminHooks(): void
	{
		add_action('admin_init', array($this, 'registerSettings'));
		add_action('admin_menu', array($this->elementService, 'addAdminMenu'));
		add_action('admin_enqueue_scripts', array($this, 'addScripts'));
		add_action('admin_enqueue_scripts', [$this, 'addApiNonce']);
	}

	private function addMiowebHooks(): void
	{
		add_action('wp_ajax_open_element_setting', [$this->fapiMemberMioweb, 'addSetting']);
		add_action('wp_ajax_open_row_setting', [$this->fapiMemberMioweb, 'addSetting']);
		add_action('mw_page_init', [$this->fapiMemberMioweb, 'hideContentIfNeeded']);
	}

	public function addDiviHooks(): void
	{
		add_action('divi_extensions_init', [$this, 'initializeDiviExtension']);

		add_filter( 'et_builder_get_parent_modules', [$this->fapiMemberDivi, 'addToggle']);

		foreach ($this->fapiMemberDivi->allowedModuleSlugs as $slug) {
			add_filter("et_pb_all_fields_unprocessed_" . $slug, [$this->fapiMemberDivi, 'addFields']);
		}

		add_filter('et_pb_module_content', [$this->fapiMemberDivi, 'hideElements'], 10, 4 );
	}

	public function initializeDiviExtension(): void
	{
		require_once plugin_dir_path( __FILE__ ) . 'Divi/includes/FmDivi.php';
	}

	function addApiNonce(): void
	{
		if (current_user_can(UserPermission::REQUIRED_CAPABILITY)) {
			$nonce = wp_create_nonce('wp_rest');
			echo "<script>window.apiInternalAccessNonce = '{$nonce}'</script>";
		}

	}

	public function addRestEndpoints(): void
	{
		$this->addRestEndpointV1('sections', 'handleApiSections', RequestMethodType::GET);
		$this->addRestEndpointV1('sections-simple', 'handleApiSectionsSimple', RequestMethodType::GET);
		$this->addRestEndpointV1('callback', 'handleApiCallback', RequestMethodType::POST);
		$this->addRestEndpointV1('check-connection', 'handleApiCheckConnectionCallback', RequestMethodType::POST);
		$this->addRestEndpointV1(
			'list-forms/(?P<user>[^/]+(?:\+[^/]+)?)',
			'handleApiListFormsCallback',
			RequestMethodType::GET,
		);
		$this->addRestEndpointV1('list-users', 'handleApiUsernamesCallback', RequestMethodType::GET);

		$this->addRestEndpointV2('sections');
		$this->addRestEndpointV2('pages');
		$this->addRestEndpointV2('emails');
		$this->addRestEndpointV2('memberships');
		$this->addRestEndpointV2('users');
		$this->addRestEndpointV2('apiConnections');
	}

	public function addRestEndpointV2(string $route): void
	{
		register_rest_route(
			'fapi/v2',
			'/' . $route,
			[
				'methods' => [RequestMethodType::GET, RequestMethodType::POST],
				'callback' => array($this->apiController, 'handleRequest'),
				'permission_callback' => function () {
					return true;
				},
			],
		);
	}

	public function addRestEndpointV1(string $route, string $functionName, string $method): void
	{
		register_rest_route(
			'fapi/v1',
			'/' . $route,
			[
				'methods' => $method,
				'callback' => array($this->requestHandler, $functionName),
				'permission_callback' => function () {
					return true;
				},
			],
		);
	}

	public function addShortcodes(): void
	{
		add_shortcode('fapi-member-login', array($this->shortcodeSubstitutor, 'shortcodeLoginForm'));
		add_shortcode('fapi-member-user', array($this->shortcodeSubstitutor, 'shortcodeUser'));
		add_shortcode('fapi-member-user-section-expiration', array($this->shortcodeSubstitutor, 'shortcodeSectionExpirationDate'));
		add_shortcode('fapi-member-level-unlock-date', array($this->shortcodeSubstitutor, 'shortcodeLevelUnlockDate'));
		add_shortcode('fapi-member-unlock-level', array($this->shortcodeSubstitutor, 'shortcodeUnlockLevel'));
	}

	public function addScripts(): void
	{
		$this->registerStyles();
		$this->registerScripts();

		global $pagenow;

		if ($pagenow === 'admin.php' || $pagenow === 'options-general.php') {
			wp_enqueue_style('fapi-member-admin-font');
			wp_enqueue_style('fapi-member-swal-css');
			wp_enqueue_script('fapi-member-swal');
			wp_enqueue_script('fapi-member-swal-promise-polyfill');
			wp_enqueue_script('fapi-member-clipboard');
			wp_enqueue_script('fapi-member-main');
		}
		if ($pagenow === 'user-edit.php') {
			wp_enqueue_style('fapi-member-user-profile');
			wp_enqueue_script('fapi-member-main');
		}

		wp_enqueue_script(
			'fm-react-app',
			trailingslashit(plugins_url('/', __FILE__ )) . '../app/dist/bundle.js',
			['jquery', 'wp-element'],
			filemtime(plugin_dir_path(__FILE__) . '../app/dist/bundle.js'),
			true,
		);

		wp_localize_script('fm-react-app', 'environmentData', array(
        'timeZoneOffset' => get_option('gmt_offset'),
    ));
	}

	public function registerStyles(): void
	{
		wp_register_style(
			'fapi-member-user-profile',
			plugins_url('fapi-member/media/fapi-user-profile.css')
		);
		wp_register_style(
			'fapi-member-admin-font',
			plugins_url('fapi-member/media/font/stylesheet.css')
		);
		wp_register_style(
			'fapi-member-swal-css',
			plugins_url('fapi-member/media/dist/sweetalert2.min.css')
		);
		wp_register_style(
			'fapi-member-public-style',
			plugins_url('fapi-member/media/fapi-member-public.css')
		);
	}

	public function registerScripts(): void
	{
		wp_register_script(
			'fapi-member-swal',
			plugins_url('fapi-member/media/dist/sweetalert2.js')
		);

		wp_register_script(
			'fapi-member-swal-promise-polyfill',
			plugins_url('fapi-member/media/dist/polyfill.min.js')
		);

		wp_register_script(
			'fapi-member-clipboard',
			plugins_url('fapi-member/media/dist/clipboard.min.js')
		);

		if (FapiMemberPlugin::isDevelopment()) {
			wp_register_script(
				'fapi-member-main',
				plugins_url('fapi-member/media/dist/fapi.dev.js')
			);
		} else {
			wp_register_script(
				'fapi-member-main',
				plugins_url('fapi-member/media/dist/fapi.dist.js')
			);
		}
	}

	public function addPublicScripts(): void
	{
		$this->registerPublicStyles();

		wp_enqueue_style('fapi-member-public-style');

		if ( defined('FAPI_SHOWING_LEVEL_SELECTION')) {
			wp_register_style(
				'fapi-member-public-levelselection-font',
				'https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap'
			);
			wp_enqueue_style('fapi-member-public-levelselection-font');
		}
	}

	public function registerPublicStyles(): void
	{
		wp_register_style(
			'fapi-member-public-style',
			plugins_url('fapi-member/media/fapi-member-public.css')
		);
	}

	public function registerRoles(): void
	{
		if ( get_role('member') === null ) {
			add_role('member', __('Člen', 'fapi-member'), get_role('subscriber')->capabilities);
		}
	}

	public function registerSettings(): void
	{
		register_setting(
			'options',
			'fapiMemberApiEmail',
			array(
				'type'         => 'string',
				'description'  => __('Fapi Member - API e-mail', 'fapi-member'),
				'show_in_rest' => false,
				'default'      => null,
			)
		);
		register_setting(
			'options',
			'fapiMemberApiKey',
			array(
				'type'         => 'string',
				'description'  => __('Fapi Member - API key', 'fapi-member'),
				'show_in_rest' => false,
				'default'      => null,
			)
		);
	}

	public function registerLevelsTaxonomy(): void
	{
		register_taxonomy(
			'fapi_levels',
			PostTypeHelper::getSupportedPostTypes(),
			array(
				'public' => false,
				'hierarchical' => true,
				'show_ui' => false,
				'show_in_rest' => false,
			)
		);
	}

}
