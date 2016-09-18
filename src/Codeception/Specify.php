<?php
namespace Codeception;

use Codeception\Specify\Config;
use Codeception\Specify\ConfigBuilder;

trait Specify {

    private $beforeSpecify = array();
    private $afterSpecify = array();

    /**
     * @var Specify\Config
     */
    private $specifyConfig;

    /**
     * @var \DeepCopy\DeepCopy()
     */
    private $copier;

    private function specifyInit()
    {
        if ($this->copier) return;
        $this->copier = new \DeepCopy\DeepCopy();
        $this->copier->skipUncloneable();
        if (!$this->specifyConfig) $this->specifyConfig = Config::create();
    }

	function specify($specification, \Closure $callable = null, $params = [])
    {
        if (!$callable) return;
        $this->specifyInit();

        $test = $callable->bindTo($this);
        $oldName = $this->getName();
        $newName = $oldName . ' | ' . $specification;

        $this->setName($newName);

        $properties = get_object_vars($this);

        // prepare for execution
        $throws = $this->getSpecifyExpectedException($params);
        $examples = $this->getSpecifyExamples($params);
        $showExamplesIndex = $examples !== [[]];

        foreach ($examples as $idx => $example) {
            if ($showExamplesIndex) {
                $this->setName($newName . ' | examples index ' . $idx);
            }

            // copy current object properties
            $this->specifyCloneProperties($properties);

            if (!empty($this->beforeSpecify) && is_array($this->beforeSpecify)) {
                foreach ($this->beforeSpecify as $closure) {
                    if ($closure instanceof \Closure) $closure->__invoke();
                }
            }
            $this->specifyExecute($test, $throws, $example);

            // restore object properties
            foreach ($properties as $property => $val) {
                if ($this->specifyConfig->propertyIgnored($property)) continue;
                $this->$property = $val;
            }
            if (!empty($this->afterSpecify) && is_array($this->afterSpecify)) {
                foreach ($this->afterSpecify as $closure) {
                    if ($closure instanceof \Closure) $closure->__invoke();
                }
            }
        }

        // restore test name
        $this->setName($oldName);
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

    private function getSpecifyExpectedException($params)
    {
        if (isset($params['throws'])) {
            $throws = (is_array($params['throws'])) ? $params['throws'][0] : $params['throws'];

            if (is_object($throws)) {
                $throws = get_class($throws);
            }
            if ($throws === 'fail') {
                $throws = 'PHPUnit_Framework_AssertionFailedError';
            }

            $message = (is_array($params['throws']) && isset($params['throws'][1])) ? $params['throws'][1] : false;

            return [$throws, $message];
        }

        return false;
    }

    private function specifyExecute($test, $throws = false, $examples = array())
    {
        $message = false;

        if (is_array($throws)) {
            $message = ($throws[1]) ? strtolower($throws[1]) : false;
            $throws = $throws[0];
        }

        $result = $this->getTestResultObject();
        try {
            call_user_func_array($test, $examples);
        } catch (\PHPUnit_Framework_AssertionFailedError $e) {
            if ($throws !== get_class($e)){
                $result->addFailure(clone($this), $e, $result->time());
            }

            if ($message !==false && $message !== strtolower($e->getMessage())) {
                $f = new \PHPUnit_Framework_AssertionFailedError("exception message '$message' was expected, but '" . $e->getMessage() . "' was received");
                $result->addFailure(clone($this), $f, $result->time());
            }
        } catch (\Exception $e) {
            if ($throws) {
                if ($throws !== get_class($e)) {
                    $f = new \PHPUnit_Framework_AssertionFailedError("exception '$throws' was expected, but " . get_class($e) . ' was thrown');
                    $result->addFailure(clone($this), $f, $result->time());
                }

                if ($message !==false && $message !== strtolower($e->getMessage())) {
                    $f = new \PHPUnit_Framework_AssertionFailedError("exception message '$message' was expected, but '" . $e->getMessage() . "' was received");
                    $result->addFailure(clone($this), $f, $result->time());
                }
            } else {
                throw $e;
            }
        }

        if ($throws) {
            if (isset($e)) {
                $this->assertTrue(true, 'exception handled');
            } else {
                $f = new \PHPUnit_Framework_AssertionFailedError("exception '$throws' was not thrown as expected");
                $result->addFailure(clone($this), $f, $result->time());
            }
        }
    }

    public function specifyConfig()
    {
        if (!$this->specifyConfig) $this->specifyConfig = Config::create();
        return new ConfigBuilder($this->specifyConfig);
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
     * @param $properties
     * @return array
     */
    private function specifyCloneProperties($properties)
    {
        foreach ($properties as $property => $val) {
            if ($this->specifyConfig->propertyIgnored($property)) {
                continue;
            }
            if ($this->specifyConfig->classIgnored($val)) {
                continue;
            }

            if ($this->specifyConfig->propertyIsShallowCloned($property)) {
                if (is_object($val)) {
                    $this->$property = clone $val;
                } else {
                    $this->$property = $val;
                }
            }
            if ($this->specifyConfig->propertyIsDeeplyCloned($property)) {
                $this->$property = $this->copier->copy($val);
            }
        }
    }
}
