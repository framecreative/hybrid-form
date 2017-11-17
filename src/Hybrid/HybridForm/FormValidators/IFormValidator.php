<?php
namespace Hybrid\HybridForm\FormValidators;

interface IFormValidator
{
    public function validate($data);
    public function getLastError();
}