<?php

    use Alo\Cache\RedisWrapper;

    $r = new RedisWrapper();

    foreach ($r as $k => $v) {
        echo debug($k, $v);
    }
