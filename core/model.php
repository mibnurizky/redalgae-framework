<?php
namespace RedAlgae\Core;

class Model{
    public function includeModel($model=''){
        $model = str_replace('.','/',$model);
        $model_file = ROOT_PATH.'/models/'.$model.'.php';
        if(file_exists($model_file)){
            require_once $model_file;
        }
    }
}
?>