<?php
   echo 'I am a sample view. If you pass me the variable \'foo\' I will display its value here: ' . $foo;

   $pr = new \Alo\Profiler();
   $pr->mark('foo');
   sleep(2);
   $pr->mark('bar');

   echo debug($pr->getMarks(), $pr->timeBetween('bar', 'foo'));