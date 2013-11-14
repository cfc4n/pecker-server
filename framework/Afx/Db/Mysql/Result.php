<?php
/**
 * @version $Id: Result.php 1 2013-09-17 10:45:47Z cfc4n $
 * @author zhangwenhao 
 *
 */
class Afx_Db_Mysql_Result implements Afx_Db_Result
{

    /**
     * @var resource
     */
    public $result;

    public $link;

    public $__result_array = array();

    public function recordCount ()
    {
        return mysql_num_rows($this->result);
    }

    public function affected ()
    {
        return mysql_affected_rows($this->link);
    }

    public function insertId ()
    {
        mysql_insert_id($this->link);
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
        return mysql_num_fields($this->result);
    }

    private function __fetch_all ()
    {
        if (count($this->__result_array)) return $this->__result_array;
        $result = $this->result;
        $result_array = array();
        if ($result)
        {
            while (FALSE != ($r = mysql_fetch_assoc($this->result)))
            {
                $result_array[] = $r;
            }
        }
        $this->__result_array = $result_array;
        return $result_array;
    }
}