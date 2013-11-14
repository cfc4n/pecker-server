<?php
class Afx_Logger
{

    public static $_logpath;

    public static $_logfile = 'Afx_log';

    public static $_logfilename = 'Afx_log';

    public static $_filenum = 0;
    
    public static $_argsnum = 1;

    //128M 一个文件
    //    public static $_log_size = 1024;
    public static $_log_size = 134217728;

    public static function cmp ($a, $b)
    {
        $ra = explode('/', $a);
        $rb = explode('/', $b);
        $a = array_pop($ra);
        $b = array_pop($rb);
        return substr($a, 7) - substr($b, 7);
    }

    public static function log ($msg = 'success', $level = E_ERROR, $no_trace = FALSE)
    {
       
        $real_log_path=self::$_logpath . date('Y-m-d');
        if (version_compare(PHP_VERSION, '5.3.6') >= 0) {
            $debug = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT);    //此参数，必须保证 php 5.3.6+
        }
        else
        {
            $debug = debug_backtrace();
        }
        
        $time = date('Y-m-d H:i:s', time());
        $logmsg = array();
        $logmsg[]=str_repeat('=', 80)."\n";
        unset($debug[0]);
        $i = 0;
        if($no_trace===FALSE)
        {
            foreach ($debug as $value)
            {
                $args = '';
                if($i < self::$_argsnum)    //只记录前self::$_argsnum次调用的参数
                {
                    $args = print_r($value['args'], TRUE);
                }
                $d_file = $file = isset($value['file']) ? $value['file'] : '';
                $line = isset($value['line']) ? $value['line'] : '';
                $type = isset($value['type'])?$value['type']:'';
                $class = isset($value['class'])?$value['class']:'';
                $function = $value['function'];
                $d_file = str_ireplace('\\', '/', $d_file);
                $logmsg[] = $time . "->" . $file . "->" . $class . $type . "" . $function . '--' . $line . '--' . $args ."\n";
                $i++;
            }
        }
        $logmsg[] = $msg;
        if (! file_exists($real_log_path))
        {
            mkdir($real_log_path, 0777, true);
        }
        $files = glob($real_log_path . '/*');
        usort($files, 'Afx_Logger::cmp');
        $file = array_pop($files);
        if ($file)
        {
            $temp = explode('/', $file);
            $file = array_pop($temp);
            self::$_logfile = basename($file);
            $mc = array();
            preg_match_all('/\d+/', $file, $mc);
            if (isset($mc[0][0]) && is_numeric($mc[0][0]))
            {
                self::$_filenum = $mc[0][0];
            }
        }
        
        if (file_exists($real_log_path . '/' . self::$_logfile)) if (filesize($real_log_path . '/' . self::$_logfile) > self::$_log_size)
        {
            self::$_logfile = self::$_logfilename . ++ self::$_filenum;
        }
        $logmsg[]="\n".str_repeat('=', 80)."\n";
        file_put_contents($real_log_path . '/' . self::$_logfile, join($logmsg, "") . "\n", FILE_APPEND | LOCK_EX);
    }
    
}
