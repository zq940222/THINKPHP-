<?php
if(version_compare(PHP_VERSION,'5.3.0','<'))  die('require PHP > 5.3.0 !');
define('BUILD_DIR_SECURE', false);
define('APP_DEBUG',false);
define('APP_PATH','./Application/');
define('APP_DEPLOY_NAME', '/yihuigou');
require './ThinkPHP/ThinkPHP.php';