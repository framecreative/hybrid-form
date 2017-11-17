<?php
namespace Hybrid\HybridForm\FormHandlers;

use \Hybrid\HybridForm\Exceptions;
use \Hybrid\HybridForm\FormHandlers\IFormHandler;

class MailFormHandler implements IFormHandler
{
    private $options;
    private $requiredOptions;

    public function __construct($options) {
        $this->requiredOptions = array(
            'from',
            'to',
            'subject'
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
        $fields = array();

        if(is_array($to)) {
            $to = implode(',', $to);
        }

        //Automatically add all data
        foreach($data as $key => $value)
            $fields[$key] = array(ucwords(str_replace(array('_','-'), ' ', $key)), $value);
        
        //Add additional fields
        $fields = $fields + array(
            'sent_at'     => array("Sent at", date("Y-m-d H:i:s")),
            'senders_ip'  => array("Sender's IP", $_SERVER['REMOTE_ADDR'])
        );

        //Set up email body 
        $body = '';
        foreach($fields as $field)
            $body .= $field[0].': '.strip_tags($field[1])."\r\n\r\n";
        $body = wordwrap($body, 70);

        // Additional headers
        $headers = "From: ".$from."\r\n";
        
        //Send mail
        mail($to, $subject, $body, $headers);
    }
}