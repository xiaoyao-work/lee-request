<?php
/**
* Logic 基类
* @author 李志亮 <lizhiliang@kankan.com>
*/
class BaseLogic {
    protected $errorCode = 0;

    /**
     * 接口返回的错误信息
     * @var null
     */
    protected $serviceErrorInfo = null;

    public function getInterfaceData($concurrence_curl_manager) {
        $response = $concurrence_curl_manager->getResponse();
        if ($response['code'] == 200) {
            $response_data = $response['data'];
            return $response_data;
        } else {
            // 记录接口请求错误
            echo ("CURL REQUEST ERROR : HTTP_CODE=" . $response['code'] . '; TOTAL_TIME=' . $response['time'] . "; EFFECTIVE_URL=" . $response['url'] . '; Data :' . $response['data']);
            return false;
        }
    }

}