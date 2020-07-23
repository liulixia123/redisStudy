<?php
namespace Lixia18\Redis\SentinelClient;
use Lixia18\Redis\SentinelClient\Traits\Sentinel;
//use Lixia18\Redis\SentinelClient\Input;
class RedisMs{
	use Sentinel;
	protected $config = [];
	protected $connection;
	protected $connIndex;

	protected $call = [
		'write'=>[
			'set',
			'sadd'
		],
		'read'=>[
			'get',
			'smembers'
		]
	];


	public function __construct($config){		
		$this->config = $config;
		$this->{$this->config['initType']}();
	}
    /*------------------初始化操作----------------------------*/
    protected function isMsInit(){
    	Input::info("主从读写分离");
		$this->connection['master'] = $this->getConnection($config['master']['host'],$config['master']['port']);
		// 创建从节点
		$this->createConns($config['slaves']);
		// 进行节点偏移量检测 -- 判断是否延迟
		$this->maintain();
    }
	protected function normalInfo()
	{
		$this->connection['master'] = $this->getConnection($this->config['host'], $this->config['port']);
	}

	/**
	 * 进行节点偏移量检测 -- 判断是否延迟
	 * @return [type] [description]
	 */
	public function maintain(){
		Input::info("检测状态");
		if($this->config['initType'] =='isSentinelInit'){
			$this->sentinelInit();
		}
		$this->delay();
		
	}
	/*------------主从维护--------------------------*/
	/*
    1. 获取主节点连接信息
    2. 获取从节点的偏移量
    3. 获取连接个数
        3.1 偏移量的计算
        3.2 维护列表
     */
	protected function delay(){
		try {
          	$masterRedis = $this->getMaster();// 故障迁移之后是使用原有主节点的连接
			//得到主节点的连接信息
			$replInfo = $masterRedis->info('replication');
       	} catch (\Exception $e) {
           Input::info("哨兵检测");
           return null;
       	}		
		// 得到主节点偏移量
        $masterOffset = $replInfo['master_repl_offset'];
        // 记录新增的从节点
        $slaves = [];
       
        for($i = 0;$i<$replInfo['connected_slaves'];$i++){
        	// 获取slave的信息
        	$slaveInfo = $this->stringToArr($replInfo['slave'.$i]);
        	$slaveFlag = $this->redisFlag($slaveInfo['ip'],$slaveInfo['port']);
        	//检测主从复制偏移量
        	if(($masterOffset-$slaveInfo['offset'])<100){
        		//正常偏移量
        		if (!in_array($slaveFlag, $this->connSlaveIndexs)) {
                          $slaves[$slaveFlag] = [
                              'host' => $slaveInfo['ip'],
                              'port' => $slaveInfo['port']
                          ];
                          Input::info($slaveFlag,"新增从节点");
                      }
                    Input::info($slaveFlag, "正常");
        	}else{
        		//延迟较高剔除从节点
        		Input::info($slaveFlag, "删除节点");
        		unset($this->connection['slaves'][$slaveFlag]);
        	}

        }

	}
	public function stringToArr($str,$flag1 = ',', $flag2 = '='){
		//ip=127.0.0.1,port=6385,state=online,offset=3655,lag=0
		$arr = explode($flag1,$str);
		$ret = [];
		foreach ($arr as $key => $value) {
			$arr2 = explode($flag2,$value);
			$ret[$arr2[0]] = $arr2[1];
		}
		return $ret;
	}

    /*-----------创建主从连接---------------*/
	public function getConnection($host,$port){
		$redis= new \Redis();
		$redis->pconnect($host,$port);
		return $redis;
	}

    private function redisFlag($host, $port)
    {
        return $host.":".$port;
    }

	public function createConns($slaves,$flags = 'slaves'){
		foreach($slaves as $slave){
			$this->connection[$flags][$this->redisFlag($slave['host'],$slave['port'])] = $this->getConnection($slave['host'],$slave['port']);
		}
		// 记录从节点的下标
        $this->connIndex[$flags] = array_keys($this->connection[$flags]);
	}
	/*---------获取主从连接--------------*/

	public function getMaster(){
		return $this->connection['master'];
	}
	public function getSlave(){
		return $this->connection['slaves'];
	}
	public function getConn($flags = 'slaves'){
		$indexs = $this->connIndex[$flags];
        $i = mt_rand(0, count($indexs) - 1);
        return $this->connection[$flags][$indexs[$i]];
	}

	/*-------------执行命令----------------*/
	public function runCall($command,$params = []){
		try{
			if ($this->config['initType']) {
				$redis = $this->getCallCommand($command);
			}else{
				$redis = $this->getMaster();
			}
			return $redis->{$command}(...$params);
		}catch(Exception $e) {
		}
	}

	public function getCallCommand($command){
		if(in_array($command,$this->call['write'])){
			return $this->getMaster();
		}elseif(in_array($command,$this->call['read'])){
			return $this->getConn();
		}else{
			throw new Exception("Error Processing Request", 1);
			
		}
	}
}