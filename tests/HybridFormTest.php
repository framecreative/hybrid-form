<?php
use Hybrid\HybridForm\HybridForm;

class HybridFormTest extends PHPUnit_Framework_TestCase
{
    public function testAddValidator()
    {
        $form = new HybridForm(array());

        $validator = $this->getMock('Hybrid\HybridForm\FormValidators\IFormValidator', array('validate'));

        $form->addValidator($validator);
    }

    public function testAddHandler()
    {
        $form = new HybridForm(array());

        $handler = $this->getMock('Hybrid\HybridForm\FormHandlers\IFormHandler', array('handle'));

        $form->addHandler($handler);
    }
}