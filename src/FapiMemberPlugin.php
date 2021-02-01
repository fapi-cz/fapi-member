<?php


class FapiMemberPlugin
{
    public function __construct()
    {
        $this->registerStyle();
    }

    public function addHooks()
    {
        add_action( 'admin_menu', [$this, 'addAdminMenu'] );
        add_action( 'admin_enqueue_scripts', [$this, 'addScripts'] );
    }

    public function registerStyle()
    {
        $p = plugins_url( 'fapi-member/media/fapi-member.css' );
        wp_register_style( 'fapi-member-admin', $p);
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

        $this->showTemplate('connection');
    }

    protected function showTemplate($name)
    {
        $path = sprintf('%s/../templates/%s.php', __DIR__, $name);
        if (file_exists($path)) {
            include $path;
        }
    }
}