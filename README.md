Specify
=======

BDD style code blocks for [PHPUnit][1] or [Codeception][2]

[![Latest Stable Version](https://poser.pugx.org/codeception/specify/v/stable)](https://packagist.org/packages/codeception/specify)
[![Total Downloads](https://poser.pugx.org/codeception/specify/downloads)](https://packagist.org/packages/codeception/specify)
[![Latest Unstable Version](https://poser.pugx.org/codeception/specify/v/unstable)](https://packagist.org/packages/codeception/specify)
[![License](https://poser.pugx.org/codeception/specify/license)](https://packagist.org/packages/codeception/specify)
[![StandWithUkraine](https://raw.githubusercontent.com/vshymanskyy/StandWithUkraine/main/badges/StandWithUkraine.svg)](https://github.com/vshymanskyy/StandWithUkraine/blob/main/docs/README.md)

Specify allows you to write your tests in more readable BDD style, the same way you might have experienced with [Jasmine][3].
Inspired by MiniTest of Ruby now you combine BDD and classical TDD style in one test.

## Installation

*Requires PHP >= 7.4*

* Install with Composer:

```
composer require codeception/specify --dev
```

* Include `Codeception\Specify` trait in your tests.


## Usage

Specify `$this->specify` method to add isolated test blocks for your PHPUnit tests! 

```php
public function testValidation()
{
    $this->assertInstanceOf('Model', $this->user);

    $this->specify('username is required', function() {
        $this->user->username = null;
        $this->assertFalse($this->user->validate(['username']));
    });

    $this->specify('username is too long', function() {
        $this->user->username = 'toolooooongnaaaaaaameeee';
        $this->assertFalse($this->user->validate(['username']));
    });
}
```

### BDD Example

Specify supports `describe-it` and `describe-should` BDD syntax inside PHPUnit

```php
public function testValidation()
{
    $this->describe('user', function () {
        $this->it('should have a name', function() {
            $this->user->username = null;
            $this->assertFalse($this->user->validate(['username']));
        });
    });

    // you can use chained methods for better readability:
    $this->describe('user')
        ->should('be ok with valid name', function() {
            $this->user->username = 'davert';
            $this->assertTrue($this->user->validate(['username']));
        })
        ->shouldNot('have long name', function() {
            $this->user->username = 'toolooooongnaaaaaaameeee';
            $this->assertFalse($this->user->validate(['username']));
        })
        // empty codeblocks are marked as Incomplete tests
        ->it('should be ok with valid name') 
    ;
}
```


### Specify + Verify Example

Use [Codeception/Verify][4] for simpler assertions:

```php
public function testValidation()
{
    $this->specify('username is too long', function() {
        $this->user->username = 'toolooooongnaaaaaaameeee';
        expect_not($this->user->validate(['username']));
    });

    $this->specify('username is ok', function() {
        $this->user->username = 'davert';
        expect_that($this->user->validate(['username']));
    });
}
```

## Use Case

This tiny library makes your tests readable by organizing them in nested code blocks.
This allows to combine similar tests into one but put them inside nested sections.

This is very similar to BDD syntax as in RSpec or Mocha but works inside PHPUnit:

```php
<?php

class UserTest extends PHPUnit\Framework\TestCase 
{
    use Codeception\Specify;

    /** @specify */
    protected $user; // is cloned inside specify blocks

    public function setUp(): void
    {
        $this->user = new User;
    }

    public function testValidation()
    {
        $this->user->name = 'davert';
        $this->specify('i can change my name', function() {
           $this->user->name = 'jon';
           $this->assertEquals('jon', $this->user->name);
        });
        // user name is 'davert' again
        $this->assertEquals('davert', $this->user->name);
    }
}
```

Each code block is isolated. This means call to `$this->specify` does not change values of properties of a test class.
Isolated properties should be marked with `@specify` annotation.

Failure in `specify` block won't get your test stopped.

```php
<?php
$this->specify('failing but test goes on', function() {
	$this->fail('bye');
});
$this->assertTrue(true);

// Assertions: 2, Failures: 1
?>
```

If a test fails you will see specification text in the result.

## Isolation

Isolation is achieved by **cloning object properties** for each specify block.
Only properties marked with `@specify` annotation are cloned. 

```php
/** @specify */
protected $user; // cloning

/** 
 * @specify 
 **/
protected $user; // cloning

protected $repository; // not cloning
```

Objects are cloned using deep cloning method. 

**If object cloning affects performance, consider turning the clonning off**.

**Mocks are isolated** by default. 

A mock defined inside a specify block won't be executed inside an outer test,
and mock from outer test won't be triggered inside codeblock.

```php
<?php
$config = $this->createMock(Config::class);
$config->expects($this->once())->method('init');

$config->init();
// success: $config->init() was executed

$this->specify('this should not fail', function () {
    $config = $this->createMock(Config::class);
    $config->expects($this->never())->method('init')->willReturn(null);
    // success: $config->init() is never executed 
});
```

## Examples: DataProviders alternative

```php
<?php
$this->specify('should calculate square numbers', function($number, $square) {
	$this->assertEquals($square, $number*$number);
}, ['examples' => [
		[2,4],
		[3,9]
]]);
```

You can also use DataProvider functions in `examples` param.

```php
<?php
$this->specify('should calculate square numbers', function($number, $square) {
	$this->assertEquals($square, $number*$number);
}, ['examples' => $this->provider()]);
```

Can also be used with real data providers:

```php
<?php
/**
 * @dataProvider someData
 */
public function testExamplesAndDataProvider($param)
{
    $this->specify('should assert data provider', function ($example) use ($param) {
        $this->assertGreaterThanOrEqual(5, $param + $example);
    }, ['examples' => [[4], [7], [5]]]);
}

public function someData()
{
    return [[1], [2]];
}
```

## Before/After

There are also before and after callbacks, which act as setUp/tearDown but for specify.

```php
<?php
$this->beforeSpecify(function() {
	// prepare something;	
});
$this->afterSpecify(function() {
	// reset something
});
$this->cleanSpecify(); // removes before/after callbacks
?>
```

## API

Available methods:

```php
// Starts a specify code block:
$this->specify(string $thing, callable $code = null, $examples = [])

// Starts a describe code block. Same as 'specify' but expects chained 'it' or 'should' methods.
$this->describe(string $feature, callable $code = null)

// Starts a code block. If 'code' is null, marks test as incomplete.
$this->it(string $spec, callable $code = null, $examples = [])
$this->its(string $spec, callable $code = null, $examples = [])

// Starts a code block. Same as 'it' but prepends 'should' or 'should not' into description.
$this->should(string $behavior, callable $code = null, $examples = [])
$this->shouldNot(string $behavior, callable $code = null, $examples = [])
```

## Printer Options

For PHPUnit, add `Codeception\Specify\ResultPrinter` printer into `phpunit.xml`

```xml
<phpunit colors="true" printerClass="Codeception\Specify\ResultPrinter">
</phpunit>
```

## Recommended

* Use [Codeception/AssertThrows][5] for exception assertions
* Use [Codeception/DomainAssert][6] for verbose domain logic assertions
* Combine this with [Codeception/Verify][4] library, to get BDD style assertions.

License: [MIT.][7]

[1]: https://phpunit.de/
[2]: https://codeception.com/
[3]: https://jasmine.github.io/
[4]: https://github.com/Codeception/Verify
[5]: https://github.com/Codeception/AssertThrows
[6]: https://github.com/Codeception/DomainAssert
[7]: /LICENSE
