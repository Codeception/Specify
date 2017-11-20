<?php
namespace Codeception;

use Codeception\Specify\SpecifyTest;
use Codeception\Specify\ObjectProperty;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\TestCase;

trait Specify
{
    private $beforeSpecify = array();
    private $afterSpecify = array();

    /**
     * @var \DeepCopy\DeepCopy()
     */
    private $copier;

    /**
     * @var SpecifyTest
     */
    private $currentSpecifyTest;

    private $specifyName = '';

    /**
     * @return SpecifyTest
     */
    public function getCurrentSpecifyTest()
    {
        return $this->currentSpecifyTest;
    }

    public function should($specification, \Closure $callable = null, $params = [])
    {
        $this->specify("should " . $specification, $callable, $params);
    }

    public function it($specification, \Closure $callable = null, $params = [])
    {
        $this->specify($specification, $callable, $params);
    }

    public function describe($specification, \Closure $callable = null)
    {
        $this->specify($specification, $callable);
    }

    public function specify($specification, \Closure $callable = null, $params = [])
    {
        if (!$callable) {
            return;
        }

        /** @var $this TestCase  **/
        if (!$this->copier) {
            $this->copier = new \DeepCopy\DeepCopy();
            $this->copier->skipUncloneable();
        }

        $properties = $this->getSpecifyObjectProperties();

        // prepare for execution
        $examples = $this->getSpecifyExamples($params);
        $showExamplesIndex = $examples !== [[]];

        $specifyName = $this->specifyName;
        $this->specifyName .= ' ' . $specification;

        foreach ($examples as $idx => $example) {
            $test = new SpecifyTest($callable->bindTo($this));
            $this->currentSpecifyTest = $test;
            $test->setName($this->getName() . ' |' . $this->specifyName);
            $test->setExample($example);
            if ($showExamplesIndex) {
                $test->setName($this->getName() . ' |' . $this->specifyName . ' # example ' . $idx);
            }

            // copy current object properties
            $this->specifyCloneProperties($properties);

            if (!empty($this->beforeSpecify) && is_array($this->beforeSpecify)) {
                foreach ($this->beforeSpecify as $closure) {
                    if ($closure instanceof \Closure) $closure->__invoke();
                }
            }

            $test->run($this->getTestResultObject());
            $this->specifyCheckMockObjects();

            // restore object properties
            $this->specifyRestoreProperties($properties);

            if (!empty($this->afterSpecify) && is_array($this->afterSpecify)) {
                foreach ($this->afterSpecify as $closure) {
                    if ($closure instanceof \Closure) $closure->__invoke();
                }
            }
        }

        // revert specify name
        $this->specifyName = $specifyName;
    }

    /**
     * @param $params
     * @return array
     * @throws \RuntimeException
     */
    private function getSpecifyExamples($params)
    {
        if (isset($params['examples'])) {
            if (!is_array($params['examples'])) throw new \RuntimeException("Examples should be an array");
            return $params['examples'];
        }
        return [[]];
    }

    /**
     * @return \ReflectionClass|null
     */
    private function specifyGetPhpUnitReflection()
    {
        if ($this instanceof \PHPUnit\Framework\TestCase) {
            return new \ReflectionClass(\PHPUnit\Framework\TestCase::class);
        }
    }

    private function specifyCheckMockObjects()
    {
        if (($phpUnitReflection = $this->specifyGetPhpUnitReflection()) !== null) {
            $verifyMockObjects = $phpUnitReflection->getMethod('verifyMockObjects');
            $verifyMockObjects->setAccessible(true);
            $verifyMockObjects->invoke($this);
        }
    }

    function beforeSpecify(\Closure $callable = null)
    {
        $this->beforeSpecify[] = $callable->bindTo($this);
    }

    function afterSpecify(\Closure $callable = null)
    {
        $this->afterSpecify[] = $callable->bindTo($this);
    }

    function cleanSpecify()
    {
        $this->beforeSpecify = $this->afterSpecify = array();
    }

    /**
     * @param ObjectProperty[] $properties
     */
    private function specifyRestoreProperties($properties)
    {
        foreach ($properties as $property) {
            $property->restoreValue();
        }
    }

    /**
     * @return ObjectProperty[]
     */
    private function getSpecifyObjectProperties()
    {
        $objectReflection = new \ReflectionObject($this);
        $properties = $objectReflection->getProperties();

        if (($classProperties = $this->specifyGetClassPrivateProperties()) !== []) {
            $properties = array_merge($properties, $classProperties);
        }

        $clonedProperties = [];

        foreach ($properties as $property) {
            /** @var $property \ReflectionProperty  **/
            $docBlock = $property->getDocComment();
            if (!$docBlock) {
                continue;
            }
            if (preg_match('~\*(\s+)?@specify\s?~', $docBlock)) {
                $property->setAccessible(true);
                $clonedProperties[] = new ObjectProperty($this, $property);
            }
        }

        // isolate mockObjects property from PHPUnit\Framework\TestCase
        if ($classReflection = $this->specifyGetPhpUnitReflection()) {
            $property = $classReflection->getProperty('mockObjects');
            // remove all mock objects inherited from parent scope(s)
            $clonedProperties[] = new ObjectProperty($this, $property);
            $property->setValue($this, []);
        }

        return $clonedProperties;
    }

    private function specifyGetClassPrivateProperties()
    {
        static $properties = [];

        if (!isset($properties[__CLASS__])) {
            $reflection = new \ReflectionClass(__CLASS__);

            $properties[__CLASS__] = (get_class($this) !== __CLASS__)
                ? $reflection->getProperties(\ReflectionProperty::IS_PRIVATE) : [];
        }

        return $properties[__CLASS__];
    }

    /**
     * @param ObjectProperty[] $properties
     */
    private function specifyCloneProperties($properties)
    {
        foreach ($properties as $property) {
            $propertyValue = $property->getValue();
            $property->setValue($this->copier->copy($propertyValue));
        }
    }
}
