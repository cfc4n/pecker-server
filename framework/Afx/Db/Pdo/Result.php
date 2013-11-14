<?php
/**
 * @version $Id: Result.php 1 2013-09-17 10:45:47Z cfc4n $
 * @author zhangwenhao 
 *
 */
class Afx_Db_Pdo_Result implements Afx_Db_Result
{

    /**
     * @var PDOStatement
     */
    public $result;

    /**
     * @var PDO
     */
    public $link;
    
    public $affected=0;
    
    public $__result_array = array();

    public function affected ()
    {
          return $this->affected;
    }

    public function insertId ()
    {
        return $this->link->lastInsertId();
    }

    public function recordCount ()
    {
        return $this->result->rowCount();
    }

    public function row ($n = 0)
    {
        $i = 0;
        $result = $this->result;
        $result_array = array();
        if ($this->result)
        {
            while ($i <= $n && $r = $result->fetch_assoc())
            {
                $result_array[] = $r;
            }
        }
        return isset($result_array[$n]) ? $result_array[$n] : array();
    }

    public function result ()
    {
        if (count($this->__result_array))
        {
            return $this->__result_array;
        }
        $result = $this->__fetch_all();
        return $result;
    }

    public function fieldCount ()
    {
        return $this->result->columnCount();
    }

    private function __fetch_all ()
    {
        if (count($this->__result_array)) return $this->__result_array;
        $result = $this->result;
        $result_array = array();
        if ($result)
        {
            while (FALSE != ($r = $result->fetch()))
            {
                $result_array[] = $r;
            }
        }
        $this->__result_array = $result_array;
        return $result_array;
    }
}