<?php declare(strict_types=1);

namespace Codeception;

use Closure;
use Codeception\Specify\SpecifyBoostrap;
use PHPUnit\Framework\TestCase;

trait Specify
{
    use SpecifyBoostrap {
        afterSpecify as public;
        beforeSpecify as public;
        cleanSpecify as public;
        getCurrentSpecifyTest as public;
    }

    public function specify(string $thing, Closure $code = null, $examples = []): ?self
    {
        if ($code instanceof Closure) {
            $this->runSpec($thing, $code, $examples);
            return null;
        }
        TestCase::markTestIncomplete();
        return $this;
    }

    public function describe(string $feature, Closure $code = null): ?self
    {
        if ($code instanceof Closure) {
            $this->runSpec($feature, $code);
            return null;
        }
        return $this;
    }

    public function it(string $specification, Closure $code = null, $examples = []): self
    {
        if ($code instanceof Closure) {
            $this->runSpec($specification, $code, $examples);
        }
        return $this;
    }

    public function its(string $specification, Closure $code = null, $examples = []): self
    {
        return $this->it($specification, $code, $examples);
    }

    public function should(string $behavior, Closure $code = null, $examples = []): self
    {
        if ($code instanceof Closure) {
            $this->runSpec('should ' . $behavior, $code, $examples);
        }
        return $this;
    }

    public function shouldNot(string $behavior, Closure $code = null, $examples = []): self
    {
        if ($code instanceof Closure) {
            $this->runSpec('should not ' . $behavior, $code, $examples);
        }
        return $this;
    }
}
