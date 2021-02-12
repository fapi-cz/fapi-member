<?php


class FapiMemberPlugin
{

    private $errorBasket = [];

    public function __construct()
    {
        $this->registerStyles();
        $this->registerScripts();
    }

    public static function isDevelopment()
    {
        $s = $_SERVER['SERVER_NAME'];
        return ($s === 'localhost');
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
            // user profile save
        add_action( 'edit_user_profile_update', [$this, 'handleUserProfileSave'] );
    }

    public function showError($type, $message)
    {
            add_action( 'admin_notices', function($e) {
                printf('<div class="notice notice-%s is-dismissible"><p>%s</p></div>', $e[0], $e[1]);
            });
    }

    public function registerStyles()
    {
        $p = plugins_url( 'fapi-member/media/fapi-member.css' );
        wp_register_style( 'fapi-member-admin', $p);
        $p = plugins_url( 'fapi-member/media/fapi-user-profile.css' );
        wp_register_style( 'fapi-member-user-profile', $p);
        $p = plugins_url( 'fapi-member/media/font/stylesheet.css' );
        wp_register_style( 'fapi-member-admin-font', $p);
        $p = plugins_url( 'fapi-member/node_modules/sweetalert2/dist/sweetalert2.min.css' );
        wp_register_style( 'fapi-member-swal-css', $p);
        $p = plugins_url( 'fapi-member/media/fapi-member-public.css' );
        wp_register_style( 'fapi-member-public-style', $p);
    }

    public function registerScripts()
    {
        $p = plugins_url( 'fapi-member/node_modules/sweetalert2/dist/sweetalert2.js' );
        wp_register_script( 'fapi-member-swal', $p);
        $p = plugins_url( 'fapi-member/node_modules/promise-polyfill/dist/polyfill.min.js');
        wp_register_script( 'fapi-member-swal-promise-polyfill', $p);
        if (self::isDevelopment()) {
            $p = plugins_url( 'fapi-member/media/dist/fapi.dev.js' );
        } else {
            $p = plugins_url( 'fapi-member/media/dist/fapi.dist.js' );
        }
        wp_register_script( 'fapi-member-main', $p);
    }

    public function registerLevelsTaxonomy()
    {
        register_taxonomy('fapi_levels', 'page', [
            'public' => true, //TODO: change
            'hierarchical' => true,
            'show_ui' => true, //TODO: change
            'show_in_rest' => false,
        ]);
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
        $t = get_terms(
            [
                'taxonomy' => 'fapi_levels',
                'hide_empty' => false,
            ]
        );
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
        return null;
    }

    public function handleApiCredentialsSubmit()
    {
        $this->verifyNonce('fapi_member_api_credentials_submit_nonce');

        $apiEmail = (isset($_POST['fapiMemberApiEmail']) && !empty($_POST['fapiMemberApiEmail'])) ? $_POST['fapiMemberApiEmail'] : null;
        $apiKey = (isset($_POST['fapiMemberApiKey']) && !empty($_POST['fapiMemberApiKey'])) ? $_POST['fapiMemberApiKey'] : null;

        if ($apiKey === null || $apiEmail === null) {
            $this->redirect('connection', 'apiFormEmpty');
        }

        //TODO: api request - verify

        update_option('fapiMemberApiEmail', $apiEmail);
        update_option('fapiMemberApiKey', $apiKey);

        $this->redirect('connection', 'apiFormSuccess');

    }

    public function handleUserProfileSave($userId)
    {
        if ( empty( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'update-user_' . $userId ) ) {
            return;
        }

        if ( !current_user_can( 'edit_user', $userId ) ) {
            return false;
        }

        $data = $_POST['Levels'];

        $memberships = [];
        $fl = new FapiLevels();
        $levels = $fl->loadAsTerms();
        $levels = array_reduce($levels, function($carry, $one) {
            $carry[$one->term_id] = $one;
            return $carry;
        }, []);

        foreach ($data as $id => $inputs) {
            if (isset($inputs['check']) && $inputs['check'] === 'on') {
                if ($levels[$id]->parent === 0) {
                    if (isset($inputs['registrationDate']) && isset($inputs['registrationTime']) && isset($inputs['membershipUntil'])) {

                        $reg = \DateTime::createFromFormat('Y-m-d\TH:i', $inputs['registrationDate'] . 'T' . $inputs['registrationTime']);
                        $mem = \DateTime::createFromFormat('Y-m-d\TH:i:s', $inputs['membershipUntil'] . 'T23:59:59');
                        if ($mem && $reg) {
                            $memberships[] = [
                                'level' => $id,
                                'registered' => $reg->format('Y-m-d\TH:i:s'),
                                'until' => $mem->format('Y-m-d\TH:i:s'),
                            ];
                        }
                    }
                } else {
                    $memberships[] = ['level' => $id];
                }
            }
        }

        update_user_meta( $userId, 'fapi_user_memberships', $memberships );
    }

    protected function verifyNonce($key)
    {
        if(
            !isset( $_POST[$key] )
            ||
            !wp_verify_nonce($_POST[$key], $key)
        ) {
            wp_die('Zabezpečení formuláře neumožnilo zpracování, zkuste obnovit stránku a odeslat znovu.');
        }
    }

    public function handleNewSection()
    {
        $this->verifyNonce('fapi_member_new_section_nonce');

        $name = (isset($_POST['fapiMemberSectionName']) && !empty($_POST['fapiMemberSectionName'])) ? $_POST['fapiMemberSectionName'] : null;

        if ($name === null ) {
            $this->redirect('settingsSectionNew', 'sectionNameEmpty');
        }

        wp_insert_term( $name, 'fapi_levels');

        $this->redirect('settingsSectionNew');

    }

    public function handleNewLevel()
    {
        $this->verifyNonce('fapi_member_new_level_nonce');

        $name = (isset($_POST['fapiMemberLevelName']) && !empty($_POST['fapiMemberLevelName'])) ? $_POST['fapiMemberLevelName'] : null;
        $parentId = (isset($_POST['fapiMemberLevelParent']) && !empty($_POST['fapiMemberLevelParent'])) ? $_POST['fapiMemberLevelParent'] : null;

        if ($name === null || $parentId === null) {
            $this->redirect('settingsLevelNew', 'levelNameOrParentEmpty');
        }

        $parent = get_term($parentId, 'fapi_levels');
        if ($parent === null) {
            $this->redirect('settingsLevelNew', 'sectionNotFound');
        }

        // check parent
        wp_insert_term( $name, 'fapi_levels', ['parent' => $parentId]);

        $this->redirect('settingsLevelNew');

    }

    public function handleAddPages()
    {
        $this->verifyNonce('fapi_member_add_pages_nonce');

        $levelId = (isset($_POST['level_id']) && !empty($_POST['level_id'])) ? $_POST['level_id'] : null;
        $toAdd = (isset($_POST['toAdd']) && !empty($_POST['toAdd'])) ? $_POST['toAdd'] : null;

        if ($levelId === null || $toAdd === null) {
            $this->redirect('settingsContentAdd', 'levelIdOrToAddEmpty');
        }

        $parent = get_term($levelId, 'fapi_levels');
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
        $this->verifyNonce('fapi_member_remove_pages_nonce');

        $levelId = (isset($_POST['level_id']) && !empty($_POST['level_id'])) ? $_POST['level_id'] : null;
        $toRemove = (isset($_POST['toRemove']) && !empty($_POST['toRemove'])) ? $_POST['toRemove'] : null;

        if ($levelId === null || $toRemove === null) {
            $this->redirect('settingsContentRemove', 'levelIdOrToAddEmpty');
        }

        $parent = get_term($levelId, 'fapi_levels');
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
        $this->verifyNonce('fapi_member_remove_level_nonce');

        $id = (isset($_POST['level_id']) && !empty($_POST['level_id'])) ? $_POST['level_id'] : null;

        if ($id === null) {
            $this->redirect('settingsSectionNew');
        }

        // check parent
        wp_delete_term($id, 'fapi_levels');

        $this->redirect('settingsLevelNew', 'removeLevelSuccessful');

    }

    public function handleEditLevel()
    {
        $this->verifyNonce('fapi_member_edit_level_nonce');

        $id = (isset($_POST['level_id']) && !empty($_POST['level_id'])) ? $_POST['level_id'] : null;
        $name = (isset($_POST['name']) && !empty($_POST['name'])) ? $_POST['name'] : null;

        if ($id === null || $name === null) {
            $this->redirect('settingsSectionNew', 'editLevelNoName');
        }
        wp_update_term($id, 'fapi_levels', ['name' => $name]);

        $this->redirect('settingsLevelNew', 'editLevelSuccessful');
    }

    public function handleEditEmail()
    {
        $this->verifyNonce('fapi_member_edit_email_nonce');

        $levelId = (isset($_POST['level_id']) && !empty($_POST['level_id'])) ? (int)$_POST['level_id'] : null;
        $emailType = (isset($_POST['email_type']) && !empty($_POST['email_type'])) ? $_POST['email_type'] : null;
        $mailSubject = (isset($_POST['mail_subject']) && !empty($_POST['mail_subject'])) ? $_POST['mail_subject'] : null;
        $mailBody = (isset($_POST['mail_body']) && !empty($_POST['mail_body'])) ? $_POST['mail_body'] : null;

        if ($mailSubject === null || $mailBody === null) {
            // remove mail template
            delete_term_meta($levelId, 'fapi_email_'.$emailType);
            $this->redirect('settingsEmails', 'editMailsRemoved', ['level' => $levelId]);
        }

        update_term_meta($levelId, 'fapi_email_'.$emailType, ['s' => $mailSubject, 'b' => $mailBody]);

        $this->redirect('settingsEmails', 'editMailsUpdated', ['level' => $levelId]);
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
        global $pagenow;
        if ($pagenow === 'options-general.php') {
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
        wp_enqueue_style('fapi-member-public-style');
    }

    public function addAdminMenu()
    {
        add_options_page( 'Fapi Member', 'Fapi Member', 'manage_options', 'fapi-member-options', [$this, 'constructAdminMenu'] );

    }

    public function addUserProfileForm(WP_User $user)
    {
        $fl = new FapiLevels();
        $levels = $fl->loadAsTerms();

        $memberships = get_user_meta($user->ID, 'fapi_user_memberships', true);
        $memberships = $this->removeMembershipsToRemovedLevels($user->ID, $memberships, $levels);
        $memberships = array_reduce($memberships, function($carry, $one) {
            $carry[$one['level']] = $one;
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
        if ( !current_user_can( 'manage_options' ) )  {
            wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
        }

        $subpage = $this->findSubpage();

        if (method_exists($this, sprintf('show%s', ucfirst($subpage)))) {
            call_user_func([$this, sprintf('show%s', ucfirst($subpage))]);
        }
    }

    protected function findSubpage()
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

    protected function showHelp()
    {
        $this->showTemplate('help');
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
            wp_redirect(admin_url(sprintf('/options-general.php?page=fapi-member-options&subpage=%s%s', $subpage, $tail)));
        } else {
            wp_redirect(admin_url(sprintf('/options-general.php?page=fapi-member-options&subpage=%s&e=%s%s', $subpage, $e, $tail)));
        }
        exit;
    }

    protected function areApiCredentialsSet()
    {
        $apiEmail = get_option('fapiMemberApiEmail', null);
        $apiKey = get_option('fapiMemberApiKey', null);
        if ($apiKey && $apiEmail && !empty($apiKey) && !empty($apiEmail)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param WP_Term $level
     * @param WP_Term[] $levels
     * @param  array $memberships
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
            $lowerHtml[] = sprintf(
                '
                    <span class="oneLevel">
                        <input type="checkbox" name="Levels[%s][check]" id="Levels[%s][check]" %s>
                        <label for="Levels[%s][check]">%s</label>
                    </span>
                    ',
                $l->term_id,
                $l->term_id,
                $checked,
                $l->term_id,
                $l->name
            );
        }

        $checked = (isset($memberships[$level->term_id])) ? 'checked' : '';
        if (isset($memberships[$level->term_id]['registered'])) {
            $reg = \DateTime::createFromFormat('Y-m-d\TH:i:s', $memberships[$level->term_id]['registered']);
            $regDate = sprintf('value="%s"', $reg->format('Y-m-d'));
            $regTime = sprintf('value="%s"', $reg->format('H:i'));
        } else {
            $regDate = '';
            $regTime = '';
        }
        if (isset($memberships[$level->term_id]['until'])) {
            $reg = \DateTime::createFromFormat('Y-m-d\TH:i:s', $memberships[$level->term_id]['until']);
            $untilDate = sprintf('value="%s"', $reg->format('Y-m-d'));
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
            </thead>
        
            <tbody id="the-list">
                <tr><td colspan="5">
                    '. join('',$lowerHtml) .'
                </td></tr>
            </tbody>
        </table>
        ';
    }

    protected function removeMembershipsToRemovedLevels($userId, $memberships, $levels)
    {
        $updated = false;
        $new = [];
        $levelIds = array_reduce($levels, function($carry, $one) {
            $carry[] = $one->term_id;
            return $carry;
        }, []);
        foreach ($memberships as $m) {
            if (in_array($m['level'], $levelIds)) {
                $new[] = $m;
            } else {
                $updated = true;
            }
        }
        if ($updated) {
            update_user_meta( $userId, 'fapi_user_memberships', $new );
        }
        return $new;
    }
}