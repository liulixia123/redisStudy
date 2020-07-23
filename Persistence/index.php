<?php
require './config.php';
require '/Redis.php';

$RedisMS = new RedisMS($config);
//$RedisMS->runCall('set',['aa','lixia']);
//var_dump($RedisMS->runCall('get',['aa']));
//require './Aof.php';

//$aof = new Aof();
//$aof->aofRewrite();