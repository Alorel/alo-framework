<?php
   echo 'I am a sample view. If you pass me the variable \'foo\' I will display its value here: ' . $foo;

   trigger_error('Test error', E_USER_NOTICE);

   throw new Exception('First exception', 666, new Exception('Second exception', 777));