<?php
   echo 'I am a sample view. If you pass me the variable \'foo\' I will display its value here: ' . $foo;

   throw new \Alo\Exception\ControllerException('foo');
   //use Alo\Profiler as P;
   //
   //$pr = new P();
   //$pr->mark('foo');
   //usleep(1000);
   //$pr->mark('bar');
   //
   //echo debug($pr->diff_on_key(P::P_MEMORY_USAGE, 'foo', 'bar'), $pr->diff_on_key(P::P_HEADERS, 'foo', 'bar'), $pr->diff_on_key('foo', 'foo', 'bar'));
   //
   //$pr->mark('end');
   //
   //echo debug($pr->getMarks());