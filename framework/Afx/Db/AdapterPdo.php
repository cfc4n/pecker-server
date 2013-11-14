<?php
/**
 * AFX FRAMEWORK
 *
 * THIS SOFTWARE IS PROVIDED BY THE AUTHOR ``AS IS'' AND ANY EXPRESS OR
 * IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES
 * OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED.
 * IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT
 * NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF
 * THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 * @copyright Copyright (c) 2012 BVISON INC.  (http://www.bvison.com)
 */
/**
 * @package Afx_Db
 * @version $Id Adapter.php
 * The Pdo Class Adapter Provider Communication With The RelationShip Database
 * @author Afx TEAM && firedtoad@gmail.com && dietoad@gmail.com
 */
class Afx_Db_Adapter_Pdo
{

    /**
     * @var PDO
     */
    private static $link_read = array();

    /**
     * @var PDO
     */
    private static $link_write = array();

    /**
     * store the slave_count by per database connection
     * @var array
     */
    private static $slave_count = array();

    /**
     * store the default dbname
     * @var string
     */
    private static $dbname = NULL;

    /**
     *
     * @var string
     */
    private static $db_changed;

    /**
     * store the Database Configuration
     * @var array
     */
    private static $options = array();

    /**
     * store the databases mapping to the tables
     * @var array
     */
    private static $mapping = array();

    /**
     * Slave numbers
     * @var int
     */
    private static $slave_num = 0;

    /**
     * @var string
     */
    private static $read_dsn = array();

    /**
     * @var string
     */
    private static $write_dsn = NULL;

    /**
     * @var Afx_Db_Adapter
     */
    private static $instance = NULL;

    /**
     * the last execute sqls
     *@var array
     */
    private static $lastSql = array();

    /**
     * last operator server
     * @var string
     */
    private static $lastServer = NULL;

    /**
     * last error info
     * @var string
     */
    private static $lastError = NULL;

    /**
     * var Boolean
     */
    public static $debug = TRUE;

    /**
     * @return the $lastSql
     */
    public static function getLastSql ()
    {
        return Afx_Db_Adapter::$lastSql;
    }

    /**
     * @return the $mapping
     */
    public static function getMapping ()
    {
        return self::$mapping;
    }

    /**
     * @param array $mapping
     */
    public static function setMapping ($mapping)
    {
        self::$mapping = $mapping;
    }

    /**
     * Initialize The Configuration
     * @param array $arr
     */
    public static function initOption ($arr = array())
    {
        self::$options = $arr;
        return TRUE;
    }

    /**
     * Get the Configuration
     * @return array
     */
    public static function getOptions ()
    {
        return self::$options;
    }

    /**
     * @return Afx_Db_Adapter
     */
    public static function Instance ()
    {
        if (NULL === self::$instance)
        {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Initialize The PDO connections
     * @throws Afx_Db_Exception
     * @throws Exception
     */
    static $count = 0;

    private function _initConnection ()
    {
        //echo "init ",self::$count++;
        if (count(self::$options) == 0)
        {
            throw new Afx_Db_Exception('no  configuration found!', 404);
        }
        if (! isset(self::$options['db']))
        {
            throw new Afx_Db_Exception('no db  configuration found!', 404);
        }
        if (! isset(self::$options['db']['master']) || ! is_array(self::$options['db']['master']))
        {
            throw new Afx_Db_Exception('no master db configuration found!', 404);
        }
        $master = self::$options['db']['master'];
        //if no slave use the master as the slave
        $slave = isset(self::$options['db']['slave']) && is_array(self::$options['db']['slave']) ? self::$options['db']['slave'] : self::$options['db']['master'];
        static $keys = array(
            'host'=>1,'user'=>1,'password'=>1,'port'=>1,'dbname'=>1,'charset'=>1
        );
        $nokeys = array_diff_key($keys, $master);
        if (count($nokeys))
        {
            foreach ($nokeys as $k => $v)
            {
                throw new Afx_Db_Exception("no $k found in db master configuration!", 404);
            }
        }
        if (count($slave))
        {
            foreach ($slave as $k => $v)
            {
                $nokeys = array_diff_key($keys, $v);
                if (count($nokeys))
                {
                    foreach ($nokeys as $k1 => $v)
                    {
                        throw new Afx_Db_Exception("no $k1 found in db slave $k configuration!", 404);
                    }
                }
            }
        }
        self::$dbname = $master['dbname'];
        self::$write_dsn[self::$dbname] = 'mysql:host=' . $master['host'] . ';port=' . $master['port'] . ';dbname=' . $master['dbname'] . ';charset=' .
         $master['charset'] . ';';
        if (! isset(self::$link_read[self::$dbname]) || ! is_array(self::$link_read[self::$dbname]))
        {
            self::$link_read[self::$dbname] = array();
        }
        if (! isset(self::$read_dsn[self::$dbname]) || ! is_array(self::$read_dsn[self::$dbname]))
        {
            self::$read_dsn[self::$dbname] = array();
        }
        self::$slave_num = 0;
        try
        {
            foreach ($slave as $k => $v)
            {
                ++ self::$slave_num;
                $dsn = 'mysql:host=' . $v['host'] . ';port=' . $v['port'] . ';dbname=' . $v['dbname'] . ';charset=' . $v['charset'] . ';';
                if ($dsn)
                {
                    self::$read_dsn[self::$dbname][] = $dsn;
                }
                self::$link_read[self::$dbname][] = new PDO($dsn, $v['user'], $v['password'], 
                array(
                    PDO::ATTR_TIMEOUT=>'1',PDO::ATTR_PERSISTENT=>0,PDO::MYSQL_ATTR_INIT_COMMAND=>'set names utf8'
                ));
            }
            self::$slave_count[self::$dbname] = self::$slave_num;
            self::$link_write[self::$dbname] = new PDO(self::$write_dsn[self::$dbname], $master['user'], $master['password'], 
            array(
                PDO::ATTR_TIMEOUT=>'1',PDO::ATTR_PERSISTENT=>0,PDO::MYSQL_ATTR_INIT_COMMAND=>'set names utf8'
            ));
        } catch (PDOException $e)
        {
            //            throw $e;
            throw new Exception($e->getMessage(), '10061');
        }
    }

    /**
     * getSlave Server link
     * @return PDO
     */
    public static function getSalve ()
    {
        $server_num = rand(0, self::$slave_count[self::$dbname]) % self::$slave_count[self::$dbname];
        if (isset(self::$link_read[self::$dbname][$server_num]))
        {
            return self::$link_read[self::$dbname][$server_num];
        }
        return NULL;
    }

    /**
     * getMaster Server link
     * @return PDO
     */
    public static function getMaster ()
    {
        if (isset(self::$link_write[self::$dbname]))
        {
            return self::$link_write[self::$dbname];
        }
        return NULL;
    }

    /**
     * The construction we do initialize the database connections
     */
    private function __construct ()
    {
        $this->_initConnection();
    }

    /**
     * ReInitialize Database Connections
     */
    public static function reInitConnection ()
    {
        if (self::$instance)
        {
            self::$instance->_initConnection();
            return TRUE;
        }
    }

    /**
     *
     * quote a string with slashs
     * @param string $str
     * @param int $style PDOPram
     */
    public function quote ($str, $style = PDO::PARAM_INT)
    {
        if ($str !== NULL && isset(self::$link_write[self::$dbname]))
        {
            if (self::$link_write[self::$dbname] instanceof PDO)
            {
                return self::$link_write[self::$dbname]->quote($str, $style);
            }
        }
    }

    /**
     * The main method execute the sql string
     * @param string $sql the sql string
     * @param string $table  on which table
     * @param  Boolean $master whether operator the master
     * @param Boolean $usetrans whether use transaction default False
     * @throws PDOException
     * @throws Exception
     */
    public function execute ($sql, $table = NULL, $master = FALSE, $usetrans = FALSE)
    {
        $server_num = rand(0, self::$slave_num) % self::$slave_num;
        // Afx_Debug_Helper::print_r(self::$slave_count);
        if (strncasecmp($sql, 'EXPLAIN', 7) != 0)
        {
            self::$lastSql[] = $sql;
        }
        self::$lastServer = $master;
        if (self::$debug)
        {
            // echo $sql, "  serverNum=$server_num\n<br/>";
            Afx_Logger::log($sql);
        }
        //we want to map the table in different database so
        //selete the default database every time
        //        if (self::$db_changed != NULL) {
        //            self::$link_read[$server_num]->exec("use " . self::$dbname);
        //            self::$link_write->exec("use " . self::$dbname);
        //            self::$db_changed = NULL;
        //        }
        //check if we need mapping
        //Notice that it exists a bug when two database have the same table name
        if ($table != NULL && is_string($table))
        {
            if (is_array(self::$mapping) && count(self::$mapping))
            {
                foreach (self::$mapping as $k => $v)
                {
                    if (isset($v[$table]))
                    {
                        $conf = Yaf_Registry::get('conf');
                        if (isset($conf['mappingdb']) && isset($conf['mappingdb'][$k]))
                        {
                            if (! isset(self::$link_read[$k]) || ! isset(self::$link_write[$k]))
                            {
                                //echo "changed and init from ",self::$dbname,' to ',$k,"\n";
                                $this->initOption($conf['mappingdb'][$k]);
                                // Afx_Debug_Helper::print_r($conf['mappingdb'][$k]);
                                $this->_initConnection($conf['mappingdb'][$k]);
                            } else 
                                if (self::$dbname !== $k)
                                {
                                    //echo "changed dbname from ",self::$dbname,' to ',$k,"\n";
                                    self::$dbname = $k;
                                }
                        }
                        //echo   $sql=str_ireplace($table, $k.".".$table, $sql);
                        //self::$link_read[$server_num]->exec("use $k");
                        //self::$link_write->exec("use $k");
                        // self::$db_changed = 'changed';
                        break;
                    }
                }
            }
        }
        // Afx_Debug_Helper::print_r(self::$read_dsn);
        if (strncasecmp($sql, 'select', 6) == 0)
        {
            //read from the database
            //default operator the slave
            try
            {
                if ($master == FALSE)
                {
                    //                    self::$link_read[$server_num]->exec();
                    $statment = self::$link_read[self::$dbname][$server_num]->prepare($sql);
                } elseif ($master == TRUE)
                {
                    $statment = self::$link_write[self::$dbname]->prepare($sql);
                }
                if ($statment instanceof PDOStatement)
                {
                    $statment->execute();
                    if ($statment->errorCode() != '00000')
                    {
                        self::$lastError = $statment->errorInfo();
                        throw new PDOException(implode('', $statment->errorInfo()) . "  execute error", $statment->errorCode());
                    }
                    $obj = $statment->fetchALL(PDO::FETCH_ASSOC);
                    if ($statment->errorCode() != '00000')
                    {
                        self::$lastError = $statment->errorInfo();
                        throw new PDOException(implode('', $statment->errorInfo()), "  fetch error", $statment->errorCode());
                    }
                    return $obj;
                }
            } catch (PDOException $e)
            {
                echo $e->getMessage();
                throw new Exception($e);
            }
        } else 
            if (strncasecmp($sql, 'delete', 6) == 0 || strncasecmp($sql, 'update', 6) == 0 || strncasecmp($sql, 'insert', 6) == 0)
            {
                //delete or update  or insert
                //operator the master
                $ret = TRUE;
                try
                {
                    if ($usetrans) self::$link_write[self::$dbname]->beginTransaction();
                    self::$link_write[self::$dbname]->exec($sql);
                    if ($usetrans) self::$link_write[self::$dbname]->commit();
                    if (self::$link_write[self::$dbname]->errorCode() != '00000')
                    {
                        self::$lastError = self::$link_write[self::$dbname]->errorInfo();
                        throw new PDOException(implode('', self::$link_write[self::$dbname]->errorInfo()), self::$link_write[self::$dbname]->errorCode());
                    }
                } catch (PDOException $e)
                {
                    $ret = FALSE;
                    throw new Exception($e);
                }
                return $ret;
            } else
            {
                $stmt = self::$link_write[self::$dbname]->prepare($sql);
                $stmt->execute();
                if ($stmt->errorCode() != '00000')
                {
                    self::$lastError = $stmt->errorInfo();
                    throw new PDOException(implode('', $stmt->errorInfo()), $stmt->errorCode());
                }
                return $stmt->fetchALL();
            }
    }
}
