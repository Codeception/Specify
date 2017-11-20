<?php

class SpecifyUnitTest extends \PHPUnit\Framework\TestCase
{
    use Codeception\Specify;

    /**
     * @specify
     */
    private $private = true;

    /**
     * @param mixed $private
     */
    protected function setPrivateProperty($private)
    {
        $this->private = $private;
    }
    /**
     * @return mixed
     */
    protected function getPrivateProperty()
    {
        return $this->private;
    }
}
