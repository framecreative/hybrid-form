<?php
/*
Plugin Name: HybridForm
Version: 1.2.0
Plugin URI: http://hybridmarketing.com.au
Description: Handles website forms and logs all wp_mail calls.
Author: Patrick Galbraith
*/

// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {exit;}

use Hybrid\HybridForm\HybridForm;
use Hybrid\HybridForm\FormHandlers\CSVFormHandler;

define('HYBF_LOG_DIR', dirname(__FILE__).DIRECTORY_SEPARATOR.'logs'.DIRECTORY_SEPARATOR);

register_activation_hook( __FILE__, array('HybridFormPlugin', 'install'));
add_action('init', array('HybridFormPlugin', 'init'));
add_filter('wp_mail', array('HybridFormPlugin', 'log_mail'), 1);

if ( is_admin() ) {
    add_action('admin_menu', array('HybridFormPlugin', 'init_plugin_menu'));
    add_action('admin_init', array('HybridFormPlugin', 'register_settings'));
    add_action('admin_notices', array('HybridFormPlugin', 'admin_notices'));
}

require_once('inc/hybrid-flash-messages.php');

class HybridFormPlugin {

    private static $default_config = array(
        'hybf_forms' => array()
    );

    static function install() {
        foreach(self::$default_config as $name => $value)
            add_option($name, $value);

        global $wp_rewrite;
        self::register_rewrite_rules();
        $wp_rewrite->flush_rules(true);
    }

    static function init() {
        self::register_rewrite_rules();
    }

    static function register_rewrite_rules() {

        add_rewrite_rule('^handle-form/?', '/content/plugins/hybrid-form/form-handler.php', 'top');
    }

    static function init_plugin_menu() {
        global $hybf_plugin_hook;
        $hybf_plugin_hook = add_submenu_page('options-general.php', __('HybridForm'), __('HybridForm'), 'manage_options', 'HybridFormPlugin', array('HybridFormPlugin', 'get_admin_page_html'));

        add_action('load-'.$hybf_plugin_hook, array('HybridFormPlugin', 'add_help_tab'));
    }

    static function admin_notices() {
        // Do some basic health checks
        if(!is_writable(HYBF_LOG_DIR)) {
            echo '<div class="error"><p><b>HybridForm Plugin:</b> Cannot write to log directory - <i>'.HYBF_LOG_DIR.'</i></p></div>';
        }
    }

    static function add_help_tab() {
        $screen = get_current_screen();

        // Default Help Tab
        ob_start();
        include 'help/default.html.php';
        $content = ob_get_clean();

        $screen->add_help_tab( array(
            'id'      => 'hybf_help_tab',
            'title'   => __('General'),
            'content' => $content,
        ));

        // Validation Help Tab
        ob_start();
        include 'help/validation.html.php';
        $content = ob_get_clean();

        $screen->add_help_tab( array(
            'id'      => 'hybf_help_tab_validation',
            'title'   => __('Config & Validation'),
            'content' => $content,
        ));

        // Examples Help Tab
        ob_start();
        include 'help/tags.html.php';
        $content = ob_get_clean();

        $screen->add_help_tab( array(
            'id'      => 'hybf_help_tab_tags',
            'title'   => __('Tags'),
            'content' => $content,
        ));
    }

    static function register_settings() {
        register_setting( 'hybf-options', 'hybf_forms' );
    }

    static function log_mail($args) {

        if ( is_admin() ) return;

        $form = new HybridForm($args);

        $form->addHandler(new CSVFormHandler(array(
            'log_path' => HYBF_LOG_DIR.'email_log.wp.csv',
        )));

        $form->process();
    }

    static function handle_options_update() {
        //Fix magic quotes
        $data = $_POST['hybf_form'];

        $func = create_function(
            '&$val, $key',
            'if(!is_numeric($val)) {$val = stripslashes($val);}'
        );
        array_walk_recursive($data, $func);

        update_option('hybf_forms', $data);
    }

    static function get_admin_page_html() {

        if (!current_user_can('manage_options')) {
            wp_die( __('You do not have sufficient permissions to access this page.') );
        }

        if( isset($_POST[ 'hybf_submit_hidden' ]) && $_POST[ 'hybf_submit_hidden' ] == 'Y' ) {
            self::handle_options_update();
            ?>
            <div class="updated"><p><strong><?php _e('Settings saved.'); ?></strong></p></div>
            <?php
        }

        $hybf_handler_list = array(
            'mail' => 'Mail',
            'campaign_monitor' => 'Campaign Monitor',
            'mail_chimp' => 'MailChimp',
            'web2lead' => 'SalesForce Web2Lead'
        );

        foreach(self::$default_config as $name => $value) {
            $$name = get_option($name, $value);
        }

        $akismet_enabled = function_exists('akismet_get_key') && akismet_get_key() != false;

        include 'inc/admin.html.php';
    }
}
