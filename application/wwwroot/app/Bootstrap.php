<?php
/**
 *
 * 框架初始化类
 *
 */
class Bootstrap extends Yaf_Bootstrap_Abstract
{

    public static $start = 0;

    public function __construct ()
    {
    }

    public function _initSystem (Yaf_Dispatcher $dispatcher)
    {
        $config = include CONFIG_PATH . '/config.php';
        if ($config['debug'])
        {
            set_error_handler('errorHandler');
        }
        else 
        {
            ini_set('memory_limit', '1280M');
            ini_set('default_socket_timeout', -1);
            ini_set('display_errors', 'off');
            ini_set('error_reporting', 0);
        }
    }

    /**
     * 初始化日志
     * @param Yaf_Dispatcher $dispatcher
     */
    public function _initLog (Yaf_Dispatcher $dispatcher)
    {
        Afx_Logger::$_logpath = SYSTEM_PATH . '/logs/';
    }

    /**
     * 初始化数据库链接 mysql mongo memcache
     * @param Yaf_Dispatcher $dispatcher
     */
    public function _initDb (Yaf_Dispatcher $dispatcher)
    {

        if (file_exists(CONFIG_PATH . '/config.php'))
        {
            $config = include CONFIG_PATH . '/config.php';
            date_default_timezone_set($config['timezone']);
            try
            {
                Yaf_Registry::set('config', $config);
                $driver=Afx_Db_Factory::DbDriver($config['mysql']['driver']);

                $driver::debug($config['debug']);
                $driver->setConfig($config['mysql']);
                Afx_Module::Instance()->setAdapter($driver);
/*                 $cache = NULL;
                switch ($config['cache']['driver'])
                {
                    case 'memcache':
                        $cache = Afx_Cache_Memcache_Adapter::Instance();
                        break;
                    case 'memcached':
                        $cache = Afx_Cache_Memcached_Adapter::Instance();
                        break;
                    case 'redis':
                        $cache = Afx_Cache_Redis_Adapter::Instance();
                        break;
                    default:
                        break;
                }
                if (extension_loaded('redis'))
                {
                    $redis = Afx_Cache_Redis_Adapter::Instance();
                    $redis->setConfig($config['redis']);
                    $redis->init();
                } */
            } catch (Exception $e)
            {
                Yaf_Registry::set('exception', $e);
                $req = $dispatcher->getRequest();
                $req->setActionName('Error');
                $req->setControllerName('Error');
            }
        }
        Yaf_Dispatcher::getInstance()->disableView();
    }

    public function _initView (Yaf_Dispatcher $displatcher)
    {
        $view = new View_Adapter(APP_PATH.'/app/views');
        Yaf_Registry::set('view', $view);
        Yaf_Dispatcher::getInstance()->setView($view);
    }

    public function __destruct ()
    {
    }
}
class View_Adapter extends Yaf_View_Simple
{

    protected  $_tpl_vars;

    private $_success = true;

    private $_message;

    public function assign ($spec, $value = null)
    {
        if (is_array($spec))
        {
            foreach ($spec as $key => $val)
            {
                $this->_tpl_vars[$key] = $val;
            }
        } else
        {
            $this->_tpl_vars[$spec] = $value;
        }
    }

    public function get($name=null)
    {
        $return['ret'] = $this->_success;
        $return['msg'] = $this->_message;
        $return['data'] = $this->_tpl_vars;
        return $return;
    }

    private function tojson()
    {
        header('content-type:application/json;charset=utf-8');
        $return['ret'] = $this->_success;
        $return['msg'] = $this->_message;
        $return['data'] = $this->_tpl_vars;
//         echo json_encode($return);
        echo 'pecker_jsonp('.json_encode($return).')';
    }
    
    public function clear ($name=NULL)
    {
        $this->_success = true;
        $this->_message = null;
        $this->_tpl_vars = null;
    }

    public function success ($data = null,$message = null)
    {
        $this->clear();
        if ($data !== null)
        {
            $this->assign($data);
        }
        $this->_success = MStatus::RETURN_SUCCESS;
        $this->_message = $message;
        return $this->tojson();
    }

    public function error ($data =null, $message = null, $errorCode)
    {
        $this->clear();
        if ($data !== null)
        {
            $this->assign($data);
        }
        $this->_success = $errorCode;
        $this->_message = $message;
        return $this->tojson();
    }
}

function errorHandler($errno, $errstr, $errfile, $errline)
{
    //不让你看
}