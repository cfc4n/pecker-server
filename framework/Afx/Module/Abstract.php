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
 * @package Afx_Module
 * @version $Id Abstract.php
 * The Module Class Impliment The Core ORM CRUD Operator
 * @author Afx team && firedtoad@gmail.com &&dietoad@gmail.com
 */
/**
 * @version $Id: Abstract.php 1 2013-09-17 10:45:47Z cfc4n $
 * @author zhangwenhao 
 *
 */
abstract class Afx_Module_Abstract
{

    public $_key = 'id';

    protected $_table_name = 't_dummy';

    /**
     * the from table
     * @var string
     */
    protected $_from = '';

    /**
     * var Boolean
     */
    protected $_distinct = FALSE;

    /**
     *
     * @var Afx_Db_Adapter
     */
    protected $_adapter = NULL;

    /**
     * store the Object fetch from database and compare for update
     * @var stdClass
     */
    private $_obj;

    /**
     * @var int $_limit the limit of per resultset
     */
    protected $_limit = 100;

    /**
     * store the condition used by the sql statment
     * @var array
     */
    protected $_where = array();

    /**
     *
     * store the fields used by the sql statment
     * @var array()
     */
    protected $_field = array();

    /**
     * store the sql statment
     * @var string $_sql
     */
    protected $_sql = NULL;

    /**
     * start offset
     * @var int
     */
    protected $_offset = 0;

    /**
     * like condition
     * @var string
     */
    protected $_like = NULL;

    /**
     * store the or condition
     * @var array
     */
    protected $_wor = array();

    /**
     * store the join condition
     * @var array
     */
    protected $_join = array();

    /**
     * store the result array
     * @var array
     */
    protected $_result_array;

    /**
     * the on condition
     * @var array
     */
    protected $_on = array();

    /**
     * @var string order
     */
    protected $_order = NULL;

    /**
     * The Only Instance
     * @var Afx_Module_Abstract::
     */
    protected static $_instance = NULL;

    /**
     * @var string
     */
    protected $_groupBy;

    /**
     * @var Boolean
     */
    protected $_random;

    /**
     * @var Afx_Moudule_Partition_PartitionInterface
     */
    public $_partition = NULL;

    /**
     * Get the pure Object Fetch From The database
     * @return stdClass
     */
    /**
     * @return the $_partition
     */
    public function getPartition ()
    {
        return $this->_partition;
    }

    /**
     * @param Afx_Moudule_Partition_PartitionInterface $_partition
     */
    public function setPartition ($_partition)
    {
        //		var_dump($_partition);
        $this->_partition = $_partition;
    }

    public function getObj ()
    {
        return $this->_obj;
    }

    /**
     * set The Pure Object  for insert
     * @deprecated
     * @param stdClass $_obj
     */
    public function setObj ($_obj)
    {
        if (is_array($_obj)) $this->_obj = $_obj;
    }

    /**
     * Get the Db Adapter
     * @return Afx_Db_Adapter
     */
    public function getAdapter ()
    {
//        print_r($this->_adapter);
//        echo $this->_adapter Instanceof Afx_Db_Adapter;
        if ($this->_adapter Instanceof Afx_Db_Adapter)
        {
            return $this->_adapter;
        } else
        {
            throw new Afx_Db_Exception('no database adapter', '404');
        }
    }

    public function quote ($v, $style = NULL)
    {
        if ($this->_adapter instanceof Afx_Db_Adapter)
        {
            return $this->_adapter->quote($v, $style);
        }
    }

    /**
     * Magic function __set
     * @param string $name
     * @param mixed $value
     */
    public function __set ($name, $value)
    {
        $this->$name = $value;
    }

    /**
     *
     * Magic function __set
     * @param string $name
     * @retunr  mixed
     */
    public function __get ($name)
    {
        //        return self::$_instance->$name;
        if (property_exists('Afx_Module_Abstract', $name))
        {
            return $this->$name;
        }
    }

    /**
     * Set the Db Adapter
     * @param Afx_Db_Adapter $_adapter
     */
    public function setAdapter ($_adapter)
    {
        $this->_adapter = $_adapter;
    }

    /**
     * constructor function
     * Here We use Default Db Adapter
     */
    public function __construct ()
    {
        //        $this->_partition = Afx_Module_Partition_Logic::Instance();
    //    	Afx_Debug_Helper::print_r($this->getPartition());
    }

    /**
     * Convert An Array To A stdClass
     * @param array $arr
     * @return stdClass
     */
    public static function fromArray ($arr = array())
    {
        if (count($arr))
        {
            $obj = new stdClass();
            foreach ($arr as $k => $v)
            {
                $obj->$k = $v;
            }
            return $obj;
        }
        return NULL;
    }

    /**
     * Convert An stdClass To A Array
     * @param stdClass $obj
     * @return array
     */
    public static function toArray ($obj = stdClass)
    {
        $arr = array();
        if (! is_object($obj))
        {
            return NULL;
        }
        foreach ($obj as $k => $v)
        {
            if (strncasecmp($k, '_', 1) == 0)
            {
                continue;
            }
            $arr[$k] = $v;
        }
        return count($arr) ? $arr : NULL;
    }

    /**
     * apply an array keys as self properties
     * @param array $arr
     */
    public function setProperties ($arr = array())
    {
        if (! is_array($arr))
        {
            return;
        }
        foreach ($arr as $k => $v)
        {
            $this->$k = $v;
        }
    }

    /**
     * inner do partition
     */
    private function _doPartition ()
    {
        if (is_object($this->_partition))
        {
            if (method_exists($this->_partition, 'doPartition'))
            {
                $this->_from = $this->_partition->doPartition($this, $this->_key);
            }
        }
    }

    /**
     * set from 
     * @param string $table_name
     * @return Afx_Module_Abstract
     */
    public function from ($table_name)
    {
        $this->_from = $table_name;
        return $this;
    }

    /**
     * save an object to the database
     * the most complicated function
     * @param void
     * @return TRUE if success else FALSE
     */
    public function save ()
    {
        //Afx_Debug_Helper::print_r($this->_partition);
        $this->_doPartition();
        $sql = '';
        if ($this->_obj === NULL)
        {
            /*
         * the _obj is null so the object is new construct
         * we insert the object to the database
         */
            $sql = 'INSERT INTO ' . $this->_from . ' (';
            $arr = self::toArray($this);
            if (! is_array($arr))
            {
                return FALSE;
            }
            foreach ($arr as $k => $v)
            {
                if (strncasecmp($k, '_', 1) == 0 || strncasecmp($k, 'id', 2) == 0)
                {
                    //filter the id and startwith '_' properties
                    //because most of the table will have the id auto_increment
                    //and nearly no table column name start with '_' so we filter them
                    unset($arr[$k]);
                    continue;
                }
            }
            if (! count($arr)) return FALSE;
            //concat the keys
            $keystr = '`' . implode('`,`', array_keys($arr)) . '`';
            //here we add ',' more one time so drop it
            $sql .= substr($keystr, 0, strlen($keystr));
            $sql .= ') VALUES(';
            //concat the values
            foreach ($arr as $k => $v)
            {
                //here we only deal with string and numeric
                //we need deal others future
                $sql .= $this->_processValue($v) . ",";
            }
            //here we add ',' more one time so drop it again
            $sql = substr($sql, 0, strlen($sql) - 1);
            $sql .= ')';
            return $this->getAdapter()->execute($sql);
        }
        if ($this->_obj && $this->_obj instanceof stdClass || is_array($this->_obj))
        {
            //the _obj exists  it is fetch from the object
            //so update it
            $sql = 'UPDATE ' . $this->_from;
            $arr_old = is_array($this->_obj) ? $this->_obj : self::toArray($this->_obj);
            $arr_this = self::toArray($this);
            if (is_array($arr_old) && is_array($arr_this))
            {
                //calculate  the properties need te be update
                $arr_update = array_intersect_key($arr_this, $arr_old);
                $arr_real_update = array_diff_assoc($arr_update, $arr_old);
                //				Afx_Debug_Helper::print_r($arr_real_update);
                if (count($arr_real_update))
                {
                    $sql .= ' set ';
                    foreach ($arr_real_update as $k => $v)
                    {
                        $sql .= '`' . $k . "`=" . $this->_processValue($v) . ",";
                    }
                    //',' more add again drop it
                    $sql = substr($sql, 0, strlen($sql) - 1);
                    $hasWhere = 0;
                    if ($this->_where && count($this->_where))
                    {
                        $sql .= " WHERE ";
                        $hasWhere = 1;
                        foreach ($this->_where as $k => $v)
                        {
                            if (isset($v[0]) && isset($v[1]))
                            {
                                $sql .= "`{$v[0]}`{$v[1]}" . $this->_processValue($v[2]) . " AND ";
                            }
                        }
                        $lastPost = strrpos($sql, "AND");
                        if ($lastPost)
                        {
                            $sql = substr($sql, 0, $lastPost);
                        }
                    }
                    if ($this->_wor && count(count($this->_wor)))
                    {
                        if (! $hasWhere)
                        {
                            $sql .= " WHERE ";
                        }
                        foreach ($this->_wor as $k => $v)
                        {
                            if (isset($v[0]) && isset($v[1]))
                            {
                                $sql .= "`{$v[0]}`{$v[1]}" . $this->_processValue($v[2]) . " OR ";
                            }
                        }
                        $lastPost = strrpos($sql, "OR");
                        if ($lastPost)
                        {
                            $sql = substr($sql, 0, $lastPost);
                        }
                    }
                    if (isset($arr_old[$this->_key]))
                    {
                        if (! $hasWhere)
                        {
                            $sql .= " WHERE ";
                        } else
                        {
                            $sql .= " AND ";
                        }
                        $sql .= "`$this->_key`=" . $this->_processValue($arr_old[$this->_key]) . 'limit 1';
                        //                        $sql .= sprintf(' where `%s`=%s', $this->_key,
                        //                        $this->_processValue($arr_old[$this->_key]));
                        return $this->getAdapter()->execute($sql);
                    } else
                    {
                        return FALSE;
                    }
                }
                return FALSE;
            } else
            {
                return FALSE;
            }
        }
    }

    /**
     * delete the object in the database
     * @param int $id
     * @return TRUE if success else FALSE
     */
    public function delete ($id = NULL)
    {
        //        $this->_doPartition();
        if ($id)
        {
            $this->where('id', $id);
             //            return FALSE;
        }
        $sql = $this->_generateSql('DELETE ');
        //$sql = sprintf('DELETE  FROM ' . $this->_from . ' WHERE `id`=%d', $rid);
        return $this->getAdapter()->execute($sql);
    }

    /**
     * select an object from the database id is the specific id
     * @param long $id
     * @param  Boolean $master whether operator the master
     * @return Afx_Module_Abstract if success else NULL
     */
    public function getOne ($id = NULL, $master = FALSE)
    {
        $this->_doPartition();
        if ($id && ! is_numeric($id))
        {
            return NULL;
        }
        $sql = '';
        if (! $this->_random)
        {
            $sql = sprintf('SELECT * FROM ' . $this->_from . ' WHERE `id`=%d', $id);
        } else
        {
            $sql = sprintf('SELECT * FROM ' . $this->_from . ' ORDER BY RAND() limit 1');
        }
        $arr = $this->getAdapter()
            ->execute($sql)
            ->row();
        if ($arr && is_array($arr[0]))
        {
            $this->_obj = self::fromArray($arr[0]);
            $this->setProperties($arr[0]);
            return $this;
        }
        return NULL;
    }

    /**
     * get an Object from the database by random use where and or condition
     * @param Boolean $master whether from the master
     * @return Afx_Module_Abstract
     */
    public function getRandom ($master = FALSE)
    {
        $this->_doPartition();
        $sql = 'SELECT * FROM ' . $this->_from;
        // process where
        $hasWhere = 0;
        if ($this->_where && count($this->_where))
        {
            $sql .= ' WHERE ';
            $hasWhere = 1;
            foreach ($this->_where as $k => $v)
            {
                if ($v[0] && $v[1]) $sql .= " `$v[0]`$v[1]" . $this->_processValue($v[2]) . " AND ";
            }
            $lastPos = strripos($sql, 'AND');
            if ($lastPos)
            {
                $sql = substr($sql, 0, $lastPos);
            }
        }
        if ($this->_wor && count($this->_wor))
        {
            if (! $hasWhere) $sql .= ' WHERE ';
            foreach ($this->_wor as $k => $v)
            {
                if ($v[0] && $v[1] && $v[2]) $sql .= " `$v[0]`$v[1]" . $this->_processValue($v[2]) . " OR ";
            }
            $lastPos = strripos($sql, 'OR');
            if ($lastPos)
            {
                $sql = substr($sql, 0, $lastPos);
            }
        }
        if (! $hasWhere)
            $sql .= "WHERE id>=(SELECT FLOOR( MAX(id) * RAND()) FROM " . $this->_from . " )  ORDER BY " . $this->_key . " limit 1";
        else
            $sql .= "AND id>=(SELECT FLOOR( MAX(id) * RAND()) FROM " . $this->_from . " )  ORDER BY " . $this->_key . " limit 1";
        $arr = $this->getAdapter()
            ->execute($sql)
            ->row();
        if ($arr && is_array($arr[0]))
        {
            $this->_obj = self::fromArray($arr[0]);
            $this->setProperties($arr[0]);
            return $this;
        }
        return NULL;
    }

    /**
     * Find One Object from database use specific key and value
     * @param string $k
     * @param  mixed $v
     * @param  Boolean $master whether operator the master
     * @return Afx_Module_Abstract
     */
    public function findOne ($k, $v, $master = FALSE)
    {
        $this->_doPartition();
        if (empty($k) || empty($v))
        {
            return NULL;
        }
        $sql = sprintf('SELECT * FROM ' . $this->_from . " WHERE `%s`=%s", $k, $this->_processValue($v));
        $arr = $this->getAdapter()
            ->execute($sql)
            ->result();
        //            print_r($arr);
        if (isset($arr[0]) && is_array($arr[0]))
        {
            $this->_obj = self::fromArray($arr[0]);
            $this->setProperties($arr[0]);
            return $this;
        }
        return NULL;
    }

    /**
     * generate the sql string use the conditions
     * @return string
     */
    private function _generateSql ($prefix = 'SELECT ')
    {
        $this->_doPartition();
        $sql = $prefix;
        if ($this->_distinct)
        {
            $sql .= 'DISTINCT ';
        }
        $hasWhere = 0;
        if (! $this->_sql)
        {
            if (! is_array($this->_field) || count($this->_field) == 0)
            {
                $prefix !== 'DELETE ' && $sql .= "*";
            } elseif (count($this->_field))
            {
                foreach ($this->_field as $k => $v)
                {
                    $sql .= '`' . $v . "`,";
                }
            }
            $lastIndex = strrpos($sql, ',');
            if ($lastIndex > 0)
            {
                $sql = substr($sql, 0, $lastIndex);
            }
            $sql .= " FROM " . $this->_from;
            if (is_array($this->_join) && count($this->_join) > 0)
            {
                foreach ($this->_join as $k => $v)
                {
                    $sql .= " JOIN " . $v[0] . " ON " . $this->_from . "." . $v[1] . "=" . $v[0] . "." . $v[2];
                }
            }
            if (is_array($this->_where) && count($this->_where) > 0)
            {
                $sql .= " WHERE ";
                $hasWhere = 1;
                foreach ($this->_where as $k => $v)
                {
                    if ($v[1] != 'in')
                    {
                        $sql .= "`$v[0]` " . $v[1] . $this->_processValue($v[2]) . " AND ";
                    } else 
                        if ($v[1] == 'in')
                        {
                            if (count($v[2])) $sql .= "`$v[0]` " . $v[1] . $this->_processIn($v[2]) . " AND ";
                        }
                }
                $lastIndex = strrpos($sql, 'AND');
                if ($lastIndex > 0)
                {
                    $sql = substr($sql, 0, $lastIndex);
                }
            }
            if (is_array($this->_wor) && count($this->_wor) > 0)
            {
                if (! $hasWhere)
                {
                    $sql .= ' WHERE ';
                } else
                {
                    $sql .= ' OR ';
                }
                foreach ($this->_wor as $k => &$v)
                {
                    if ($v[1] != 'in')
                    {
                        $sql .= "`$v[0]` " . $v[1] . $this->_processValue($v[2]) . " OR ";
                    } else 
                        if ($v[1] == 'in')
                        {
                            if (count($v[2])) $sql .= "`$v[0]` " . $v[1] . $this->_processIn($v[2]) . " OR ";
                        }
                }
                $lastIndex = strrpos($sql, 'OR');
                if ($lastIndex > 0)
                {
                    $sql = substr($sql, 0, $lastIndex);
                }
            }
            if ($this->_groupBy)
            {
                $sql .= ' GROUP BY `' . $this->_groupBy . '` ';
            }
            if ($this->_order && ! $this->_random)
            {
                $sql .= $this->_order;
            } else 
                if ($this->_random)
                {
                    $sql .= " ORDER BY RAND()";
                }
            if ($this->_limit)
            {
                $prefix !== 'DELETE ' && $sql .= " limit " . $this->_offset . "," . $this->_limit;
                $prefix === 'DELETE ' && $sql .= ' limit ' . $this->_limit;
            }
        }
        $this->_where = array();
        $this->_join = array();
        $this->_on = array();
        $this->_like = NULL;
        $this->_field = NULL;
        $this->_wor = array();
        $this->_limit = 100;
        $this->_distinct = FALSE;
        $this->_groupBy = NULL;
        $this->_random = NULL;
        $this->_order = NULL;
        $this->_from = '';
        return $sql;
    }

    /**
     * 兼容CI框架
     * @return Afx_Db_Result
     */
    public function get ()
    {
        $sql = $this->_generateSql();
        return $this->getAdapter()->execute($sql);
    }

    /**
     * select the result from database use conditions
     * @param int $limit
     * @param int $offset
     * @param Boolean $master
     */
    public function select ($limit = 100, $offset = 0, $master = FALSE)
    {
        //$this->_from=$this->_partition->doPartition($this,$this->_key);
        $this->_limit = $limit == 100 ? $this->_limit : $limit;
        $sql = 'SELECT ';
        if ($this->_distinct)
        {
            $sql .= 'DISTINCT ';
        }
        $hasWhere = 0;
        if (! $this->_sql)
        {
            if (! is_array($this->_field) || count($this->_field) == 0)
            {
                $sql .= "*";
            } elseif (count($this->_field))
            {
                foreach ($this->_field as $k => $v)
                {
                    $sql .= '`' . $v . "`,";
                }
            }
            $lastIndex = strrpos($sql, ',');
            if ($lastIndex > 0)
            {
                $sql = substr($sql, 0, $lastIndex);
            }
            $sql .= " FROM " . $this->_from;
            if (is_array($this->_join) && count($this->_join) > 0)
            {
                foreach ($this->_join as $k => $v)
                {
                    $sql .= " $v[3] JOIN " . $v[0] . " ON " . $this->_from . "." . $v[1] . "=" . $v[0] . "." . $v[2];
                }
            }
            if (is_array($this->_where) && count($this->_where) > 0)
            {
                $sql .= " WHERE ";
                $hasWhere = 1;
                foreach ($this->_where as $k => $v)
                {
                    if ($v[1] != 'in')
                    {
                        $sql .= "`$v[0]` " . $v[1] . $this->_processValue($v[2]) . " AND ";
                    } else 
                        if ($v[1] == 'in')
                        {
                            if (count($v[2])) $sql .= "`$v[0]` " . $v[1] . $this->_processIn($v[2]) . " AND ";
                        }
                }
                $lastIndex = strrpos($sql, 'AND');
                if ($lastIndex > 0)
                {
                    $sql = substr($sql, 0, $lastIndex);
                }
            }
            if (is_array($this->_wor) && count($this->_wor) > 0)
            {
                if (! $hasWhere)
                {
                    $sql .= ' WHERE ';
                } else
                {
                    $sql .= ' OR ';
                }
                foreach ($this->_wor as $k => &$v)
                {
                    if ($v[1] != 'in')
                    {
                        $sql .= "`$v[0]` " . $v[1] . $this->_processValue($v[2]) . " OR ";
                    } else 
                        if ($v[1] == 'in')
                        {
                            if (count($v[2])) $sql .= "`$v[0]` " . $v[1] . $this->_processIn($v[2]) . " OR ";
                        }
                }
                $lastIndex = strrpos($sql, 'OR');
                if ($lastIndex > 0)
                {
                    $sql = substr($sql, 0, $lastIndex);
                }
            }
            if ($this->_groupBy)
            {
                $sql .= ' GROUP BY `' . $this->_groupBy . '` ';
            }
            if ($this->_order && ! $this->_random)
            {
                $sql .= $this->_order;
            } else 
                if ($this->_random)
                {
                    $sql .= " ORDER BY RAND()";
                }
            if ($this->_limit)
            {
                $sql .= " limit $offset," . $this->_limit;
            }
        }
        $this->_where = array();
        $this->_join = array();
        $this->_on = array();
        $this->_like = NULL;
        $this->_field = NULL;
        $this->_wor = array();
        $this->_limit = 100;
        $this->_distinct = FALSE;
        $this->_groupBy = NULL;
        $this->_random = NULL;
        $this->_order = NULL;
        $this->_from = '';
        return $this->_result_array = $this->getAdapter()
            ->execute($sql)
            ->result();
    }

    /**
     * set limit
     * @param int $limit
     */
    public function limit ($limit = 100)
    {
        $this->_limit = $limit;
        return $this;
    }

    /**
     * set order
     * @param string $key
     * @param string $desc
     */
    public function order ($key, $desc = 'DESC')
    {
        $this->_order = " ORDER BY $key $desc";
        return $this;
    }

    /**
     * set where condition
     * @param string $key
     * @param mixed $value
     * @param string $exp can be [>|<|!=|>=|<=|like|nlike]
     * @return Afx_Module_Abstract
     */
    public function where ($key = NULL, $value = NULL, $exp = '=')
    {
        static $allowExp = array(
            '='=>1,'>'=>1,'in'=>1,'<'=>1,'!='=>1,'>='=>1,'<='=>1,'like'=>1,'nlike'=>1,'IS'=>1
        );
        if (! $exp) $exp = '=';
        if (! isset($allowExp[$exp]))
        {
            return $this;
        }
        if ($key) $this->_where[] = array(
            $key,$exp,$value
        );
        return $this;
    }

    /**
     * set fetch field
     * @param array $fields
     */
    public function field ($fields = array())
    {
        if (count($fields))
        {
            foreach ($fields as $k => $v)
            {
                $this->_field[$k] = $v;
            }
        }
        return $this;
    }

    /**
     * find A List from the Database
     * @param array $options
     * @param int $limit
     * @param int $offset
     * @param  Boolean $master whether operator the master
     * @return array
     */
    public function findList ($options = array(), $limit = 1000, $offset = 0, $master = FALSE)
    {
        $this->_doPartition();
        if (! count($options))
        {
            return array();
        }
        $sql = 'SELECT * FROM ' . $this->_from . " WHERE ";
        foreach ($options as $k => $v)
        {
            $sql .= '`' . $k . "`=" . $this->_processValue($v) . " AND ";
        }
        $sql = substr($sql, 0, strlen($sql) - 4);
        if (is_numeric($offset) && is_numeric($limit))
        {
            if ($offset == 0)
                $sql .= " limit $limit";
            else
                $sql .= " limit $offset,$limit";
        }
        return $this->_result_array = $this->getAdapter()
            ->execute($sql)
            ->result();
    }

    /**
     * get a list from the database
     * @param int $offset
     * @param int $limit
     * @param  Boolean $master whether operator the masters
     * @return array
     */
    public function getList ($offset = 0, $limit = 1000, $master = FALSE)
    {
        //$this->_from=$this->_partition->doPartition($this,$this->_key);
        $sql = sprintf('SELECT * FROM ' . $this->_from . " LIMIT $offset,$limit ");
        return $this->_result_array = $this->getAdapter()
            ->execute($sql)
            ->result();
    }

    /**
     * @return array
     */
    public function getListALL ()
    {
        //    	$this->_from=$this->_partition->doPartition($this,$this->_key);
        $limit = $this->_limit;
        $this->_limit = NULL;
        $ret = $this->select();
        $this->_limit = $limit;
        return $ret;
    }

    /**
     *
     * set offset
     * @param int $offset
     * @return Afx_Module_Abstract
     */
    public function offset ($offset)
    {
        $this->_offset = $offset;
        return $this;
    }

    /**
     * set like condition
     * @param string $key
     * @param mixed $value
     * @return Afx_Module_Abstract

    public function like ($key,$value)
    {
      if($key)
      $this->_like=$key ." LIKE %$value% ";
      return $this;
    }
     */
    /**
     *
     * set join condition
     * @param string $table the table to join
     * @param string $lkey the left join key
     * @param string $rkey the right join key
     * @return Afx_Module_Abstract
     */
    public function join ($table, $lkey, $rkey, $type = "LEFT")
    {
        if ($table) $this->_join[] = array(
            $table,$lkey,$rkey,$type
        );
        return $this;
    }

    /**
     *
     * set or conditon
     * @param string $key
     * @param mix $value
     * @param string $exp can be [>|<|!=|>=|<=|like|nlike]
     * @return Afx_Module_Abstract
     */
    public function wor ($key = NULL, $value = NULL, $exp = '=')
    {
        static $allowExp = array(
            '>'=>1,'='=>1,'in'=>1,'<'=>1,'!='=>1,'>='=>1,'<='=>1,'like'=>1,'nlike'=>1,'IS'=>1
        );
        if (! isset($allowExp[$exp]))
        {
            return $this;
        }
        if ($key) $this->_wor[] = array(
            $key,$exp,$value
        );
        return $this;
    }

    /**
     * @param array $arr the array to insert can be one or two demision
     * @param string $table the table to insert into 
     * @param Boolean $master whether operator the master
     * @param Boolean $usetrans whether use transection
     * @return Afx_Db_Result
     */
    public function insert ($arr = array(), $table, $master = FALSE, $usetrans = FALSE)
    {
        $ret = TRUE;
        $single=FALSE;
        if ($table) $this->_from = $table;
        //    	Afx_Debug_Helper::print_r($arr);
        //        echo sizeof($arr),"<br/>";
        if (is_array($arr) && count($arr) > 0)
        {
            $first = current($arr);
            if (! is_array($first))
            {
                $sql = 'INSERT INTO ' . $this->_from . ' (';
                foreach (array_keys($arr) as $k)
                {
                    if ($k == 'id') continue;
                    $sql .= '`' . $k . '`,';
                }
                $lastIndex = strrpos($sql, ',');
                $sql = substr($sql, 0, $lastIndex);
                $sql .= ') VALUES (';
                foreach ($arr as $k => $v)
                {
                    if ($k == 'id') continue;
                    $sql .= $this->_processValue($v) . ',';
                }
                $lastIndex = strrpos($sql, ',');
                $sql = substr($sql, 0, $lastIndex);
                $sql .= ')';
                //                echo '<br>';
                $ret = $this->getAdapter()->execute($sql);
                $single=TRUE;
            }
            /**
             * 添加到数据库表时解决 column does not match values 悲剧
             */
            if(!$single&&!$this->_is_standard_2darray($arr))
            {
                $arr=$this->_classify($arr);
                foreach ($arr as $ak=>$av) 
                {
                    $ret=$this->insert($av,$table);
                    if(!$ret)
                    {
                        break;
                    }
                }
                return $ret;
            }
            
            if (is_array($first))
            {
                $sql = '';
                if (is_array($first) && count($first) > 0)
                {
                    $sql = 'INSERT INTO ' . $this->_from . ' (';
                    foreach (array_keys($first) as $k)
                    {
                        if ($k == 'id') continue;
                        $sql .= '`' . $k . '`,';
                    }
                    $lastIndex = strrpos($sql, ',');
                    $sql = substr($sql, 0, $lastIndex);
                    $sql .= ') VALUES';
                    foreach ($arr as $k => $v)
                    {
                        if (is_array($v) && count($v) > 0)
                        {
                            $sql .= ' (';
                            foreach ($v as $k1 => $v1)
                            {
                                if ($k1 == 'id') continue;
                                $sql .= $this->_processValue($v1) . ',';
                            }
                            $lastIndex = strrpos($sql, ',');
                            $sql = substr($sql, 0, $lastIndex);
                            $sql .= ' ),';
                        }
                    }
                    $lastIndex = strrpos($sql, ',');
                    $sql = substr($sql, 0, $lastIndex);
                }
                if ($sql)
                {
                    $ret = $this->getAdapter()->execute($sql);
                }
            }
        }
        $this->_from = '';
        return $ret;
    }

    /**
     * @param array $arr the array to insert can be one or two demision
     * @param string $table the table to replace into 
     * @param Boolean $master whether operator the master
     * @param Boolean $usetrans whether use transection
     * @return Afx_Db_Result
     */
    public function replace ($arr = array(), $table, $master = FALSE, $usetrans = FALSE)
    {
        $ret = TRUE;
        $single=FALSE;
        if ($table) $this->_from = $table;
        if (is_array($arr) && count($arr) > 0)
        {
            $first = current($arr);
            if (! is_array($first))
            {
                $sql = 'REPLACE INTO ' . $this->_from . ' (';
                foreach (array_keys($arr) as $k)
                {
                    //                     if ($k == 'id') continue;
                    $sql .= '`' . $k . '`,';
                }
                $lastIndex = strrpos($sql, ',');
                $sql = substr($sql, 0, $lastIndex);
                $sql .= ') VALUES (';
                foreach ($arr as $k => $v)
                {
                    //                     if ($k == 'id') continue;
                    $sql .= $this->_processValue($v) . ',';
                }
                $lastIndex = strrpos($sql, ',');
                $sql = substr($sql, 0, $lastIndex);
                $sql .= ')';
                $ret = $this->getAdapter()->execute($sql);
                $single=TRUE;
            }
             /**
             * 替换数据库表数据时解决 column does not match values 悲剧
             */
            if(!$single&&!$this->_is_standard_2darray($arr))
            {
                $arr=$this->_classify($arr);
                foreach ($arr as $ak=>$av) 
                {
                    $ret=$this->replace($av,$table);
                    if(!$ret)
                    {
                        break;
                    }
                }
                return $ret;
            }
            
            if (is_array($first))
            {
                $sql = '';
                if (is_array($first) && count($first) > 0)
                {
                    $sql = 'REPLACE INTO ' . $this->_from . ' (';
                    foreach (array_keys($first) as $k)
                    {
                        $sql .= '`' . $k . '`,';
                    }
                    $lastIndex = strrpos($sql, ',');
                    $sql = substr($sql, 0, $lastIndex);
                    $sql .= ') VALUES';
                    foreach ($arr as $k => $v)
                    {
                        if (is_array($v) && count($v) > 0)
                        {
                            $sql .= ' (';
                            foreach ($v as $k1 => $v1)
                            {
                                $sql .= $this->_processValue($v1) . ',';
                            }
                            $lastIndex = strrpos($sql, ',');
                            $sql = substr($sql, 0, $lastIndex);
                            $sql .= ' ),';
                        }
                    }
                    $lastIndex = strrpos($sql, ',');
                    $sql = substr($sql, 0, $lastIndex);
                }
                if ($sql)
                {
                    $ret = $this->getAdapter()->execute($sql);
                }
            }
        }
        $this->_from = '';
        return $ret;
    }

    public function expain ()
    {
        $sql = "EXPLAIN " . $this->getAdapter()->getLastSql();
        $explain = $this->getAdapter()
            ->execute($sql)
            ->row();
        Afx_Debug_Helper::print_r($explain);
    }

    /**
     * pass the value ;
     * @param mixed $v
     */
    private function _processValue ($v)
    {
        $ret = '';
        switch (strtolower(gettype($v)))
        {
            case 'string':
                if ($v === 'IS' || $v === 'NULL') return '  ' . $v;
                $ret = $this->quote($v);
                break;
            case 'integer':
                $ret = $this->quote($v);
                break;
            case 'double':
                $ret = $this->quote($v);
                break;
            case 'boolean':
                $ret = $this->quote($v);
                break;
            case 'null':
                $ret = $this->quote('0');
                break;
            default:
                break;
        }
        //        $ret = $this->quote($v);
        return $ret;
    }

    /**
     * @var mixed array or sql string $in
     */
    private function _processIn ($inArr)
    {
        $str = NULL;
        if (count($inArr))
        {
            $str = "('" . join("','", $inArr) . "')";
        }
        if (is_string($inArr) && strncasecmp($inArr, 'select', '6'))
        {
            $str = "( $inArr )";
        }
        return $str;
    }

    /**
     * 检查是否为标准数组
     * 每一维的键值的crc32值也必须相等
     * @param array $a
     */
    protected function _is_standard_2darray ($a = array())
    {
        $count = count($a);
        if ($count > 0)
        {
            $first = current($a);
            if(is_array($first))
            {
                 $crc=crc32(join('',array_keys($first)));
                 foreach ($a as $value) 
                 {
                    $lcrc=crc32(join('',array_keys($value)));
                    if($lcrc!=$crc)
                    {
                        return FALSE;
                    }
                 } 
                 return TRUE;
            }
        }
        return FALSE;
    }
   /**
    * 给二维数组分类
    * 按键值的crc32分类
    * @param array $array
    * @return array classified array
    */
    protected function _classify ($array)
    {
        $carray = array();
        foreach ($array as $k => $value)
        {
            $len = crc32(join('',array_keys($value)));
            if (! isset($carray[$len]))
            {
                $carray[$len] = array();
            }
            $carray[$len][] = $value;
        }
        return $carray;
    }

    protected function _init ($create=FALSE)
    {
        $config = Yaf_Registry::get('config');
        $adapter = Afx_Db_Factory::DbDriver($config['mysql']['driver'], $create);
        $adapter->setConfig($config['mysql']);
        $this->setAdapter($adapter);
    }

    /**
     * count the result from the database
     * @return int
     */
    public function count ()
    {
        $limit = $this->_limit;
        $this->_limit = NULL;
        $sql = $this->_generateSql();
        if ($sql && stripos($sql, '*') > 0)
        {
            $sql = str_ireplace('*', 'COUNT(1)', $sql);
        }
        $this->_limit = $limit;
        if (is_array($this->_result_array))
        {
            //              Afx_Debug_Helper::print_r(($this->_result_array));
            return count($this->_result_array);
        }
        if (! $sql) $sql = sprintf('SELECT COUNT(1) FROM `%s`', $this->_from);
        $ret = $this->getAdapter()
            ->execute($sql)
            ->row();
        return isset($ret['COUNT(1)']) ? $ret['COUNT(1)'] : 0;
    }

    /**
     * @return Afx_Module_Abstract
     */
    public function distinct ()
    {
        $this->_distinct = TRUE;
        return $this;
    }

    /**
     * @return Afx_Module_Abstract
     */
    public function random ()
    {
        $this->_random = TRUE;
        return $this;
    }

    /**
     * @return Afx_Module_Abstract
     */
    public function groupBy ($key)
    {
        if ($key) $this->_groupBy = $key;
        return $this;
    }
    
    public function __destruct()
    {
    }
}
