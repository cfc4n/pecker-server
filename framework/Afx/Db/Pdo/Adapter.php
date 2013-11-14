<?php
/**
 * @version $Id: Adapter.php 1 2013-09-17 10:45:47Z cfc4n $
 * @author zhangwenhao 
 *
 */
class Afx_Db_Pdo_Adapter implements Afx_Db_Adapter
{

    /**
     * the only instance
     * @var Afx_Db_Pdo_Adapter
     */
    private static $__instance = NULL;

    /**
     * the Pdo link
     * @var PDO
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
     * @return Afx_Db_Pdo_Adapter
     */
    public static function Instance ($create = FALSE)
    {
        if ($create)
        {
            return new self();
        }
        if (! self::$__instance instanceof Afx_Db_Pdo_Adapter)
        {
            self::$__instance = new self();
        }
        return self::$__instance;
    }

    public function close()
    {
        return 'pdo_not_implement';
    }
    
    public function ping()
    {
        return 'pdo_not_support_for_ping';
    }
    
    /**
     * @param string $dbname
     */
    public function selectDatabase ($dbname)
    {
        $oldname=$this->__dbname;
        $this->__dbname=$dbname;
        $this->__config['dbname']=$dbname;
        if (! is_object($this->__link))
        {
            $this->__init();
        }else{
            $this->__link->exec('use '.$dbname);
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
        //        return $v;
        //        return gettype($v) == 'string' ? "'" . $this->__link->real_escape_string($v) . "'" : $v;
        //        return gettype($v)=='string'?"'$v'":$v;
        if (! $this->__link) $this->__init();
        return $this->__link->quote($v, $type);
    }

    public function commit ()
    {
        $this->__link->commit();
        $this->__link->setAttribute(PDO::ATTR_AUTOCOMMIT, TRUE);
    }

    public function startTrans ()
    {
        $this->__link->setAttribute(PDO::ATTR_AUTOCOMMIT, FALSE);
        $this->__link->beginTransaction();
    }

    public function rollBack ()
    {
        $this->__link->rollback();
        $this->__link->setAttribute(PDO::ATTR_AUTOCOMMIT, TRUE);
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

    private function __init ()
    {
        if (! count($this->__config))
        {
            throw new Exception('please call setConfig first');
        }
        $dbname = $this->__dbname;
        $host = $this->__host;
        try
        {
            $this->__link = new PDO("mysql:dbname=$dbname;host=$host", $this->__user, $this->__pass, array(
                PDO::ATTR_TIMEOUT=>$this->__timeout,PDO::ATTR_AUTOCOMMIT=>1,PDO::ATTR_PERSISTENT=>$this->__persist,PDO::MYSQL_ATTR_INIT_COMMAND=>'set names utf8'
            ));
        } catch (PDOException $pe)
        {
            ob_clean();
            throw new Afx_Db_Exception($pe->getMessage(), $pe->getCode(), $pe);
        }
    }

    private function __execute ($sql)
    {
        $this->__last_sql = $sql;
        $this->__sqls[] = $sql;
        $affected = 0;
        //        $sql = $this->__link->real_escape_string($sql);
        if (strncasecmp($sql, 'DELETE', 6) == 0 || strncasecmp($sql, 'UPDATE', 6) == 0 || strncasecmp($sql, 'INSERT', 6) == 0)
        {
            $affected = $this->__link->exec($sql);
        } else
        {
            $this->__result = $this->__link->query($sql);
        }
        if ($this->__link->errorCode() != '0000')
        {
            if (self::$debug)
            {
                echo $sql;
                print_r($this->__link->errorInfo());
            }
            Afx_Logger::log(join('', $this->__link->errorInfo()));
            if ($this->__link->getAttribute(PDO::ATTR_SERVER_INFO) == 'MySQL server has gone away') {
                //当发生2006错误时，重新连接MYSQL
                $this->__link = false;
            }
        }
        $result = new Afx_Db_Pdo_Result();
        $result->result = $this->__result;
        $result->link = $this->__link;
        $result->affected = $affected;
        return $result;
         //       print_r($this->__result);
    }
}