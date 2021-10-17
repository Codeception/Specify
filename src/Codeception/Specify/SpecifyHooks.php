<?php

declare(strict_types=1);

namespace Codeception\Specify;

use Closure;
use DeepCopy\DeepCopy;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionException;
use ReflectionObject;
use ReflectionProperty;
use RuntimeException;

trait SpecifyHooks
{
    private array $afterSpecify = [];

    private array $beforeSpecify = [];

    private ?DeepCopy $copier = null;

    private ?SpecifyTest $currentSpecifyTest = null;

    private string $specifyName = '';

    private function getCurrentSpecifyTest(): SpecifyTest
    {
        return $this->currentSpecifyTest;
    }

    /**
     * @param string $specification
     * @param Closure|null $callable
     * @param callable|array $params
     */
    private function runSpec(string $specification, Closure $callable = null, $params = [])
    {
        if ($callable === null) {
            return;
        }

        if (!$this->copier) {
            $this->copier = new DeepCopy();
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
                    if ($closure instanceof Closure) $closure->__invoke();
                }
            }

            $test->run($this->getTestResultObject());
            $this->specifyCheckMockObjects();

            // restore object properties
            $this->specifyRestoreProperties($properties);

            if (!empty($this->afterSpecify) && is_array($this->afterSpecify)) {
                foreach ($this->afterSpecify as $closure) {
                    if ($closure instanceof Closure) $closure->__invoke();
                }
            }
        }

        // revert specify name
        $this->specifyName = $specifyName;
    }

    /**
     * @param $params
     * @return array
     */
    private function getSpecifyExamples($params): array
    {
        if (isset($params['examples'])) {
            if (!is_array($params['examples'])) {
                throw new RuntimeException("Examples should be an array");
            }

            return $params['examples'];
        }

        return [[]];
    }

    private function specifyGetPhpUnitReflection(): ?ReflectionClass
    {
        if ($this instanceof TestCase) {
            return new ReflectionClass(TestCase::class);
        }

        return null;
    }

    private function specifyCheckMockObjects()
    {
        if (($phpUnitReflection = $this->specifyGetPhpUnitReflection()) !== null) {
            try {
                $verifyMockObjects = $phpUnitReflection->getMethod('verifyMockObjects');
                $verifyMockObjects->setAccessible(true);
                $verifyMockObjects->invoke($this);
            } catch (ReflectionException $e) {
            }
        }
    }

    /**
     * @param ObjectProperty[] $properties
     */
    private function specifyRestoreProperties(array $properties)
    {
        foreach ($properties as $property) {
            $property->restoreValue();
        }
    }

    /**
     * @return ObjectProperty[]
     */
    private function getSpecifyObjectProperties(): array
    {
        $objectReflection = new ReflectionObject($this);
        $properties = $objectReflection->getProperties();

        if (($classProperties = $this->specifyGetClassPrivateProperties()) !== []) {
            $properties = array_merge($properties, $classProperties);
        }

        $clonedProperties = [];

        foreach ($properties as $property) {
            /** @var ReflectionProperty $property  **/
            $docBlock = $property->getDocComment();
            if (!$docBlock) {
                continue;
            }

            if (preg_match('#\*(\s+)?@specify\s?#', $docBlock)) {
                $property->setAccessible(true);
                $clonedProperties[] = new ObjectProperty($this, $property);
            }
        }

        // isolate mockObjects property from PHPUnit\Framework\TestCase
        if ($classReflection = $this->specifyGetPhpUnitReflection()) {
            try {
                $property = $classReflection->getProperty('mockObjects');
                // remove all mock objects inherited from parent scope(s)
                $clonedProperties[] = new ObjectProperty($this, $property);
                $property->setValue($this, []);
            } catch (ReflectionException $e) {
            }
        }

        return $clonedProperties;
    }

    private function specifyGetClassPrivateProperties(): array
    {
        static $properties = [];

        if (!isset($properties[__CLASS__])) {
            $reflection = new ReflectionClass(__CLASS__);

            $properties[__CLASS__] = (get_class($this) !== __CLASS__)
                ? $reflection->getProperties(ReflectionProperty::IS_PRIVATE) : [];
        }

        return $properties[__CLASS__];
    }

    /**
     * @param ObjectProperty[] $properties
     */
    private function specifyCloneProperties(array $properties)
    {
        foreach ($properties as $property) {
            $propertyValue = $property->getValue();
            $property->setValue($this->copier->copy($propertyValue));
        }
    }

    private function beforeSpecify(Closure $callable = null)
    {
        $this->beforeSpecify[] = $callable->bindTo($this);
    }

    private function afterSpecify(Closure $callable = null)
    {
        $this->afterSpecify[] = $callable->bindTo($this);
    }

    private function cleanSpecify()
    {
        $this->afterSpecify = [];
        $this->beforeSpecify = [];
    }
}