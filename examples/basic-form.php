<?php
require('../vendor/autoload.php');

use Hybrid\HybridForm\HybridForm;
use Hybrid\HybridForm\FormHandlers\CSVFormHandler;
use Hybrid\HybridForm\FormHandlers\MailFormHandler;

if( empty($_POST) ) {
    exit('You need to post some data.');
}

$form = new HybridForm($_POST);

// This handler will always log the data to a csv file even if the form data is not valid.
// Note: Put this handler first so it runs first.
$form->addHandler(new CSVFormHandler(array(
    'log_path' => dirname(__FILE__).DIRECTORY_SEPARATOR.'email_log.csv.php',
)));

// Simple mail handler uses php mail() to send notification to specified recipients.
// Note: You can have multiple mail handlers if you want to send different messages to
//       multiple email addresses.
$form->addHandler(new MailFormHandler(array(
    'from'    => 'Website form<no-reply@hybridmarketing.com.au>',
    'to'      => array(
                    'patrick@hybridmarketing.com.au',
                    'shou@hybridmarketing.com.au'
                 ),
    'subject' => 'Email From Website Form'
)));

$form->process();

header("Location: examples.html?contact=success");
