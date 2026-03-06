<?php

use CodeIgniter\Boot;
use Config\Paths;

/*
 *---------------------------------------------------------------
 * SECURITY HEADERS
 *---------------------------------------------------------------
 */

// Set basic security headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');

// Remove server signature
header_remove('X-Powered-By');
header_remove('Server');

/*
 *---------------------------------------------------------------
 * SECURITY CHECKS
 *---------------------------------------------------------------
 */

// Get request URI and script name
$requestUri = $_SERVER['REQUEST_URI'] ?? '';
$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';

// List of blocked files and patterns
$blockedFiles = [
    'env', '.env', 'spark', 'spark.php', 'composer.json', 'composer.lock', 
    'package.json', '.gitignore', 'README.md', 'LICENSE', 'CHANGELOG.md',
    '.htaccess', '.htpasswd', 'web.config'
];

// List of blocked patterns
$blockedPatterns = [
    '/\.\./',           // Directory traversal
    '/app/',            // App directory
    '/system/',         // System directory
    '/writable/',       // Writable directory
    '/vendor/',         // Vendor directory
    '/tests/',          // Tests directory
    '/node_modules/',   // Node modules
    '/\.git/',          // Git directory
    '/\.svn/',          // SVN directory
    '/\..*/',           // Any hidden directory
];

// Check for blocked files
$pathInfo = pathinfo($requestUri);
$requestedFile = basename(parse_url($requestUri, PHP_URL_PATH));

foreach ($blockedFiles as $blockedFile) {
    if ($requestedFile === $blockedFile || strpos($requestUri, '/' . $blockedFile) !== false) {
        http_response_code(403);
        echo '<!DOCTYPE html>
<html>
<head>
    <title>403 - Access Denied</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body { 
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; 
            text-align: center; 
            margin: 0; 
            padding: 50px 20px; 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .container {
            background: rgba(255,255,255,0.1);
            padding: 40px;
            border-radius: 15px;
            backdrop-filter: blur(10px);
            box-shadow: 0 8px 32px rgba(0,0,0,0.1);
        }
        h1 { color: #fff; font-size: 3rem; margin-bottom: 20px; }
        p { color: rgba(255,255,255,0.8); font-size: 1.2rem; }
        .icon { font-size: 4rem; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="icon">🔒</div>
        <h1>403 - Access Denied</h1>
        <p>This page isn\'t working</p>
        <p>You don\'t have permission to access this resource.</p>
    </div>
</body>
</html>';
        exit(1);
    }
}

// Check for blocked patterns
foreach ($blockedPatterns as $pattern) {
    if (preg_match($pattern, $requestUri)) {
        http_response_code(403);
        echo '<!DOCTYPE html>
<html>
<head>
    <title>403 - Access Denied</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body { 
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; 
            text-align: center; 
            margin: 0; 
            padding: 50px 20px; 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .container {
            background: rgba(255,255,255,0.1);
            padding: 40px;
            border-radius: 15px;
            backdrop-filter: blur(10px);
            box-shadow: 0 8px 32px rgba(0,0,0,0.1);
        }
        h1 { color: #fff; font-size: 3rem; margin-bottom: 20px; }
        p { color: rgba(255,255,255,0.8); font-size: 1.2rem; }
        .icon { font-size: 4rem; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="icon">🚫</div>
        <h1>403 - Directory Access Denied</h1>
        <p>This page isn\'t working</p>
        <p>Access to this directory is forbidden.</p>
    </div>
</body>
</html>';
        exit(1);
    }
}

/*
 *---------------------------------------------------------------
 * CHECK PHP VERSION
 *---------------------------------------------------------------
 */

$minPhpVersion = '8.1'; // If you update this, don't forget to update `spark`.
if (version_compare(PHP_VERSION, $minPhpVersion, '<')) {
    $message = sprintf(
        'Your PHP version must be %s or higher to run CodeIgniter. Current version: %s',
        $minPhpVersion,
        PHP_VERSION,
    );

    header('HTTP/1.1 503 Service Unavailable.', true, 503);
    echo $message;
    exit(1);
}

/*
 *---------------------------------------------------------------
 * SET THE CURRENT DIRECTORY
 *---------------------------------------------------------------
 */

// Path to the front controller (this file)
define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR);

// Ensure the current directory is pointing to the front controller's directory
if (getcwd() . DIRECTORY_SEPARATOR !== FCPATH) {
    chdir(FCPATH);
}

/*
 *---------------------------------------------------------------
 * BOOTSTRAP THE APPLICATION
 *---------------------------------------------------------------
 * This process sets up the path constants, loads and registers
 * our autoloader, along with Composer's, loads our constants
 * and fires up an environment-specific bootstrapping.
 */

// LOAD OUR PATHS CONFIG FILE
// This is the line that might need to be changed, depending on your folder structure.
require FCPATH . 'app/Config/Paths.php';
// ^^^ Change this line if you move your application folder

$paths = new Paths();

// LOAD THE FRAMEWORK BOOTSTRAP FILE
require $paths->systemDirectory . '/Boot.php';

exit(Boot::bootWeb($paths));