<?php
/**
 * @version $Id: Index.php 2 2013-09-22 07:30:39Z cfc4n $
 * @author CFC4N 
 *
 */
class IndexController extends Yaf_Controller_abstract
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