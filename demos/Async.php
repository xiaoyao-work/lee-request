<?php
    require __DIR__ . "/../vendor/autoload.php";
    echo "开始请求百度<br />\r\n";
    $time = microtime(true);
    BaseService::asyncPost('http://www.baidu.com');
    echo "异步发送请求完成！" . "<br />\r\n";
    echo "耗时" . (microtime(true) - $time) * 1000 . 'ms' . "<br />\r\n";

    echo "开始请求测试文件" . "<br />\r\n";
    $time = microtime(true);
    BaseService::asyncPost('http://localhost/lee-request/demos/test.php', array('key' => 'value'));
    echo "异步发送请求完成！" . "<br />\r\n";
    echo "耗时" . (microtime(true) - $time) * 1000 . 'ms' . "<br />\r\n";
