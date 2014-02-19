<?php
namespace Hybrid\HybridForm\FormValidators;

use \Hybrid\HybridForm\Exceptions;

class EmailValidator implements IFormValidator
{
    private $options;
    private $requiredOptions;
    private $errorMessage;

    public function __construct($options) {
        $this->requiredOptions = array(
            'field_name'
        );

        foreach($this->requiredOptions as $option) {
            if(!isset($options[$option])) {
                throw new RequiredOptionNotSet($option);
            }
        }

        $this->options = $options;
    }

    public function validate($data) {
        extract($this->options);
        
        if(empty($data[$field_name])) {
            $this->errorMessage = 'Required field "'.$field_name.'" not set';
            return false;
        }

        if(filter_var($data[$field_name], FILTER_VALIDATE_EMAIL) === false) {
            $this->errorMessage = 'Please enter a valid email address.';
            return false;
        }

        return true;
    }

    public function getLastError() {
        return $this->errorMessage;
    }
}