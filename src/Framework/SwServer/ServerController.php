<?php
/**
 * Created by PhpStorm.
 * User: hdeng
 * Date: 2019/1/2
 * Time: 9:44
 */

namespace Framework\SwServer;
use Framework\SwServer\Base\BaseObject;
use Framework\SwServer\Http\HttpInput;
use Framework\SwServer\ServerManager;

class ServerController extends BaseObject
{
    protected $httpInput=null;
    protected $httpOutput=null;


    public function init()
    {
        $this->httpInput=new HttpInput(ServerManager::getApp()->request);
        //$this->httpOutput=new HttpInput(ServerManager::getApp()->response);
    }

    public function __call($name, $arguments='')
    {
        // TODO: Implement __call() method.
        echo __CLASS__."方法{$name}不存在!!";
    }


}