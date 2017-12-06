<?php
namespace Lee\Request;

/**
 * ConcurrenceCurl 操作管理
 * @author 逍遥·李志亮 <xiaoyao.working@gmail.com>
 */
class ConcurrenceCurlManager {
    private $key;
    private $concurrenceCurl;

    public function __construct($key) {
        $this->key             = $key;
        $this->concurrenceCurl = ConcurrenceCurl::getInstance();
    }

    public function getResponse() {
        return $this->concurrenceCurl->getResult($this->key);
    }

    public function __get($name) {
        $responses = $this->concurrenceCurl->getResult($this->key);
        return $responses[$name];
    }

    public function __isset($name) {
        $val = self::__get($name);
        return empty($val);
    }
}