<?php
namespace Hybrid\HybridForm\FormHandlers;

use \Hybrid\HybridForm\Exceptions;
use \Hybrid\HybridForm\FormHandlers\IFormHandler;
use \Web2Lead;

class Web2LeadFormHandler implements IFormHandler
{
    private $options;
    private $requiredOptions;
    private $defaultOptions;

    public function __construct($options) {
        $this->requiredOptions = array(
            'org_id',
            'lead_source'
        );

        $this->defaultOptions = array(
            'email_field' => 'email',
            'first_name_field' => 'first_name',
            'last_name_field' => 'last_name',
            'full_name_field' => 'name'
        );

        foreach($this->requiredOptions as $option) {
            if(!isset($options[$option])) {
                throw new RequiredOptionNotSet($option);
            }
        }

        $this->options = array_merge($this->defaultOptions, $options);
    }

    public function handle($data, $valid) {
        //Skip this handler if data is not valid
        if(!$valid) {
            return;
        }
        
        extract($this->options);

        $email = isset($data[$email_field]) ? $data[$email_field] : '';

        if(isset($data[$full_name_field])) {
            $full_name = trim($data[$full_name_field]);
            $full_name = explode(' ', $full_name);
            $first_name = count($full_name) > 1 ? implode(' ', array_slice($full_name, 0, -1)) : $full_name[0];
            $last_name = count($full_name) > 1 ? end($full_name) : '';
        } else {
            $first_name = isset($data[$first_name_field]) ? $data[$first_name_field] : '';
            $last_name = isset($data[$last_name_field]) ? $data[$last_name_field] : '';
        }

        foreach($data as $key => $value) {
            if($key == $email_field || $key == $first_name_field || $key == $last_name_field)
                continue;

            $custom_fields[$key] = $value;
        }

        $web2lead = new Web2Lead($org_id);
		
		if(!empty($data['lead_source'])) {
			$web2lead->setLeadSource($data['lead_source']);
		} else {
			$web2lead->setLeadSource($lead_source);
		}
        
        $result = $web2lead->toSalesforce(array(
           'first_name' => $first_name,
           'last_name' => $last_name,
           'email' => $email
        ) + $custom_fields);

        if(!$result){
            error_log('Something went wrong with sending lead to Web2Lead.');
        }
    }
}