<?php

namespace Codeception\Specify;

use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\SelfDescribing;
use PHPUnit\Framework\TestResult;
use SebastianBergmann\Exporter\Exporter;

class SpecifyTest implements \PHPUnit\Framework\Test, SelfDescribing
{
    protected $name;

    protected $test;

    protected $example;

    protected $throws;

    public function __construct($test)
    {
        $this->test = $test;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function toString()
    {
        return $this->name;
    }

    public function getName($withDataSet = true)
    {
        if ($withDataSet && !empty($this->example)) {
            $exporter = new Exporter();
            return $this->name . ' | ' . $exporter->shortenedRecursiveExport($this->example);
        }

        return $this->name;
    }

    /**
     * Count elements of an object
     * @link http://php.net/manual/en/countable.count.php
     * @return int The custom count as an integer.
     * </p>
     * <p>
     * The return value is cast to an integer.
     * @since 5.1.0
     */
    public function count()
    {
        return 1;
    }

    /**
     * Runs a test and collects its result in a TestResult instance.
     *
     * @param TestResult $result
     *
     * @return TestResult
     */
    public function run(TestResult $result = null)
    {
        try {
            call_user_func_array($this->test, $this->example);
        } catch (AssertionFailedError $e) {
            $result->addFailure(clone($this), $e, $result->time());
        }

    }

    /**
     * @param mixed $example
     */
    public function setExample($example)
    {
        $this->example = $example;
    }

    /**
     * @param mixed $throws
     */
    public function setThrows($throws)
    {
        $this->throws = $throws;
    }
}