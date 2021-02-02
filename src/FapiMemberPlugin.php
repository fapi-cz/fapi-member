<?php


class FapiMemberPlugin
{

    private $errorBasket = [];

    public function __construct()
    {
        $this->registerStyle();
    }

    public function addHooks()
    {
        add_action('admin_menu', [$this, 'addAdminMenu'] );
        add_action('admin_enqueue_scripts', [$this, 'addScripts'] );
        add_action('admin_init', [$this, 'registerSettings']);
        add_action('admin_post_fapi_member_api_credentials_submit', [$this, 'handleApiCredentialsSubmit']);
    }

    public function showError($type, $message)
    {
            add_action( 'admin_notices', function($e) {
                printf('<div class="notice notice-%s is-dismissible"><p>%s</p></div>', $e[0], $e[1]);
            });
    }

    public function registerStyle()
    {
        $p = plugins_url( 'fapi-member/media/fapi-member.css' );
        wp_register_style( 'fapi-member-admin', $p);
    }

    public function handleApiCredentialsSubmit()
    {
        if(
            !isset( $_POST['fapi_member_api_credentials_submit_nonce'] )
            ||
            !wp_verify_nonce($_POST['fapi_member_api_credentials_submit_nonce'], 'fapi_member_api_credentials_submit_nonce')
        ) {
            wp_die('Zabezpečení formuláře neumožnilo zpracování, zkuste obnovit stránku a odeslat znovu.');
        }

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
            wp_enqueue_style('fapi-member-admin');
        }
    }

    public function addAdminMenu()
    {
        add_options_page( 'Fapi Member', 'Fapi Member', 'manage_options', 'fapi-member-options', [$this, 'constructAdminMenu'] );

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

    protected function showSettings()
    {
        $this->showTemplate('settings');
    }

    protected function showSettingsContent()
    {
        $this->showTemplate('settingsContent');
    }

    protected function showConnection()
    {
        $this->showTemplate('connection');
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

    protected function redirect($subpage, $e = null) {
        if ($e === null) {
            wp_redirect(admin_url(sprintf('/options-general.php?page=fapi-member-options&subpage=%s', $subpage)));
        } else {
            wp_redirect(admin_url(sprintf('/options-general.php?page=fapi-member-options&subpage=%s&e=%s', $subpage, $e)));
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
}