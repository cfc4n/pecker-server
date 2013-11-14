<?php
/**
 * @version $Id: Adapter.php 1 2013-09-17 10:45:47Z cfc4n $
 * @author zhangwenhao 
 *
 */
class Afx_Db_Mysqli_Adapter implements Afx_Db_Adapter
{

    /**
     * the only instance
     * @var Afx_Db_Mysqli_Adapter
     */
    private static $__instance = NULL;

    /**
     * the mysqli link
     * @var mysqli
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

    private $__socket;

    private $__user;

    private $__pass;

    private $__persist;

    private $__timeout;

    private $__wait_timeout = 86400;

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
     * @return Afx_Db_Mysqli_Adapter
     */
    public static function Instance ($create = FALSE)
    {
        if ($create)
        {
            return new self();
        }
        if (! self::$__instance instanceof Afx_Db_Mysqli_Adapter)
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
                $this->__link->select_db($dbname);
            }
        }
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
        if ($this->__persist == TRUE)
        {
            $this->__host = 'p:' . $this->__host;
        }
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
        //        return $v;
        return gettype($v) == 'string' ? "'" . $this->__link->real_escape_string($v) . "'" : $v;
         //        return gettype($v)=='string'?"'$v'":$v;
    }

    public function commit ()
    {
        $this->__link->commit();
        $this->__link->autocommit(TRUE);
    }

    public function startTrans ()
    {
        $this->__link->autocommit(FALSE);
         //        $this->__link->
    }

    public function rollBack ()
    {
        $this->__link->rollback();
        $this->__link->autocommit(TRUE);
    }

    //    public  function recordCount()
    //    {
    //        return $this->__result->num_rows;
    //    }
    /**
     * @return Afx_Db_Mysqli_Result
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
        if (! $this->__link || ! $this->__link->ping())
        {
            $this->__init();
        }
    }

    /**
     * close the link
     * @see Afx_Db_Adapter::close()
     */
    public function close ()
    {
        if ($this->__link)
        {
            $this->__link->close();
        }
    }

    private function __init ()
    {
        if (! count($this->__config))
        {
            throw new Exception('please call setConfig first');
        }
        $this->__link = mysqli_init();
        $this->__link->set_opt(MYSQLI_OPT_CONNECT_TIMEOUT, $this->__timeout);
        //            $this->__link = new Mysqli($this->__host, $this->__user, $this->__pass, $this->__dbname, $this->__port, $this->__socket);
        
        $this->__link->real_connect($this->__host, $this->__user, $this->__pass, $this->__dbname, $this->__port, $this->__socket);
        if ($this->__link->errno == 0)
        {
            $this->__link->set_charset($this->__charset);
             //            $this->__link->query('set wait_timeout='.$this->__wait_timeout);
        } else
        {
            ob_clean();
            throw new Afx_Db_Exception($this->__link->error, $this->__link->errno);
        }
    }

    private function __execute ($sql)
    {
        //        echo $sql,"\n";
        $this->__last_sql = $sql;
        //        $this->__sqls[] = $sql;
        //        $sql = $this->__link->real_escape_string($sql);
        $this->__result = $this->__link->query($sql);
        //         Afx_Logger::log("sql=" . $sql);
        if ($this->__link->errno)
        {
            //            echo $this->__link->error;
            $sqls = join("\n", $this->__sqls);
            if (self::$debug)
            {
                echo $sql;
                echo $this->__link->error;
            }
            Afx_Logger::log("sql=" . $sql . "\nthread_id=" . $this->__link->thread_id . "\n" . $this->__link->error);
            echo $this->__link->errno;
            if ($this->__link->errno == 2006)
            {
                //当发生2006错误时，重新连接MYSQL
                $this->__link = false;
            }
        }
        //        Afx_Logger::log("sql=" . $sql,1,TRUE);
        $result = new Afx_Db_Mysqli_Result();
        $result->result = $this->__result;
        $result->link = $this->__link;
        return $result;
         //       print_r($this->__result);
    }

    public function __destruct ()
    {
        if ($this->__link)
        {
            $this->close();
        }
    }
}