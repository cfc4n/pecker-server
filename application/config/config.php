<?php
return array(
     'timezone'=>'Asia/Shanghai',
     'debug'=>TRUE,
     'mysql'=>array(
     'driver'=>'mysqli',
     'host'=>'localhost',
     'user'=>'root',
     'port'=>3306,
     'dbname'=>'pecker',
     'socket'=>'',
     'pass'=>'',
     'persist'=>FALSE,
     'timeout'=>10,
     'waittimeout'=>86400,
    ),
    'scandir' => dirname(__FILE__),
    'extend' => array('php','inc','php5'),
    'function' => array('exec','system','create_function','passthru','shell_exec','proc_open','popen','curl_exec','parse_ini_file','show_source','assert'),
);