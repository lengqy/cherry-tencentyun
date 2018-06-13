<?php 

return [
	'sdkappid' 	 => 'App 在云通信控制台上获取的 Appid',
	'identifier' => '用户名(必须为 App 管理员帐号)',
	'user_sign'  => [
		'private_pem_path' => '密钥文件路径',
		'cache_file_path'  => '密钥缓存在本地的文件路径',
		'expired_days'     => '密钥有效期，官方默认为180天，建议小于180天',
	],
];