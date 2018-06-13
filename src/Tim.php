<?php 

namespace Cherry\Tencentyun;

use GuzzleHttp\Client;

class Tim
{
	use Helpers;
	
	/**
	 * App 在云通信控制台上获取的 Appid
	 */
	private $sdkappid;

	/**
	 * 用户名(必须为 App 管理员帐号)
	 */
	private $identifier;

	/**
	 * 用户名对应的签名
	 */
	private $user_sign;

	/**
	 * 缓存文件创建的时间
	 */
	private $user_sign_cache_create;

	/**
	 * 缓存文件失效时间
	 */
	private $user_sign_expired_seconds;

	/**
	 * \GuzzleHttp\Client
	 */
	private $client;

	/**
	 * Self Class
	 */
	private static $tim;

	private function __construct($sdkappid, $identifier, array $user_sign)
	{
		$this->sdkappid                  = $sdkappid;
		$this->identifier                = $identifier;
		$this->user_sign_expired_seconds = $user_sign['expired_days'] * 86400;
		$this->user_sign                 = $this->getUserSignature($user_sign['cache_file'], $sdkappid, $identifier, $user_sign['private_pem']);
		$this->user_sign_cache_create    = filemtime($user_sign['cache_file']);

		$this->client = new Client([
		    'base_uri' => 'https://console.tim.qq.com/v4/',
		    'timeout'  => 2.0,
		]);
	}

	/**
	 * 初始化类
	 */
	public static function init()
	{
		if (!empty(self::$tim))
			return self::$tim;

		$config = include(dirname(__FILE__).'/../config/tencenttim.php');
		$sdkappid   = $config['sdkappid'];
		$identifier = $config['identifier'];
		$user_sign  = [
			'private_pem'  => $config['user_sign']['private_pem_path'],
			'cache_file'   => $config['user_sign']['cache_file_path'],
			'expired_days' => (int) $config['user_sign']['expired_days'],
		];
		return new self($sdkappid, $identifier, $user_sign);
	}

	/**
	 * 获取sign
	 * @return string
	 */
	public function getUserSign()
	{
		return $this->user_sign;
	}

	/**
	 * 获取Sign失效时间
	 * @return int timestamp
	 */
	public function getUserSignExpiredAt()
	{
		return $this->user_sign_cache_create + $this->user_sign_expired_seconds;
	}

	/**
	 * 创建用户
	 * @param string $name 用户名
	 * @return void
	 */
	public function createUser($name)
	{
		$url = $this->getRequestUrl('im_open_login_svc', 'account_import');
		$json = [
			'Identifier' => $name
		];
		$respose = $this->client->post($url, compact('json'));

		$http_code = $respose->getStatusCode();
		$http_body = $respose->getBody();
		if ($http_code != 200)
			throw new TencentyunException("请求失败", 1);

		$http_body = json_decode($http_body, true);
		if (empty($http_body) || $http_body['ErrorCode'] !== 0)
			throw new TencentyunException("用户注册失败", 2);

		return true;
	}
	
}