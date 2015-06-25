<?php

    class Foo implements IteratorAggregate {

        private $data = ['foo' => 'bar'];

        public function getIterator() {
            return new ArrayIterator($this->data);
        }
    }

    $f = new Foo();

    foreach ($f as $k => $v) {
        echo $k . '=>' . $v . '<br/>';
    }

    $a = (array)$f;
    echo debug($a);
