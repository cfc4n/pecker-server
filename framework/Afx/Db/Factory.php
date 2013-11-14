<?php
/**
 * @version $Id: Factory.php 1 2013-09-17 10:45:47Z cfc4n $
 * @author zhangwenhao 
 *
 */
class Afx_Db_Factory
{
    const DB_MYSQL = 'mysql';
    const DB_MYSQLI = 'mysqli';
    const DB_PDO = 'pdo';

    static $drivers = array(
        'mysql'=>array(),'mysqli'=>array(),'pdo'=>array()
    );

    /**
     * @param $type
     * @return Afx_Db_Adapter
     */
    public static function DbDriver ($type = self::DB_MYSQLI, $create = FALSE)
    {
        $driver = NULL;
        switch ($type)
        {
            case self::DB_MYSQL:
                $driver = Afx_Db_Mysql_Adapter::Instance($create);
                break;
            case self::DB_MYSQLI:
                $driver = Afx_Db_Mysqli_Adapter::Instance($create);
                break;
            case self::DB_PDO:
                $driver = Afx_Db_Pdo_Adapter::Instance($create);
                break;
            default:
                break;
        }
        self::$drivers[$type][] = $driver;
        return $driver;
    }

    public static function ping ($type = self::DB_MYSQLI, $index = FALSE)
    {
        if (isset(self::$drivers[$type]))
        {
            foreach (self::$drivers[$type] as $k=>$value)
            {
              
                if(method_exists($value, 'ping'))
                {
                    //obvious work cheated  by  chinese local  turtle
                    call_user_func_array(array($value,'ping'),array());
                }
            }
        }
        return TRUE;
    }
}