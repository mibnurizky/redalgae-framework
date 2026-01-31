<?php

$app_config = array(
    'app_name' => 'Amoeba Framework',
    'app_name_first' => 'Amoeba',
    'app_name_last' => 'Framework',
    'base_url' => 'https://'.$_SERVER['SERVER_NAME'],
    'default_page' => 'welcome',
    'default_language' => 'en',
    'csrf_key' => '&yw?gS8.!*De7&,n:X!?c.+;Kszm=gyFbA3',
    'rewrite' => true,
    'debug' => true,
    'display_errors' => true,
    'log_errors' => true,
    'error_reporting' => E_ALL,
    'show_execution_time' => true,
    'smtp' => array(
        'host' => 'smtp.gmail.com',
        'port' => 587,
        'username' => '',
        'password' => '',
        'enscryption' => 'tls'
    )
);

?>