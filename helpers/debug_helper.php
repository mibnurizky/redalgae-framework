<?php

function debug($data,$data_type=false){
    echo '<pre>';
    if($data_type){
        var_dump($data_type);
    }
    else{
        print_r($data);
    }
    die();
}

function debugFile($var,$file='text.txt',$flag=0){
    $dir = documentRoot('/writepath/debug/');
    if(!is_dir($dir)){
        mkdir($dir,0755,true);
    }
    file_put_contents(documentRoot('/writepath/debug/'.$file), print_r($var,true), $flag);
}

?>