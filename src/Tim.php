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
	private $admin_identifier;

	/**
	 * 管理员签名
	 */
	private $admin_sign;

	/**
	 * 私钥文件路径
	 */
	private $private_pem;

	/**
	 * 签名缓存目录
	 */
	private $sign_cache_directory;

	/**
	 * 密钥缓存失效时间
	 */
	private $sign_expired_seconds;

	/**
	 * \GuzzleHttp\Client
	 */
	private $client;

	/**
	 * Self Class
	 */
	private static $tim;

	private function __construct($sdkappid, $admin_identifier, array $sign)
	{
		$this->sdkappid                  = $sdkappid;
		$this->admin_identifier          = $admin_identifier;
		$this->private_pem				 = $sign['private_pem'];
		$this->sign_cache_directory      = $sign['cache_directory'];
		$this->sign_expired_seconds 	 = $sign['expired_seconds'];
		$this->admin_sign                = $this->getUserSignature($admin_identifier);

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
		$identifier = $config['admin_identifier'];
		$sign  = [
			'private_pem'     => $config['sign']['private_pem'],
			'cache_directory' => $config['sign']['cache_directory'],
			'expired_seconds' => (int) $config['sign']['expired_days'] * 86400,
		];
		return new self($sdkappid, $identifier, $sign);
	}

	/**
	 * 获取sign
	 * @param string $account 用户账号
	 * @return string user_sign  密钥
	 * @return int    expired_at 密钥失效时间 时间戳
	 */
	public function getUserSign($account)
	{
		$user_sign  = $this->getUserSignature($account);
		$expired_at = filemtime($this->getUserSignatureCatchFile($account)) + $this->sign_expired_seconds;
		return compact('user_sign', 'expired_at');
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
			throw new TencentyunException("用户注册失败".$http_body['ErrorCode'].$http_body['ErrorInfo'], 2);

		return true;
	}
	
}