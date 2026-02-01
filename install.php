#!/usr/bin/env php
<?php
/**
 * RedAlgae Framework Installer Script
 * Run after 'composer create-project' to setup new project
 */

/**
 * Recursively copy a directory
 */
function recursiveCopy($src, $dest) {
    $dir = opendir($src);
    @mkdir($dest, 0755, true);
    
    while (false !== ($file = readdir($dir))) {
        if ($file != '.' && $file != '..') {
            $srcFile = $src . '/' . $file;
            $destFile = $dest . '/' . $file;
            
            if (is_dir($srcFile)) {
                recursiveCopy($srcFile, $destFile);
            } else {
                copy($srcFile, $destFile);
            }
        }
    }
    closedir($dir);
}

echo "\n";
echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║        RedAlgae Framework Installation Starting...           ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

$projectRoot = getcwd();

// 1. Copy framework folders
echo "📁 Setting up framework structure...\n";

$frameworkFolders = [
    'core',
    'components',
    'helpers',
    'middleware',
    'models',
    'views',
];

foreach ($frameworkFolders as $folder) {
    $src = __DIR__ . '/' . $folder;
    $dest = $projectRoot . '/' . $folder;
    
    if (is_dir($src)) {
        if (!is_dir($dest)) {
            recursiveCopy($src, $dest);
            echo "  ✓ Copied: $folder/\n";
        } else {
            echo "  ✓ Already exists: $folder/\n";
        }
    }
}

// 2. Create writepath directory if not exists
echo "\n📁 Creating writepath directory...\n";
$writepathDirs = [
    'writepath',
    'writepath/cache',
    'writepath/logs',
];

foreach ($writepathDirs as $dir) {
    $path = $projectRoot . '/' . $dir;
    if (!is_dir($path)) {
        mkdir($path, 0755, true);
        echo "  ✓ Created: $dir/\n";
    } else {
        echo "  ✓ Already exists: $dir/\n";
    }
}

// 3. Copy important files from framework
echo "\n📄 Copying framework files...\n";

$frameworkFiles = [
    '.gitignore',
    'README.md',
    'LICENSE',
];

foreach ($frameworkFiles as $file) {
    $src = __DIR__ . '/' . $file;
    $dest = $projectRoot . '/' . $file;
    
    if (file_exists($src) && !file_exists($dest)) {
        copy($src, $dest);
        echo "  ✓ Copied: $file\n";
    }
}
// 4. Copy stub files if they don't exist
echo "\n📝 Setting up configuration files...\n";

$stubFiles = [
    'config/app.php' => 'config_app',
    'config/database.php' => 'config_database',
];

foreach ($stubFiles as $file => $stubName) {
    $filePath = $projectRoot . '/' . $file;
    if (!file_exists($filePath)) {
        $stubPath = __DIR__ . '/resources/stubs/' . $stubName . '.stub';
        if (file_exists($stubPath)) {
            copy($stubPath, $filePath);
            echo "  ✓ Created: $file\n";
        }
    } else {
        echo "  ✓ Already exists: $file\n";
    }
}

// 5. Create .htaccess if not exists
echo "\n🔒 Setting up .htaccess...\n";
$htaccessPath = $projectRoot . '/.htaccess';
if (!file_exists($htaccessPath)) {
    $htaccess = file_get_contents(__DIR__ . '/resources/stubs/htaccess.stub');
    file_put_contents($htaccessPath, $htaccess);
    echo "  ✓ Created: .htaccess\n";
} else {
    echo "  ✓ Already exists: .htaccess\n";
}

// 6. Create index.php if not exists
echo "\n📝 Setting up entry point...\n";
$indexPath = $projectRoot . '/index.php';
if (!file_exists($indexPath)) {
    $indexContent = file_get_contents(__DIR__ . '/resources/stubs/index.stub');
    file_put_contents($indexPath, $indexContent);
    echo "  ✓ Created: index.php\n";
} else {
    echo "  ✓ Already exists: index.php\n";
}

// 7. Set permissions
echo "\n🔐 Setting directory permissions...\n";
chmod($projectRoot . '/writepath', 0755);
chmod($projectRoot . '/writepath/cache', 0755);
echo "  ✓ Permissions set\n";

echo "\n";
echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║          ✓ RedAlgae Framework Ready to Use!                 ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n";
echo "\nNext steps:\n";
echo "  1. Edit config/app.php for application settings\n";
echo "  2. Edit config/database.php for database configuration\n";
echo "  3. Run: php -S localhost:8000\n";
echo "  4. Visit: http://localhost:8000\n\n";
