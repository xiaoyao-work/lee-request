<?php
    file_put_contents('./log.log', json_encode($_REQUEST));
    sleep(1);