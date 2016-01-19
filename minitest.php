<?php

/**
 * A very small PHP unit testing framework.
 *
 * Released under the GNU LGPL v2.1.
 *
 * Copyright Osprey Design Company, LLC.
 */

require './lib/minilog/minilog.php';

class MiniTestSuite {
  protected $cases = array();
  protected $results = array();

  public function __construct() {
    $this->logger = new Minilog(new StdIODestination());
  }

  public function addCase($description, $test) {
    $this->cases[] = new BasicTestCase($description, $test);
  }

  public function test() {
    foreach ($this->cases as $key => $case) {
      
      // If we've moved beyond the first test, we supply the next
      // test with any result of the last test
      if ($key > 0 and is_array($this->results[$key - 1])) {
        $this->results[$key] = $case->test($this->results[$key - 1]['result']);
      }
      else {
        $this->results[$key] = $case->test();
      }

      // Print the result of each test case
      if ((is_array($this->results[$key]) && $this->results[$key]['passed'] === TRUE) || $this->results[$key] === TRUE) {
        $this->logger->info('PASSED: ' . $case->description);
      }
      else {
        $this->logger->error('FAILED: ' . $case->description);
      }
    }

    // Print summary data
    $this->afterTest();
  }

  protected function afterTest() {
    $total = count($this->results);

    // Add up all the tests that passed
    $passed = 0;
    foreach ($this->results as $result) {
      $passed += $result !== FALSE ? 1 : 0;
    }

    $percentage = number_format(($passed/(float)$total) * 100, 2);
    $this->logger->info($percentage . '% of test cases passed.');
  }
}


/**
 * Abstract test case class.  Does
 * basic housekeeping.
 */
abstract class MiniTestCase {
  public $description = '';
  public $test = null;

  public function __construct($description , $test) {
    $this->description = $description;
    $this->test = $test;
  }

  public function test($result = NULL) {
    return !$result ? $this->test->__invoke() : $this->test->__invoke($result);
  }
}

/**
 * Class to be used for basic tests.
 */
class BasicTestCase extends MiniTestCase {}
