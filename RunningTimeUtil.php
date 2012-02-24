<?php

/*
 * 记算脚本的运行时间
 * 调用顺序: run_start -> run_end -> getResult
 */

/**
 * Description of RunningTimeUtil
 *
 * @author samuel.li
 */
class RunningTimeUtil {
    public static $start_time=0;
    public static $end_time=0;
    public static $total_time=0;
    public static function run_start() {
        $mtime = microtime();
        $mtime = explode(" ",$mtime);
        $mtime = $mtime[1] + $mtime[0];
        self::$start_time = $mtime;
     }
     
     public static function run_end() {
        $mtime = microtime();        
        $mtime = explode(" ",$mtime);
        $mtime = $mtime[1] + $mtime[0];           
        self::$end_time = $mtime;
        self::$total_time = (self::$end_time - self::$start_time);
     }
     
     public static function getResult() {
         return "Total time: ".self::$total_time;
     }
}

?>
