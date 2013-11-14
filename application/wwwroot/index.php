<?php
error_reporting(-1);
//ini_set('apc.debug', '0');

define('APP_PATH', dirname(realpath(__FILE__)));
define('SYSTEM_PATH', dirname(APP_PATH));
define('CONFIG_PATH',SYSTEM_PATH.'/config');
define('USE_GATEWAY',TRUE);
/**
 * yaf默认只支持application/library 目录下类的加载
 * 并且不允许Yaf打头的第三方类出现
 * 这里注册一个__autoload顺序加载application 下所有文件夹下的类
 * Yaf 默认认为每个类都应该有下划线每个下划线代表一层目录
 */
spl_autoload_register('__autoload');

try
{
    
    $app = new Yaf_Application(CONFIG_PATH . '/application.ini', 'production');
    $app->bootstrap()->run();
}
catch (Exception $e)
{
    //print_r($e);
    echo '<a href="http://www.cnxct.com/pecker-scanner/">Coming Soon......</a>';
}

function __autoload ($class_name)
{
    $path = dirname(APP_PATH);
    $root = APP_PATH . '/app';
    $lib_path = dirname($path) . "/framework";
    $module_path = $path . "/lib";
    $load_paths = "$root/models;$root/modules;$root/plugins;$root/controllers";
    $paths = explode(";", $load_paths);
    if (strstr($class_name, "_"))
    {
        $class_name = str_ireplace("_", "/", $class_name);
    }
    if (strstr($class_name, 'Controller'))
    {
        $class_name = str_ireplace("Controller", "", $class_name);
    }
    if (strncasecmp($class_name, 'Afx', 3) == 0)
    {
        if (file_exists($lib_path . "/" . $class_name . ".php"))
        {
            require_once $lib_path . "/" . $class_name . ".php";
            return;
        }
    }
    if (file_exists($module_path . "/" . $class_name . ".php"))
    {
        require_once $module_path . "/" . $class_name . ".php";
        return;
    }
    if (is_array($paths))
    {
        $i = 0;
        foreach ($paths as $path)
        {
            if (file_exists($path . "/" . $class_name . ".php"))
            {
                require_once $path . "/" . $class_name . ".php";
                return;
            }
        }
    }
    if (file_exists($class_name . "php"))
    {
        require_once $class_name . "php";
    }
}