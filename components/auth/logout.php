<?php

$SESSION->destroy();
if(!empty($_REQUEST['backurl'])){
    $COMPONENT->redirect('auth.login',['backurl' => $_REQUEST['backurl']]);
}
else{
    $COMPONENT->redirect('auth.login');
}

?>