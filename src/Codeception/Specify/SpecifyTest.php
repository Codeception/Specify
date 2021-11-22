<?php

declare(strict_types=1);

namespace Codeception\Specify;

use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\SelfDescribing;
use PHPUnit\Framework\Test;
use PHPUnit\Framework\TestResult;
use SebastianBergmann\Exporter\Exporter;

class SpecifyTest implements Test, SelfDescribing
{
    protected string $name = '';

    /** @var callable */
    protected $test = null;

    protected array $example = [];

    /**
     * @var mixed|null
     */
    protected $throws;

    public function __construct(callable $test)
    {
        $this->test = $test;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function toString(): string
    {
        return $this->name;
    }

    public function getName($withDataSet = true): string
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
     *
     * The return value is cast to an integer.
     * @since 5.1.0
     */
    public function count(): int
    {
        return 1;
    }

    /**
     * Runs a test and collects its result in a TestResult instance.
     */
    public function run(TestResult $result = null): TestResult
    {
        try {
            call_user_func_array($this->test, $this->example);
        } catch (AssertionFailedError $error) {
            $result->addFailure(clone($this), $error, $result->time());
        }

        return $result;
    }

    public function setExample(array $example): void
    {
        $this->example = $example;
    }

    /**
     * @param mixed $throws
     */
    public function setThrows($throws): void
    {
        $this->throws = $throws;
    }
}