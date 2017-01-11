<?php
namespace Common\Lib\Log;

/**
 * 日志级别
 */
class LogLevel {
    const EMERGENCY = 'emergency';  //紧急
    const ALERT     = 'alert';      //警报
    const CRITICAL  = 'critical';   //严重
    const ERROR     = 'error';      //错误
    const WARNING   = 'warning';    //警告
    const NOTICE    = 'notice';     //通知
    const INFO      = 'info';       //信息
    const DEBUG     = 'debug';      //调试
}