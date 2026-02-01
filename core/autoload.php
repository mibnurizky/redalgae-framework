<?php
ini_set('session.cookie_lifetime', 0);
session_start();

/** Define */
define('ROOT_PATH',$_SERVER['DOCUMENT_ROOT']);

require_once ROOT_PATH . '/config/app.php';

if (!empty($app_config['log_errors'])) {
    ini_set('log_errors', '1');
    ini_set('error_log', $app_config['error_log_path']);
} else {
    ini_set('log_errors', '0');
}

if (!empty($app_config['debug'])) {
    ini_set('display_errors', $app_config['display_errors'] ? '1' : '0');
    ini_set('display_startup_errors', '1');
    error_reporting($app_config['error_reporting']);
} else {
    ini_set('display_errors', '0');
    ini_set('display_startup_errors', '0');
    error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
}

set_exception_handler(function ($e) use ($app_config) {
    if (!empty($app_config['debug'])) {
        echo "<pre>";
        echo "Uncaught Exception:\n";
        echo get_class($e).": ".$e->getMessage()."\n";
        echo $e->getFile().":".$e->getLine()."\n\n";
        echo $e->getTraceAsString();
        echo "</pre>";
    } else {
        http_response_code(500);
        echo "Internal Server Error";
    }
});

set_error_handler(function ($severity, $message, $file, $line) use ($app_config) {
    if (!empty($app_config['debug'])) {
        echo "<pre>PHP Error: $message\n$file:$line</pre>";
    }
});

/** Composer */
require_once ROOT_PATH.'/vendor/autoload.php';

/** Include Helper */
foreach (glob(ROOT_PATH.'/helpers/*.php') as $filename)
{
    include_once $filename;
}

spl_autoload_register(function ($class) {

    $prefix = 'RedAlgae\\';
    $baseDir = ROOT_PATH.'/';

    if (strncmp($prefix, $class, strlen($prefix)) !== 0) {
        return;
    }

    $relativeClass = substr($class, strlen($prefix));
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
    if(!is_file($file)){
        $file = $baseDir . str_replace('\\', '/', strtolower($relativeClass)) . '.php';
    }

    if (is_file($file)) {
        require_once $file;
    }
});

/** Bootstrap */
global $CComponent,$CApp,$CDatabase,$CModel,$CCache,$CSession,$CExecution,$CMiddleware;
$CComponent = new \RedAlgae\Core\Component();
$CApp = new \RedAlgae\Core\App();
$CDatabase = new \RedAlgae\Core\Database();
$CModel = new \RedAlgae\Core\Model();
$CCache = new \RedAlgae\Core\Cache();
$CSession = new \RedAlgae\Core\Session(true);
$CExecution = new \RedAlgae\Core\Execution();
$CMiddleware = new \RedAlgae\Core\Middleware();
$CLanguage = new \RedAlgae\Core\Language();

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
    $pathfix = str_replace('-','.',$explode[0]);
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