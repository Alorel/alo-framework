<?php

    use Alo\Cache\MemcachedWrapper;
    use Alo\Cache\RedisWrapper;

    class AbstractCacheTest extends PHPUnit_Framework_TestCase {

        /**
         * @var MemcachedWrapper|RedisWrapper
         */
        protected $wrapper;

        /** @var string */
        protected $wrapper_name;

        /**
         * @dataProvider definedProvider
         */
        function testDefined($const) {
            $this->assertTrue(defined($const), $const . ' wasn\'t defined.');
        }

        function testValueSet() {
            $toTest = ['val_int'    => 515,
                       'val_string' => 'str',
                       'val_float'  => 1.1,
                       'val_array'  => ['foo' => 'bar'],
                       'val_obj'    => new \stdClass];

            foreach ($toTest as $key => $val) {
                $this->assertTrue($this->wrapper->set($key, $val),
                                  'Failed to set ' . $this->wrapper_name . ' ' . json_encode(['key' => $key,
                                                                                              'val' => $val]));
                $get = $this->wrapper->get($key);

                $this->assertEquals($val, $get, _unit_dump(['Key'      => $key,
                                                            'Val'      => $val,
                                                            'Expected' => $val,
                                                            'Actual'   => $get]));
            }
        }

        function testPurge() {
            $this->wrapper->set('foo', 1);

            $this->assertTrue($this->wrapper->purge(), 'Purge returned false');
        }

        function testDelete() {
            $this->wrapper->set('test_del', 1);
            $this->wrapper->delete('test_del');

            $this->assertEmpty($this->wrapper->get('test_del'),
                               'Test_del returned: ' . $this->wrapper->get('test_del'));
        }

        function testGetAll() {
            $this->wrapper->purge();
            $this->wrapper->set('aloframework', 'just works');
            $getall = $this->wrapper->getAll();

            $this->assertEquals(['aloframework' => 'just works'], $getall, _unit_dump($getall));
        }
    }
