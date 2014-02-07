<?php
namespace Hybrid\HybridForm\FormHandlers;

interface IFormHandler
{
    public function handle($data, $valid);
}