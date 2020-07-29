<?php
/**
 * Created by PhpStorm.
 * User: hdeng
 * Date: 2018/12/17
 * Time: 17:39
 */

namespace Framework\SwServer\Pool;

use Framework\Traits\SingletonTrait;
use Framework\SwServer\Rpc\Client\RpcClient;

class RpcClientPoolManager extends PoolBase
{
    use SingletonTrait;
    public $poolObject = '';
    public $default_config = [
        'clients' => [],
        'timeout' => 1.5,
        'space_time' => 100,
        'mix_pool_size' => 2,     //最小连接池大小
        'max_pool_size' => 10,    //最大连接池大小
        'pool_get_timeout' => 4, //当在此时间内未获得到一个连接，会立即返回。（表示所以的连接都已在使用中）
    ];

    public function checkConnection()
    {
        try {
            $rpcClient = new RpcClient($this->config);
            if (!$rpcClient->isConnected()) {
                throw new \Exception("failed to connect rpc client.");
            }
        } catch (\Exception $e) {
            echo $e->getMessage() . "\r\n";
            return false;
        }
        return $rpcClient;
    }


    public static function checkIsConnection(RpcClient $rpcClient)
    {
        $isConnection = true;
        if (!$rpcClient->isConnected()) {
            $isConnection = false;
        }
        return $isConnection;
    }

    public function __construct($config = [])
    {
        if ($config) {
            $this->config = array_merge($this->default_config, $config);
        } else {
            $this->config = $this->default_config;
        }
        $this->config = array_filter($this->config, function ($val) {
            if (!$val) {
                if (is_numeric($val) && $val === 0) {
                    return true;
                }
                return false;
            }
            return true;
        });
        parent::__construct($this->config);
    }
}