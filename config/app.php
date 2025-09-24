<?php

$app_config = array(
    'app_name' => 'Amoeba Framework',
    'app_name_first' => 'Amoeba',
    'app_name_last' => 'Framework',
    'base_url' => 'https://'.$_SERVER['SERVER_NAME'],
    'default_page' => 'auth.login',
    'default_language' => 'en',
    'csrf_key' => '&yw?gS8.!*De7&,n:X!?c.+;Kszm=gyFbA3',
    'rewrite' => true,
    'smtp' => array(
        'host' => 'smtp.gmail.com',
        'port' => 587,
        'username' => '',
        'password' => '',
        'enscryption' => 'tls'
    )
);

?>