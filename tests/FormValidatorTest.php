<?php

   use Alo\Validators\Form as F;

   class FormValidatorTest extends \PHPUnit_Framework_TestCase {

      /**
       * @dataProvider trueProvider
       */
      function testTrue($validationCriteria, $validatedValue, $exptOutcome = true) {
         $f = new F([
                       'foo' => $validatedValue
                    ]);

         $f->bind('foo',
                  [
                     $validationCriteria => $exptOutcome
                  ]);

         $f->evaluate();

         $eval = $f->getEvaluation();

         $this->assertTrue($eval['OK'],
                           _unit_dump([
                                         '$validatedValue'     => $validatedValue,
                                         '$validationCriteria' => $validationCriteria,
                                         '$exptOutcome'        => $exptOutcome,
                                         '$eval'               => $eval
                                      ]));
         ob_flush();
      }

      /**
       * @dataProvider falseProvider
       */
      function testFalse($validationCriteria, $validatedValue, $exptOutcome = true) {
         $f = new F([
                       'foo' => $validatedValue
                    ]);

         $f->bind('foo',
                  [
                     $validationCriteria => $exptOutcome
                  ]);

         $f->evaluate();

         $eval = $f->getEvaluation();

         $this->assertFalse($eval['OK'],
                            _unit_dump([
                                          '$validatedValue'     => $validatedValue,
                                          '$validationCriteria' => $validationCriteria,
                                          '$exptOutcome'        => $exptOutcome,
                                          '$eval'               => $eval
                                       ]));
         ob_flush();
      }

      function trueProvider() {
         return [
            [F::R_EMAIL, 'art@some-mail.com'],
            [F::R_EMAIL, 'art@foo.bar'],
            [F::R_EMAIL, 'a.rt@foo.xx'],
            [F::R_EMAIL, 'a.-_@foo.info'],
            [F::R_REQUIRED, 'a'],
            [F::R_REQUIRED, 1],
            [F::R_NUMERIC, 124],
            [F::R_NUMERIC, '124'],
            [F::R_NUMERIC, 12.4],
            [F::R_NUMERIC, '12.4'],
            [F::R_LENGTH_MIN, 'foo', 3],
            [F::R_LENGTH_MIN, 'foo', 2],
            [F::R_LENGTH_MIN, 'foo', 0],
            [F::R_LENGTH_MIN, 'foo', -2],
            [F::R_LENGTH_MIN, 'foo', -5],
            [F::R_LENGTH_MAX, 'foo', 5],
            [F::R_LENGTH_MAX, 'foo', 3],
            [F::R_REGEX, 'a5p', '/^[a-z]+[0-9]{1}[m-x]{1,5}B*/'],
            [F::R_CONTAIN_UPPERCASE, 'agdgag(&()!#51A'],
            [F::R_CONTAIN_UPPERCASE, 'A'],
            [F::R_CONTAIN_UPPERCASE, 'Agagg*(!'],
            [F::R_CONTAIN_LOWERCASE, 'HJBSAD(&()!#51a'],
            [F::R_CONTAIN_LOWERCASE, 'a'],
            [F::R_CONTAIN_LOWERCASE, 'aUJHBN*(!'],
            [F::R_CONTAIN_NUMBER, 45],
            [F::R_CONTAIN_NUMBER, '45'],
            [F::R_CONTAIN_NUMBER, '45.2'],
            [F::R_CONTAIN_NUMBER, 485.3],
            [F::R_CONTAIN_NUMBER, 'a4'],
            [F::R_CONTAIN_NUMBER, '4a'],
            [F::R_CONTAIN_NUMBER, 'a4a'],
            [F::R_CONTAIN_NONALPHANUM, '!'],
            [F::R_CONTAIN_NONALPHANUM, 'a!'],
            [F::R_CONTAIN_NONALPHANUM, '!a'],
            [F::R_CONTAIN_NONALPHANUM, 'a!a'],
            [F::R_VAL_LT, '5', '6'],
            [F::R_VAL_LT, 5, '6'],
            [F::R_VAL_LT, '5', 6],
            [F::R_VAL_LT, 5.99, 6],
            [F::R_VAL_GT, '6', '5'],
            [F::R_VAL_GT, 6, '5'],
            [F::R_VAL_GT, '6', 5],
            [F::R_VAL_GT, 5.99, 5.98],
            [F::R_VAL_RANGE, 2, [1, 2, 3]],
            [F::R_VAL_RANGE, '2', [1, 2, 3]],
            [F::R_VAL_LTE, '5', '6'],
            [F::R_VAL_LTE, 5, '6'],
            [F::R_VAL_LTE, '5', 6],
            [F::R_VAL_LTE, 6, 6],
            [F::R_VAL_LTE, 5.99, 6],
            [F::R_VAL_GTE, '6', '5'],
            [F::R_VAL_GTE, 6, '5'],
            [F::R_VAL_GTE, '6', 5],
            [F::R_VAL_GTE, 5.99, 5.98],
            [F::R_VAL_GTE, 6, 6]
         ];
      }

      function falseProvider() {
         return [
            [F::R_EMAIL, 'fuck@off'],
            [F::R_EMAIL, 'nope'],
            [F::R_EMAIL, '1'],
            [F::R_EMAIL, ''],
            [F::R_REQUIRED, ''],
            [F::R_REQUIRED, 0],
            [F::R_REQUIRED, []],
            [F::R_NUMERIC, []],
            [F::R_NUMERIC, new stdClass()],
            [F::R_NUMERIC, 'a'],
            [F::R_NUMERIC, 'a2'],
            [F::R_LENGTH_MIN, 'foo', 4],
            [F::R_LENGTH_MAX, 'foo', 2],
            [F::R_REGEX, 'a5p(', '/^[a-z]+[0-9]{1}[m-x]{1,5}B*$/'],
            [F::R_CONTAIN_UPPERCASE, 'agdgag(&()!#515'],
            [F::R_CONTAIN_LOWERCASE, 'HJBSAD(&()!#51'],
            [F::R_CONTAIN_NUMBER, 'nope'],
            [F::R_CONTAIN_NUMBER, '!nope'],
            [F::R_CONTAIN_NUMBER, []],
            [F::R_CONTAIN_NUMBER, new stdClass()],
            [F::R_CONTAIN_NUMBER, [1]],
            [F::R_CONTAIN_NONALPHANUM, 'a'],
            [F::R_CONTAIN_NONALPHANUM, '5'],
            [F::R_CONTAIN_NONALPHANUM, 'a5'],
            [F::R_VAL_LT, '6', '6'],
            [F::R_VAL_LT, 6, '6'],
            [F::R_VAL_LT, '6', 6],
            [F::R_VAL_LT, 6.01, 6],
            [F::R_VAL_GT, '6', '6'],
            [F::R_VAL_GT, 6, '6'],
            [F::R_VAL_GT, '6', 6],
            [F::R_VAL_GT, 5.97, 5.98],
            [F::R_VAL_RANGE, 2, [1, 4, 3]],
            [F::R_VAL_RANGE, '2', [1, 4, 3]],
            [F::R_VAL_LTE, '6.01', '6'],
            [F::R_VAL_LTE, 7, '6'],
            [F::R_VAL_LTE, '7', 6],
            [F::R_VAL_GTE, '4', '5'],
            [F::R_VAL_GTE, 4, '5'],
            [F::R_VAL_GTE, '4', 5]
         ];
      }
   }
