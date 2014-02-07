<?php
namespace Hybrid\HybridForm;

class HybridForm
{
    private $handlers;
    private $validators;
    private $data; //Form Data

    public function __construct($data = array(), $handlers = array(), $validators = array()) {
        $this->handlers = $handlers;
        $this->validators = $validators;
        $this->data = $data;
    }

    public function addHandler(\Hybrid\HybridForm\FormHandlers\IFormHandler $handler) {
        $this->handlers[] = $handler;
    }

    public function addValidator(\Hybrid\HybridForm\FormValidators\IFormValidator $validator) {
        $this->validators[] = $validator;
    }

    public function appendData($data) {
        $this->data += $data;
    }

    public function process() {
        $valid = true;

        foreach($this->validators as $validator) {
            $result = $validators->validate($this->data);

            if(!$result) {
                $valid = false;
                break;
            }
        }

        foreach($this->handlers as $handler) {
            $handler->handle($this->data, $valid);
        }

        return $valid;
    }
}