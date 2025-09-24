<?php
global $MODEL;
$MODEL->includeModel('user');

if(isset($_POST['ajax_act'])){
    switch ($_POST['ajax_act']){
        case 'submit':
            submit();
            break;
    }
}
else{
    view_auth('auth.register');
}

function submit(){
    $lang = new Language('components.auth.register');

    $limiter = new Limiter('auth.register',3,60);
    if($limiter->isMaxAttempt()){
        responseJSON(array(
            'status' => 'error',
            'message' => 'try again in '.$limiter->avaliableIn().' seconds',
            'csrf' => csrf_get_token('auth.register')
        ));
    }
    $limiter->hit();

    $fields = array(
        'FULL_NAME' => $_REQUEST['name'],
        'WORKSPACE_NAME' => $_REQUEST['workspace'],
        'EMAIL' => $_REQUEST['email'],
        'PASSWORD' => $_REQUEST['password'],
        'CONFIRM_PASSWORD' => $_REQUEST['confirm_password']
    );

    /**
     * Check CSRF
     */
    if(!csrf_input_validate('auth.register')){
        responseJSON(array(
            'status' => 'error',
            'message' => $lang->get('auth_register_error_csrf'),
            'csrf' => csrf_get_token('auth.register')
        ));
    }

    $user = new userModel();
    $error = "";
    $newuserid = $user->add($fields,$error);
    if(!empty($error)){
        responseJSON(array(
            'status' => 'error',
            'message' => $error,
            'csrf' => csrf_get_token('auth.register')
        ));
    }

    $limiter->reset();
    responseJSON(array(
        'status' => 'success',
        'message' => '',
        'csrf' => csrf_get_token('auth.register'),
        'newuserid' => $newuserid
    ));

}

?>