<?php
namespace Hybrid\HybridForm\FormValidators;

use \Hybrid\HybridForm\Exceptions;

class AkismetValidator
{
    private $options;
    private $requiredOptions;

    public function __construct($options = array()) {
        $this->options = $options;
    }

    public function handle($data) {
        global $akismet_api_host, $akismet_api_port;

        // This validator only works on Wordpress
        if ( !function_exists( 'akismet_http_post' ) )
            return false;

        // Get data to send to Akismet
        if(isset($data['name']))
            $data['comment_author'] = isset($data['name']) ? $data['name'] : null;
        else if(isset($data['first_name']))
            $data['comment_author'] = isset($data['first_name']) ? $data['first_name'] : null;

        $data['comment_author_email'] = isset($data['email']) ? $data['email'] : null;
        $data['comment_author_IP']    = $_SERVER['REMOTE_ADDR'];
        $data['comment_content']      = isset($data['message']) ? $data['message'] : null;
        $data['comment_type']         = 'contact_form';
        $data['user_ip']              = $_SERVER['REMOTE_ADDR'];
        $data['user_agent']           = $_SERVER['HTTP_USER_AGENT'];
        $data['referrer']             = $_SERVER['HTTP_REFERER'];
        $data['blog']                 = get_option( 'home' );

        $ignore = array( 'HTTP_COOKIE' );

        foreach ( $_SERVER as $k => $value )
            if ( !in_array( $k, $ignore ) && is_string( $value ) )
                $data["$k"] = $value;

        // Send to Akismet
        $query_string = http_build_query( $data );
        $response = akismet_http_post( $query_string, $akismet_api_host, '/1.1/comment-check', $akismet_api_port );
        $result = false;
        
        if ( isset( $response[1] ) && 'true' == trim( $response[1] ) ) // 'true' is spam
            $result = true;

        return $result;
    }
}