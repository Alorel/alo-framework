<?php

   namespace Alo\Test;

   use Alo\Exception\TesterException as TE;
   use Alo\Statics\Format as F;

   if (!defined('GEN_START')) {
      http_response_code(404);
      die();
   }

   /**
    * The abstract tester class
    *
    * @author     Art <a.molcanovas@gmail.com>
    * @package    TestingSuite
    * @deprecated Since v1.1
    */
   abstract class AbstractTester {

      /**
       * The test queue
       *
       * @var array
       */
      protected $queue;

      /**
       * The test results
       *
       * @var array
       */
      protected $results;

      /**
       * Defines a parameter as "name"
       *
       * @var string
       */
      const P_NAME = 'name';

      /**
       * Defines a parameter as "arguments"
       *
       * @var string
       */
      const P_ARGS = 'args';

      /**
       * Defines a parameter as "expected outcome"
       *
       * @var string
       */
      const P_OUTCOME = 'outcome';

      /**
       * Defines a parameter as "type"
       *
       * @var string
       */
      const P_TYPE = 'type';

      /**
       * Defines a parameter as "definition"
       *
       * @var string
       */
      const P_DEFINITION = 'definition';

      /**
       * Defines a parameter as "passed"
       *
       * @var string
       */
      const P_PASSED = 'passed';

      /**
       * Defines the test type as "output test"
       *
       * @var string
       */
      const T_OUTPUT = 'output';

      /**
       * Defines the test type as "return test"
       *
       * @var string
       */
      const T_RETURN = 'return';

      /**
       * Defines an outcome as "method/function not callable"
       *
       * @var string
       */
      const O_NOT_CALLABLE = 'Not callable';

      /**
       * Defines a parameter as "start of test"
       *
       * @var string
       */
      const P_TEST_START = 'start';

      /**
       * Defines a parameter as "end of test"
       *
       * @var string
       */
      const P_TEST_END = 'end';

      /**
       * Defines a parameter as "test runtime"
       *
       * @var string
       */
      const P_TEST_RUNTIME = 'runtime';

      /**
       * Instantiates the tester
       *
       * @author Art <a.molcanovas@gmail.com>
       */
      function __construct() {
         $this->queue = $this->results = [];
      }

      /**
       * Runs the tests
       *
       * @author Art <a.molcanovas@gmail.com>
       * @return array Test results
       */
      function runTests() {
         foreach ($this->queue as $test) {
            $this->runTest($test);
         }

         return $this->results;
      }

      /**
       * Runs an individual test
       *
       * @author Art <a.molcanovas@gmail.com>
       * @param array $test The test specs
       * @return AbstractTester
       */
      protected function runTest(array $test) {
         $callable = $this->getCallable($test[self::P_DEFINITION][self::P_NAME]);

         $add = [
            self::P_DEFINITION => $test[self::P_DEFINITION],
            self::P_TEST_START => microtime(true)
         ];

         if (!is_callable($callable)) {
            $add[self::P_PASSED] = false;
            $add[self::P_OUTCOME] = self::O_NOT_CALLABLE;
            $add[self::P_TEST_END] = microtime(true);
         } else {
            ob_start();
            $call = call_user_func_array($callable, $test[self::P_DEFINITION][self::P_ARGS]);
            $ob = ob_get_clean();
            $add[self::P_TEST_END] = microtime(true);

            $add[self::P_OUTCOME] = $test[self::P_TYPE] == self::T_OUTPUT ? $ob : $call;
            $add[self::P_PASSED] = $add[self::P_OUTCOME] == $test[self::P_DEFINITION][self::P_OUTCOME];
         }

         $add[self::P_TEST_RUNTIME] = $add[self::P_TEST_END] - $add[self::P_TEST_START];
         $this->results[] = $add;

         return $this;
      }

      /**
       * Returns the callable parameter for call_user_func_array()
       *
       * @author Art <a.molcanovas@gmail.com>
       * @param string $name Function/method name
       * @return array|string
       */
      abstract protected function getCallable($name);

      /**
       * Adds a test for the output
       *
       * @author Art <a.molcanovas@gmail.com>
       * @param string $type    The type of the test - see this class' T_* constants
       * @param string $name    The method/function name
       * @param mixed  $outcome The expected outcome
       * @param array  $args    The arguments to pass on to the function/method
       * @throws TE When the method/function name is invalid
       * @return AbstractTester
       */
      protected function addGenericTest($type, $name, $outcome, array $args = []) {
         if (!is_string($name)) {
            throw new TE('The name must be a string!', TE::E_NAME_INVALID);
         } else {
            $this->queue[] = [
               self::P_TYPE       => $type,
               self::P_DEFINITION => [
                  self::P_NAME    => $name,
                  self::P_ARGS    => $args,
                  self::P_OUTCOME => $outcome
               ]
            ];
         }

         return $this;
      }

      /**
       * Adds a test for the output
       *
       * @author Art <a.molcanovas@gmail.com>
       * @param string $name    The method/function name
       * @param mixed  $outcome The expected outcome
       * @param array  $args    The arguments to pass on to the function/method
       * @return AbstractTester
       */
      function addOutputTest($name, $outcome, array $args = []) {
         return $this->addGenericTest(self::T_OUTPUT, $name, $outcome, $args);
      }

      /**
       * Adds a test for the return value
       *
       * @author Art <a.molcanovas@gmail.com>
       * @param string $name    The method/function name
       * @param mixed  $outcome The expected outcome
       * @param array  $args    The arguments to pass on to the function/method
       * @return AbstractTester
       */
      function addReturnTest($name, $outcome, array $args = []) {
         return $this->addGenericTest(self::T_RETURN, $name, $outcome, $args);
      }

      /**
       * Returns the common queue
       *
       * @return array
       */
      function getQueue() {
         return $this->queue;
      }

      /**
       * Returns the testing results
       *
       * @author Art <a.molcanovas@gmail.com>
       * @return array
       */
      function getResults() {
         return $this->results;
      }

      /**
       * Returns the test results as plaintext
       *
       * @author Art <a.molcanovas@gmail.com>
       * @return string
       */
      function toPlaintextString() {
         $ret = "";

         foreach ($this->results as $r) {
            $ret .= $r[self::P_DEFINITION][self::P_NAME] . "\n"
               . "\tArgs:\t\t\t" . F::scalarOutput(array_shift($r[self::P_DEFINITION][self::P_ARGS])) . "\n";

            foreach ($r[self::P_DEFINITION][self::P_ARGS] as $arg) {
               $ret .= "\t\t\t\t" . F::scalarOutput($arg) . "\n";
            }

            $ret .= "\tExpected outcome:\t" . F::scalarOutput($r[self::P_DEFINITION][self::P_OUTCOME]) . "\n"
               . "\tOutcome:\t\t" . F::scalarOutput($r[self::P_OUTCOME]) . "\n"
               . "\tResult:\t\t\t" . ($r[self::P_PASSED] ? 'PASSED' : 'FAILED') . "\n"
               . "\tStart time:\t\t" . \timestamp_precise($r[self::P_TEST_START]) . "\n"
               . "\tEnd time:\t\t" . \timestamp_precise($r[self::P_TEST_END]) . "\n"
               . "\tRuntime:\t\t" . (($r[self::P_TEST_END] - $r[self::P_TEST_START]) * 1000) . "ms\n\n";

         }

         return $ret;
      }

      /**
       * Returns the test results in an HTML table
       *
       * @author Art <a.molcanovas@gmail.com>
       * @return string HTML code
       */
      function __toString() {
         $ret = '<table border="1" style="background:#000;color:#fff" cellpadding="2">'
            . '<thead>'
            . '<tr>'
            . '<th>Tested item</th>'
            . '<th>Item args</th>'
            . '<th>Expected outcome</th>'
            . '<th>Outcome</th>'
            . '<th>Result</th>'
            . '<th>Start time</th>'
            . '<th>End time</th>'
            . '<th>Runtime</th>'
            . '</tr>'
            . '</thead>'
            . '<tbody>';

         foreach ($this->results as $r) {
            foreach ($r[self::P_DEFINITION][self::P_ARGS] as &$a) {
               $a = '<span style="color:gold">' . F::scalarOutput($a) . '</span>';
            }

            $ret .= '<tr>'
               . '<td>' . $r[self::P_DEFINITION][self::P_NAME] . '</td>'
               . '<td><ol style="margin:0;"><li>' . implode('</li><li>', $r[self::P_DEFINITION][self::P_ARGS]) . '</li></ol></td>'
               . '<td><pre>' . F::scalarOutput($r[self::P_DEFINITION][self::P_OUTCOME]) . '</pre></td>'
               . '<td><pre>' . F::scalarOutput($r[self::P_OUTCOME]) . '</pre></td>'
               . '<td style="color:';

            if ($r[self::P_PASSED]) {
               $ret .= 'lime">PASSED';
            } else {
               $ret .= 'red">FAILED';
            }

            $ret .= '</td>'
               . '<td>' . \timestamp_precise($r[self::P_TEST_START]) . '</td>'
               . '<td>' . \timestamp_precise($r[self::P_TEST_END]) . '</td>'
               . '<td>' . (($r[self::P_TEST_END] - $r[self::P_TEST_START]) * 1000) . 'ms</td>'
               . '</tr>';
         }

         return $ret . '</tbody></table>';
      }
   }