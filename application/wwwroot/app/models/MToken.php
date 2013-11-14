<?php 
/**
 * @version $Id: MToken.php 1 2013-09-17 10:45:47Z cfc4n $
 * @author CFC4N
 */

class MToken extends Afx_Module_Abstract
{
    private $_token;
    private $_table_tokens = 'tokens';
    private $_table_tokens_version = 'tokens_version';
    private $_table_version = 'version';
    private $_table_project = 'project';
    
    function __construct($str)
    {
        $this->_token = $str;
        $this->_init();
    }
    
    
    function getToken($token='')
    {
        if ($token == '')
        {
            $token = $this->_token;
        }
        return $this->_getToken($token);
    }
    
    private function _getToken($token)
    {
        $arr = array();
        $this->from($this->_table_tokens)->where('tid',$token);
        $arr = $this->get()->row();
        return $arr;
    }
    
    function getTokenMore($token='')
    {
        if ($token == '')
        {
            $token = $this->_token;
        }
        return $this->_getTokenMore($token);
    }
    
    private function _getTokenMore($token)
    {
        $arr = $this->_getTokenVersion($token);
        $arrReturn = $arrVersion = $arrProject =  array();
        foreach ($arr as $v)
        {
            $arrReturn[] = $v;
            if ($v['vid'] > 0)
            {
                $arrVersion[] = $v['vid'];
            }
            if ($v['pid'] > 0)
            {
                $arrProject[] = $v['pid'];
            }
        }
        if (count($arrReturn) == 0)
        {
            return array();
        }
        $arrVersionInfo = $this->_getVersion($arrVersion);
        foreach ($arrReturn as &$val)
        {
            if (isset($arrVersionInfo[$val['vid']]))
            {
                $val['version_info'] = $arrVersionInfo[$val['vid']];
            }
            else
            {
                $val['version_info'] = array();
            }
        }
        
        $arrProjectInfo = $this->_getProject($arrProject);
        foreach ($arrReturn as &$val)
        {
            if (isset($arrProjectInfo[$val['pid']]))
            {
                $val['project_info'] = $arrProjectInfo[$val['pid']];
            }
            else
            {
                $val['project_info'] = array();
            }
        }
        return $arrReturn;
    }
    
    /**
     * 获取该token对应的程序版本信息
     * @param string $token
     * @return array 多维，可能存在于多个项目中
     */
    private function _getTokenVersion($token)
    {
        $arr = array();
        $this->from($this->_table_tokens_version)->where('tid',$token);
        $rs = $this->get()->result();
        return $rs;
    }
    
    /**
     * 获取数组的所有版本列表
     * @param array $arr
     * @return array
     */
    private function _getVersion(array $arr)
    {
        if (count($arr) == 0) {
            return array();
        }
        $array = array();
        $this->from($this->_table_version)->where('vid',$arr, 'in');
        $rs = $this->get()->result();
        foreach ($rs as $v)
        {
            $array[$v['vid']] = $v;
        }
        return $array;
    }
    
    /**
     * 获取数组的所有项目列表
     * @param array $arr
     * @return array 
     */
    private function _getProject(array $arr)
    {
        if (count($arr) == 0) {
            return array();
        }
        $array = array();
        $this->from($this->_table_project)->where('pid',$arr, 'in');
        $rs = $this->get()->result();
        foreach ($rs as $v)
        {
            $array[$v['pid']] = $v;
        }
        return $array;
    }
}
?>