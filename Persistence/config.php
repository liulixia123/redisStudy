<?php
$config = [
	'is_master' => "true",
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

];