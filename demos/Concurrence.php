<?php
    require '../Lib/ConcurrenceCurl.class.php';
    require './BaseService.class.php';
    require './BaseLogic.class.php';

    $logic = new \BaseLogic();

    /* 发送请求 */
    $tp_admin_request = \BaseService::call('http://tp3.hhailuo.com/');
    $baidu_request = \BaseService::call('http://www.baidu.com/');

    /* 获取请求的数据 */
    $tp_admin_response = $logic->getInterfaceData($tp_admin_request);
    $baidu_request = $logic->getInterfaceData($baidu_request);

    echo $tp_admin_response;