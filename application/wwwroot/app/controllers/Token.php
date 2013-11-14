<?php
/**
 * @version $Id: Token.php 9 2013-11-14 07:22:38Z cfc4n $
 * @author CFC4N 
 *
 */
class TokenController extends BaseController
{

    private $_mToken;
    
    public function indexAction ()
    {
        $t = trim($this->getRequest()->getRequest('t'));
        $sourceMd5 = trim($this->getRequest()->getRequest('md5'));
        $arrFailed = array($sourceMd5 => array());
        if ($t == '' || ($strToken = base64_decode($t)) === false)
        {
            $this->getView()->error($arrFailed, 'Parameter is not legitimate.',MStatus::RETURN_ILLEGAL_PARAM);
            return ;
        }
        $config = Yaf_Registry::get('config');

        $scaner = new Pecker_Scanner();
        $scaner->setPath($config['scandir']);    // set directory to scan
        $scaner->setExtend($config['extend']);
        $scaner->setFunction($config['function']);
        $scaner->ScanCode('<?php '.$strToken.'; ?>');
        $result = $scaner->getReport();    //其实，就一个文件，所以 result里，就一个大数组

        $arrTokens = array();
        $arrParse = array();
        foreach ($result as $k => $v)
        {
            if ($v['parser'] === false)
            {
                $this->getView()->error($arrFailed, $v['message'],MStatus::RETURN_SYNTAX_ERROR);
                return;
            }
            else 
            {
                foreach ($v['function'] as $func => $line)
                {
                    foreach ($line as $c)
                    {
                        $strEvilCode = $func.$c['codeless'];
                        $arrTokens[] = md5($strEvilCode);
                        $arrParse[] = array('tid'=>md5($strEvilCode),'token_info'=>$strEvilCode,'token_function'=>$func,'status'=>0,'add_time'=>time());
                    }
                }
                if (count($arrTokens) == 0)
                {
                    $this->getView()->error($arrFailed, 'Not found evil code.',MStatus::RETURN_NONE_EVIL_CODE);
                    return ;
                }
            }
        }
        
        $strMd5 = $arrTokens[0];
        $arrReturn = $this->_getToken($strMd5);
        if (count($arrReturn))
        {
            $this->getView()->success(array($strMd5=>$arrReturn));
            return ;
        }
        $this->getView()->error($arrFailed, 'Thanks for first commit,We will confirm it next time.',MStatus::RETURN_NOT_FOUND_IN_DB);
        return ;
    }

    /**
     * 根据MD5 hash 获取信息
     * 外部调用方法
     */
    public function md5Action()
    {
        $t = trim($this->getRequest()->getRequest('t'));
        $sourceMd5 = trim($this->getRequest()->getRequest('md5'));
        $arrFailed = array($sourceMd5 => array());
        if (strlen($t) !== 32)
        {
            $this->getView()->error($arrFailed, 'Parameter is not legitimate.',MStatus::RETURN_ILLEGAL_PARAM);
            return ;
        }
        $arrReturn = $this->_getToken($t);
        if (count($arrReturn))
        {
            $this->getView()->success(array($t=>$arrReturn));
            return ;
        }
        $this->getView()->error($arrFailed, 'Thanks for first commit,We will confirm it next time.',MStatus::RETURN_NOT_FOUND_IN_DB);
        return ;
    }

    /**
     * 根据token的MD5 hash ，获取该token相关信息
     * @param string $md5
     * @return array
     */
    private function _getToken($md5)
    {
        $arrReturn = array();
        //从DB中拉取该MD5对应的数据
        $this->_mToken = new MToken($md5);    //只检测第一个恶意函数。
        $arrInfo = $this->_mToken->getToken();
        if (count($arrInfo))
        {
            $arrInfoMore = $this->_mToken->getTokenMore();
            $arrReturn = $this->_resetToken($arrInfo, $arrInfoMore);
        }
        return  $arrReturn;
    }

    /**
     * 整理返回结果函数
     * @param array $info
     * @param array $infoMore
     * @return array
     */
    private function _resetToken(array $info, array $infoMore)
    {
        $arrReturn = array();
        $arrReturn['hash'] = $info['tid'];
        $arrReturn['info'] = $info['token_info'];
        $arrReturn['func'] = $info['token_function'];
//        $arrReturn['status'] = $info['status'];
        $arrReturn['time'] = $info['add_time'];
        $arrReturn['version'] = array();
        $i = 0;
        foreach ($infoMore as $more)
        {
            $arrReturn['version'][$i] = array('path'=>$more['path'],'line'=>$more['line'],'version'=>'','project'=>'','status'=>$more['status']);
            if (is_array($more['version_info']))
            {
                $arrReturn['version'][$i]['version'] = $more['version_info']['version_name'];
            }
            if (is_array($more['project_info']))
            {
                $arrReturn['version'][$i]['project'] = $more['project_info']['project_name'];
            }
            $i++;
        }
        return $arrReturn;
    }
    
    
    function __destruct()
    {
    }
}