<?php
namespace Common\Lib\Log;

/**
 * 日志psr3接口
 */
abstract class AbstractLogger {

    /**
     * 系统不可用
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function emergency($message, array $context = array()){}

    /**
     * 必须马上执行修复
     *
     * 例如: 页面崩溃，数据库不可用等等，发生这样的错误后，系统需要发送短信提示相关人员
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function alert($message, array $context = array()){}

    /**
     * 很严重的情况
     *
     * 例如: 程序组件不可用，抛出未知异常
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function critical($message, array $context = array()){}

    /**
     * 运行时错误，这样的错误不必马上修复，但需要被记录和监控。
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function error($message, array $context = array()){}

    /**
     * 发生异常，但没有到错误级别.
     *
     * 例子: 使用了官方反对的函数，或使用了多余的东西。
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function warning($message, array $context = array()){}

    /**
     * 发生正常但重要的事件
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function notice($message, array $context = array()){}

    /**
     * 感兴趣的事件
     *
     * 例子: 用户执行的sql.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function info($message, array $context = array()){}

    /**
     * 调试信息
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function debug($message, array $context = array()){}

    /**
     * 任意级别日志
     *
     * @param mixed $level
     * @param string $message
     * @param array $context
     * @return null
     */
    public function log($level, $message, array $context = array()){}

}