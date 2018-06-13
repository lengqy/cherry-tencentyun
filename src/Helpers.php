<?php 

namespace Cherry\Tencentyun;

Trait Helpers
{

	/** 
	 * 构造访问REST服务器的url
	 * @param $server_name 服务名
	 * @param $cmd_name    命令名
	 */
	protected function getRequestUrl($service_name, $cmd_name)
	{
		return $service_name . '/' . $cmd_name
			. '?usersig=' . $this->user_sign
            . '&identifier=' . $this->identifier
            . '&sdkappid=' . $this->sdkappid
            . '&contenttype=json';
	}

	/**
	 * 获取sign
	 * @param $cache_file  缓存文件地址
	 * @param $sdkappid    App 在云通信控制台上获取的 Appid
	 * @param $identifier  用户名(必须为 App 管理员帐号)
	 * @param $private_pem 私钥文件路径
	 */
	protected function getUserSignature($cache_file, $sdkappid, $identifier, $private_pem)
	{
		if(file_exists($cache_file) && (time() - filemtime($cache_file)) <= $this->user_sign_expired_seconds)
			return file_get_contents($cache_file);
			
		return $this->makeUserSignature($cache_file, $sdkappid, $identifier, $private_pem);
	}

	/**
	 * 生成sign
	 * @param $cache_file  缓存文件地址
	 * @param $sdkappid    App 在云通信控制台上获取的 Appid
	 * @param $identifier  用户名(必须为 App 管理员帐号)
	 * @param $private_pem 私钥文件路径
	 */
	protected function makeUserSignature($cache_file, $sdkappid, $identifier, $private_pem)
	{
		$command = escapeshellarg($this->getToolPath())
			. ' '. escapeshellarg($private_pem)
			. ' ' . escapeshellarg($sdkappid)
			. ' ' .escapeshellarg($identifier);
		$ret = exec($command, $out, $status);
		if ($status == -1)
			throw new TencentyunException("生成sig失败", 1);
			
    	$user_sign = $out[0];
    	file_put_contents($cache_file, $user_sign);
    	return $user_sign;
	}

	/**
	 * 获取生成UserSig工具的地址
	 */
	protected function getToolPath()
	{
		if($this->is64Bit()){
			if(PATH_SEPARATOR==':')
				$signature = "signature/linux-signature64";
			else
				$signature = "signature/windows-signature64.exe";
		}else{
			if(PATH_SEPARATOR==':')
				$signature = "signature/linux-signature32";
			else
				$signature = "signature/windows-signature32.exe";
		}
		return dirname(__FILE__).'/../'.$signature;
	}

	/**
	 * 判断系统bit
	 */
	protected function is64Bit()
	{
		$int = "9223372036854775807";
		$int = intval($int);
		if ($int == 9223372036854775807)
			return true;
		elseif ($int == 2147483647)
			return false;
		
		throw new TencentyunException("操作系统不可用", 1);
	}

}