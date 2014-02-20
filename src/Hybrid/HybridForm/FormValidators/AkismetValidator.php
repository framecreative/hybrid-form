<?php
namespace Hybrid\HybridForm\FormValidators;

use \Hybrid\HybridForm\Exceptions;

class AkismetValidator implements IFormValidator
{
    private $options;
    private $requiredOptions;
    private $errorMessage;

    public function __construct($options = array()) {
        $this->defaultOptions = array(
            'email_field' => 'email',
            'name_field' => 'name',
            'subject_field' => 'subject',
            'content_field' => 'message'
        );

        $this->options = array_merge($this->defaultOptions, $options);
    }

    public function handle($data) {
        // This validator only works on Wordpress
        if (!function_exists('akismet_http_post')) {
            return true;
        }

        extract($this->options);

        global $akismet_api_host, $akismet_api_port;

        if(is_array($name_field)) {
            $name_data = array();
            foreach($data as $key => $val) {
                if(in_array($key, $name_field))
                    $name_data[] = $val;
            }
            $name = implode(' ', $name_data);
        } else {
            $name = isset($data[$name_field]) ? $data[$name_field] : '';
        }

        // Get data to send to Akismet
        $data['comment_author']       = $name;
        $data['comment_author_email'] = isset($data[$email_field]) ? $data[$email_field] : null;
        $data['comment_author_IP']    = $_SERVER['REMOTE_ADDR'];
        $data['comment_content']      = isset($data[$content_field]) ? $data[$content_field] : null;
        $data['comment_type']         = 'contact_form';
        $data['contact_form_subject'] = isset($data[$subject_field]) ? $data[$subject_field] : null;
        $data['user_ip']              = $_SERVER['REMOTE_ADDR'];
        $data['user_agent']           = $_SERVER['HTTP_USER_AGENT'];
        $data['referrer']             = $_SERVER['HTTP_REFERER'];
        $data['blog']                 = get_option('home');

        $ignore = array('HTTP_COOKIE');

        foreach ($_SERVER as $k => $value)
            if (!in_array($k, $ignore) && is_string($value))
                $data["$k"] = $value;

        // Send to Akismet
        $query_string = http_build_query($data);
        $response = akismet_http_post($query_string, $akismet_api_host, '/1.1/comment-check', $akismet_api_port);
        $result = true;

        if (isset($response[1]) && 'true' == trim($response[1]))
            $result = false;

        return $result;
    }

    public function getLastError() {
        return $this->errorMessage;
    }
}