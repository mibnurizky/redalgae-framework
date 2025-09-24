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
    public function runMiddlewareGeneral($run='before',$component){
        global $COMPONENT;

        $middleware_file = ROOT_PATH.'/middleware/.general.php';
        if(file_exists($middleware_file)){
            include $middleware_file;

            foreach($middleware_default as $key => $row){
                $exclude = array();
                $include = array();
                if(!empty($row['exclude'])){
                    $exclude = (!is_array($row['exclude']) ? [$row['exclude']] : $row['exclude']);
                    $arExclude = array();
                    foreach($exclude as $key_exclude => $row_exclude){
                        if(!empty($row_exclude) AND $COMPONENT->isComponent($row_exclude)){
                            $arExclude[] = $row_exclude;
                        }
                    }
                    $exclude = $arExclude;
                }
                if(!empty($row['include'])){
                    $include = (!is_array($row['include']) ? [$row['include']] : $row['include']);
                    $arInclude = array();
                    foreach($include as $key_include => $row_include){
                        if(!empty($row_include) AND $COMPONENT->isComponent($row_include)){
                            $arInclude[] = $row_include;
                        }
                    }
                    $include = $arInclude;
                }

                if(!empty($include)){
                    if(in_array($component,$include) AND $COMPONENT->isComponent($component)){
                        $this->runMiddleware($key,$run);
                    }
                }
                else{
                    if(in_array($component,$exclude)){

                    }
                    else{
                        if($COMPONENT->isComponent($component)){
                            $this->runMiddleware($key,$run);
                        }
                    }
                }
            }
        }
    }
}
?>