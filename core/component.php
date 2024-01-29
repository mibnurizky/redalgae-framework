<?php
class Component{
    public function includeComponent($component='',$parameters=array(),$middleware=array(),$direct=true){
        $app = new App();
        $CModel = new Model();
        $CCache = new Cache();
        $CDatabase = new Database();
        $CComponent = new Component();
        $CSession = new Session();
        if(empty($component)){
            $component = $app->default_component;
        }

        $component = str_replace('.','/',$component);

        $component_file = ROOT_PATH.'/components/'.$component.'.php';
        if(file_exists($component_file)){

            MIDDLEWARE->runMiddleware($middleware,['before'],$parameters);

            include $component_file;

            MIDDLEWARE->runMiddleware($middleware,['after'],$arResult);

        }
        else{
            if($direct){
                $this->redirect('error.404');
                exit();
            }
        }
    }
    public function includeView($view='',$data=array(),$return=false){
        $view = str_replace('.','/',$view);
        $view_file = ROOT_PATH.'/views/'.$view.'.php';
        if(file_exists($view_file)){
            ob_start();
            extract($data);
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
        $parameters['c'] = $component;
        $query = http_build_query($parameters);
        header('Location: ?'.$query,true,$response_code);
        exit();
    }
    public function routeto($component,$parameters=array()){
        $parameters['c'] = $component;
        $query = http_build_query($parameters);
        return APP->base_url().'?'.$query;
    }
}
?>