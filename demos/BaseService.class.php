<?php
use Lee\Request\Async;
use Lee\Request\ConcurrenceCurl;

/**
 * 服务接口基类
 * @author 逍遥·李志亮 <xiaoyao.working@gmail.com>
 */
class BaseService {

	static protected $ConcurrenceCurl = NULL;
	/**
	 * 接口调用
	 * @param  string $request_url 接口请求地址
	 * @param  array  $options     CURL请求附加参数
	 * @return ConcurrenceCurlManager
	 */
	static public function call($request_url, $options = []) {
		if (empty(self::$ConcurrenceCurl)) {
			self::$ConcurrenceCurl = ConcurrenceCurl::getInstance();
		}
		return self::$ConcurrenceCurl->addUrl($request_url, $options);
	}

	static public function post($request_url, $params = [], $options = []) {
		$options[CURLOPT_POST]       = true;
		$options[CURLOPT_POSTFIELDS] = $params;
		return self::call($request_url, $options);
	}

	static public function asyncGet($request_url, $params = []) {
		Async::get($request_url, $params);
	}

	static public function asyncPost($request_url, $params = []) {
		Async::post($request_url, $params);
	}
}