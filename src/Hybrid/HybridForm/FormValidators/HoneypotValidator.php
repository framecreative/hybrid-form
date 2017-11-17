<?php
namespace Hybrid\HybridForm\FormValidators;

use \Hybrid\HybridForm\Exceptions;

class HoneypotValidator implements IFormValidator
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
        
        if(!empty($data[$field_name])) {
            $this->errorMessage = 'Tsk tsk tsk!';
            return false;
        }

        return true;
    }

    public function getLastError() {
        return $this->errorMessage;
    }
}