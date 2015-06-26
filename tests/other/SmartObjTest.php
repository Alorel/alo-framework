<?php

    use Alo\Traversables\SmartObj;

    class SmartObjTest extends PHPUnit_Framework_TestCase {

        /** @var SmartObj */
        private $obj;

        function testDeleteRegex() {
            $this->reloadObj();
            $this->obj->deleteWithKeyRegex('~^foo~', true);

            $this->assertEquals(['bar_1' => 1,
                                 'bar_2' => ['bar_3' => 1]], $this->obj->toArray());
        }

        private function reloadObj() {
            $this->obj = new SmartObj(['foo_1' => 1,
                                       'foo_2' => ['foo_3' => 1,
                                                   'bar_3' => 1],
                                       'bar_1' => 1,
                                       'bar_2' => ['foo_3' => 1,
                                                   'bar_3' => 1]]);
        }

        function testDeleteInverse() {
            $this->reloadObj();
            $this->obj->deleteWithKeyRegex('~^foo', true, true);

            $this->assertEquals(['foo_1' => 1,
                                 'foo_2' => ['foo_3' => 1]], $this->obj->toArray());
        }
    }
