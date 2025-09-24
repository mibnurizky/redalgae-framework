<?php
global $MODEL, $SESSION, $COMPONENT;
$MODEL->includeModel('workspace_user');

$lang = new Language('web.manage.workspace.view');
$workspace_user = new workspaceUserModel();
if(!$workspace_user->canAccess($_REQUEST['id'])){
    $SESSION->flash_set('alert',array(
        'title' => 'Access Denied',
        'message' => 'you do not have access to this workspace',
        'type' => 'error'
    ));

    $COMPONENT->redirect('web.manage.workspace');
}

?>