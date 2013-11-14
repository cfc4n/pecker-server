<?php
/**
 * @version $Id: Adapter.php 1 2013-09-17 10:45:47Z cfc4n $
 * @author zhangwenhao 
 *
 */
interface Afx_Db_Adapter
{

    public static function Instance ();

    public static function debug ($bool);

    public function setConfig ($config);

    public function getConfig ();

    public function selectDatabase ($dbname);

    public function startTrans ();

    public function rollBack ();

    public function commit ();

    public function getLastSql ();

    public function quote ($v, $type);
    
    public function ping();
    
    /**
     * @param string $sql
     * @return Afx_Db_Result
     */
    public function execute ($sql);
    
    public function close();
}