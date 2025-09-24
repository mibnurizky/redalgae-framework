<?php
global $MODEL;
$MODEL->includeModel('user');

if(isset($_POST['ajax_act'])){
    switch ($_POST['ajax_act']){
        case 'send_verification':
            send_verification();
            break;
    }
}
else{

    if(!empty($_REQUEST['email']) AND !empty($_REQUEST['verification_code'])){
        process_verification();
    }
    else{
        view_auth('auth.verify');
    }

}

function process_verification(){
    global $SESSION, $COMPONENT;
    $lang = new Language('components.auth.verify');

    $user = new userModel();
    $error = "";
    $error_code = "";
    $result = $user->verification($_REQUEST['email'],$_REQUEST['verification_code'],$error,$error_code);
    if($result){
        $SESSION->flash_set('alert',array(
            'title' => 'Success',
            'message' => $lang->get('auth_verify_success_verify'),
            'type' => 'success'
        ));
        $COMPONENT->redirect('auth.login');
    }
    else{
        $SESSION->flash_set('alert',array(
            'title' => 'Error',
            'message' => $error,
            'type' => 'error'
        ));

        if($error_code == 'EXPIRED'){
            $COMPONENT->redirect('auth.verify',['email' => $_REQUEST['email']]);
        }
        else{
            $COMPONENT->redirect('auth.login');
        }
    }
}

function send_verification(){
    $lang = new Language('components.auth.verify');

    /**
     * Check CSRF
     */
    if(!csrf_input_validate('auth.verify')){
        responseJSON(array(
            'status' => 'error',
            'message' => $lang->get('auth_verify_error_csrf'),
            'csrf' => csrf_get_token('auth.verify')
        ));
    }

    $limiter = new Limiter('auth.verify.send_verification',1,60);
    if($limiter->isMaxAttempt()){
        responseJSON(array(
            'status' => 'info',
            'message' => $lang->get('auth_verify_error_attempt',['AVAILABLE_IN' => $limiter->avaliableIn()]),
            'csrf' => csrf_get_token('auth.verify')
        ));
    }
    $limiter->hit();

    $user = new userModel();
    $error = "";
    $result = $user->sendVerificationLink($_POST['email'],$error);
    if($result){
        responseJSON(array(
            'status' => 'success',
            'message' => $lang->get('auth_verify_success_send'),
            'csrf' => csrf_get_token('auth.verify')
        ));
    }
    else{
        responseJSON(array(
            'status' => 'info',
            'message' => $error,
            'csrf' => csrf_get_token('auth.verify')
        ));
    }
}

?>