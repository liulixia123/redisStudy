<?php
require __DIR__.'/../../vendor/autoload.php';
use Lixia18\Redis\SentinelClient\RedisMs;
use Lixia18\Redis\SentinelClient\Input;
require_once './config.php';
global $redisMS;
$redisMS = new RedisMs($config);