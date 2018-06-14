<?php 

return [
	'sdkappid' 	       => 'App 在云通信控制台上获取的 Appid',
	'admin_identifier' => '用户名(必须为 App 管理员帐号)',
	'sign'             => [
		'private_pem'     => '密钥文件路径',
		'cache_directory' => '密钥缓存在本地的文件夹，需要有写入权限',
		'expired_days'    => '密钥有效期，官方默认为180天，建议小于180天',
	],
];