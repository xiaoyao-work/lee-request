<?php
    file_put_contents('./request.log', json_encode($_REQUEST));
    echo '休眠一秒钟';
    sleep(1);
    echo '休眠结束';