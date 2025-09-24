<?php

function smtp_notif_send($subject,$body,$to=array(),$cc=array(),$bcc=array(),$attachments=array(),&$error=""){
    global $APP;
    $email = new Email();

    $host = $APP->config['smtp']['host'];
    $port = $APP->config['smtp']['port'];
    $username = $APP->config['smtp']['username'];
    $password = $APP->config['smtp']['password'];
    $alias = 'Toddler IO Notification';
    $encryption = $APP->config['smtp']['encryption'];
    $email->smtpInit(
        host: $host,
        port: $port,
        username: $username,
        password: $password,
        alias: $alias,
        encryption: $encryption
    );

    $result = $email->smtpSend(
        subject: $subject,
        body: $body,
        to: $to,
        cc: $cc,
        bcc: $bcc,
        attachments: $attachments,
        error: $error
    );

    if($result){
        return true;
    }
    else{
        return false;
    }
}

?>