<?php
/**
 * @version $Id: Adapter.php 1 2013-09-17 10:45:47Z cfc4n $
 * @author zhangwenhao 
 *
 */
class Afx_Db_Mysql_Adapter implements Afx_Db_Adapter
{

    /**
     * the only instance
     * @var Afx_Db_Mysql_Adapter
     */
    private static $__instance = NULL;

    /**
     * the Pdo link
     * @var resource
     */
    private $__link;

    /**
     * store the config information
     * 
     * @var array
     */
    private $__config = array();

    private $__host;

    private $__port;

    private $__dbname;

    private $__timeout;

    private $__socket;

    private $__user;

    private $__pass;

    private $__persist;

    private $__charset = 'utf8';

    /**
     * @var mysqli_stmt
     */
    private $__statment;

    /**
     * @var mysqli_result
     */
    private $__result;

    /**
     * @var mysqli_warning
     */
    private $__warnning;

    private $__last_sql;

    private $__sqls = array();

    public static $debug = TRUE;

    public function __construct ()
    {
        //        $this->__init();
    }

    public static function debug ($bool)
    {
        self::$debug = $bool;
    }

    /**
     * @param boolean $create
     * @return Afx_Db_Mysql_Adapter
     */
    public static function Instance ($create = FALSE)
    {
        if ($create)
        {
            return new self();
        }
        if (! self::$__instance instanceof Afx_Db_Mysql_Adapter)
        {
            self::$__instance = new self();
        }
        return self::$__instance;
    }

    /**
     * @param string $dbname
     */
    public function selectDatabase ($dbname)
    {
        $oldname = $this->__dbname;
        $this->__dbname = $dbname;
        $this->__config['dbname'] = $dbname;
        if (! is_object($this->__link))
        {
            $this->__init();
        } else
        {
            if ($oldname != $dbname)
            {
                mysql_select_db($dbname, $this->__link);
            }
        }
    }

    
    public function close()
    {
        mysql_close($this->__link);
    }
    
    public function setConfig ($config)
    {
        $this->__host = $config['host'];
        $this->__port = $config['port'];
        $this->__user = $config['user'];
        $this->__pass = $config['pass'];
        $this->__dbname = $config['dbname'];
        $this->__socket = $config['socket'];
        $this->__persist = $config['persist'];
        $this->__timeout = $config['timeout'];
        //        if ($this->__persist == TRUE)
        //        {
        //            $this->__host = 'p:' . $this->__host;
        //        }
        $this->__config = $config;
    }

    public function getConfig ()
    {
        return $this->__config;
    }

    public function getLastSql ()
    {
        return $this->__last_sql;
    }

    public function quote ($v, $type)
    {
        if (! $this->__link) $this->__init();
        return gettype($v) == 'string' ? "'" . mysql_real_escape_string($v, $this->__link) . "'" : $v;
         //        return mysql_real_escape_string($v, $this->__link);
    }

    public function commit ()
    {
        mysql_query('COMMIT');
        mysql_query('SET AUTOCOMMIT=ON');
    }

    public function startTrans ()
    {
        mysql_query('SET AUTOCOMMIT=OFF');
        mysql_query('START TRANSACTION');
    }

    public function rollBack ()
    {
        mysql_query('ROLLBACK');
        mysql_query('SET AUTOCOMMIT=ON');
    }

    /**
     * @return Afx_Db_Result
     */
    public function execute ($sql)
    {
        if (! $this->__link) $this->__init();
        return $this->__execute($sql);
    }

    /**
     * ping mysql tell it keep-alive
     * @see Afx_Db_Adapter::ping()
     */
    public function ping ()
    {
        if (! $this->__link->ping())
        {
            $this->__init();
        }
    }

    private function __init ()
    {
        if (! count($this->__config))
        {
            throw new Exception('please call setConfig first');
        }
        if ($this->__persist)
        {
            $this->__link = mysql_pconnect($this->__host . ':' . $this->__port, $this->__user, $this->__pass);
        } else
        {
            $this->__link = mysql_connect($this->__host . ':' . $this->__port, $this->__user, $this->__pass);
        }
        if (is_resource($this->__link))
        {
            mysql_set_charset($this->__charset, $this->__link);
            mysql_select_db($this->__dbname, $this->__link);
        } else
        {
            ob_clean();
            throw new Afx_Db_Exception(mysql_error(), mysql_errno());
        }
    }

    private function __execute ($sql)
    {
        $this->__last_sql = $sql;
        $this->__sqls[] = $sql;
        //        $sql = $this->__link->real_escape_string($sql);
        $this->__result = mysql_query($sql, $this->__link);
        if (mysql_errno($this->__link) != '')
        {
            if (self::$debug)
            {
                echo $sql;
                print_r(mysql_error($this->__link));
            }
            Afx_Logger::log(mysql_error($this->__link));
            if (mysql_errno($this->__link) == 2006) {
                //当发生2006错误时，重新连接MYSQL
                $this->__link = false;
            }
        }
        $result = new Afx_Db_Mysql_Result();
        $result->result = $this->__result;
        $result->link = $this->__link;
        return $result;
         //       print_r($this->__result);
    }
}