<?php


class FapiMemberPlugin
{
    private $errorBasket = [];
    private $fapiLevels = null;
    private $userUtils = null;
    private $fapiMembershipLoader = null;
    private $fapiApi = null;

    const OPTION_KEY_SETTINGS = 'fapiSettings';
    const OPTION_KEY_API_USER = 'fapiMemberApiEmail';
    const OPTION_KEY_API_KEY = 'fapiMemberApiKey';
    const OPTION_KEY_API_CHECKED = 'fapiMemberApiChecked';
    const REQUIRED_CAPABILITY = 'manage_options';
    const DF = 'Y-m-d\TH:i:s';

    public function __construct()
    {

        $this->addHooks();
    }

    public static function isDevelopment()
    {
        $s = $_SERVER['SERVER_NAME'];
        return ($s === 'localhost');
    }

    public function levels()
    {
        if ($this->fapiLevels === null) {
            $this->fapiLevels = new FapiLevels();
        }
        return $this->fapiLevels;
    }

    public function userUtils()
    {
        if ($this->userUtils === null) {
            $this->userUtils = new UserUtils();
        }
        return $this->userUtils;
    }

    public function fapiMembershipLoader()
    {
        if ($this->fapiMembershipLoader === null) {
            $this->fapiMembershipLoader = new FapiMembershipLoader($this->levels());
        }
        return $this->fapiMembershipLoader;
    }

    public function fapiApi()
    {
        if ($this->fapiApi === null) {
            $apiUser = get_option(self::OPTION_KEY_API_USER, null);
            $apiKey = get_option(self::OPTION_KEY_API_KEY, null);
            $this->fapiApi = new FapiApi($apiUser, $apiKey);
        }
        return $this->fapiApi;
    }

    public function addHooks()
    {
        add_action('admin_menu', [$this, 'addAdminMenu'] );
        add_action('admin_enqueue_scripts', [$this, 'addScripts'] );
        add_action('wp_enqueue_scripts', [$this, 'addPublicScripts'] );
        add_action('admin_init', [$this, 'registerSettings']);

        add_action('init', [$this, 'registerLevelsTaxonomy']);
        add_action('init', [$this, 'addShortcodes']);
        add_action('rest_api_init', [$this, 'addRestEndpoints']);
        // check if page in fapi level
        add_action('template_redirect', [$this, 'checkPage']);
        // level selection in front-end
        add_action( 'init', [$this, 'checkIfLevelSelection'] );

        //user profile
        add_action('edit_user_profile', [$this, 'addUserProfileForm']);

        // admin form handling
        add_action('admin_post_fapi_member_api_credentials_submit', [$this, 'handleApiCredentialsSubmit']);
        add_action('admin_post_fapi_member_new_section', [$this, 'handleNewSection']);
        add_action('admin_post_fapi_member_new_level', [$this, 'handleNewLevel']);
        add_action('admin_post_fapi_member_remove_level', [$this, 'handleRemoveLevel']);
        add_action('admin_post_fapi_member_edit_level', [$this, 'handleEditLevel']);
        add_action('admin_post_fapi_member_add_pages', [$this, 'handleAddPages']);
        add_action('admin_post_fapi_member_remove_pages', [$this, 'handleRemovePages']);
        add_action('admin_post_fapi_member_edit_email', [$this, 'handleEditEmail']);
        add_action('admin_post_fapi_member_set_other_page', [$this, 'handleSetOtherPage']);
        add_action('admin_post_fapi_member_set_settings', [$this, 'handleSetSettings']);
            // user profile save
        add_action( 'edit_user_profile_update', [$this, 'handleUserProfileSave'] );

        add_image_size( 'level-selection', 300, 164, true );
        add_filter( 'login_redirect', [$this, 'loginRedirect'], 10, 3 );
    }

    public function showError($type, $message)
    {
        add_action( 'admin_notices', function($e) {
            printf('<div class="notice notice-%s is-dismissible"><p>%s</p></div>', $e[0], $e[1]);
        });
    }

    public function registerStyles()
    {
        wp_register_style(
            'fapi-member-admin',
            plugins_url('fapi-member/media/fapi-member.css')
        );
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
            plugins_url('fapi-member/node_modules/sweetalert2/dist/sweetalert2.min.css')
        );
        wp_register_style(
            'fapi-member-public-style',
            plugins_url('fapi-member/media/fapi-member-public.css')
        );
    }

    public function registerPublicStyles()
    {
        wp_register_style(
            'fapi-member-public-style',
            plugins_url('fapi-member/media/fapi-member-public.css')
        );
    }

    public function registerScripts()
    {
        wp_register_script(
            'fapi-member-swal',
            plugins_url('fapi-member/node_modules/sweetalert2/dist/sweetalert2.js')
        );
        wp_register_script(
            'fapi-member-swal-promise-polyfill',
            plugins_url( 'fapi-member/node_modules/promise-polyfill/dist/polyfill.min.js')
        );
        if (self::isDevelopment()) {
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

    public function registerLevelsTaxonomy()
    {
        $this->levels()->registerTaxonomy();
    }

    public function addShortcodes()
    {
        add_shortcode('fapi-member-login', [$this, 'shortcodeLogin']);
        add_shortcode('fapi-member-user', [$this, 'shortcodeUser']);
    }

    public function shortcodeLogin()
    {
        include_once __DIR__.'/../templates/functions.php';
        return shortcodeLoginForm();
    }

    public function shortcodeUser()
    {
        include_once __DIR__.'/../templates/functions.php';
        return shortcodeUser();
    }

    public function addRestEndpoints()
    {
        register_rest_route(
            'fapi/v1',
            '/sections',
            [
                'methods' => 'GET',
                'callback' => [$this, 'handleApiSections'],
            ]
        );
        register_rest_route(
            'fapi/v1',
            '/callback',
            [
                'methods' => 'POST',
                'callback' => [$this, 'handleApiCallback'],
            ]
        );
    }

    public function handleApiSections()
    {
        $t = $this->levels()->loadAsTerms();
        $t = array_map(function($one) {
            return [
                'id' => $one->term_id,
                'parent' => $one->parent,
                'name' => $one->name
            ];
        }, $t);
        $sections = array_reduce($t, function($carry, $one) use ($t) {
            if ($one['parent'] === 0) {
                $children = array_values(
                    array_filter($t, function($i) use ($one) {
                        return ($i['parent'] === $one['id']);
                    })
                );
                $children = array_map(function($j) {
                    unset($j['parent']);
                    return $j;
                }, $children);
                $one['levels'] = $children;
                unset($one['parent']);
                $carry[] = $one;
            }
            return $carry;
        }, []);
        return new WP_REST_Response($sections);
    }

    public function handleApiCallback(WP_REST_Request $request)
    {
        $get = $request->get_params();
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
        parse_str($body, $d);
        $invoiceId = $d['id'];

        $invoice = $this->fapiApi()->getInvoice($invoiceId);

        if (!isset($get['level'])) {
            return false;
        }
        if (!isset($invoice['paid']) || $invoice['paid'] !== true) {
            return false;
        }
        if (!isset($invoice['customer']) || !isset($invoice['customer']['email'])) {
            return false;
        }

        $props = [];
        $email = $invoice['customer']['email'];
        $userCreated = $this->userUtils()->createUser($email, $props);

        // create or prolong membership
        if (!is_array($get['level'])) {
            $levelIds = [(int)[$get['level']]];
        } else {
            $levelIds = array_map('intval', $get['level']);
        }
        if (!isset($get['days'])) {
            $days = false;
            $isUnlimited = true;
        } else {
            $days = (int)$get['days'];
            $isUnlimited = false;
        }

        // add parents where needed
        $added = [];
        for ($i = 0; $i < count($levelIds); $i++) {
            $l = $this->levels()->loadById($levelIds[$i]);
            if ($l->parent !== 0 && !in_array($l->parent, $levelIds)) {
                $added[] = $l->parent;
            }
        }
        $levelIds = array_values(array_merge($levelIds, $added));

        foreach ($levelIds as $id) {

            $mainLevel = $this->levels()->loadById($id);
            if (!$mainLevel) {
                continue;
            }

            $this->createOrProlongMembership($email, $id, $days, $isUnlimited, $props);
            $this->enhanceProps($props);

            // send emails
            // Pokud je uživatel úplně nový, pošli REG email
            if (isset($props['new_user']) && $props['new_user']) {
                $this->sendEmail($email, FapiLevels::EMAIL_TYPE_AFTER_REGISTRATION, $id, $props);
                continue;
            }
            // Pokud uživatel získal sekci, kterou ještě neměl, pošli REG email
            if (
                (isset($props['membership_level_added']) && $props['membership_level_added'])
                &&
                (isset($props['membership_level_added_is_section']) && $props['membership_level_added_is_section'] === true)
                &&
                (!isset($props['did_user_had_this_level_before']) || $props['did_user_had_this_level_before'] === false)
            ) {
                $this->sendEmail($email, FapiLevels::EMAIL_TYPE_AFTER_REGISTRATION, $id, $props);
                continue;
            }
            // Pokud uživatel získal úroveň v sekci co už měl, pošli Při přidání
            if (
                (isset($props['membership_level_added']) && $props['membership_level_added'])
            ) {
                $l = $this->levels()->loadById($props['membership_level_added_level']);
                if ($l->parent !== 0) {
                    $sectionLevel = $this->levels()->loadById($l->parent);
                    if ($this->fapiMembershipLoader()->didUserHadLevelMembershipBefore($props['user_id'], $sectionLevel->term_id)) {
                        $this->sendEmail($email, FapiLevels::EMAIL_TYPE_AFTER_ADDING, $id, $props);
                        continue;
                    }
                }
            }
            // Pokud uživatel koupil sekce nebo úroveň kterou již měl, pokud nebyla neomezená
            if (
            (isset($props['membership_prolonged']) && $props['membership_prolonged'])
            ) {

                $this->sendEmail($email, FapiLevels::EMAIL_TYPE_AFTER_MEMBERSHIP_PROLONGED, $id, $props);
                continue;
            }
        }
    }

    protected function enhanceProps(&$props)
    {
        if (isset($props['membership_level_added_level'])) {
            $props['membership_level_added_level_name'] = $this->levels()->loadById($props['membership_level_added_level'])->name;
        }
        if (isset($props['membership_child_level_added_level'])) {
            $props['membership_child_level_added_level_name'] = $this->levels()->loadById($props['membership_child_level_added_level'])->name;
        }
        if (isset($props['membership_prolonged_level'])) {
            $props['membership_prolonged_level_name'] = $this->levels()->loadById($props['membership_prolonged_level'])->name;
        }
        $props['login_link'] = sprintf('<a href="%s">zde</a>', $this->getLoginUrl());
        $props['login_link_url'] = $this->getLoginUrl();
    }

    protected function getLoginUrl()
    {
        $setLoginPageId = $this->getSetting('login_page_id');
        if ($setLoginPageId === null) {
            return wp_login_url();
        } else {
            return get_permalink($setLoginPageId);
        }
    }

    public function handleApiCredentialsSubmit()
    {
        $this->verifyNonceAndCapability('api_credentials_submit');

        $apiEmail = $this->input(self::OPTION_KEY_API_USER);
        $apiKey = $this->input(self::OPTION_KEY_API_KEY);

        if ($apiKey === null || $apiEmail === null) {
            $this->redirect('connection', 'apiFormEmpty');
        }

        update_option(self::OPTION_KEY_API_USER, $apiEmail);
        update_option(self::OPTION_KEY_API_KEY, $apiKey);

        $credentialsOk = $this->fapiApi()->checkCredentials();
        if ($credentialsOk) {
            update_option(self::OPTION_KEY_API_CHECKED, true);
            $this->redirect('connection', 'apiFormSuccess');
        } else {
            update_option(self::OPTION_KEY_API_CHECKED, false);
            $this->redirect('connection', 'apiFormError');
        }



    }

    public function handleUserProfileSave($userId)
    {
        if ( empty( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'update-user_' . $userId ) ) {
            return;
        }
        if (!current_user_can(self::REQUIRED_CAPABILITY)) { return false; }

        $data = $_POST['Levels'];

        $memberships = [];
        $levels = $this->levels()->loadAsTerms();
        $levels = array_reduce($levels, function($carry, $one) {
            $carry[$one->term_id] = $one;
            return $carry;
        }, []);

        foreach ($data as $id => $inputs) {
            if (isset($inputs['check']) && $inputs['check'] === 'on') {
                if (
                    isset($inputs['registrationDate'])
                    &&
                    isset($inputs['registrationTime'])
                    &&
                    (isset($inputs['membershipUntil']) || (isset($inputs['isUnlimited']) && $inputs['isUnlimited'] === 'on'))
                ) {
                    $reg = \DateTime::createFromFormat('Y-m-d\TH:i', $inputs['registrationDate'] . 'T' . $inputs['registrationTime']);
                    if (isset($inputs['membershipUntil']) && $inputs['membershipUntil'] !== '') {
                        $until = \DateTime::createFromFormat('Y-m-d\TH:i:s', $inputs['membershipUntil'] . 'T23:59:59');
                    } else {
                        $until = null;
                    }
                    if (isset($inputs['isUnlimited']) && $inputs['isUnlimited'] === 'on') {
                        $isUnlimited = true;
                    } else {
                        $isUnlimited = false;
                    }

                    $memberships[] = new FapiMembership($id, $reg, $until, $isUnlimited);
                }
            }
        }
        $this->fapiMembershipLoader()->saveForUser($userId, $memberships);
    }

    protected function verifyNonceAndCapability($hook)
    {
        $nonce = sprintf('fapi_member_%s_nonce', $hook);
        if(
            !isset( $_POST[$nonce] )
            ||
            !wp_verify_nonce($_POST[$nonce], $nonce)
        ) {
            wp_die('Zabezpečení formuláře neumožnilo zpracování, zkuste obnovit stránku a odeslat znovu.');
        }
        if ( !current_user_can( self::REQUIRED_CAPABILITY ) )  {
            wp_die('Nemáte potřebná oprvánění.');
        }
    }

    protected function input($key, $type = 'string', $method = 'POST')
    {
        switch ($method) {
            case 'POST':
                $o = (isset($_POST[$key]) && !empty($_POST[$key])) ? $_POST[$key] : null;
                break;
            default:
                throw new \RuntimeException('Not implemented.');
        }

        switch (true) {
            case ($type === 'int' && $o !== null):
                return (int)$o;
            default:
                return $o;
        }
    }

    public function handleNewSection()
    {
        $this->verifyNonceAndCapability('new_section');

        $name = $this->input('fapiMemberSectionName');

        if ($name === null ) {
            $this->redirect('settingsSectionNew', 'sectionNameEmpty');
        }

        $this->levels()->insert($name);

        $this->redirect('settingsSectionNew');
    }

    public function handleNewLevel()
    {
        $this->verifyNonceAndCapability('new_level');

        $name = $this->input('fapiMemberLevelName');
        $parentId = $this->input('fapiMemberLevelParent', 'int');

        if ($name === null || $parentId === null) {
            $this->redirect('settingsLevelNew', 'levelNameOrParentEmpty');
        }

        $parent = $this->levels()->loadById($parentId);
        if ($parent === null) {
            $this->redirect('settingsLevelNew', 'sectionNotFound');
        }

        // check parent
        $this->levels()->insert($name, $parentId);

        $this->redirect('settingsLevelNew');

    }

    public function handleAddPages()
    {
        $this->verifyNonceAndCapability('add_pages');

        $levelId = $this->input('level_id', 'int');
        $toAdd = $this->input('toAdd');

        if ($levelId === null || $toAdd === null) {
            $this->redirect('settingsContentAdd', 'levelIdOrToAddEmpty');
        }

        $parent = $this->levels()->loadById($levelId);
        if ($parent === null) {
            $this->redirect('settingsContentAdd', 'sectionNotFound');
        }

        // check parent
        $old = get_term_meta($parent->term_id, 'fapi_pages', true);

        $old = (empty($old)) ? null : json_decode($old);

        $all = ($old === null) ? $toAdd : array_merge($old, $toAdd);
        $all = array_values(array_unique($all));
        $all = array_map('intval', $all);
        update_term_meta($parent->term_id, 'fapi_pages', json_encode($all));

        $this->redirect('settingsContentRemove', null, ['level' => $levelId]);

    }

    public function handleRemovePages()
    {
        $this->verifyNonceAndCapability('remove_pages');

        $levelId = $this->input('level_id', 'int');
        $toRemove = $this->input('toRemove');

        if ($levelId === null || $toRemove === null) {
            $this->redirect('settingsContentRemove', 'levelIdOrToAddEmpty');
        }

        $parent = $this->levels()->loadById($levelId);
        if ($parent === null) {
            $this->redirect('settingsContentRemove', 'sectionNotFound');
        }

        $toRemove = array_map('intval', $toRemove);

        // check parent
        $old = get_term_meta($parent->term_id, 'fapi_pages', true);

        $old = (empty($old)) ? [] : json_decode($old);

        $new = array_values(array_filter($old, function($one) use ($toRemove){
            return !in_array($one, $toRemove);
        }));
        update_term_meta($parent->term_id, 'fapi_pages', json_encode($new));

        $this->redirect('settingsContentRemove', null, ['level' => $levelId]);

    }

    public function handleRemoveLevel()
    {
        $this->verifyNonceAndCapability('remove_level');

        $id = $this->input('level_id', 'int');

        if ($id === null) {
            $this->redirect('settingsSectionNew');
        }

        $this->levels()->remove($id);

        $this->redirect('settingsLevelNew', 'removeLevelSuccessful');
    }

    public function handleEditLevel()
    {
        $this->verifyNonceAndCapability('edit_level');

        $id = $this->input('level_id', 'int');
        $name = $this->input('name');

        if ($id === null || $name === null) {
            $this->redirect('settingsSectionNew', 'editLevelNoName');
        }

        $this->levels()->update($id, $name);

        $this->redirect('settingsLevelNew', 'editLevelSuccessful');
    }

    public function handleEditEmail()
    {
        $this->verifyNonceAndCapability('edit_email');

        $levelId = $this->input('level_id', 'int');
        $emailType = $this->input('email_type');
        $mailSubject = $this->input('mail_subject');
        $mailBody = $this->input('mail_body');

        if ($mailSubject === null || $mailBody === null) {
            // remove mail template
            delete_term_meta(
                $levelId,
                $this->levels()->constructEmailTemplateKey($emailType)
            );
            $this->redirect('settingsEmails', 'editMailsRemoved', ['level' => $levelId]);
        }

        update_term_meta(
            $levelId,
            $this->levels()->constructEmailTemplateKey($emailType),
            ['s' => $mailSubject, 'b' => $mailBody]
        );

        $this->redirect('settingsEmails', 'editMailsUpdated', ['level' => $levelId]);
    }

    public function handleSetOtherPage()
    {
        $this->verifyNonceAndCapability('set_other_page');

        $levelId = $this->input('level_id', 'int');
        $pageType = $this->input('page_type');
        $page = $this->input('page');

        if ($page === null) {
            // remove mail template
            delete_term_meta($levelId, $this->levels()->constructOtherPageKey($pageType));
            $this->redirect('settingsPages', 'editOtherPagesRemoved', ['level' => $levelId]);
        }

        update_term_meta($levelId, $this->levels()->constructOtherPageKey($pageType), $page);

        $this->redirect('settingsPages', 'editOtherPagesUpdated', ['level' => $levelId]);
    }

    public function handleSetSettings()
    {
        $this->verifyNonceAndCapability('set_settings');

        $currentSettings = get_option(self::OPTION_KEY_SETTINGS);

        $loginPageId = $this->input('login_page_id', 'int');
        if ($loginPageId === null) {
            unset($currentSettings['login_page_id']);
            update_option(self::OPTION_KEY_SETTINGS, $currentSettings);
            $this->redirect('settingsSettings', 'settingsSettingsUpdated');
        }
        $page = get_post($loginPageId);
        if ($page === null) {
            $this->redirect('settingsSettings', 'settingsSettingsNoValidPage');
        }

        $currentSettings['login_page_id'] = $loginPageId;
        update_option(self::OPTION_KEY_SETTINGS, $currentSettings);
        $this->redirect('settingsSettings', 'settingsSettingsUpdated');
    }

    public function registerSettings()
    {
        register_setting( 'options', 'fapiMemberApiEmail', [
            'type' => 'string',
            'description' => 'Fapi Member - API e-mail',
            'show_in_rest' => false,
            'default' => null,
        ]);
        register_setting( 'options', 'fapiMemberApiKey', [
            'type' => 'string',
            'description' => 'Fapi Member - API key',
            'show_in_rest' => false,
            'default' => null,
        ]);
    }

    public function addScripts()
    {
        $this->registerStyles();
        $this->registerScripts();
        global $pagenow;
        if ($pagenow === 'admin.php') {
            wp_enqueue_style('fapi-member-admin-font');
            wp_enqueue_style('fapi-member-admin');
            wp_enqueue_style('fapi-member-swal-css');
            wp_enqueue_script('fapi-member-swal');
            wp_enqueue_script('fapi-member-swal-promise-polyfill');
            wp_enqueue_script('fapi-member-main');
        }
        if ($pagenow === 'user-edit.php') {
            wp_enqueue_style('fapi-member-user-profile');
        }
    }

    public function addPublicScripts()
    {
        $this->registerPublicStyles();

        wp_enqueue_style('fapi-member-public-style');
    }

    public function addAdminMenu()
    {
        add_menu_page(
            'FAPI Member',
            'FAPI Member',
            self::REQUIRED_CAPABILITY,
            'fapi-member-options',
            [$this, 'constructAdminMenu'],
            '',
            81
        );
    }

    public function addUserProfileForm(WP_User $user)
    {
        $levels = $this->levels()->loadAsTerms();

        $memberships = $this->fapiMembershipLoader()->loadForUser($user->ID);
        $memberships = array_reduce($memberships, function($carry, $one) {
            $carry[$one->level] = $one;
            return $carry;
        }, []);
        $o[] =  '<h2>Členské sekce</h2>';



        foreach ($levels as $lvl) {
            if ($lvl->parent === 0) {
                $o[] = $this->tUserProfileOneSection($lvl, $levels, $memberships);
            }
        }

        echo join('', $o);
    }

    public function constructAdminMenu()
    {
        if ( !current_user_can( self::REQUIRED_CAPABILITY ) )  {
            wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
        }

        $subpage = $this->findSubpage();

        if (method_exists($this, sprintf('show%s', ucfirst($subpage)))) {
            call_user_func([$this, sprintf('show%s', ucfirst($subpage))]);
        }
    }

    public function findSubpage()
    {
        return (isset($_GET['subpage'])) ? $_GET['subpage'] : 'index';
    }

    protected function showIndex()
    {
        if (!$this->areApiCredentialsSet()) {
            $this->showTemplate('connection');
        }
        $this->showTemplate('index');
    }

    protected function showSettingsSectionNew()
    {
        $this->showTemplate('settingsSectionNew');
    }

    protected function showSettingsLevelNew()
    {
        $this->showTemplate('settingsLevelNew');
    }

    protected function showSettingsContentSelect()
    {
        $this->showTemplate('settingsContentSelect');
    }

    protected function showSettingsContentRemove()
    {
        $this->showTemplate('settingsContentRemove');
    }

    protected function showSettingsContentAdd()
    {
        $this->showTemplate('settingsContentAdd');
    }

    protected function showConnection()
    {
        $this->showTemplate('connection');
    }

    protected function showSettingsEmails()
    {
        $this->showTemplate('settingsEmails');
    }

    protected function showSettingsElements()
    {
        $this->showTemplate('settingsElements');
    }

    protected function showSettingsSettings()
    {
        $this->showTemplate('settingsSettings');
    }

    protected function showHelp()
    {
        $this->showTemplate('help');
    }

    protected function showSettingsPages()
    {
        $this->showTemplate('settingsPages');
    }

    protected function showTemplate($name)
    {
        $areApiCredentialsSet = $this->areApiCredentialsSet();
        $subpage = $this->findSubpage();

        $path = sprintf('%s/../templates/%s.php', __DIR__, $name);
        if (file_exists($path)) {
            include $path;
        }
    }

    protected function redirect($subpage, $e = null, $other = []) {
        $tail = '';
        foreach ($other as $key => $value) {
            $tail .= sprintf('&%s=%s', $key, urlencode($value));
        }
        if ($e === null) {
            wp_redirect(admin_url(sprintf('/admin.php?page=fapi-member-options&subpage=%s%s', $subpage, $tail)));
        } else {
            wp_redirect(admin_url(sprintf('/admin.php?page=fapi-member-options&subpage=%s&e=%s%s', $subpage, $e, $tail)));
        }
        exit;
    }

    public function areApiCredentialsSet()
    {
        return get_option(self::OPTION_KEY_API_CHECKED, false);
    }

    public function recheckApiCredentials()
    {
        return $this->fapiApi()->checkCredentials();
    }

    /**
     * @param WP_Term $level
     * @param WP_Term[] $levels
     * @param  FapiMembership[] $memberships
     *
     * @return string
     */
    private function tUserProfileOneSection(WP_Term $level, $levels, $memberships)
    {
        $lower = array_filter($levels, function($one) use ($level) {
            return $one->parent === $level->term_id;
        });
        $lowerHtml = [];
        foreach ($lower as $l) {
            $checked = (isset($memberships[$l->term_id])) ? 'checked' : '';
            if ($memberships[$l->term_id]->registered !== null) {
                $reg = $memberships[$l->term_id]->registered;
                $regDate = sprintf('value="%s"', $reg->format('Y-m-d'));
                $regTime = sprintf('value="%s"', $reg->format('H:i'));
            } else {
                $regDate = '';
                $regTime = '';
            }
            if ($memberships[$l->term_id]->until !== null) {
                $untilDate = sprintf('value="%s"', $memberships[$l->term_id]->until->format('Y-m-d'));
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
                        <label class="membershipUntil" for="Levels[%s][membershipUntil]">Členství do</label>
                        <input class="membershipUntilInput" type="date" name="Levels[%s][membershipUntil]" %s>
                        <label class="isUnlimited" for="Levels[%s][isUnlimited]">Je neomezené?</label>
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
                $untilDate,
                $l->term_id,
                $l->term_id,
                ($memberships[$l->term_id]->isUnlimited) ? 'checked':''
            );
        }

        $checked = (isset($memberships[$level->term_id])) ? 'checked' : '';
        $isUnlimited = (isset($memberships[$level->term_id]) && $memberships[$level->term_id]->isUnlimited) ? 'checked' : '';
        if ($memberships[$level->term_id]->registered !== null) {
            $reg = $memberships[$level->term_id]->registered;
            $regDate = sprintf('value="%s"', $reg->format('Y-m-d'));
            $regTime = sprintf('value="%s"', $reg->format('H:i'));
        } else {
            $regDate = '';
            $regTime = '';
        }
        if ($memberships[$level->term_id]->until !== null) {
            $untilDate = sprintf('value="%s"', $memberships[$level->term_id]->until->format('Y-m-d'));
        } else {
            $untilDate = '';
        }

        return '
        <table class="wp-list-table widefat fixed striped fapiMembership">
            <thead>
            <tr>
                <td id="cb" class="manage-column column-cb check-column">
                    <label class="screen-reader-text" for="Levels['.$level->term_id.'][check]">Vybrat</label>
                    <input id="Levels['.$level->term_id.'][check]" name="Levels['.$level->term_id.'][check]" type="checkbox" '. $checked .'>
                </td>
                <th scope="col" id="title" class="manage-column column-title column-primary">
                    <span>'.$level->name.'</span>
                </th>
                <th scope="col" class="manage-column fields">
                    <span class="a">Datum registrace</span>
                    <span class="b">
                    <input type="date" name="Levels['.$level->term_id.'][registrationDate]" '.$regDate.'>
                    </span>
                </th>
                <th scope="col" class="manage-column fields">
                    <span class="a">Čas registrace</span>
                    <span class="b">
                    <input type="time" name="Levels['.$level->term_id.'][registrationTime]" '.$regTime.'>
                    </span>
                </th>
                <th scope="col" class="manage-column fields">
                    <span class="a">Členství do</span>
                    <span class="b">
                    <input type="date" name="Levels['.$level->term_id.'][membershipUntil]" '.$untilDate.'>
                    </span>
                </th>
                <th scope="col" class="manage-column fields">
                    <span class="a">Je neomezené?</span>
                    <span class="b">
                    <input type="checkbox" name="Levels['.$level->term_id.'][isUnlimited]" '.$isUnlimited.'>
                    </span>
                </th>
            </thead>
        
            <tbody id="the-list">
                <tr><td colspan="6">
                    '. join('',$lowerHtml) .'
                </td></tr>
            </tbody>
        </table>
        ';
    }

    public function getSetting($key)
    {
        $o = get_option(self::OPTION_KEY_SETTINGS);
        if ($o === false) {
            $o = [];
        }
        return (isset($o[$key])) ? $o[$key] : null;
    }

    public function getAllMemberships()
    {
        // it looks utterly inefficient, but users meta should be loaded with get_users to cache
        $users = get_users(['fields' => ['ID']]);
        $memberships = [];
        foreach($users as $user){
            $memberships[$user->ID] = $this->fapiMembershipLoader()->loadForUser($user->ID);

        }
        return $memberships;
    }

    public function checkPage()
    {
        global $wp_query;
        if (!isset($wp_query->post) || !($wp_query->post instanceof WP_Post) || $wp_query->post->post_type !== 'page') {
            return;
        }
        $pageId = $wp_query->post->ID;
        $levelsToPages = $this->levels()->levelsToPages();
        $levelsForThisPage = [];
        foreach ($levelsToPages as $levelId => $pageIds) {
            if (in_array($pageId, $pageIds)) {
                $levelsForThisPage[] = $levelId;
            }
        }
        if (count($levelsForThisPage) === 0) {
            // page is not in any level, not protecting
            return;
        }

        // page is protected for users with membership

        if (!is_user_logged_in()) {
            // user not logged in
            // we do not know what level to choose, choosing first
            $firstLevel = $levelsForThisPage[0];
            $this->redirectToNoAccessPage($firstLevel);
        }

        // user is logged in
        if (current_user_can(self::REQUIRED_CAPABILITY)) {
            // admins can access anything
            return;
        }

        $memberships = $this->fapiMembershipLoader()->loadForUser(get_current_user_id());

        // Does user have membership for any level that page is in
        foreach ($memberships as $m) {
            if (in_array($m->level, $levelsForThisPage)) {
                return true;
            }
        }

        // no, he does not
        $firstLevel = $levelsForThisPage[0];
        $this->redirectToNoAccessPage($firstLevel);
    }

    protected  function redirectToNoAccessPage($levelId)
    {
        $otherPages = $this->levels()->loadOtherPagesForLevel($levelId, true);
        $noAccessPageId = (isset($otherPages['noAccess'])) ? $otherPages['noAccess'] : null;
        if ($noAccessPageId) {
            wp_redirect(get_permalink($noAccessPageId));
            exit;
        } else {
            wp_redirect(home_url());
            exit;
        }
    }

    public function checkIfLevelSelection()
    {
        $isFapiLevelSelection = (isset($_GET['fapi-level-selection']) && intval($_GET['fapi-level-selection']) === 1) ? true : false;
        if (!$isFapiLevelSelection) {
            return true;
        }
        $this->showLevelSelectionPage();
    }

    protected function showLevelSelectionPage()
    {
        $mem = $this->fapiMembershipLoader()->loadForUser(get_current_user_id());
        $pages = array_map(function($m) {
            $p =  $this->levels()->loadOtherPagesForLevel($m->level, true);
            return (isset($p['afterLogin'])) ? $p['afterLogin'] : null;
        }, $mem);
        $pages = array_unique(array_filter($pages));
        if (count($pages) === 0) {
            // no afterLogin page set anywhere
            return;
        }
        if (count($pages) === 1) {
            // exactly one afterLogin page
            $f = array_shift($pages);
            $page = get_post($f);
            wp_redirect(get_permalink($page));
            exit;
        }
        include(__DIR__ . '/../templates/levelSelection.php');
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
    public function loginRedirect($redirectTo, $request, $user)
    {
        if ((strpos($request, '?') !== false)) {
            if ((strpos($request, 'fapi-level-selection') !== false)) {
                return $request;
            }
            return $request . '&fapi-level-selection=1';
        } else {
            return $request . '?fapi-level-selection=1';
        }
    }

    protected function createOrProlongMembership($email, $levelId, $days, $isUnlimited, &$props)
    {
        $user = get_user_by('email', $email);
        if ($user === false) {
            return;
        }
        $fm = new FapiMembershipLoader($this->levels());
        $memberships = $fm->loadForUser($user->ID);
        $membershipKey = null;
        foreach ($memberships as $k => $m) {
            /** @var FapiMembership $m */
            if ($m->level === $levelId) {
                $membershipKey = $k;
                break;
            }
        }

        if ($membershipKey !== null) {
            // level is there, we are prolonging
            /** @var FapiMembership $levelMembership */
            $levelMembership = $memberships[$membershipKey];
            if (!$levelMembership->isUnlimited) {
                $props['membership_prolonged'] = true;
                $props['membership_prolonged_level'] = $levelId;
                $wasUnlimitedBefore = false;
            } else {
                $wasUnlimitedBefore = true;
            }
            if ($isUnlimited || $levelMembership->isUnlimited) {
                $levelMembership->isUnlimited = true;
                if (!$wasUnlimitedBefore) {
                    $props['membership_prolonged_to_unlimited'] = true;
                }
            } else {
                $levelMembership->until = $levelMembership->until->modify(sprintf('+ %s days', $days));
                $props['membership_prolonged_days'] = $days;
                $props['membership_prolonged_until'] = $levelMembership->until;
            }
            $this->fapiMembershipLoader()->saveMembershipToHistory($user->ID, $levelMembership);
            $this->fapiMembershipLoader()->saveForUser($user->ID, $memberships);

        } else {
            // new level membership
            $props['membership_level_added'] = true;
            $props['membership_level_added_level'] = $levelId;
            $levelTerm = $this->levels()->loadById($levelId);
            if ($levelTerm->parent === 0) {
                $props['membership_level_added_is_section'] = true;
            } else {
                $props['membership_level_added_is_section'] = false;
            }
            $props['did_user_had_this_level_before'] = $this->fapiMembershipLoader()->didUserHadLevelMembershipBefore(
                $user->ID,
                $levelId
            );

            $registered = new DateTime();
            if ($isUnlimited) {
                $props['membership_level_added_unlimited'] = true;
                $until = null;
            } else {
                $until = new DateTime();
                $until->modify(sprintf('+ %s days', $days));
                $props['membership_level_added_until'] = $until;
                $props['membership_level_added_days'] = $days;
            }
            $new = new FapiMembership($levelId, $registered, $until, $isUnlimited);
            $memberships[] = $new;
            $this->fapiMembershipLoader()->saveMembershipToHistory($user->ID, $new);
            $this->fapiMembershipLoader()->saveForUser($user->ID, $memberships);
        }
        return true;
    }

    protected function sendEmail($email, $type, $levelId, $props)
    {
        $emails = $this->levels()->loadEmailTemplatesForLevel($levelId, true);
        if (count($emails) === 0) {
            // No emails defined
            return false;
        }
        if (!isset($emails[$type])) {
            // No emails of this type defined
            return false;
        }

        $subject = $emails[$type]['s'];
        $body = $emails[$type]['b'];
        $subject = $this->applyEmailShortcodes($subject, $props);
        $body = $this->applyEmailShortcodes($body, $props);

        return wp_mail($email, $subject, $body);
    }

    protected function applyEmailShortcodes($text, $props)
    {
        $map = [
            '%%SEKCE%%' => function($props) {
                if (isset($props['membership_prolonged_level_name'])) {
                    return $props['membership_prolonged_level_name'];
                } elseif (isset($props['membership_level_added_level_name'])) {
                    return $props['membership_level_added_level_name'];
                } else {
                    return '';
                }
            },
            '%%UROVEN%%' => function($props) {
                if (isset($props['membership_child_level_added_level_name'])) {
                    return $props['membership_child_level_added_level_name'];
                } else {
                    return '';
                }
            },
            '%%DNI%%'=> function($props) {
                if (isset($props['membership_prolonged_days'])) {
                    return $props['membership_prolonged_days'];
                } elseif (isset($props['membership_level_added_days'])) {
                    return $props['membership_level_added_days'];
                } elseif (isset($props['membership_prolonged_to_unlimited']) || isset($props['membership_level_added_unlimited'])) {
                    return 'neomezeně';
                } else {
                    return '';
                }
            },
            '%%CLENSTVI_DO%%'=> function($props) {
                if (isset($props['membership_prolonged_until'])) {
                    return $props['membership_prolonged_until']->format('j. n. Y H:i');
                } elseif (isset($props['membership_level_added_until'])) {
                    return $props['membership_level_added_until']->format('j. n. Y H:i');
                } elseif (isset($props['membership_prolonged_to_unlimited']) || isset($props['membership_level_added_unlimited'])) {
                    return 'neomezené';
                } else {
                    return '';
                }
            },
            '%%PRIHLASENI_ODKAZ%%' => function($props) {
                return $props['login_link_url'];
            },
            '%%PRIHLASOVACI_JMENO%%' => function($props) {
                if (isset($props['login'])) {
                    return $props['login'];
                } else {
                    return '';
                }
            },
            '%%HESLO%%' => function($props) {
                if (isset($props['password'])) {
                    return $props['password'];
                } else {
                    return '';
                }
            }
        ];

        foreach ($map as $key => $func) {
            $text = str_replace($key, $func($props), $text);
        }

        return $text;
    }
}