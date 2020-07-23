<?php
namespace Lixia18\Redis\MasterAndSlave;
class RedisMs{
	protected $config = [];
	protected $connection;
	protected $connSlaveIndexs;

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
		if($config['is_master']){
			$this->connection['master'] = $this->getConnection($config['master']['host'],$config['master']['port']);
			$this->createSlave($config['slaves']);
			//Input::info($this->connection, "这是获取的连接");
            //Input::info($this->connSlaveIndexs, "这是连接的下标");
		}else{

		}
		$this->maintain();
		$this->config = $config;
	}
	/*------------主从维护--------------------------*/
	/*
    1. 获取主节点连接信息
    2. 获取从节点的偏移量
    3. 获取连接个数
        3.1 偏移量的计算
        3.2 维护列表
     */
	public function maintain(){
		$masterRedis = $this->getMaster();
		//得到主节点的连接信息
		$replInfo = $masterRedis->info('replication');
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

	public function createSlave($slaves){
		foreach($slaves as $slave){
			$this->connection['slaves'][$this->redisFlag($slave['host'],$slave['port'])] = $this->getConnection($slave['host'],$slave['port']);
		}
		// 记录从节点的下标
        $this->connSlaveIndexs = array_keys($this->connection['slaves']);
	}
	/*---------获取主从连接--------------*/

	public function getMaster(){
		return $this->connection['master'];
	}
	public function getSlave(){
		return $this->connection['slaves'];
	}
	public function getOneSlave(){
		$indexs = $this->connSlaveIndexs;
        $i = mt_rand(0, count($indexs) - 1);
        return $this->connections['slaves'][$indexs[$i]];
	}

	/*-------------执行命令----------------*/
	public function runCall($command,$params = []){
		try {
			if($this->config['is_master']){
				$redis= $this->getCallCommand($command);
				return $redis->{$command}(...$params);
			}
		}catch (\Exception $e) {}
	}

	public function getCallCommand($command){
		if(in_array($command,$this->call['write'])){
			return $this->getMaster();
		}elseif(in_array($command,$this->call['read'])){
			return $this->getOneSlave();
		}else{
			throw new Exception("Error Processing Request", 1);
			
		}
	}
}