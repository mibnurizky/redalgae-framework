<?php

function setEncrypt($string,$secret_key='74j5f3d'){
    $encrypt_method = "AES-256-CBC";
    $secret_key = $secret_key;
    $secret_iv = 'M0dulBu4t4n4sk4R4';
    // hash
    $key = hash('sha256', $secret_key);

    $iv = substr(hash('sha256', $secret_iv), 0, 2);

    $output = openssl_encrypt($string, $encrypt_method, $key, 0);
    $output = base64_encode($output);

    return $output;
}
function getDecrypt($string,$secret_key='74j5f3d'){
    $encrypt_method = "AES-256-CBC";
    $secret_key = $secret_key;
    $secret_iv = 'M0dulBu4t4n4sk4R4';
    // hash
    $key = hash('sha256', $secret_key);

    $iv = substr(hash('sha256', $secret_iv), 0, 2);
    $output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0);

    return $output;
}
function documentRoot($path=''){
    $php_self = str_replace('index.php','',$_SERVER['PHP_SELF']);
    $document_root = $_SERVER['DOCUMENT_ROOT'].$php_self.$path;
    $document_root = str_replace('//','/',$document_root);
    $document_root = str_replace('///','/',$document_root);
    return $document_root;
}
function responseJSON($response){
    header('Content-Type: application/json');
    echo json_encode($response);
    die();
}
function autoLoad(){
    require_once documentRoot('/vendor/autoload.php');
}

?>