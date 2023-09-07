<?php
class Component{
    public function includeComponent($component=''){
        $app = new App();
        $CModel = new Model();
        $CCache = new Cache();
        $CDatabase = new Database();
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
    public function includeView($view='',$data=array(),$return=false){
        $view = str_replace('.','/',$view);
        $view = ROOT_PATH.'/views/'.$view.'.php';
        if(file_exists($component_file)){
            ob_start();
            extract($data);
            include $component_file;
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
    public function redirect($component,$parameters=array()){
        $parameters['c'] = $component;
        $query = http_build_query($parameters);
        header('Location: ?'.$query);
        exit();
    }
}
?>