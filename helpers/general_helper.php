<?php

function setEncrypt($string, $secret_key = '') {
    $encrypt_method = "AES-256-CBC";
    $secret_iv = APP->config['encryption']['secret_iv'];

    if(empty($secret_key)){
        $secret_key = APP->config['encryption']['secret_key'];
    }

    $key = hash('sha256', $secret_key, true);

    $iv = substr(hash('sha256', $secret_iv, true), 0, 16);

    $output = openssl_encrypt($string, $encrypt_method, $key, OPENSSL_RAW_DATA, $iv);

    return base64_encode($output);
}

function getDecrypt($string, $secret_key = '') {
    $encrypt_method = "AES-256-CBC";
    $secret_iv = APP->config['encryption']['secret_iv'];

    if(empty($secret_key)){
        $secret_key = APP->config['encryption']['secret_key'];
    }

    $key = hash('sha256', $secret_key, true);
    $iv  = substr(hash('sha256', $secret_iv, true), 0, 16);

    return openssl_decrypt(base64_decode($string), $encrypt_method, $key, OPENSSL_RAW_DATA, $iv);
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

function dateChange($date,$change,$time=true){
    if($time){
        return date('Y-m-d H:i:s', strtotime($date.' '.$change));
    }
    else{
        return date('Y-m-d', strtotime($date.' '.$change));
    }
}

function baseUrl($link){
    if(!empty($_SERVER['HTTPS']) AND $_SERVER['HTTPS'] == 'on'){
        $link = 'https://'.$_SERVER['SERVER_NAME'].$link;
    }
    else{
        $link = 'http://'.$_SERVER['SERVER_NAME'].$link;
    }

    return $link;
}

function isset_multi(array $array, array $keys): bool
{
    $temp = $array;
    foreach ($keys as $key) {
        if (!isset($temp[$key])) {
            return false;
        }
        $temp = $temp[$key];
    }
    return true;
}

function optional_array(array $array, array $keys, $default=''){
    if(isset_multi($array,$keys)){
        $temp = $array;
        foreach ($keys as $key) {
            $temp = $temp[$key];
        }
        return $temp;
    }
    else{
        return $default;
    }
}

function optional($variable,$default=''){
    if(isset($variable)){
        return $variable;
    }
    else{
        return $default;
    }
}

function file_to_base64($filepath){
    $imagePath = $_SERVER['DOCUMENT_ROOT'].$filepath;
    $imageData = file_get_contents($imagePath);
    $base64 = base64_encode($imageData);
    $mime = mime_content_type($imagePath);
    $base64Image = "data:" . $mime . ";base64," . $base64;

    return $base64Image;
}

function current_component(){
    return $GLOBALS['CURRENT_COMPONENT'];
}

function is_part_of_current_component($string){
    $term = explode('-',$string);
    $current = explode('-',current_component());

    $is_partof = array();
    foreach($term as $key => $value){
        if($value == optional_array($current,[$key])){
            $is_partof[] = 'Y';
        }
        else{
            $is_partof[] = 'N';
        }
    }

    if(in_array('N',$is_partof)){
        return false;
    }

    return true;
}

?>