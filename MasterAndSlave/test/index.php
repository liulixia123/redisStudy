<?php
require __DIR__.'/../../vendor/autoload.php';
use Lixia18\Redis\MasterAndSlave\RedisMs;
use Lixia18\Redis\MasterAndSlave\Input;
require_once './config.php';
global $redisMS;
$redisMS = new RedisMs($config);