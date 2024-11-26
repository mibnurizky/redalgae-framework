<?php
class App{
    public $default_component            = '';
    public $config                       = array();
    public $session_db                   = true;
    public $show_execution_time          = false;
    public $http_host                    = '';

    public function __construct(){
        include ROOT_PATH.'/config/app.php';

        $this->default_component = $app_config['default_page'];
        $this->config = $app_config;
        if(isset($app_config['base_url'])){
            $this->http_host = $app_config['base_url'];
        }
    }

    public function base_url($url=''){
        if(!empty($this->http_host)){
            return $this->http_host.$url;
        }

        $https = ($_SERVER['HTTPS'] == 'on') ? 'https' : 'http';
        $https = $https.'://'.$_SERVER['HTTP_HOST'].$url;
        return $https;
    }
}
?>