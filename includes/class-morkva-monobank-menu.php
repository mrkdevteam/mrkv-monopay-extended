<?php
/**
 * Class for add monobank to wordpress menu
 * 
 * */
Class MorkvaMonopayMenu
{
    /**
     * Slug for page in Woo Tab Sections
     * 
     * */
    public $slug = 'admin.php?page=wc-settings&tab=checkout&section=morkva-monopay';

    /**
     * Constructor for create menu
     * 
     * */
    public function __construct()
    {
        # Add menu
        add_action('admin_menu', array($this, 'mrkv_mono_register_admin_menu'));
    }

    /**
     * Register menu page
     * 
     * */
    public function mrkv_mono_register_admin_menu()
    {
        # Add menu Monopay
        add_menu_page('Morkva Monopay', 'Morkva Monopay', 'manage_options', $this->slug, false, plugin_dir_url(__DIR__) . 'assets/images/morkva-monopay-logo.svg', 26);
    }
}