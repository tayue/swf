<?php
/**
 * 用户自定义进程
 */

namespace Framework\SwServer\Process;

use Framework\Tool\Tool;
class CustomerProcess
{
    public $mpid = 0;
    public $works = [];
    public $max_precess = 3;
    public $new_index = 0;
    private $processName;
    private $childProcessNamePre;
    private $config;
    private $callback;

    public function __construct($processName, $childProcessNamePre, $processNums = 0, $callback = null, $config = [])
    {
        try {
            $this->config = $config;
            $processNums && $this->max_precess = $processNums;
            $this->processName = $processName;
            $this->childProcessNamePre = $childProcessNamePre;
            $this->callback=$callback;
            swoole_set_process_name(sprintf('SwooleCustomerProcess:%s', $this->processName));
            $this->mpid = posix_getpid();
            $this->run($callback);
            $this->processWait();
        } catch (\Exception $e) {
            die('ALL ERROR: ' . $e->getMessage());
        }
    }

    public function run($callback)
    {
        for ($i = 1; $i <= $this->max_precess; $i++) {
            $this->CreateProcess($i, $callback);
        }
    }

    public function CreateProcess($index = null, $callback)
    {
        $process = new \swoole_process(function (\swoole_process $worker) use ($index, $callback) { //子进程
            if (is_null($index)) {
                $index = $this->new_index;
                $this->new_index++;
            }
            swoole_set_process_name(sprintf('php-ps:%s', $this->childProcessNamePre . $index));
            $this->checkMpid($worker);
            Tool::call($callback, [$index,$this->config]);
        }, false, false,true);
        $pid = $process->start();
        $this->works[$index] = $pid;
        return $pid;
    }

    public function checkMpid(&$worker)
    {
        if (!\swoole_process::kill($this->mpid, 0)) { //向主进程发送信号检测主进程是否存活
            $worker->exit();
            // 这句提示,实际是看不到的.需要写到日志中
            echo "Master process exited, I [{$worker['pid']}] also quit\n";
        }
    }

    public function rebootProcess($ret)
    {
        $pid = $ret['pid'];
        $index = array_search($pid, $this->works);
        if ($index !== false) {
            $index = intval($index);
            $new_pid = $this->CreateProcess($index,$this->callback);
            echo "rebootProcess: {$index}={$new_pid} Done\n";
            return;
        }
        throw new \Exception('rebootProcess Error: no pid');
    }

    public function processWait()
    {
        while (1) {
            if (count($this->works)) {
                $ret = \swoole_process::wait();
                if ($ret) {
                    $this->rebootProcess($ret);
                }
            } else {
                break;
            }
        }
    }
}

;
