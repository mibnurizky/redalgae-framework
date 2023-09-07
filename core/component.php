<?php
class Component{
    public function includeComponent($component=''){
        $app = new App();
        $CModel = new Model();
        $CCache = new Cache();
        if(empty($component)){
            $component = $app->default_component;
        }

        $component = str_replace('.','/',$component);

        $component_file = ROOT_PATH.'/components/'.$component.'.php';
        if(file_exists($component_file)){
            include $component_file;
        }
        else{
            echo "Page Not Found";
            exit();
        }
    }
    public function redirect($component,$parameters=array()){
        $parameters['c'] = $component;
        $query = http_build_query($parameters);
        header('Location: ?'.$query);
        exit();
    }
}
?>