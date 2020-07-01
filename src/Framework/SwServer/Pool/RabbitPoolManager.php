<?php
/**
 * Created by PhpStorm.
 * User: hdeng
 * Date: 2018/12/17
 * Time: 15:13
 */

namespace Framework\SwServer\Pool;

use Framework\Traits\SingletonTrait;
use PhpAmqpLib\Connection\AbstractConnection;
use PhpAmqpLib\Connection\AMQPStreamConnection;

class RabbitPoolManager extends PoolBase
{
    use SingletonTrait;
    public $poolObject = '';
    public $default_config = [
        'host' => '192.168.99.88',   //ip
        'port' => 5672,          //端口
        'user' => 'admin',        //用户名
        'password' => 'admin', //密码
        'vhost' => 'my_vhost',   //默认主机
        'space_time' => 100,       //100s间隔
        'mix_pool_size' => 2,     //最小连接池大小
        'max_pool_size' => 3,    //最大连接池大小
        'pool_get_timeout' => 4, //当在此时间内未获得到一个连接，会立即返回。（表示所以的连接都已在使用中）
    ];

    public function checkConnection()
    {
        try {
            // 1、连接到 RabbitMQ Broker，建立一个连接
            $connection = new AMQPStreamConnection($this->config['host'], $this->config['port'], $this->config['user'], $this->config['password'], $this->config['vhost']);
            if (!$connection) {
                //连接失败，抛弃常
                throw new \Exception("failed to connect Rabbitmq server.");
            }
        } catch (\Exception $e) {
            echo $e->getMessage() . "\r\n";
            return false;
        }
        return $connection;
    }

    public static function checkIsConnection($connection){
        $isConnection=true;
        if(!$connection->isConnected()){
            $isConnection=false;
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
        parent::__construct($this->config);
    }


}