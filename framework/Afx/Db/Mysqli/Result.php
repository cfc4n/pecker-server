<?php
/**
 * @version $Id: Result.php 1 2013-09-17 10:45:47Z cfc4n $
 * @author zhangwenhao 
 *
 */
class Afx_Db_Mysqli_Result implements Afx_Db_Result
{

    /**
     * @var mysqli_result
     */
    public $result;
    /**
     * @var mysqli
     */
    public $link;
    public $__result_array = array();

    
    public function affected ()
    {
        return  $this->link->affected_rows;
    }
    public function insertId()
    {
        return $this->link->insert_id;
    }
    public function recordCount ()
    {
        return $this->result->num_rows;
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
        return $this->result->field_count;
    }

    private function __fetch_all ()
    {
        if(count($this->__result_array))return $this->__result_array;
        $result = $this->result;
        $result_array = array();
        if ($result)
        {
            while (FALSE != ($r = $result->fetch_assoc()))
            {
                $result_array[] = $r;
            }
        }
        $this->__result_array = $result_array;
        return $result_array;
    }
}