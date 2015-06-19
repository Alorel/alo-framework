<?php

   $mcw = new \Alo\Cache\MemcachedWrapper();

   echo debug($mcw->purge());
