<?php
use Hybrid\HybridForm\HybridForm;

use Hybrid\HybridForm\FormValidators\AkismetValidator;
use Hybrid\HybridForm\FormValidators\ConfigurableValidator;

use Hybrid\HybridForm\FormHandlers\CSVFormHandler;
use Hybrid\HybridForm\FormHandlers\MailFormHandler;
use Hybrid\HybridForm\FormHandlers\MailChimpFormHandler;
use Hybrid\HybridForm\FormHandlers\Web2LeadFormHandler;
use Hybrid\HybridForm\FormHandlers\CampaignMonitorFormHandler;

// Load WordPress
define('WP_USE_THEMES', false);

if(file_exists('../../../wp-blog-header.php')) {
    require('../../../wp-blog-header.php');
} else if(file_exists('../../../wordpress/wp-blog-header.php')) {
    require('../../../wordpress/wp-blog-header.php');
} else {
    exit('Can\'t find WordPress installation. Is WordPress installed in a sub-directory?');
}

class HybridFormPluginHandleForm {

    public static function init() {
        add_action('hybf_apply_validators', array('HybridFormPluginHandleForm', 'apply_validators_action'), 10, 3);
        add_action('hybf_apply_handlers', array('HybridFormPluginHandleForm', 'apply_handlers_action'), 10, 3);
    }

    public static function main() {
        // HOOK -Pre plugin hook
        if(apply_filters('hybf_pre_hook', true) === false) {
            return;
        }

        // HOOK -Apply $_POST data filter
        $_POST = apply_filters('hybf_post_data', $_POST);

        // Handle invalid forms
        if(empty($_POST)) {
            exit('You need to post some data.');
        }

        if(isset($_POST['_form_id'])) {
            $form_id = absint($_POST['_form_id']);
        } else {
            $form_id = null;
        }

        $hybf_forms = get_option('hybf_forms');
        $form_config = array();

        // Find form data
        foreach ($hybf_forms as $key => $value) {
            if($value['id'] == $form_id) {
                $form_config = $value;
            }
        }

        // Form data not found so _form_id is invalid
        if(empty($form_config)) {
            $form_id = null;
        }

        // HOOK - Apply form_id filter
        $form_id = apply_filters('hybf_form_id', $form_id);

        // HOOK - Apply form_config filter
        $form_config = apply_filters('hybf_form_config', $form_config);

        if($form_id !== null) {
            $form = new HybridForm($_POST);

            $form->addHandler(new CSVFormHandler(array(
                'log_path' => HYBF_LOG_DIR.'email_log.'.$form_id.'.csv',
            )));

            $wp_post_id = false;
            if(isset($_POST['_post_id'])) {
                $wp_post_id = absint($_POST['_post_id']);
            }

            // HOOK - Add validators
            do_action('hybf_apply_validators', $form_id, $form, $form_config);

            // HOOK - Add handlers
            do_action('hybf_apply_handlers', $form_id, $form, $form_config);

            // Process form
            $form_result = $form->process();

            // HOOK - Apply form result filter
            $form_result = apply_filters('hybf_form_result', $form_result, $form);

            if($form_result->isValid()) {
                $redirect = self::process_meta_tags($form_config['redirect'], $wp_post_id);

                // HOOK - Apply redirect filters
                $redirect = apply_filters('hybf_redirect', $redirect);

                header("Location: ".$redirect);
            } else {
                hyb_flash_message("form_error", $form_result->getErrorString());
                header("Location: ".$_SERVER['HTTP_REFERER']); //redirect back
            }
        } else {
            //Unknown form so log its output then redirect to homepage
            $form = new HybridForm($_POST);

            $form->addHandler(new CSVFormHandler(array(
                'log_path' => HYBF_LOG_DIR.'email_log.unknownid.csv',
            )));

            $form->process();

            header("Location: /");
        }
    }

    public static function apply_validators_action($form_id, $form, $form_config) {
        $form_user_config = isset($form_config['config']) ? json_decode($form_config['config'], true) : array();

        if(isset($form_user_config['validation']) && is_array($form_user_config['validation'])) {
            $form->addValidator(new ConfigurableValidator(array(
                'rules' => $form_user_config['validation']
            )));
        }

        if(function_exists('akismet_get_key') && akismet_get_key() != false) {
            if(isset($form_user_config['akismet']['enabled']) && $form_user_config['akismet']['enabled'] != false) {
                $args = array();
                if(isset($form_user_config['akismet']['fields'])) {
                    foreach($form_user_config['akismet']['fields'] as $key => $value) {
                        $args[$key.'_field'] = $value;
                    }
                }
                $form->addValidator(new AkismetValidator($args));
            }
        }
    }

    public static function apply_handlers_action($form_id, $form, $form_config) {
        foreach ($form_config['handlers'] as $key => $handler) {
            $handler_options = json_decode($handler['options'], true);

            if($handler_options == null) {
                error_log('HybridForm: Handler could not be decoded! FormId: '.$form_id);
                continue;
            }

            foreach ($handler_options as &$value) {
                if(is_string($value)) {
                    $value = self::process_meta_tags($value, $wp_post_id);
                }
            }

            if($handler['name'] == 'mail') {
                $form->addHandler(new MailFormHandler($handler_options));
            } elseif($handler['name'] == 'mail_chimp') {
                $form->addHandler(new MailChimpFormHandler($handler_options));
            } elseif($handler['name'] == 'web2lead') {
                $form->addHandler(new Web2LeadFormHandler($handler_options));
            } elseif($handler['name'] == 'campaign_monitor') {
                $form->addHandler(new CampaignMonitorFormHandler($handler_options));
            } else {
                error_log('HybridForm: Unknown handler name! FormId: '.$form_id);
            }
        }
    }

    // This allows for dynamic fields to be inserted as handler options
    //
    // Available META keys:
    //
    // {{POSTTITLE}} = post title
    // {{POSTMETA[metakey]}} = meta key value
    //                         e.g. {{POSTMETA[contact_form.to_email]}}
    //                              will load the contact_form.to_email meta value from the specified post
    // {{POSTSLUG}} = post slug
    // {{POSTURL}} = post url e.g. http://blog.com/the-post
    // {{POST[key]}} = $_POST variable
    public static function process_meta_tags($option, $wp_post_id = false) {
        if(preg_match_all("/\{\{([^&]*?)\}\}/", $option, $matches)) {

            foreach($matches[1] as $str) {

                if($wp_post_id !== false) {
                    if(strpos($str, 'POSTMETA[') !== false) {
                        $meta = get_post_meta($wp_post_id, substr($str, 9, -1), true);
                        if(!empty($meta)) {
                            $option = str_replace('{{'.$str.'}}', $meta, $option);
                        }
                    }

                    if(strpos($str, 'POSTTITLE') !== false) {
                        $title = get_the_title($wp_post_id);
                        if(!empty($title)) {
                            $option = preg_replace("/{{POSTTITLE}}/", $title, $option);
                        }
                    }

                    if(strpos($str, 'POSTSLUG') !== false) {
                        $url = basename(get_permalink($wp_post_id));
                        if(!empty($url)) {
                            $option = preg_replace("/{{POSTSLUG}}/", $url, $option);
                        }
                    }

                    if(strpos($str, 'POSTURL') !== false) {
                        $url = get_permalink($wp_post_id);
                        if(!empty($url)) {
                            $option = preg_replace("/{{POSTURL}}/", $url, $option);
                        }
                    }
                }

                if(strpos($str, 'POST[') !== false) {
                    $key = substr($str, 5, -1);
                    if($key) {
                        $meta = isset($_POST[$key]) ? $_POST[$key] : null;
                        if(!empty($meta)) {
                            $option = str_replace('{{'.$str.'}}', $meta, $option);
                        }
                    }
                }
            }

            //Remove unmatched tags
            $option = preg_replace("/\{\{([^&]*?)\}\}/", '', $option);
        }

        return $option;
    }
}

HybridFormPluginHandleForm::init();
HybridFormPluginHandleForm::main();
