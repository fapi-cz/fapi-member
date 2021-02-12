<?php


class FapiMemberPlugin
{
    private $errorBasket = [];
    private $fapiLevels = null;

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

    public function levels()
    {
        if ($this->fapiLevels === null) {
            $this->fapiLevels = new FapiLevels();
        }
        return $this->fapiLevels;
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
        add_action('admin_post_fapi_member_set_other_page', [$this, 'handleSetOtherPage']);
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
        return null;
    }

    public function handleApiCredentialsSubmit()
    {
        $this->verifyNonce('api_credentials_submit');

        $apiEmail = $this->input('fapiMemberApiEmail');
        $apiKey = $this->input('fapiMemberApiKey');

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
        if (!current_user_can('edit_user', $userId)) { return false; }

        $data = $_POST['Levels'];

        $memberships = [];
        $levels = $this->levels()->loadAsTerms();
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

    protected function verifyNonce($hook)
    {
        $nonce = sprintf('fapi_member_%s_nonce', $hook);
        if(
            !isset( $_POST[$nonce] )
            ||
            !wp_verify_nonce($_POST[$nonce], $nonce)
        ) {
            wp_die('Zabezpečení formuláře neumožnilo zpracování, zkuste obnovit stránku a odeslat znovu.');
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
        $this->verifyNonce('new_section');

        $name = $this->input('fapiMemberSectionName');

        if ($name === null ) {
            $this->redirect('settingsSectionNew', 'sectionNameEmpty');
        }

        $this->levels()->insert($name);

        $this->redirect('settingsSectionNew');
    }

    public function handleNewLevel()
    {
        $this->verifyNonce('new_level');

        $name = $this->input('fapiMemberLevelName');
        $parentId = $this->input('fapiMemberLevelParent', 'int');

        if ($name === null || $parentId === null) {
            $this->redirect('settingsLevelNew', 'levelNameOrParentEmpty');
        }

        $parent = get_term($parentId, 'fapi_levels');
        if ($parent === null) {
            $this->redirect('settingsLevelNew', 'sectionNotFound');
        }

        // check parent
        $this->levels()->insert($name, $parentId);

        $this->redirect('settingsLevelNew');

    }

    public function handleAddPages()
    {
        $this->verifyNonce('add_pages');

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
        $this->verifyNonce('remove_pages');

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
        $this->verifyNonce('remove_level');

        $id = $this->input('level_id', 'int');

        if ($id === null) {
            $this->redirect('settingsSectionNew');
        }

        $this->levels()->remove($id);

        $this->redirect('settingsLevelNew', 'removeLevelSuccessful');
    }

    public function handleEditLevel()
    {
        $this->verifyNonce('edit_level');

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
        $this->verifyNonce('edit_email');

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
        $this->verifyNonce('set_other_page');

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
        $levels = $this->levels()->loadAsTerms();

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
            wp_redirect(admin_url(sprintf('/options-general.php?page=fapi-member-options&subpage=%s%s', $subpage, $tail)));
        } else {
            wp_redirect(admin_url(sprintf('/options-general.php?page=fapi-member-options&subpage=%s&e=%s%s', $subpage, $e, $tail)));
        }
        exit;
    }

    public function areApiCredentialsSet()
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