<?php
namespace RedAlgae\Controllers;

class WelcomeController{
    public function index(){
        echo '<a href="'.route('welcome.test',['id'=>1]).'">oke</a>';
    }
    public function test($id){
        echo $id;
    }
}

?>