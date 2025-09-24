<?php
global $MODEL;
$MODEL->includeModel('user');

if(isset($_POST['ajax_act'])){
    switch ($_POST['ajax_act']){
        case 'send_forgotpassword':
            send_forgotpassword();
            break;
        case 'change_password':
            change_forgotpassword();
            break;
    }
}
else{
    if(!empty($_REQUEST['email']) AND !empty($_REQUEST['code'])){
        $user = new userModel();
        $error = "";
        if(!$user->checkResetPassword($_REQUEST['email'],$_REQUEST['code'],$error)){
            $SESSION->flash_set('alert',array(
                'title' => 'Error',
                'message' => $error,
                'type' => 'error'
            ));
            $COMPONENT->redirect('auth.forgotpassword');
        }
        view_auth('auth.forgotpassword_change');
    }
    else{
        view_auth('auth.forgotpassword');
    }
}

function change_forgotpassword(){
    $lang = new Language('components.auth.forgotpassword');

    /**
     * Limiter
     */
    $limiter = new Limiter('auth.forgotpassword.change',3,60);
    if($limiter->isMaxAttempt()){
        responseJSON(array(
            'status' => 'error',
            'message' => $lang->get('auth_forgotpassword_error_attempt',['AVAILABLE_IN' => $limiter->avaliableIn()]),
            'csrf' => csrf_get_token('auth.forgotpassword.change')
        ));
    }
    $limiter->hit();

    /**
     * Check CSRF
     */
    if(!csrf_input_validate('auth.forgotpassword.change')){
        responseJSON(array(
            'status' => 'error',
            'message' => $lang->get('auth_forgotpassword_error_csrf'),
            'csrf' => csrf_get_token('auth.forgotpassword.change')
        ));
    }

    /**
     * Change Password
     */
    $error = "";
    $user = new userModel();
    $result = $user->changeResetPassword($_REQUEST['email'],$_REQUEST['newpassword'],$_REQUEST['confirmnewpassword'],$_REQUEST['code'],$error);
    if(!$result){
        responseJSON(array(
            'status' => 'error',
            'message' => $error,
            'csrf' => csrf_get_token('auth.forgotpassword.change')
        ));
    }
    else{
        responseJSON(array(
            'status' => 'success',
            'message' => $lang->get('auth_forgotpassword_success_change'),
            'csrf' => csrf_get_token('auth.forgotpassword.change')
        ));
    }
}

function send_forgotpassword(){
    $lang = new Language('components.auth.forgotpassword');

    /**
     * Limiter Success
     */
    $limiter_success = new Limiter('auth.forgotpassword.success',1,600);
    if($limiter_success->isMaxAttempt()){
        responseJSON(array(
            'status' => 'error',
            'message' => $lang->get('auth_forgotpassword_error_attempt',['AVAILABLE_IN' => $limiter_success->avaliableIn()]),
            'csrf' => csrf_get_token('auth.forgotpassword')
        ));
    }

    /**
     * Limiter
     */
    $limiter = new Limiter('auth.forgotpassword',3,60);
    if($limiter->isMaxAttempt()){
        responseJSON(array(
            'status' => 'error',
            'message' => $lang->get('auth_forgotpassword_error_attempt',['AVAILABLE_IN' => $limiter->avaliableIn()]),
            'csrf' => csrf_get_token('auth.forgotpassword')
        ));
    }
    $limiter->hit();

    /**
     * Check CSRF
     */
    if(!csrf_input_validate('auth.forgotpassword')){
        responseJSON(array(
            'status' => 'error',
            'message' => $lang->get('auth_forgotpassword_error_csrf'),
            'csrf' => csrf_get_token('auth.forgotpassword')
        ));
    }

    /**
     * Send Email Forgot Password
     */
    $error = "";
    $user = new userModel();
    $result = $user->sendResetPasswordLink($_REQUEST['email'],$error);
    if(!$result){
        responseJSON(array(
            'status' => 'error',
            'message' => $error,
            'csrf' => csrf_get_token('auth.forgotpassword')
        ));
    }
    else{
        $limiter_success->hit();
        responseJSON(array(
            'status' => 'success',
            'message' => $lang->get('auth_forgotpassword_success_send'),
            'csrf' => csrf_get_token('auth.forgotpassword')
        ));
    }
}

?>