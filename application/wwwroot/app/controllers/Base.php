<?php 
class BaseController extends Yaf_Controller_Abstract
{
    
    function init()
    {
        $request = $this->getRequest()->getRequest();
        if (isset($request['pecker_jsonp']))
        {
            $this->getView()->setResultType(true);
        }
    }
}

?>