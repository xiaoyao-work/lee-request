<?php
    require '../Lib/Async.class.php';
    require './BaseService.class.php';

    echo "开始请求百度" . "<br />";
    $time = microtime(true);
    BaseService::asyncPost('http://www.baidu.com');
    echo "异步发送请求完成！" . "<br />";
    echo "耗时" . (microtime(true) - $time) * 1000 . 'ms' . "<br />";

    echo "开始请求Google" . "<br />";
    $time = microtime(true);
    BaseService::asyncPost('http://localhost/php-async-concurrence/demos/test.php', array('key' => 'value'));
    echo "异步发送请求完成！" . "<br />";
    echo "耗时" . (microtime(true) - $time) * 1000 . 'ms' . "<br />";