<?php
namespace Hybrid\HybridForm\FormHandlers;

use \Hybrid\HybridForm\Exceptions;
use \Hybrid\HybridForm\FormHandlers\IFormHandler;
use \MailChimp;

class MailChimpFormHandler implements IFormHandler
{
    private $options;
    private $requiredOptions;

    public function __construct($options) {
        $this->requiredOptions = array(
            'api_key',
            'list_id',
            
            'email_field',
            'first_name_field',
            'last_name_field'
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
        $first_name = $data[$first_name_field];
        $last_name = $data[$last_name_field];
        
        $MailChimp = new MailChimp($api_key);
        $result = $MailChimp->call('lists/subscribe', array(
            'id'                => $list_id,
            'email'             => array('email'=>$email),
            'merge_vars'        => array('FNAME'=>$first_name, 'LNAME'=>$last_name),
            'double_optin'      => false,
            'update_existing'   => true,
            'replace_interests' => false,
            'send_welcome'      => false,
        ));
    }
}
