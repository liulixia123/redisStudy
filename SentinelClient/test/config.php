<?php
namespace Lixia18\Redis;
$config = [
	'host' => '',
	'port' => '',
	'is_master' => "true",
	'initType' => 'isSentinelInit',//初始化操作isMsInit isSentinelInit normalInfo
	'master' => [
		'host' => '127.0.0.1',
		'port' => '6385'
	],
	'slaves' => [
		'slave1' => ['host' => '127.0.0.1',
					'port' => '6384'],
		'slave2' => ['host' => '127.0.0.1',
					'port' => '6386']
	],
	'sentinels' => [
		'masterName' => 'mymaster',
		'addr' => [
			[
			'host' => '127.0.0.1',
			'port' => 16378
			],
			[
			'host' => '127.0.0.1',
			'port' => 16379
			],
			[
			'host' => '127.0.0.1',
			'port' => 16380
			],
		]
	]

];