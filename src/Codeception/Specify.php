<?php
namespace Codeception;

trait Specify {

    protected $__beforeSpecify;
    protected $__afterSpecify;

	function specify($specification, \Closure $callable)
	{
        $properties = get_object_vars($this);

        // cloning object properties
        foreach ($properties as $property => $val) {
            if ($property == '__beforeSpecify') continue;
            if ($property == '__afterSpecify') continue;
            if (is_object($val)) {
                $this->$property = clone($val);
            }
        }
        $test = $callable->bindTo($this);
        $name = $this->getName();
        $this->setName($this->getName().'/ '.$specification);
        $result = $this->getTestResultObject();
        $result->stopOnFailure(false);
        try {
            if ($this->__beforeSpecify instanceof \Closure) $this->__beforeSpecify->__invoke();
            $test();
        } catch (\PHPUnit_Framework_AssertionFailedError $f) {
            $result->addFailure(clone($this), $f, $result->time());
        }
        $result->stopOnFailure(true);

        // restoring object properties
        foreach ($properties as $property => $val) {
            $this->$property = $val;
        }

        if ($this->__afterSpecify instanceof \Closure) $this->__afterSpecify->__invoke();        
        $this->setName($name);
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
