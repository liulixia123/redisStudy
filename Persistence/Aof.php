<?php
require "./Input.php";
require "./RedisModel.php";
class Aof{
	protected $redis;
	protected $filePath;

	public function __construct($host='127.0.0.1',$port="6384",$file='/aof_file.aof'){
		$this->redis = new RedisModel();
		$this->redis->pconnect($host, $port);
		$this->filePath= __DIR__.$file;
	}

	 public function aofRewrite(){
	 	//1.查看有多少个库
	 	$dbs = $this->redis->config('GET', 'databases')['databases'];
	 	//2. 根据库去循环获取key scan
	 	for($i=0;$i<$dbs;$i++){
	 		// 切换数据库
            $this->redis->select($i);
            $commands = null;
            //针对于 $i 这个数据库去进行命令的重写
            $commands = $this->rewrite($commands);
            if (!empty($commands)) {
              Input::info($commands);
              $this->rewriteFile( "db:".$i.";key:".$commands);
            }
            
	 	}
	 	//Input::info($dbs);
	 }
	//命令的重写
	protected function rewrite($commands, $iterator = -1){
		$keys = $this->redis->scan($iterator);
		//Input::info($keys);
		// 是否有数据
        if (empty($keys)) {
            return ;
        }
        // 重写获取的key的数据
        foreach ($keys as $key) {
            // 得到key的类型
            $keyType = $this->redis->getType($key);
            // 再根据类型去重写命令，并且拼接
            $commands .= $this->{"rewrite".$keyType}($key);
        }
        //判断后面是否还有数据
        if($iterator>0){
        	return $this->rewrite($commands);
        }else{
        	return $commands;
        }
	}
	//写入文件中
	protected function rewriteFile($commands)
    {
        file_put_contents($this->filePath, $commands, 8);
    }
    //
	protected function rewriteSet($key)
    {
        $value = $this->redis->sMembers($key);
        return $this->rewriteCommand('SADD', $key, implode(" ", $value));
    }
    protected function rewriteString($key)
    {
    	$value = $this->redis->get($key);
        return $this->rewriteCommand('SET', $key, $value);
    }
    protected function rewriteList($key)
    {
    	$value = $this->redis->lrange($key,0,-1);
        return $this->rewriteCommand('LPUSH', $key, implode(" ", $value));
    }
    protected function rewriteZset($key)
    {
        $value = $this->redis->zrange($key,0,-1 ,true);
        $com = '';
    	foreach ($value as $k => $v) {
    		$com .= $v. " " .$k." ";
    	}
        return $this->rewriteCommand('ZADD', $key, $com);
    }
    protected function rewriteHash($key)
    {
    	$value = $this->redis->hgetall($key);
    	$com = '';
    	foreach ($value as $k => $v) {
    		$com .= $k. " " .$v." ";
    	}
        return $this->rewriteCommand('HMSET', $key, $com);
    }
    protected function rewriteExpireTime($key)
    {
        return $this->rewriteCommand('SADD', $key, $value);
    }
	protected function rewriteCommand($method, $key, $value)
    {
        return $method." ".$key." ".$value.";";
    }
}