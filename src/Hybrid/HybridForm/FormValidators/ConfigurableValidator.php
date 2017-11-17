<?php
namespace Hybrid\HybridForm\FormValidators;

use \Hybrid\HybridForm\Exceptions;
use \Valitron\Validator as Valitron;

class ConfigurableValidator implements IFormValidator
{
    private $options;
    private $requiredOptions;
    private $errors;

    public function __construct($options) {
        $this->requiredOptions = array(
            'rules'
        );

        foreach($this->requiredOptions as $option) {
            if(!isset($options[$option])) {
                throw new RequiredOptionNotSet($option);
            }
        }

        if(!is_array($options['rules']))
            $options['rules'] = json_decode($options['rules']);

        // Add the honeypot validation rule
        Valitron::addRule('honeypot', function($field, $value, array $params) {
            return empty($value);
        }, 'Tsk Tsk Tsk...');

        $this->options = $options;
    }

    public function validate($data) {

        $v = new Valitron($data);
        $v->rules($this->options['rules']);
        $result = $v->validate();
        
        if(!$result)
            $this->errors = $v->errors();

        return $result;
    }

    public function getLastError() {
        $error_string = '';

        foreach($this->errors as $error) {
            $error_string .= ucfirst(strtolower(implode(", ", $error)));
            $error_string .= "\r\n";
        }
        return $error_string;
    }
}