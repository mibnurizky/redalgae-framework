<?php

function view($view='',$data=array(),$return=false){
    global $COMPONENT;

    $content = $COMPONENT->includeView($view,$data,true);
    $navbar = $COMPONENT->includeView('assets.ui.main.navbar',[],true);
    $sidebar = $COMPONENT->includeView('assets.ui.main.sidebar',[],true);
    $floatingfab = $COMPONENT->includeView('assets.ui.main.floatingfab',[],true);
    $body = $COMPONENT->includeView('assets.ui.main.body',[
        'toddler_content' => $content,
        'toddler_navbar' => $navbar,
        'toddler_sidebar' => $sidebar,
        'toddler_floating_fab' => $floatingfab
    ],true);

    $html = $body;

    if($return){
        return $html;
    }
    else{
        echo $html;
    }
}

function view_auth($view='',$data=array(),$return=false){
    global $COMPONENT;

    if($return){
        $result = $COMPONENT->includeView('assets.ui.auth.header',$data,$return);
        $result .= $COMPONENT->includeView($view,$data,$return);
        $result .= $COMPONENT->includeView('assets.ui.auth.footer',$data,$return);
        return $result;
    }
    else{
        $COMPONENT->includeView('assets.ui.auth.header',$data);
        $COMPONENT->includeView($view,$data,$return);
        $COMPONENT->includeView('assets.ui.auth.footer',$data);
    }
}

?>