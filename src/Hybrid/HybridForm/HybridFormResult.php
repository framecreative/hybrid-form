<?php
namespace Hybrid\HybridForm;

class HybridFormResult
{
    private $valid;
    private $errors;

    public function __construct($valid = true, $errors = array()) {
        $this->valid = $valid;
        $this->errors = $errors;
    }

    public function isValid() {
        return $this->valid;
    }

    public function setValid($valid) {
        $this->valid = $valid;
    }

    public function addError($reference, $message) {
        $this->errors[$reference] = $message; 
    }

    public function getErrorString() {
        return implode("\r\n", $this->errors);
    }
}