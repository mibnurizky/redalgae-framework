<?php

function csrf_get_token($key){
    global $APP;
    $csrf = new Csrf($APP->config['csrf_key']);
    $token = $csrf->getToken($key);
    return $token;
}

function csrf_validate_token($key,$token){
    global $APP;
    $csrf = new Csrf($APP->config['csrf_key']);

    if($csrf->validate($key,$token)){
        return true;
    }
    else{
        return false;
    }
}

function csrf_input($key,$name='_csrf_token'){
    $token = csrf_get_token($key);
    echo '<input type="hidden" name="'.$name.'" value="'.$token.'">';
}

function csrf_input_validate($key,$name='_csrf_token'){
    $token = $_REQUEST[$name];
    if(csrf_validate_token($key,$token)){
        return true;
    }
    else{
        return false;
    }
}

?>