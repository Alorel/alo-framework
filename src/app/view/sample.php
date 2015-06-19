<?php

   $db = new \Alo\Db\MySQL();

   initSession($db);

   echo debug($_SESSION);

   $_SESSION['rand'] = microtime(true);

   echo debug($_SESSION);
