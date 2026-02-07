<?php
namespace RedAlgae\Core;

class Component{
    public function includeComponent($component='',$parameters=array(),$middleware=array(),$direct=true){

        if(empty($component)){
            $component = APP->default_component;
        }

        $component = str_replace('-','/',$component);

        $component_file = ROOT_PATH.'/components/'.$component.'.php';
        if(file_exists($component_file)){

            MIDDLEWARE->runMiddleware($middleware,'before',$parameters);

            include $component_file;

            if(!isset($arResult)){
                $arResult = array();
            }
            MIDDLEWARE->runMiddleware($middleware,'after',$arResult);

        }
        else{
            if($direct){
                http_response_code(404);
                $this->includeView('error.404');
                exit();
            }
        }
    }
    public function includeView($view='',$data=array(),$return=false){

        $view = str_replace('.','/',$view);
        $view_file = ROOT_PATH.'/views/'.$view.'.php';
        $view_lang = ROOT_PATH.'/lang/'.APP->config['default_language'].'/views/'.$view.'.php';
        if(file_exists($view_file)){
            ob_start();
            extract($data);
            if(file_exists($view_lang)){
                include $view_lang;
            }
            include $view_file;
            $viewcontent = ob_get_contents();
            ob_end_clean();
            if($return){
                return $viewcontent;
            }
            else{
                echo $viewcontent;
            }
        }
    }
    public function redirect($component,$parameters=array(),$response_code=0){

        $component = str_replace('.','-',$component);

        if(APP->config['rewrite']){
            if(count($parameters) > 0){
                $query = http_build_query($parameters);
                header('Location: /'.$component.'?'.$query,true,$response_code);
            }
            else{
                header('Location: /'.$component,true,$response_code);
            }
            exit();
        }
        else{
            $parameters['c'] = $component;
            $query = http_build_query($parameters);
            header('Location: ?'.$query,true,$response_code);
            exit();
        }
    }
    public function routeto($component,$parameters=array()){

        $component = str_replace('.','-',$component);

        if(APP->config['rewrite']) {
            if (count($parameters) > 0) {
                $query = http_build_query($parameters);
                return APP->base_url().'/'.$component.'?'.$query;
            }
            else{
                return APP->base_url().'/'.$component;
            }
        }
        else{
            $parameters['c'] = $component;
            $query = http_build_query($parameters);
            return APP->base_url().'?'.$query;
        }
    }

    public function isComponent($component){
        $component = str_replace('.','/',$component);

        $component_file = ROOT_PATH.'/components/'.$component.'.php';
        if(file_exists($component_file)){
            return true;
        }
        else{
            return false;
        }
    }
}
?>