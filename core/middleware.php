<?php
class Middleware{
    public function includeMiddleware($middleware=''){
        $middleware = str_replace('.','/',$middleware);
        $middleware_file = ROOT_PATH.'/middleware/'.$middleware.'.php';
        if(file_exists($middleware_file)){
            include_once $middleware_file;
        }
    }
}
?>