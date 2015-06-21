<?php

    use Alo\Cache\MemcachedWrapper;
    use Alo\Db\MySQL;

    $db = new MySQL();
    $mc = new MemcachedWrapper();

    var_dump($mc->set('str', 'a string'));
    var_dump($mc->set('arr', [['foo' => 'bar']]));
    var_dump($mc->set('obj', new \stdClass()));
    var_dump($mc->getAll());
