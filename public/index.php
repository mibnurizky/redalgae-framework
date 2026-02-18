<?php
ini_set('session.cookie_lifetime', 0);
session_start();

/** Define */
define('ROOT_PATH',$_SERVER['DOCUMENT_ROOT']);
define('BASE_PATH', dirname(__DIR__));

require_once BASE_PATH . '/config/app.php';

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
require_once BASE_PATH.'/vendor/autoload.php';

/** Include Helper */
foreach (glob(BASE_PATH.'/helpers/*.php') as $filename)
{
    include_once $filename;
}

spl_autoload_register(function ($class) {

    $prefix  = 'RedAlgae\\';
    $baseDir = BASE_PATH . '/';

    if (strncmp($prefix, $class, strlen($prefix)) !== 0) {
        return;
    }

    $relativeClass = substr($class, strlen($prefix));
    $parts         = explode('\\', $relativeClass);
    $className     = array_pop($parts); // Nama class tetap as-is

    // Coba folder as-is dulu
    $dirAsIs   = $baseDir . implode('/', $parts);
    $fileAsIs  = $dirAsIs . '/' . $className . '.php';

    if (is_file($fileAsIs)) {
        require_once $fileAsIs;
        return;
    }

    // Fallback: folder lowercase, nama class tetap
    $dirLower  = $baseDir . strtolower(implode('/', $parts));
    $fileLower = $dirLower . '/' . $className . '.php';

    if (is_file($fileLower)) {
        require_once $fileLower;
        return;
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

$router = new \RedAlgae\Core\Router();

foreach (glob(BASE_PATH.'/routes/*.php') as $filename)
{
    require_once $filename;
}

$router->dispatch();