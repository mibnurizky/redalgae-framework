<?php

$app_config = array(
    'app_name' => 'RedAlgae Framework',
    'app_name_first' => 'RedAlgae',
    'app_name_last' => 'Framework',
    'base_url' => 'https://'.$_SERVER['SERVER_NAME'],
    'default_page' => 'welcome',
    'default_language' => 'en',
    'csrf_key' => '&yw?gS8.!*De7&,n:X!?c.+;Kszm=gyFbA3',
    'rewrite' => true,
    'debug' => true,
    'display_errors' => true,
    'log_errors' => true,
    'error_log_path' => BASE_PATH.'/writepath/logs/error.log',
    'error_reporting' => E_ALL,
    'show_execution_time' => false,
    'smtp' => array(
        'host' => 'smtp.gmail.com',
        'port' => 587,
        'username' => '',
        'password' => '',
        'enscryption' => 'tls'
    ),
    'encryption' => array(
        'secret_iv' => 'DALdsPXxqfYzNPfl',
        'secret_key' => 'vXTX1t0'
    )
);

?>