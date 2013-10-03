Specify
=======

BDD style code blocks for PHPUnit / Codeception

Specify allows you to write your tests in more readable BDD style, the same way you might have experienced with [Jasmine](http://pivotal.github.io/jasmine/).
Inspired by MiniTest of Ruby now you combine BDD and classical TDD style in one test.

[![Build Status](https://travis-ci.org/Codeception/Specify.png?branch=master)](https://travis-ci.org/Codeception/Specify) [![Latest Stable Version](https://poser.pugx.org/codeception/specify/v/stable.png)](https://packagist.org/packages/codeception/specify)

Additionaly, we recommend to combine this with [**Codeception/Verify**](https://github.com/Codeception/Verify) library, to get BDD style assertions.

``` php
<?
class UserTest extends PHPUnit_Framework_TestCase {

	use Codeception\Specify;

	public function setUp()
	{		
		$this->user = new User;
	}

	public function testValidation()
	{
		$this->assertInstanceOf('Model', $this->user);

		$this->specify("username is required", function() {
			$this->user->username = null;
			verify($user->validate(['username'])->false());	
		});

		$this->specify("username is too long", function() {
			$user->username = 'toolooooongnaaaaaaameeee',
			verify($user->validate(['username'])->false());			
		});

		// alternative, TDD assertions can be used too.
		$this->specify("username is ok", function() {
			$user->username = 'davert',
			$this->assertTrue($user->validate(['username']));			
		});				
	}
}
?>
```

## Purpose

This tiny library makes your tests a bit readable, by orginizing test in well described code blocks.
Each code block is isolated. 

This means call to `$this->specify` does not affect any instance variable of a test class.

``` php
<?php
$this->user->name = 'davert';
$this->specify("i can change my name", function() {
   $this->user->name = 'jon';
   $this->assertEquals('jon', $this->user->name);
});
       
$this->assertEquals('davert', $this->user->name);
?>        
```


Failure in `specify` block won't get your test stopped.

``` php
<?php
$this->specify("failing but test goes on", function() {
	$this->fail('bye');
});
$this->assertTrue(true);

// Assertions: 2, Failures: 1
?>
```

If a test fails you will specification text in the result.

## Exceptions

You can wait for exception thronw inside a block.

``` php
<?php

$this->specify('404 if user does not exist', function() {
	$this->userController->show(999);
}, ['throws' => 'NotFoundException']);

// alternatively
$this->specify('404 if user does not exist', function() {
	$this->userController->show(999);
}, ['throws' => new NotFoundException]);
?>
```

Also you can handle fails inside a block. 

``` php
<?php

$this->specify('this assertion is failing', function() {
	$this->assertEquals(2, 3+5);
}, ['throws' => 'fail']);
?>
```

## Examples

DataProviders alternative. Quite useful for basic data providers.

``` php
<?php
$this->specify("should calculate square numbers", function($number, $square) {
	$this->assertEquals($square, $number*$number);
}, ['examples' => [
		[2,4],
		[3,9]
]]);
?>
```

You can also use DataProvider functions in `examples` param.

``` php
<?php
$this->specify("should calculate square numbers", function($number, $square) {
	$this->assertEquals($square, $number*$number);
}, ['examples' => $this->provider()]);
?>
```

## Before/After

There are also before and after callbacks, which act as setUp/tearDown but only for specify.

``` php
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

## Installation

*Requires PHP >= 5.4.*

Install with Composer:


```
"require-dev": {
    "codeception/specify": "*",
    "codeception/verify": "*"

}
```
Include `Codeception\Specifiy` trait into your test.

License: MIT