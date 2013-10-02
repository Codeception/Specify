<?php
namespace Codeception;

trait Specify {

    protected $__beforeSpecify;
    protected $__afterSpecify;

	function specify($specification, \Closure $callable, $params = [])
	{
        // config
        $test = $callable->bindTo($this);
        $name = $this->getName();
        $this->setName($this->getName().' | '.$specification);
        $this->getTestResultObject()->stopOnFailure(false);

        // copy current object properties
        $properties = get_object_vars($this);
        foreach ($properties as $property => $val) {
            if ($property == '__beforeSpecify') continue;
            if ($property == '__afterSpecify') continue;
            if ($property == '__savedProperties') continue;
            if (is_object($val)) {
                $this->$property = clone($val);
            }
        }

        // prepare for execution
        $throws = $this->getSpecifyExpectedException($params);
        $examples = $this->getSpecifyExamples($params);

        foreach ($examples as $example) {
            if ($this->__beforeSpecify instanceof \Closure) $this->__beforeSpecify->__invoke();
            $this->specifyExecute($test, $throws, $example);

            // restore class properties
            foreach ($properties as $property => $val) {
                $this->$property = $val;
            }
            if ($this->__afterSpecify instanceof \Closure) $this->__afterSpecify->__invoke();
        }

        $this->getTestResultObject()->stopOnFailure(true);
        $this->setName($name);
	}

    /**
     * @param $params
     * @return array
     * @throws \RuntimeException
     */
    private function getSpecifyExamples($params)
    {
        if (isset($params['examples'])) {
            if (!is_array($params['examples'])) throw new \RuntimeException("Examples should be array");
            return $params['examples'];
        }
        return [[]];
    }

    private function getSpecifyExpectedException($params)
    {
        $throws = false;
        if (isset($params['throws'])) {
            $throws = $params['throws'];
            if (is_object($throws)) {
                $throws = get_class($throws);
            }
        }
        return $throws;
    }

    private function specifyExecute($test, $throws = false, $examples = array())
    {
        $result = $this->getTestResultObject();
        try {
            call_user_func_array($test, $examples);
        } catch (\PHPUnit_Framework_AssertionFailedError $f) {
            $result->addFailure(clone($this), $f, $result->time());
        } catch (\Exception $e) {
            if ($throws and ($throws !== get_class($e))) {
                $f = new \PHPUnit_Framework_AssertionFailedError("exception '$throws' was expected, but " . get_class($e) . ' was thrown');
                $result->addFailure(clone($this), $f, $result->time());
            }
        }

        if ($throws and !isset($e)) {
            $f = new \PHPUnit_Framework_AssertionFailedError("exception '$throws' was not thrown as expected");
            $result->addFailure(clone($this), $f, $result->time());
        }
    }

    function beforeSpecify(\Closure $callable = null)
    {
        $this->__beforeSpecify = $callable->bindTo($this);
    }

    function afterSpecify(\Closure $callable = null)
    {
        $this->__afterSpecify = $callable->bindTo($this);   
    }

    function cleanSpecify()
    {
        $this->__beforeSpecify = $this->__afterSpecify = null;
    }    


}
