<?php
/**
 * Created by PhpStorm.
 * User: dengh
 * Date: 2018/11/15
 * Time: 9:32
 */

namespace Framework\Traits;

use Framework\SwServer\ServerManager;

trait DaoTrait
{
    private $_daos = [];
    public function setDaos($daos)
    {
        foreach ($daos as $id => $dao) {
            $this->createDaoObject($id, $dao);
        }
    }

    /**
     * coreDaos 定义核心服务
     * @return   array
     */
    public function coreDaos()
    {
        return [];
    }

    /**
     * creatObject 创建服务对象
     * @param    string $com_alias_name 组件别名
     * @param    array $defination 组件定义类
     * @return   array
     */

    public function createDaoObject(string $com_alias_name = null, array $defination = [])
    {
        // 动态创建公用组件
        if (!isset($this->_daos[$com_alias_name])) {
            if (isset($defination['class'])) {
                $class = $defination['class'];
                if (!isset($this->_singletons[$class])) {
                    $this->registerObject($com_alias_name, $defination);
                    $this->_daos[$com_alias_name] = $class;
                    return $this->_singletons[$class];
                } else {
                    return $this->_singletons[$class];
                }
            } else {
                throw new \Exception("dao:" . $com_alias_name . 'must be set class', 1);
            }
        } else {
            return $this->_singletons[$this->_daos[$com_alias_name]];
        }
        return false;
    }


    /**
     * clearDao
     * @param    string|array $service_alias_name
     * @return   boolean
     */
    public function clearDao($com_alias_name = null)
    {
        if (!is_null($com_alias_name) && is_string($com_alias_name)) {
            $com_alias_name = (array)$com_alias_name;
        } else if (is_array($com_alias_name)) {
            $com_alias_name = array_unique($com_alias_name);
        } else {
            return false;
        }
        foreach ($com_alias_name as $alias_name) {
            unset($this->_singletons[$this->_daos[$alias_name]]);
            unset($this->_daos[$alias_name]);
        }
        return true;
    }

    public function initDaos()
    {
        // 配置文件初始化创建公用对象
        $coreDaos = $this->coreDaos();
        $daos = array_merge($coreDaos, ServerManager::$config['daos']);
        foreach ($daos as $com_name => $service) {
            // 存在直接跳过
            if (isset($this->_daos[$com_name])) {
                continue;
            }
            if (isset($service['class']) && $service['class'] != '') {
                $defination = $service;
                $this->createDaoObject($com_name, $defination);
                $this->_daos[$com_name] = $service['class'];
            }
        }
        return $this->_singletons;
    }


    public function getDaos()
    {
        return $this->_daos;
    }

    public function getDao($alias_name)
    {
        if (isset($this->_daos[$alias_name])) {
            return $this->_singletons[$this->_daos[$alias_name]];
        } else if (in_array($alias_name, array_keys(ServerManager::$config['daos']))) {
            return $this->createDaoObject($alias_name, ServerManager::$config['daos'][$alias_name]);
        }
        return false;
    }


}
