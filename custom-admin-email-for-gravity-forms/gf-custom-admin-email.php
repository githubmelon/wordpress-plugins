<?php
/**
 * Plugin Name:       Custom Admin Email for Gravity Forms
 * Plugin URI:        https://watermelonwebworks.com/gravity-forms-custom-admin-email/
 * Description:       Adds a new settings tab to Gravity Forms to globally override the {admin_email} merge tag destination. Includes multisite support.
 * Version:           1.0.1
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Watermelon Web Works, LLC
 * Author URI:        https://watermelonwebworks.com/
 * License:           GPL-2.0+
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       custom-admin-email-for-gravity-forms
 * Network:           true
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * Register the Add-On on 'init' to comply with WP 6.7+ requirements.
 * Loading translations earlier than this triggers a PHP notice.
 */
add_action( 'init', 'load_gf_custom_admin_email_addon', 10 );

function load_gf_custom_admin_email_addon() {
    // Ensure Gravity Forms is active and the framework is ready.
    if ( ! class_exists( 'GFForms' ) || ! method_exists( 'GFForms', 'include_addon_framework' ) ) {
        return;
    }

    GFForms::include_addon_framework();

    class GFCustomAdminEmailAddOn extends GFAddOn {

        protected $_version = '1.0.1';
        protected $_min_gravityforms_version = '2.5';
        protected $_slug = 'custom-admin-email-for-gravity-forms';
        protected $_path = 'custom-admin-email-for-gravity-forms/custom-admin-email-for-gravity-forms.php';
        protected $_full_path = __FILE__;
        protected $_title = '';
        protected $_short_title = '';

        private static $_instance = null;

        /**
         * Singleton instance.
         */
        public static function get_instance() {
            if ( self::$_instance == null ) {
                self::$_instance = new GFCustomAdminEmailAddOn();
            }
            return self::$_instance;
        }

        /**
         * Setup titles inside the constructor. Safe here because we are on 'init'.
         */
        public function __construct() {
            $this->_title       = esc_html__( 'Global Admin Email Override', 'custom-admin-email-for-gravity-forms' );
            $this->_short_title = esc_html__( 'Admin Email', 'custom-admin-email-for-gravity-forms' );
            parent::__construct();
        }

        /**
         * Initialize hooks.
         */
        public function init() {
            parent::init();
            
            // Intercept notifications before they are sent.
            add_filter( 'gform_notification', array( $this, 'override_admin_email_notification' ), 10, 3 );
            
            // Add a settings link to the Plugins page.
            add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'plugin_settings_link' ), 10, 2 );
        }

        /**
         * Add "Settings" link next to Deactivate/Activate on the Plugins page.
         * FIXED: Signature updated to include $file to match GFAddOn parent method.
         */
        public function plugin_settings_link( $links, $file ) {
            $settings_url = admin_url( 'admin.php?page=gf_settings&subview=' . $this->_slug );
            $settings_link = '<a href="' . esc_url( $settings_url ) . '">' . esc_html__( 'Settings', 'custom-admin-email-for-gravity-forms' ) . '</a>';
            array_unshift( $links, $settings_link );
            return $links;
        }

        /**
         * Define the settings page layout.
         */
        public function plugin_settings_fields() {
            return array(
                array(
                    'title'  => esc_html__( 'Custom Admin Email Setting', 'custom-admin-email-for-gravity-forms' ),
                    'description' => wp_kses_post( __( 'Enter the email address to replace the <strong>{admin_email}</strong> merge tag. On Multisite, a Network-wide setting will take priority if defined.', 'custom-admin-email-for-gravity-forms' ) ),
                    'fields' => array(
                        array(
                            'name'              => 'custom_admin_email',
                            'label'             => esc_html__( 'Gravity Forms Admin Email', 'custom-admin-email-for-gravity-forms' ),
                            'type'              => 'text',
                            'class'             => 'large',
                            'default_value'     => get_option( 'admin_email' ),
                            'feedback_callback' => array( $this, 'validate_email_setting' ), // Server-side validation.
                        )
                    )
                )
            );
        }

        /**
         * Validate the email before saving to database.
         */
        public function validate_email_setting( $value ) {
            if ( ! empty( $value ) && ! is_email( $value ) ) {
                $this->set_field_error( array( 'name' => 'custom_admin_email' ), esc_html__( 'Please enter a valid email address.', 'custom-admin-email-for-gravity-forms' ) );
                return false;
            }
            return true;
        }

        /**
         * Retrieves the override email, prioritizing Multisite Network options.
         */
        private function get_override_email() {
            $email = '';

            // 1. Check for Network-wide override if on Multisite.
            if ( is_multisite() ) {
                $network_email = get_site_option( 'gf_global_admin_email_override' );
                if ( ! empty( $network_email ) && is_email( $network_email ) ) {
                    $email = $network_email;
                }
            }

            // 2. If no network setting, use the site-specific Add-On setting.
            if ( empty( $email ) ) {
                $email = $this->get_plugin_setting( 'custom_admin_email' );
            }

            return sanitize_email( $email ); // Final sanitization.
        }

        /**
         * Swap {admin_email} in notification fields.
         */
        public function override_admin_email_notification( $notification, $form, $entry ) {
            $custom_email = $this->get_override_email();

            // Do nothing if the email is invalid or empty.
            if ( empty( $custom_email ) || ! is_email( $custom_email ) ) {
                return $notification;
            }

            // Define which notification fields to process.
            $target_fields = array( 'to', 'from', 'replyTo', 'bcc' );

            foreach ( $target_fields as $field ) {
                if ( ! empty( $notification[ $field ] ) ) {
                    // Perform the case-sensitive string replacement.
                    $notification[ $field ] = str_replace( '{admin_email}', $custom_email, $notification[ $field ] );
                }
            }

            return $notification;
        }
    }

    // Register the Add-On with Gravity Forms.
    GFAddOn::register( 'GFCustomAdminEmailAddOn' );
}