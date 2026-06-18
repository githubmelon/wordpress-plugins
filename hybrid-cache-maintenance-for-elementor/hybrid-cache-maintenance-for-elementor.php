<?php
/**
 * Plugin Name:       Hybrid Cache Maintenance & CSS Healer for Elementor
 * Plugin URI:        https://watermelonwebworks.com/plugins/hybrid-cache-maintenance-elementor/
 * Description:       Clears Elementor cache, purges popular caching layers (LiteSpeed, WP Rocket, W3TC, etc.) every 12 hours, warms the homepage, and heals missing CSS via 404 interception.
 * Version:           2.1.0
 * Author:            Watermelon Web Works, LLC
 * Author URI:        https://watermelonwebworks.com/
 * License:           GPL-2.0+
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       hybrid-cache-maintenance-for-elementor
 * Requires at least: 5.8
 * Requires PHP:      7.4
 * Tested up to:      7.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

// Global Configuration Constants.
define( 'WM_ECH_CRON_HOOK', 'wm_ech_elementor_clear_cache' );
define( 'WM_ECH_CRON_SCHEDULE', 'wm_ech_every_12_hours' );
define( 'WM_ECH_CRON_INTERVAL', 43200 ); // 12 Hours in seconds.

/**
 * Lightweight internal logger abstraction.
 */
if ( ! defined( 'WM_ECH_DEBUG' ) ) {
    define( 'WM_ECH_DEBUG', false );
}

function wm_ech_log( $message ) {
    if ( ! WM_ECH_DEBUG ) {
        return;
    }
    // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
    error_log( '[WM_ECH_HYBRID] ' . $message );
}

/**
 * 1) Dependency Verification Mechanism.
 * Ensures Elementor is active before allowing the plugin functionality to boot.
 */
add_action( 'admin_init', 'wm_ech_check_dependencies' );
function wm_ech_check_dependencies() {
    if ( ! did_action( 'elementor/loaded' ) ) {
        add_action( 'admin_notices', 'wm_ech_missing_elementor_notice' );
        return false;
    }
    return true;
}

/**
 * Render a graceful error notice if requirements are unfulfilled.
 */
function wm_ech_missing_elementor_notice() {
    $class   = 'notice notice-error is-dismissible';
    $message = __( 'Hybrid Cache Maintenance & CSS Healer requires Elementor to be installed and active.', 'hybrid-cache-maintenance-for-elementor' );
    printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) );
}

/**
 * 2) Register Custom 12-Hour Cron Interval.
 */
add_filter( 'cron_schedules', 'wm_ech_add_cron_schedule' );
function wm_ech_add_cron_schedule( $schedules ) {
    if ( ! isset( $schedules[ WM_ECH_CRON_SCHEDULE ] ) ) {
        $schedules[ WM_ECH_CRON_SCHEDULE ] = array(
            'interval' => WM_ECH_CRON_INTERVAL,
            'display'  => esc_html__( 'Every 12 Hours', 'hybrid-cache-maintenance-for-elementor' ),
        );
    }
    return $schedules;
}

/**
 * Idempotent scheduling helper engine.
 */
function wm_ech_schedule_event() {
    if ( ! did_action( 'elementor/loaded' ) ) {
        return;
    }
    if ( wp_next_scheduled( WM_ECH_CRON_HOOK ) ) {
        return;
    }
    wp_schedule_event( time(), WM_ECH_CRON_SCHEDULE, WM_ECH_CRON_HOOK );
}

/**
 * 3) Lifecycle Activation Setup Hooks.
 */
register_activation_hook( __FILE__, 'wm_ech_activate_plugin' );
function wm_ech_activate_plugin() {
    add_filter( 'cron_schedules', 'wm_ech_add_cron_schedule' );
    wm_ech_schedule_event();
}

/**
 * Admin Init Fallback Integrity Verification.
 */
add_action( 'admin_init', 'wm_ech_schedule_event' );

/**
 * 4) Lifecycle Deactivation Cleanup Hooks.
 */
register_deactivation_hook( __FILE__, 'wm_ech_deactivate_plugin' );
function wm_ech_deactivate_plugin() {
    $timestamp = wp_next_scheduled( WM_ECH_CRON_HOOK );
    while ( $timestamp ) {
        wp_unschedule_event( $timestamp, WM_ECH_CRON_HOOK );
        $timestamp = wp_next_scheduled( WM_ECH_CRON_HOOK );
    }
}

/**
 * 5) Master Cron Automation Callback Core.
 * Clears Elementor and loops through all major caching platforms dynamically.
 */
add_action( WM_ECH_CRON_HOOK, 'wm_ech_execute_maintenance_clear' );
function wm_ech_execute_maintenance_clear() {
    if ( ! did_action( 'elementor/loaded' ) ) {
        return;
    }

    wm_ech_log( 'Maintenance Cron automated processing triggered.' );

    // Force Elementor file flush.
    try {
        \Elementor\Plugin::instance()->files_manager->clear_cache();
        wm_ech_log( 'Elementor Asset Files Engine Cache cleared.' );
    } catch ( \Throwable $e ) {
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
            error_log( '[WM_ECH_HYBRID_ERR] Elementor flush failure: ' . $e->getMessage() );
        }
    }

    // Dynamic Multi-Cache Clearing Environment Matrix.
    
    // Option A: LiteSpeed Cache Engine
    if ( defined( 'LSCWP_V' ) ) {
        // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
        do_action( 'litespeed_purge_all' );
        wm_ech_log( 'Purged LiteSpeed Engine Cache.' );
    }

    // Option B: WP Rocket
    if ( function_exists( 'rocket_clean_domain' ) ) {
        rocket_clean_domain();
        wm_ech_log( 'Purged WP Rocket Engine Cache.' );
    }

    // Option C: W3 Total Cache
    if ( function_exists( 'w3tc_flush_all' ) ) {
        w3tc_flush_all();
        wm_ech_log( 'Purged W3 Total Cache Engine.' );
    }

    // Option D: WP Super Cache
    if ( function_exists( 'wp_cache_clear_cache' ) ) {
        wp_cache_clear_cache();
        wm_ech_log( 'Purged WP Super Cache Engine.' );
    }
}

/**
 * 6) Asynchronous Loopback Engine Optimization Warmer Hooks.
 */
add_action( 'elementor/core/files/clear_cache', 'wm_ech_register_warmer_shutdown', PHP_INT_MAX );
function wm_ech_register_warmer_shutdown() {
    if ( ! did_action( 'elementor/loaded' ) ) {
        return;
    }
    static $done = false;
    if ( true === $done ) {
        return;
    }
    $done = true;

    add_action( 'shutdown', 'wm_ech_dispatch_loopback_warmer' );
}

function wm_ech_dispatch_loopback_warmer() {
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    if ( isset( $_GET['elw'] ) || isset( $_SERVER['HTTP_X_ELEMENTOR_WARM'] ) ) {
        return;
    }

    wm_ech_log( 'Dispatching non-blocking request to warm primary homepage template.' );
    $warm_url = add_query_arg( 'elw', time(), home_url( '/' ) );

    wp_remote_get(
        $warm_url,
        array(
            'blocking'  => false,
            'timeout'   => 0.01,
            'sslverify' => false,
            'headers'   => array( 'X-Elementor-Warm' => '1' ),
        )
    );
}

/**
 * 7) Autonomous Self-Healing Virtual Interceptor Engine.
 * Adapted from Robert Went's open-source utility under GPL-2.0+ licensing parameters.
 */
class WM_ECH_CSS_Self_Healer {

    public function __construct() {
        add_action( 'template_redirect', array( $this, 'intercept_missing_css_404' ), 1 );
    }

    public function intercept_missing_css_404() {
        if ( ! is_404() || ! did_action( 'elementor/loaded' ) ) {
            return;
        }

        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $request_uri = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';

        if ( false === strpos( $request_uri, '/elementor/css/' ) ) {
            return;
        }

        $filename = basename( strtok( $request_uri, '?' ) );
        if ( ! preg_match( '/^(post|loop)-(\d+)\.css$/', $filename, $matches ) ) {
            return;
        }

        $asset_type = $matches[1];
        $post_id    = (int) $matches[2];

        $lock_key = "wm_ech_css_lock_{$asset_type}_{$post_id}";
        if ( get_transient( $lock_key ) ) {
            sleep( 1 );
            $this->stream_css_asset_payload( $asset_type, $post_id );
            return;
        }
        set_transient( $lock_key, true, 20 );

        if ( ! get_post( $post_id ) || ! $this->verify_elementor_footprint( $post_id ) ) {
            delete_transient( $lock_key );
            return;
        }

        wm_ech_log( "Missing asset condition caught: {$asset_type}-{$post_id}.css. Rebuilding..." );

        $regeneration_success = $this->trigger_on_demand_regeneration( $post_id );
        delete_transient( $lock_key );

        if ( true === $regeneration_success ) {
            $this->stream_css_asset_payload( $asset_type, $post_id );
        }
    }

    private function verify_elementor_footprint( $post_id ) {
        $document = \Elementor\Plugin::$instance->documents->get( $post_id );
        return ( $document && $document->is_built_with_elementor() );
    }

    private function trigger_on_demand_regeneration( $post_id ) {
        try {
            $document = \Elementor\Plugin::$instance->documents->get_doc_for_frontend( $post_id );
            if ( ! $document ) {
                return false;
            }

            $css_file_object = \Elementor\Core\Files\CSS\Post::create( $post_id );
            if ( ! $css_file_object ) {
                return false;
            }

            $css_file_object->update();
            return true;
        } catch ( \Throwable $e ) {
            if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
                // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
                error_log( '[WM_ECH_HYBRID_ERR] Structural rebuild error: ' . $e->getMessage() );
            }
            return false;
        }
    }

    private function stream_css_asset_payload( $asset_type, $post_id ) {
        $wp_upload_paths = wp_upload_dir();
        $absolute_path   = sprintf( '%s/elementor/css/%s-%d.css', $wp_upload_paths['basedir'], $asset_type, $post_id );

        if ( ! file_exists( $absolute_path ) ) {
            return;
        }

        status_header( 200 );
        header( 'Content-Type: text/css; charset=UTF-8' );
        header( 'Cache-Control: public, max-age=31536000' );
        header( 'Content-Length: ' . filesize( $absolute_path ) );

        // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_readfile
        readfile( $absolute_path );
        exit;
    }
}

// Fire initialization sequences.
new WM_ECH_CSS_Self_Healer();