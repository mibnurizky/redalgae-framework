<?php

class Email
{
    private $smtp_host = null;
    private $smtp_username = null;
    private $smtp_password = null;
    private $smtp_encryption = 'tls';
    private $smtp_use_password = null;
    private $smtp_port = null;
    private $smtp_alias = null;
    public function smtpInit($host=null,$username=null,$alias=null,$password=null,$port=null,$encryption=null,$use_password=true){
        $this->smtp_host = $host;
        $this->smtp_username = $username;
        $this->smtp_password = $password;
        $this->smtp_encryption = $encryption;
        $this->smtp_use_password = $use_password;
        $this->smtp_port = $port;
        $this->smtp_alias = $alias;
    }

    public function smtpSend($subject,$body,$to=array(),$cc=array(),$bcc=array(),$attachments=array(),&$error="",$debug=false){
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        try {
            if($debug){
                $mail->SMTPDebug = \PHPMailer\PHPMailer\SMTP::DEBUG_SERVER;
            }
            else{
                $mail->SMTPDebug = \PHPMailer\PHPMailer\SMTP::DEBUG_OFF;
            }

            $mail->isSMTP();
            $mail->Host       = $this->smtp_host;
            $mail->Username   = $this->smtp_username;
            if(!empty($this->smtp_password)){
                $mail->Password = $this->smtp_password;
                $mail->SMTPAuth = true;
            }
            else{
                $mail->SMTPAuth = false;
            }
            if(!empty($this->smtp_encryption)){
                $mail->SMTPSecure = $this->smtp_encryption;
            }
            else{
                if($this->smtp_port == '465'){
                    $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;         //Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` encouraged
                }
                else{
                    $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;         //Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` encouraged
                }
            }
            $mail->Port       = $this->smtp_port;                                    //TCP port to connect to, use 465 for `PHPMailer::ENCRYPTION_SMTPS` above

            //Recipients
            if(!empty($this->smtp_alias)){
                $mail->setFrom($this->smtp_username, $this->smtp_alias);
            }
            else{
                $mail->setFrom($this->smtp_username);
            }

            foreach($to as $key => $value){
                $mail->addAddress($value);     //Add a recipient
            }
            foreach($cc as $key => $value){
                $mail->addCC($value);     //Add a recipient
            }
            foreach($bcc as $key => $value){
                $mail->addBCC($value);     //Add a recipient
            }
            foreach($attachments as $key => $value){
                if(is_array($value)){
                    $mail->addAttachment($value['path'], $value['name']);
                }
                else{
                    $mail->addAttachment($value);
                }
            }

            //Content
            $mail->isHTML(true);                                  //Set email format to HTML
            $mail->Subject = $subject;
            $mail->Body    = $body;
            $mail->AltBody = strip_tags($body);

            if($mail->send()){
                return true;
            }
            else{
                $error = $mail->ErrorInfo;
                return false;
            }
        } catch (\PHPMailer\PHPMailer\Exception $e){
            $error = $mail->ErrorInfo;
            return false;
        }
    }
}