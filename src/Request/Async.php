<?php
namespace Lee\Request;

/**
* 异步请求
* @author 逍遥·李志亮 <xiaoyao.working@gmail.com>
*/
class Async {
    public static function get($url, $params=array(), $cookies=array()) {
        $parts = parse_url($url);
        try {
            $fp = self::createSock($parts);
        } catch (\Exception $e) {
            echo $e->getMessage();
            return false;
        }
        $query_string = "";
        if (!empty($params)) {
            $query_string = '?' . http_build_query($params);
        }
        $parts['path'] = isset($parts['path']) ? $parts['path'] : '/';
        $request = "GET {$parts['path']}{$query_string}  / HTTP/1.1\r\n";
        $request .= "Host: {$parts['host']}\r\n";
        $request .= "Cache-Control: max-age=0\r\n";
        // $request .= 'User-Agent: Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.2.1) Gecko/20021204\r\n';
        // $request .= 'Accept: text/xml,application/xml,application/xhtml+xml,';
        // $request .= 'text/html;q=0.9,text/plain;q=0.8,video/x-mng,image/png,';
        // $request .= "image/jpeg,image/gif;q=0.2,text/css,*/*;q=0.1\r\n";
        // $request .= "Accept-Language: en-us, en;q=0.50\r\n";
        // $request .= "Accept-Encoding: gzip, deflate, compress;q=0.9\r\n";
        // $request .= "Accept-Charset: ISO-8859-1, utf-8;q=0.66, *;q=0.66\r\n";
        // $request .= "Keep-Alive: 300\r\n";
        $request .= "Referer: http://www.hhailuo.com/\r\n";
        if (!empty($cookies)) {
            $request .= parseCookie($cookies) . "\r\n";
        }
        $request .= "Connection: Close\r\n\r\n";

        fwrite($fp, $request);
        fclose($fp);
    }

    public static function post($url, $params=array(), $cookies=array()) {
        $parts = parse_url($url);
        try {
            $fp = self::createSock($parts);
        } catch (\Exception $e) {
            echo $e->getMessage();
            return false;
        }
        $parts['path'] = isset($parts['path']) ? $parts['path'] : '/';
        $request = "POST ".$parts['path']." HTTP/1.1\r\n";
        $request.= "Host: ".$parts['host']."\r\n";
        $request.= "Content-Type: application/x-www-form-urlencoded\r\n";
        $post_string = "";
        if (!empty($params)) {
            $post_string = http_build_query($params);
        }
        $request.= "Content-Length: ".strlen($post_string)."\r\n";
        $request.= "Connection: Close\r\n";
        if (!empty($cookies)) {
            $request .= parseCookie($cookies) . "\r\n";
        }
        $request .= "\r\n" . $post_string;
        fwrite($fp, $request);
        fclose($fp);
    }

    protected static function createSock($parts) {
        $fp = fsockopen($parts['host'], isset($parts['port']) ? $parts['port']:80, $errno, $errstr, 1);
        if (!$fp) {
            throw new \Exception("创建sock失败", $errno);
        }
        return $fp;
    }

    /**
     * 解析cookie
     * @param  array $cookies cookie数组
     * @return string curl cookie
     */
    protected static function parseCookie($cookies) {
        if (empty($cookies)) {
            return '';
        }
        $cookie_string = 'Cookie: ';
        foreach ($cookies as $key => $value) {
            $cookie_string .= ($key . '=' . $value . '; ');
        }
        return $cookie_string;
    }

}
