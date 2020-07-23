<?php
namespace Lixia18\Redis\SentinelClient\Traits;
use Lixia18\Redis\SentinelClient\Input;
trait Sentinel{
	protected $maseterName = 'mymaster';
	protected $masterFlag = null;
	protected $sentinelFlag = 'sentinels';

	protected function isSentinelInit()
    {
        $this->masterFlag = $this->redisFlag( $this->config['master']['host'],  $this->config['master']['port']);
        $this->setSentinels($this->config['sentinels']['addr']);
        $this->sentinelInit();
    }
	/**
	 * 得到redis的详细信息，master与slave的 ip及port
	 * @return [type] [description]
	 */
	public function sentinelInit(){
		//1 获取哨兵信息
		$sentinel = $this->getConn($this->sentinelFlag);
		// 2. 根据哨兵获取主节点及从节点信息
		$masterInfo = $sentinel->rawCommand('sentinel', 'get-master-addr-by-name', $this->config['sentinels']['masterName']);
		$slaveInfo = $sentinel->rawCommand('sentinel', 'slaves', $this->config['sentinels']['masterName']);
		//Input::info($masterInfo);
		//Input::info($slaveInfo);
		$newFlag = $this->redisFlag($masterInfo[0], $masterInfo[1]);		
		 // 判断是否新的主节点
		if($this->masterFlag == $newFlag){
			Input::info('主节点没有问题');
			return;
		}
		Input::info($newFlag , "主节点有问题，切换节点");
        $this->masterFlag = $newFlag;

        //配置维护
        unset($this->config['master']); 
        unset($this->config['slaves']);
        $this->config['master'] = [
            'host' => $masterInfo[0],
            'port' => $masterInfo[1]
        ];
        foreach ($slaveInfo as $key => $slave) {
            $this->config['slaves'][$key] = [
                'host' => $slave[3],
                'port' => $slave[5]
            ];
        }

        Input::info($this->config);

        $this->isMsInit();

	}
	/**
	 * 设置哨兵
	 * @param [type] $sentinels [description]
	 */
	public function setSentinels($sentinels)
	{
		$this->createConns($sentinels, $this->sentinelFlag);
	}
}