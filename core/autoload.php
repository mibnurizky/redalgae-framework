<?php
ini_set('session.cookie_lifetime', 0);
session_start();

/** Define */
define('ROOT_PATH',$_SERVER['DOCUMENT_ROOT']);

/** Include Helper */
foreach (glob(ROOT_PATH.'/helpers/*.php') as $filename)
{
    include_once $filename;
}

/** Include Database */
include_once ROOT_PATH.'/core/database.php';

/** Include Cache */
include_once ROOT_PATH.'/core/cache.php';

/** Include App Config */
include_once ROOT_PATH.'/core/app.php';

/** Include Component */
include_once ROOT_PATH.'/core/session.php';

/** Include Component */
include_once ROOT_PATH.'/core/component.php';

/** Include Model */
include_once ROOT_PATH.'/core/model.php';

/** Include Model */
include_once ROOT_PATH.'/core/execution.php';

/** Include Middleware */
include_once ROOT_PATH.'/core/middleware.php';

/** Bootstrap */
global $CComponent,$CApp,$CDatabase,$CModel;
$CComponent = new Component();
$CApp = new App();
$CDatabase = new Database();
$CModel = new Model();
$CCache = new Cache();
$CSession = new Session();
$CExecution = new Execution();
$CMiddleware = new Middleware();

/** Defined */
define('COMPONENT',$CComponent);
define('APP',$CApp);
define('DATABASE',$CDatabase);
define('MODEL',$CModel);
define('CACHE',$CCache);
define('SESSION',$CSession);
define('EXECUTION',$CExecution);
define('MIDDLEWARE',$CMiddleware);

$path = ltrim($_SERVER['REQUEST_URI'], '/');
$explode = explode('/',$path);
$explode = explode('?',$explode[0]);

if($CApp->config['rewrite']){
    if(empty($explode[0])){
        $CComponent->redirect($CApp->default_component);
    }
}
else{
    if(empty($_GET['c'])){
        $CComponent->redirect($CApp->default_component);
    }
}

EXECUTION->start('GENERAL');
MIDDLEWARE->runMiddlewareGeneral('before');

if($CApp->config['rewrite']){
    $CComponent->includeComponent($explode[0]);
}
else{
    $CComponent->includeComponent($_GET['c']);
}

MIDDLEWARE->runMiddlewareGeneral('after');
EXECUTION->end('GENERAL');
if($CApp->show_execution_time){
    echo "<pre>";
    print_r(EXECUTION->calculate_all());
}
?>