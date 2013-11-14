<?php
/**
 * @version $Id: Index.php 9 2013-11-14 07:22:38Z cfc4n $
 * @author CFC4N 
 *
 */
class IndexController extends BaseController
{

    public function indexAction ()
    {
/*         Yaf_Dispatcher::getInstance()->throwException(true);
        $this->_view->assign('msg', 'Coming Soon');
        $this->getView()->display('index\pindex.html'); */
        Header("HTTP/1.1 301 Moved Permanently");
        Header("Location: http://www.cnxct.com/pecker-scanner/");
        return true;
    }

    function __destruct()
    {
    }
}