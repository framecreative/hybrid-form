<?php
namespace Hybrid\HybridForm\FormHandlers;

use \Hybrid\HybridForm\Exceptions\RequiredOptionNotSet;
use \Hybrid\HybridForm\FormHandlers\IFormHandler;
use \Drewm\MailChimp\MailChimp;

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

        $vars = array('FNAME'=>$first_name, 'LNAME'=>$last_name);

        foreach ($this->options as $mc_name => $post_name){
            if (isset($_POST[$post_name]) && $mc_name != "api_key" && $mc_name != "list_id" && $mc_name != "email_field" && $mc_name != "first_name_field" && $mc_name != "last_name_field"){
                $vars[$mc_name] = $_POST[$post_name];
            }
        }

        $MailChimp = new MailChimp($api_key);
        $result = $MailChimp->call('lists/subscribe', array(
            'id'                => $list_id,
            'email'             => array('email'=>$email),
            'merge_vars'        => $vars,
            'double_optin'      => false,
            'update_existing'   => true,
            'replace_interests' => false,
            'send_welcome'      => false,
        ));
    }
}
