<?php

function curl2run($method,$url,$data=array(),$header=array(),$port='',$timeout=30){
    $curl = curl_init();

    $url = $url;

    $setting = array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => $timeout,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_POSTFIELDS => http_build_query($data)
    );

    if(!empty($header)){
        $setting[CURLOPT_HTTPHEADER] = $header;
    }

    if(!empty($port)){
        $setting[CURLOPT_PORT] = $port;
    }

    curl_setopt_array($curl, $setting);

    $response = curl_exec($curl);
    debug_file($response,'/curl.txt');
    $err = curl_error($curl);
    debug_file($err,'/curl_error.txt');

    curl_close($curl);

    if($err){
        return array();
    }
    else{
        $json = json_decode($response,true);
        return $json;
    }
}

?>