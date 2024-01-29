<?php
class Middleware{
    public function includeMiddleware($middleware=''){
        $middleware = str_replace('.','/',$middleware);
        $middleware_file = ROOT_PATH.'/middleware/'.$middleware.'.php';
        if(file_exists($middleware_file)){
            include_once $middleware_file;
        }
    }
    public function runMiddleware($middleware=array(),$run='before',$parameters=array()){
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

            if($run == 'before'){
                foreach($midd as $key_mid_class => $val_mid_class){
                    if(class_exists($val_mid_class)){
                        $instance_mid = new $val_mid_class();
                        $instance_mid->before($parameters);
                    }
                }
            }

            if($run == 'after'){
                foreach($midd as $key_mid_class => $val_mid_class){
                    if(class_exists($val_mid_class)){
                        $instance_mid = new $val_mid_class();
                        $instance_mid->after($parameters);
                    }
                }
            }
        }
    }
    public function runMiddlewareGeneral($run='before'){
        $middleware_file = ROOT_PATH.'/middleware/.default.php';
        if(file_exists($middleware_file)){
            include $middleware_file;

            foreach($middleware_default as $key => $row){
                $exclude = array();
                $include = array();
                if(!empty($row['exclude'])){
                    $exclude = (!is_array($row['exclude']) ? [$row['exclude']] : $row['exclude']);
                }
                if(!empty($row['include'])){
                    $include = (!is_array($row['include']) ? [$row['include']] : $row['include']);
                }

                if(!empty($include)){
                    if(in_array($_GET['c'],$include)){
                        $this->runMiddleware($key,$run);
                    }
                }
                else{
                    if(in_array($_GET['c'],$exclude)){

                    }
                    else{
                        $this->runMiddleware($key,$run);
                    }
                }
            }
        }
    }
}
?>