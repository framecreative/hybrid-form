<?php
namespace Hybrid\HybridForm;

use Hybrid\HybridForm\HybridFormResult;

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
        $result = new HybridFormResult();

        foreach($this->validators as $validator) {
            $valid = $validator->validate($this->data);

            if(!$valid) {
                $result->setValid(false);
                $result->addError(get_class($validator), $validator->getLastError());
                break;
            }
        }

        foreach($this->handlers as $handler) {
            try {
                $handler->handle($this->data, $result->isValid());
            } catch (\Exception $e) {
                error_log('HybridForm Handler Error: '.$e->getMessage());
            }
        }

        return $result;
    }
}