<?php

global $MODEL, $SESSION;
$MODEL->includeModel('user');

if(isset($_POST['ajax_act'])){
    switch ($_POST['ajax_act']){
        case 'submit':
            submit();
            break;
    }
}
else{
    view_auth('auth.login');
}

function submit(){
    global $COMPONENT;
    $lang = new Language('components.auth.login');
    $usermodel = new userModel();

    /**
     * Check Limiter
     */
    $limiter = new Limiter('auth.login',3,60);
    if($limiter->isMaxAttempt()){
        responseJSON(array(
            'status' => 'error',
            'message' => $lang->get('auth_login_error_attempt',['AVAILABLE_IN' => $limiter->avaliableIn()]),
            'csrf' => csrf_get_token('auth.login')
        ));
    }
    $limiter->hit();

    /**
     * Check CSRF
     */
    if(!csrf_input_validate('auth.login')){
        responseJSON(array(
            'status' => 'error',
            'message' => 'CSRF Token not valid',
            'csrf' => csrf_get_token('auth.login')
        ));
    }

    $error = "";
    $error_code = "";
    $result = $usermodel->auth($_POST['email'],$_POST['password'],$error,$error_code);
    if(!$result){
        $message = array(
            'status' => 'error',
            'message' => $error,
            'csrf' => csrf_get_token('auth.login')
        );

        if($error_code == 'USER_NOT_VERIFIED'){
            $message['url'] = $COMPONENT->routeto('auth.verify',['email'=>$_POST['email']]);
            $limiter->reset();
        }
        responseJSON($message);
    }

    $limiter->reset();
    responseJSON(array(
        'status' => 'success',
        'message' => '',
        'csrf' => csrf_get_token('auth.login')
    ));
}
?>