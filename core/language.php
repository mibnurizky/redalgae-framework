<?php

class Language{
    public $lang = [];
    public $lang_path = "";

    public function __construct($langpath='',$langcode=''){
        global $APP;

        if($langpath != ''){
            $langpath_ori = $langpath;
            if(empty($langcode)){
                $langcode = $APP->config['default_language'];
            }

            $langpath = str_replace('.','/',$langpath);
            if(file_exists(ROOT_PATH.'/lang/'.$langcode.'/'.$langpath.'.php')){
                include ROOT_PATH.'/lang/'.$langcode.'/'.$langpath.'.php';
            }

            $this->lang_path = $langpath_ori;
            $this->lang[$this->lang_path] = $lang;
        }
    }

    public function set($langpath,$langcode=''){
        global $APP;
        if($langpath != ''){
            if(empty($langcode)){
                $langcode = $APP->config['default_language'];
            }

            $langpath = str_replace('.','/',$langpath);
            if(file_exists(ROOT_PATH.'/lang/'.$langcode.'/'.$langpath.'.php')){
                include ROOT_PATH.'/lang/'.$langcode.'/'.$langpath.'.php';
            }

            $this->lang_path = $langpath;
            $this->lang[$this->lang_path] = $lang;
        }
    }

    public function get($key='',$params=array()){
        if($key == ''){
            return $this->lang;
        }
        else{
            if(is_array($params) AND count($params) > 0){
                $lang = isset_multi($this->lang,[$this->lang_path,$key]) ? $this->lang[$this->lang_path][$key] : '';
                foreach ($params as $key => $value){
                    $lang = str_replace('#'.$key.'#',$value,$lang);
                }
            }
            else{
                $lang = isset_multi($this->lang,[$this->lang_path,$key]) ? $this->lang[$this->lang_path][$key] : '';
            }

            return $lang;
        }
    }
}

?>