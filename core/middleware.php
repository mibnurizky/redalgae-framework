<?php
class Middleware{
    public function includeMiddleware($middleware=''){
        $middleware = str_replace('.','/',$middleware);
        $middleware_file = ROOT_PATH.'/middleware/'.$middleware.'.php';
        if(file_exists($middleware_file)){
            include_once $middleware_file;
        }
    }
    public function runMiddleware($middleware=array(),$run=array('before'),$parameters=array()){
        if(!empty($middleware)){
            if(!is_array($middleware)){
                $middleware = [$middleware];
            }
            $midd = array();
            foreach($middleware as $key_mid => $val_mid){
                $this->includeMiddleware($val_mid);
                $mid_explode = explode('.',$val_mid);
                $arMid = array();
                foreach($mid_explode as $key_mid_ex => $val_mid_ex){
                    $arMid[] = ucfirst($val_mid_ex);
                }
                $midd[] = implode('',$arMid).'Middleware';
            }

            if(in_array('before',$run)){
                foreach($midd as $key_mid_class => $val_mid_class){
                    if(class_exists($val_mid_class)){
                        $instance_mid = new $val_mid_class();
                        $instance_mid->before($parameters);
                    }
                }
            }

            if(in_array('after',$run)){
                foreach($midd as $key_mid_class => $val_mid_class){
                    if(class_exists($val_mid_class)){
                        $instance_mid = new $val_mid_class();
                        $instance_mid->after($parameters);
                    }
                }
            }
        }
    }
}
?>