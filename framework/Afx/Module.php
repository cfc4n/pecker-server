<?php
class Afx_Module extends Afx_Module_Abstract
{

    protected  static $__instance;
    /**
     * create the module object using the given table name
     * @param string $tablename
     * @return Afx_Module
     */
    public function __construct ($tablename = 'tablename')
    {
//        $adapter=$this->getAdapter();
//        $newadaptor=clone($adapter);
        
//        $this->
        $this->_tablename = $tablename;
        $this->_from = $tablename;
    }
    /**
     * @param boolean $create
     * @param string $database
     * @return Afx_Module_Abstract
     */
    public static function Instance($create=FALSE,$database=NULL)
    {
        $adapter=NULL;
        if((self::$__instance instanceof Afx_Module)&& (self::$__instance->_adapter instanceof Afx_Db_Adapter)&&$create)
        {
            
            $config=Yaf_Registry::get('config');
            $adapter=Afx_Db_Factory::DbDriver($config['mysql']['driver'],TRUE);
            $adapter->setConfig(self::$__instance->_adapter->getConfig());
            $th=new static();
            $th->setAdapter($adapter);
            if($database!=NULL)
            {
                $th->getAdapter()->selectDatabase($database);
            }
            return $th;
        }

        
        if(!self::$__instance instanceof Afx_Module)
        {
            self::$__instance=new static();
        }
        return self::$__instance;
    }
}