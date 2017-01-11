<?php
namespace Common\Lib\Log;

use DateTime;
use RuntimeException;


/**
 * 文件日志类
 */
class Logger extends AbstractLogger{
    /**
     * 配置选项
     * 非核心配置通过构造参数传递
     *
     * 核心配置包含日志路径和日志最低记录级别
     *
     * @var array
     */
    protected $options = array (
        'extension'      => 'txt',
        'dateFormat'     => 'Y-m-d G:i:s.u',
        'filename'       => false,
        'flushFrequency' => false,
        'prefix'         => 'log_',
        'logFormat'      => false,
        'appendContext'  => true,
    );

    //日志保存路径
    private $logFilePath;

    //记录的日志的最低级别
    protected $logLevelThreshold = LogLevel::DEBUG;

    //一个周期记录的最大行数
    private $logLineCount = 0;

    //日志等级
    protected $logLevels = array(
        LogLevel::EMERGENCY => 0,
        LogLevel::ALERT     => 1,
        LogLevel::CRITICAL  => 2,
        LogLevel::ERROR     => 3,
        LogLevel::WARNING   => 4,
        LogLevel::NOTICE    => 5,
        LogLevel::INFO      => 6,
        LogLevel::DEBUG     => 7
    );

    //日志文件处理句柄
    private $fileHandle;

    //保存最后一行日志，用于单元测试
    private $lastLine = '';

    //日志文件权限
    private $defaultPermissions = 0777;

    /**
     * 构造方法
     *
     * @param string $logDirectory      日志文件夹路径
     * @param string $logLevelThreshold 最低级别
     * @param array  $options
     */
    public function __construct($logDirectory, $logLevelThreshold = LogLevel::DEBUG, array $options = array()){
        $this->logLevelThreshold = $logLevelThreshold;
        $this->options = array_merge($this->options, $options);
        $logDirectory = rtrim($logDirectory, DIRECTORY_SEPARATOR);
        if ( ! file_exists($logDirectory)) {
            mkdir($logDirectory, $this->defaultPermissions, true);
        }
        if(strpos($logDirectory, 'php://') === 0) {
            $this->setLogToStdOut($logDirectory);
            $this->setFileHandle('w+');
        } else {
            $this->setLogFilePath($logDirectory);
            if(file_exists($this->logFilePath) && !is_writable($this->logFilePath)) {
//                throw new RuntimeException('文件不可写入，请检查文件权限是否已经被设置.');
                return false;
            }
            $this->setFileHandle('a');
        }
        if ( ! $this->fileHandle) {
//            throw new RuntimeException('文件不能打开，请检查文件权限.');
            return false;
        }
    }

    /**
     * 析构函数
     */
    public function __destruct()
    {
        if ($this->fileHandle) {
            fclose($this->fileHandle);
        }
    }

    /**
     * @param string $stdOutPath
     */
    public function setLogToStdOut($stdOutPath) {
        $this->logFilePath = $stdOutPath;
    }

    /**
     * @param string $logDirectory
     */
    public function setLogFilePath($logDirectory) {
        if ($this->options['filename']) {
            if (strpos($this->options['filename'], '.log') !== false || strpos($this->options['filename'], '.txt') !== false) {
                $this->logFilePath = $logDirectory.DIRECTORY_SEPARATOR.$this->options['filename'];
            }
            else {
                $this->logFilePath = $logDirectory.DIRECTORY_SEPARATOR.$this->options['filename'].'.'.$this->options['extension'];
            }
        } else {
            $this->logFilePath = $logDirectory.DIRECTORY_SEPARATOR.$this->options['prefix'].date('Y-m-d').'.'.$this->options['extension'];
        }
    }

    /**
     * @param $writeMode
     *
     * @internal param resource $fileHandle
     */
    public function setFileHandle($writeMode) {
        $this->fileHandle = fopen($this->logFilePath, $writeMode);
    }


    /**
     * 设置日志的时间格式
     *
     * @param string $dateFormat date()函数有效的时间格式
     */
    public function setDateFormat($dateFormat){
        $this->options['dateFormat'] = $dateFormat;
    }

    /**
     * 设置最低日志级别
     *
     * @param string $logLevelThreshold 最低级别
     */
    public function setLogLevelThreshold($logLevelThreshold){
        $this->logLevelThreshold = $logLevelThreshold;
    }

    /**
     * 记录任意级别日志
     *
     * @param mixed $level
     * @param string $message
     * @param array $context
     * @return null
     */
    public function log($level, $message, array $context = array()){
        if ($this->logLevels[$this->logLevelThreshold] < $this->logLevels[$level]) {
            return;
        }
        $message = $this->formatMessage($level, $message, $context);
        $this->write($message);
    }

    /**
     * 写入日志，没有在前面加时间和状态
     *
     * @param string 写入的内容
     * @return void
     */
    public function write($message){
        if (null !== $this->fileHandle) {
            if (fwrite($this->fileHandle, $message) === false) {
//                throw new RuntimeException('文件不可写入，请检查文件权限是否已经被设置.');
                return false;
            } else {
                $this->lastLine = trim($message);
                $this->logLineCount++;
                if ($this->options['flushFrequency'] && $this->logLineCount % $this->options['flushFrequency'] === 0) {
                    fflush($this->fileHandle);
                }
            }
        }
    }

    /**
     * 获取文件路径
     *
     * @return string
     */
    public function getLogFilePath(){
        return $this->logFilePath;
    }

    /**
     * 获取最后一行日志
     *
     * @return string
     */
    public function getLastLogLine(){
        return $this->lastLine;
    }

    /**
     * 格式化日志内容.
     *
     * @param  string $level   级别
     * @param  string $message 日志内容
     * @param  array  $context 上下文数据
     * @return string
     */
    protected function formatMessage($level, $message, $context){
        if ($this->options['logFormat']) {
            $parts = array(
                'date'          => $this->getTimestamp(),
                'level'         => strtoupper($level),
                'level-padding' => str_repeat(' ', 9 - strlen($level)),
                'priority'      => $this->logLevels[$level],
                'message'       => $message,
                'context'       => json_encode($context),
            );
            $message = $this->options['logFormat'];
            foreach ($parts as $part => $value) {
                $message = str_replace('{'.$part.'}', $value, $message);
            }
        } else {
            $message = "[{$this->getTimestamp()}] [{$level}] {$message}";
        }
        if ($this->options['appendContext'] && ! empty($context)) {
            $message .= PHP_EOL.$this->indent($this->contextToString($context));
        }
        return $message.PHP_EOL;
    }

    /**
     * 获取当前时间格式.
     *
     * PHP DateTime is dump, and you have to resort to trickery to get microseconds
     * to work correctly, so here it is.
     *
     * @return string
     */
    private function getTimestamp(){
        $originalTime = microtime(true);
        $micro = sprintf("%06d", ($originalTime - floor($originalTime)) * 1000000);
        $date = new DateTime(date('Y-m-d H:i:s.'.$micro, $originalTime));
        return $date->format($this->options['dateFormat']);
    }

    /**
     * 转换context为字符串.
     *
     * @param  array $context The Context
     * @return string
     */
    protected function contextToString($context){
        $export = '';
        foreach ($context as $key => $value) {
            $export .= "{$key}: ";
            $export .= preg_replace(array(
                '/=>\s+([a-zA-Z])/im',
                '/array\(\s+\)/im',
                '/^  |\G  /m'
            ), array(
                '=> $1',
                'array()',
                '    '
            ), str_replace('array (', 'array(', var_export($value, true)));
            $export .= PHP_EOL;
        }
        return str_replace(array('\\\\', '\\\''), array('\\', '\''), rtrim($export));
    }

    /**
     * 缩进
     *
     * @param  string $string The string to indent
     * @param  string $indent What to use as the indent.
     * @return string
     */
    protected function indent($string, $indent = '    '){
        return $indent.str_replace("\n", "\n".$indent, $string);
    }
}