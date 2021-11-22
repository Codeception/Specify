<?php

declare(strict_types=1);

namespace Codeception\Specify;

use ReflectionProperty;

/**
 * Helper for manipulating by an object property.
 *
 * @author Roman Ishchenko <roman@ishchenko.ck.ua>
 */
class ObjectProperty
{
    /**
     * @var mixed
     */
    private $owner;

    /**
     * @var ReflectionProperty|string
     */
    private $property;

    /**
     * @var mixed
     */
    private $initValue;

    /**
     * ObjectProperty constructor.
     *
     * @param $owner
     * @param $property
     * @param $value
     */
    public function __construct($owner, $property, $value = null)
    {
        $this->owner = $owner;
        $this->property = $property;

        if (!($this->property instanceof ReflectionProperty)) {
            $this->property = new ReflectionProperty($owner, $this->property);
        }

        $this->property->setAccessible(true);

        $this->initValue = ($value ?? $this->getValue());
    }

    public function getName(): string
    {
        return $this->property->getName();
    }

    /**
     * Restores initial value
     */
    public function restoreValue(): void
    {
        $this->setValue($this->initValue);
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->property->getValue($this->owner);
    }

    /**
     * @param mixed $value
     */
    public function setValue($value): void
    {
        $this->property->setValue($this->owner, $value);
    }
}
