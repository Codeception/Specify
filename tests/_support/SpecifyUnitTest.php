<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class SpecifyUnitTest extends TestCase
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
