Specify
=======

BDD style code blocks for PHPUnit / Codeception

Specify allows you to write your tests in more readable BDD style, the same way you might have experienced with [Jasmine](http://pivotal.github.io/jasmine/).
Inspired by MiniTest of Ruby now you combine BDD and classical TDD style in one test.

Additionaly, we recommend to combine this with [`codeception/verify`](https://github.com/Codeception/Verify) library, to get BDD style assertions.

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