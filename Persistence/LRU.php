<?php
require "./Input.php";
class LRU{
	protected $lru = [];
	protected $maxCount;

	public function __construct($maxCount=5){
		$this->maxCount = $maxCount;
	}
	public function set($key,$value){
		//判断队列中是否存在该元素
		if(array_key_exists($key,$this->lru)){
			//放在队列头部
			unset($this->lru[$key]);
		}
		//判断是否超出对列长度，超出剔除尾部
		if(count($this->lru)>$this->maxCount){
			array_pop($this->lru);
		}
		$this->lru[$key] = $value;
	}
	public  function get($key,$value){
		if(array_key_exists($key,$this->lru)){
			return $this->lru[$key];	
		}
    }
}
$LRU = new LRU(6);
$LRU->set(1,2);
$LRU->set(2,3);
$LRU->set(2,5);
$LRU->set(3,6);
$LRU->set(4,7);
$LRU->set(2,8);
Input::info($LRU);
