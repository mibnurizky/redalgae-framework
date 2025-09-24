<?php
MODEL->includeModel('workspace');
MODEL->includeModel('workspace_user');

switch ($_REQUEST['page']){
    case 'add':
        break;
    case 'sidebar':
        workspace_sidebar();
        break;
    case 'form':
        workspace_form();
    default:
        workspace_list();
}

function workspace_form(){
    switch ($_REQUEST['act']) {
        case 'save':
            workspace_form_save();
            break;
    }
}

function workspace_form_save(){
    global $CURRENTUSER;
    $lang = new Language('components.web.manage.workspace');

    if(!csrf_validate_token('workspace.form',$_REQUEST['_csrf_token'])){
        responseJSON(array(
            'status' => 'error',
            'message' => $lang->get('csrf_not_valid'),
            'csrf' => csrf_get_token('workspace.form')
        ));
    }

    $workspace = new workspaceModel($CURRENTUSER['ID']);
    $error = "";
    $result = $workspace->add($_REQUEST['name'],$error);
    if($result){
        responseJSON(array(
            'status' => 'success',
            'message' => $lang->get('form_add_success'),
            'csrf' => csrf_get_token('workspace.form')
        ));
    }
    else{
        responseJSON(array(
            'status' => 'error',
            'message' => $error,
            'csrf' => csrf_get_token('workspace.form')
        ));
    }
}

function workspace_sidebar(){
    global $CURRENTUSER;
    $lang = new Language('components.web.manage.workspace');

    switch ($_REQUEST['act']) {
        case 'set_active':
            $workspace_user = new workspaceUserModel($CURRENTUSER['ID']);
            $error = "";
            $result = $workspace_user->setActive($_REQUEST['id'],"",$error);
            if($result){
                $workspace_data = $workspace_user->workspace->getByID($_REQUEST['id']);
                responseJSON(array(
                    'status' => 'success',
                    'message' => $lang->get('workspace_sidebar_set_active_success',['WORKSPACE_NAME' => $workspace_data['NAME']]),
                    'csrf' => csrf_get_token('workspace.sidebar')
                ));
            }
            else{
                responseJSON(array(
                    'status' => 'error',
                    'message' => $error,
                    'csrf' => csrf_get_token('workspace.sidebar')
                ));
            }
            break;
    }
}

function workspace_list(){
    switch ($_REQUEST['act']){
        case 'workspace_get_list':
            workspace_list_table();
            break;
        case 'delete':
            workspace_delete();
            break;
    }

    if(empty($_REQUEST['act'])){
        view('main.manage.workspace.list');
    }
}

function workspace_list_table(){
    global $CURRENTUSER, $COMPONENT;
    $workspace = new workspaceModel();
    $workspace_user = new workspaceUserModel();
    $lang = new Language('components.web.manage.workspace');

    if(!csrf_validate_token('workspace.list.table',$_REQUEST['_csrf_token'])){
        responseJSON(array(
            'error' => $lang->get('csrf_not_valid')
        ));
    }

    $table = "
        ".$workspace_user->table." as tbl_workspace_user
        JOIN ".$workspace->table." as tbl_workspace ON tbl_workspace_user.WORKSPACE_ID = tbl_workspace.ID AND tbl_workspace_user.USER_ID = '".$CURRENTUSER['ID']."' AND tbl_workspace.IS_DELETED != 'Y'
    ";

    // Table's primary key
    $primaryKey = 'tbl_workspace_user.WORKSPACE_ID';

    $default_workspace = $workspace_user->getDefaultID();

    $columns = array(
        array('db' => 'tbl_workspace.NAME', 'dt' => 0, 'as' => 'WORKSPACE_NAME', 'formatter' => function ($d, $row) use ($lang) {
            $default = "";
            if($row['IS_DEFAULT'] == 'Y'){
                $default = '<span class="tag is-primary">'.$lang->get('workspace_default').'</span>';
            }
            return $row['WORKSPACE_NAME'].' '.$default;
        }),
        array('db' => 'tbl_workspace_user.ROLE', 'dt' => 1, 'formatter' => function ($d, $row) {
            return $row['ROLE'];
        }),
        array('db' => 'tbl_workspace_user.WORKSPACE_ID', 'dt' => 2, 'formatter' => function ($d, $row) use ($COMPONENT, $lang, $default_workspace) {
            if($row['WORKSPACE_ID'] == $default_workspace){
                $html = '
                    <div class="buttons">
                        <a href="'.$COMPONENT->routeto('web.manage.workspace.view',['id' => $row['WORKSPACE_ID']]).'" class="button is-small is-link">'.$lang->get('workspace_table_button_action_view').'</a>
                    </div>
                ';
            }
            else{
                $html = '
                    <div class="buttons">
                        <a href="'.$COMPONENT->routeto('web.manage.workspace.view',['id' => $row['WORKSPACE_ID']]).'" class="button is-small is-link">'.$lang->get('workspace_table_button_action_view').'</a>
                        <a href="'.$COMPONENT->routeto('web.manage.workspace',['act' => 'delete', 'id' => $row['WORKSPACE_ID']]).'" class="button is-small is-danger link-confirm" data-title="'.$lang->get('workspace_confirm_delete_title').'" data-description="'.$lang->get('workspace_confirm_delete_description').'" data-type="warning" data-button-yes="'.$lang->get('workspace_confirm_delete_button_yes').'" data-button-no="'.$lang->get('workspace_confirm_delete_button_no').'">
                            <span class="icon">
                                <i class="fas fa-times"></i>
                            </span>
                        </a>
                    </div>
                ';
            }
            return $html;
        }),
        array('db' => 'tbl_workspace_user.IS_DEFAULT', 'dt' => 3)
    );

    $whereResult = "";
    if (isset($_POST['search'])) {
        $search = $_POST['search']['value'];
        $whereResult = " tbl_workspace.NAME LIKE '%" . $search . "%'";

        unset($_POST['search']);
    }

    $result = SSP::complex($_POST, $table, $primaryKey, $columns, $whereResult);
    $result['csrf'] = csrf_get_token('workspace.list.table');
    responseJSON($result);
}

function workspace_delete(){
    global $SESSION, $COMPONENT;

    $lang = new Language('components.web.manage.workspace');
    $workspace_user = new workspaceUserModel();

    if($workspace_user->isOwner($_REQUEST['id'])){
        $error = "";
        $result = $workspace_user->workspace->delete($_REQUEST['id'],"",$error);
        if($result){
            $workspacedata = $workspace_user->workspace->getByID($_REQUEST['id']);
            $SESSION->flash_set('alert',array(
                'title' => $lang->get('workspace_process_success'),
                'message' => $lang->get('workspace_delete_success',['WORKSPACE_NAME' => $workspacedata['NAME']]),
                'type' => 'success'
            ));
        }
        else{
            $SESSION->flash_set('alert',array(
                'title' => $lang->get('workspace_process_error'),
                'message' => $error,
                'type' => 'error'
            ));
        }
    }
    else{
        $SESSION->flash_set('alert',array(
            'title' => $lang->get('workspace_access_denied'),
            'message' => $lang->get('workspace_access_denied_description'),
            'type' => 'error'
        ));
    }

    $COMPONENT->redirect('web.manage.workspace');
}

?>