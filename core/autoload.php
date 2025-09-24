<?php
ini_set('session.cookie_lifetime', 0);
session_start();

/** Define */
define('ROOT_PATH',$_SERVER['DOCUMENT_ROOT']);

/** Composer */
require_once ROOT_PATH.'/core/vendor/autoload.php';

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

/** Include Language */
include_once ROOT_PATH.'/core/language.php';

/** Include CSRF */
include_once ROOT_PATH.'/core/csrf.php';

/** Include Limiter */
include_once ROOT_PATH.'/core/limiter.php';

/** Include Email */
include_once ROOT_PATH.'/core/email.php';

/** SSP Datatable */
include_once ROOT_PATH.'/core/ssp.php';

/** Bootstrap */
global $CComponent,$CApp,$CDatabase,$CModel,$CCache,$CSession,$CExecution,$CMiddleware;
$CComponent = new Component();
$CApp = new App();
$CDatabase = new Database();
$CModel = new Model();
$CCache = new Cache();
$CSession = new Session(true);
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
define('LANGUAGE',$CLanguage);
$GLOBALS['COMPONENT'] = $CComponent;
$GLOBALS['APP'] = $CApp;
$GLOBALS['DATABASE'] = $CDatabase;
$GLOBALS['MODEL'] = $CModel;
$GLOBALS['CACHE'] = $CCache;
$GLOBALS['SESSION'] = $CSession;
$GLOBALS['EXECUTION'] = $CExecution;
$GLOBALS['MIDDLEWARE'] = $CMiddleware;

$phpinput = file_get_contents('php://input');
$jsoninput = json_decode($phpinput,true);
if(!empty($jsoninput)){
    $_REQUEST = array_merge($_REQUEST,$jsoninput);
}
else{
    $phpinputarray = array();
    parse_str($phpinput, $phpinputarray);
    if(is_array($phpinputarray) AND count($phpinputarray) > 0){
        $_REQUEST = array_merge($_REQUEST,$phpinputarray);
    }
}

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

$CExecution->start('GENERAL');
if($CApp->config['rewrite']) {
    $GLOBALS['CURRENT_COMPONENT'] = $explode[0];
    $CMiddleware->runMiddlewareGeneral('before',$explode[0]);
}
else{
    $GLOBALS['CURRENT_COMPONENT'] = $_GET['c'];
    $CMiddleware->runMiddlewareGeneral('before',$_GET['c']);
}

if($CApp->config['rewrite']){
    $CComponent->includeComponent($explode[0]);
}
else{
    $CComponent->includeComponent($_GET['c']);
}

if($CApp->config['rewrite']) {
    $CMiddleware->runMiddlewareGeneral('after',$explode[0]);
}
else{
    $CMiddleware->runMiddlewareGeneral('after',$_GET['c']);
}
$CExecution->end('GENERAL');
if($CApp->show_execution_time){
    echo "<pre>";
    print_r($CExecution->calculate_all());
}
?>