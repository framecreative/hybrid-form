<?php
namespace Hybrid\HybridForm\FormHandlers;

use \Hybrid\HybridForm\Exceptions;
use \Hybrid\HybridForm\FormHandlers\IFormHandler;
use \CS_REST_Subscribers;

class CampaignMonitorFormHandler implements IFormHandler
{
    private $options;
    private $requiredOptions;

    public function __construct($options) {
        $this->requiredOptions = array(
            'access_token',
            'refresh_token',
            'list_id',
            
            'email_field',
            'name_field'
        );

        foreach($this->requiredOptions as $option) {
            if(!isset($options[$option])) {
                throw new RequiredOptionNotSet($option);
            }
        }

        $this->options = $options;
    }

    public function handle($data, $valid) {
        //Skip this handler if data is not valid
        if(!$valid) {
            return;
        }
        
        extract($this->options);

        $email = $data[$email_field];
        $name = null;
        $custom_fields = array();

        if(is_array($name_field)) {
            $name_data = array();
            foreach($data as $key, $val) {
                if(in_array($key, $name_field))
                    $name_data[] = $val;
            }
            $name = implode(' ', $name_data);
        } else {
            $name = $data[$name_field];
        }

        foreach($data as $key => $value) {
            if($key == $email_field || $key == $name_field)
                continue;

            $custom_fields[] = array(
                'Key' => $key,
                'Value' => $value
            );
        }
        
        $wrap = new CS_REST_Subscribers($list_id, array(
            'access_token'  => $access_token,
            'refresh_token' => $refresh_token
        ));

        $result = $wrap->add(array(
            'EmailAddress' => $email,
            'Name' => $name,
            'CustomFields' => $custom_fields,
            'Resubscribe' => true
        ));

        if(!$result->was_successful()) {
            error_log('Failed to subscribe to CampaignMonitor with '.
                      'Code: '.$result->http_status_code.' '.
                      'Response: '.var_export($result->response, true) );
        }
    }
}