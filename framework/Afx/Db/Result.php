<?php
interface Afx_Db_Result
{

    public function recordCount ();

    public function row ($n = 0);

    public function result ();

    public function fieldCount ();
    
    public function insertId();
    
    public function affected();
}