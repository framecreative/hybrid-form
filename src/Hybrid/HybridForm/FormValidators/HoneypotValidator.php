<?php
namespace Hybrid\HybridForm\FormValidators;

use \Hybrid\HybridForm\Exceptions;

class HoneypotValidator
{
    private $options;
    private $requiredOptions;

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

    public function handle($data) {
        extract($this->options);
        
        if(!empty($data[$field_name])) {
            return false;
        }

        return true;
    }
}