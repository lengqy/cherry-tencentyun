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
			. '?usersig=' . $this->admin_sign
            . '&identifier=' . $this->admin_identifier
            . '&sdkappid=' . $this->sdkappid
            . '&contenttype=json';
	}

	/**
	 * 获取sign
	 * @param $cache_path  缓存文件路径
	 * @param $identifier  用户名
	 */
	protected function getUserSignature($identifier)
	{
		$cache_file = $this->getUserSignatureCatchFile($identifier);
		if(file_exists($cache_file) && (time() - filemtime($cache_file)) <= $this->sign_expired_seconds)
			return file_get_contents($cache_file);
			
		return $this->makeUserSignature($cache_file, $identifier);
	}

	/**
	 * 获取sign缓存文件
	 * @param $identifier  用户名
	 */
	protected function getUserSignatureCatchFile($identifier)
	{
		$cache_path = $this->sign_cache_directory;
		if (substr($cache_path, 0, -1) != '/' || substr($cache_path, 0, -1) != '\\')
			$cache_path = $cache_path . DIRECTORY_SEPARATOR;

		return $cache_path . 'tencentyun_sig_' . $identifier;
	}

	/**
	 * 生成sign
	 * @param $cache_file  缓存文件地址
	 * @param $identifier  用户名(必须为 App 管理员帐号)
	 */
	protected function makeUserSignature($cache_file, $identifier)
	{
		$command = escapeshellarg($this->getToolPath())
			. ' '. escapeshellarg($this->private_pem)
			. ' ' . escapeshellarg($this->sdkappid)
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