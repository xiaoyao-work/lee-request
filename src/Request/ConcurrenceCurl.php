<?php
namespace Lee\Request;

use Exception;

/**
 * 并发CURL类库
 * @author 逍遥·李志亮 <xiaoyao.working@gmail.com>
 */
class ConcurrenceCurl {
	const TIMEOUT_MS        = 5000;
	const CONNECTTIMEOUT_MS = 2000;
	static $inst            = null;
	static $singleton       = 0;
	private $mc;
	private $msgs;
	private $running;
	private $execStatus;
	private $selectStatus;
	private $sleepIncrement = 1.1;
	private $requests       = [];
	private $responses      = [];
	private $properties     = [];
	private $curlExecTime   = [];

	public function __construct() {
		if (self::$singleton == 0) {
			throw new Exception('This class cannot be instantiated by the new keyword.  You must instantiate it using: $obj = ConcurrenceCurl::getInstance();');
		}
		$this->mc = curl_multi_init();
		/**
		 *  这个参数可能是以下常量之一:
		 *  CURLINFO_EFFECTIVE_URL - 最后一个有效的URL地址
		 *  CURLINFO_HTTP_CODE - 最后一个收到的HTTP代码
		 *  CURLINFO_FILETIME - 远程获取文档的时间，如果无法获取，则返回值为“-1”
		 *  CURLINFO_TOTAL_TIME - 最后一次传输所消耗的时间
		 *  CURLINFO_NAMELOOKUP_TIME - 名称解析所消耗的时间
		 *  CURLINFO_CONNECT_TIME - 建立连接所消耗的时间
		 *  CURLINFO_PRETRANSFER_TIME - 从建立连接到准备传输所使用的时间
		 *  CURLINFO_STARTTRANSFER_TIME - 从建立连接到传输开始所使用的时间
		 *  CURLINFO_REDIRECT_TIME - 在事务传输开始前重定向所使用的时间
		 *  CURLINFO_SIZE_UPLOAD - 以字节为单位返回上传数据量的总值
		 *  CURLINFO_SIZE_DOWNLOAD - 以字节为单位返回下载数据量的总值
		 *  CURLINFO_SPEED_DOWNLOAD - 平均下载速度
		 *  CURLINFO_SPEED_UPLOAD - 平均上传速度
		 *  CURLINFO_HEADER_SIZE - header部分的大小
		 *  CURLINFO_HEADER_OUT - 发送请求的字符串
		 *  CURLINFO_REQUEST_SIZE - 在HTTP请求中有问题的请求的大小
		 *  CURLINFO_SSL_VERIFYRESULT - 通过设置CURLOPT_SSL_VERIFYPEER返回的SSL证书验证请求的结果
		 *  CURLINFO_CONTENT_LENGTH_DOWNLOAD - 从Content-Length: field中读取的下载内容长度
		 *  CURLINFO_CONTENT_LENGTH_UPLOAD - 上传内容大小的说明
		 *  CURLINFO_CONTENT_TYPE - 下载内容的Content-Type:值，NULL表示服务器没有发送有效的Content-Type: header
		 */
		$this->properties = [
			'code'   => CURLINFO_HTTP_CODE,
			'time'   => CURLINFO_TOTAL_TIME,
			'length' => CURLINFO_CONTENT_LENGTH_DOWNLOAD,
			'type'   => CURLINFO_CONTENT_TYPE,
			'url'    => CURLINFO_EFFECTIVE_URL,
		];
	}

	// simplifies example and allows for additional curl options to be passed in via array
	public function addURL($url, $options = []) {
		$ch = curl_init($url);
		// 记录CURL开始时间
		$this->curlExecTime[$this->getKey($ch)] = microtime(true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_TIMEOUT_MS, self::TIMEOUT_MS);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, self::CONNECTTIMEOUT_MS);
		curl_setopt($ch, CURLOPT_VERBOSE, true);

		foreach ($options as $option => $value) {
			curl_setopt($ch, $option, $value);
		}
		return $this->addCurl($ch);
	}

	public function addCurl($ch) {
		$key                  = $this->getKey($ch);
		$this->requests[$key] = $ch;
		curl_setopt($ch, CURLOPT_HEADERFUNCTION, [$this, 'headerCallback']);
		$code = curl_multi_add_handle($this->mc, $ch);
		if ($code === CURLM_OK || $code === CURLM_CALL_MULTI_PERFORM) {
			do {
				$code = $this->execStatus = curl_multi_exec($this->mc, $this->running);
			} while ($this->execStatus === CURLM_CALL_MULTI_PERFORM);
			return new ConcurrenceCurlManager($key);
		} else {
			return $code;
		}
	}

	public function getResult($key = null) {
		if ($key != null) {
			if (isset($this->responses[$key]['data'])) {
				return $this->responses[$key];
			}
			$innerSleepInt = $outerSleepInt = 100;

			// 超时判断
			while ($this->running && ($this->execStatus == CURLM_OK || $this->execStatus == CURLM_CALL_MULTI_PERFORM)) {
				usleep($outerSleepInt);
				$outerSleepInt *= $this->sleepIncrement;
				$ms = curl_multi_select($this->mc);
				if ($ms >= CURLM_CALL_MULTI_PERFORM) {
					do {
						$this->execStatus = curl_multi_exec($this->mc, $this->running);
						usleep($innerSleepInt);
						$innerSleepInt *= $this->sleepIncrement;
					} while ($this->execStatus == CURLM_CALL_MULTI_PERFORM);
					$innerSleepInt = 100;
				}
				$this->storeResponses();
				if (isset($this->responses[$key]['data'])) {
					return $this->responses[$key];
				}
				$runningCurrent = $this->running;
			}
			return null;
		}
		return false;
	}

	public function cleanupResponses() {
		$this->responses = [];
	}

	private function getKey($ch) {
		return (string) $ch;
	}

	private function headerCallback($ch, $header) {
		$_header  = trim($header);
		$colonPos = strpos($_header, ':');
		if ($colonPos > 0) {
			$key                                                  = substr($_header, 0, $colonPos);
			$val                                                  = preg_replace('/^\W+/', '', substr($_header, $colonPos));
			$this->responses[$this->getKey($ch)]['headers'][$key] = $val;
		}
		return strlen($header);
	}

	private function storeResponses() {
		while ($done = curl_multi_info_read($this->mc)) {
			$key                           = (string) $done['handle'];
			$this->responses[$key]['data'] = curl_multi_getcontent($done['handle']);
			foreach ($this->properties as $name => $const) {
				$this->responses[$key][$name] = curl_getinfo($done['handle'], $const);
			}
            $this->responses[$key]['error'] = curl_error($done['handle']);
            $this->responses[$key]['exec_time'] = microtime(true) - $this->curlExecTime[$key];
			curl_multi_remove_handle($this->mc, $done['handle']);
			curl_close($done['handle']);
		}
	}

	public static function getInstance() {
		if (self::$inst == null) {
			self::$singleton = 1;
			self::$inst      = new self();
		}
		return self::$inst;
	}

	public function __destruct() {
		curl_multi_close($this->mc);
	}
}
