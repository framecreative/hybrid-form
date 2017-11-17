<?php
namespace Hybrid\HybridForm\FormHandlers;

use \Hybrid\HybridForm\Exceptions;
use \Hybrid\HybridForm\FormHandlers\IFormHandler;

class CSVFormHandler implements IFormHandler
{
    private $options;
    private $requiredOptions;

    public function __construct($options) {
        $this->requiredOptions = array(
            'log_path'
        );

        foreach($this->requiredOptions as $option) {
            if(!isset($options[$option])) {
                throw new RequiredOptionNotSet($option);
            }
        }

        $this->options = $options;
    }

    public function handle($data, $valid) {
        extract($this->options);
        $pre_exists = true;
        
        if(!file_exists($log_path)) {
            $pre_exists = false;
        }

        $fp = @fopen($log_path, 'a');
        
        if($fp) {
            if(!$pre_exists && pathinfo($log_path, PATHINFO_EXTENSION) == 'php') {
                fwrite($fp, "<?php exit('Go Away!'); ?>".PHP_EOL);
            }

            foreach($data as &$value) {
                $value = preg_replace('/\s+/', ' ', $value);
            }

            $data['user_agent'] = $_SERVER['HTTP_USER_AGENT'];

            fputcsv($fp, array(
                date(DATE_RFC2822),
                ($valid ? 'VALID' : 'NOTVALID')
            ) + $data);
            
            fclose($fp);
        } else {
            error_log('Failed to open email log file pointer. '.$log_path);
        }
    }
}