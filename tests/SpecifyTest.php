<?php
require_once __DIR__.'/../src/Codeception/Specify.php';

class SpecifyTest extends \PHPUnit_Framework_TestCase {

    use Codeception\Specify;

    protected $user;

    public function testSpecification()
    {
        $this->user = new stdClass();
        $this->user->name = 'davert';
        $this->specify("i can change my name", function() {
           $this->user->name = 'jon';
           $this->assertEquals('jon', $this->user->name);
        });
               
        $this->assertEquals('davert', $this->user->name);

        $this->specify('i can fail here but test goes on', function() {
            $this->markTestIncomplete();
        });
        $this->assertTrue(true);
    }

    function testBeforeCallback()
    {
        $this->beforeSpecify(function() {
            $this->user = "davert";
        });
        $this->specify("user should be davert", function() {
            $this->assertEquals('davert', $this->user);
        });
    }

    function testAfterCallback()
    {
        $this->afterSpecify(function() {
            $this->user = "davert";
        });
        $this->specify("user should be davert", function() {
            $this->user = "jon";
        });
        $this->assertEquals('davert', $this->user);
    }    

    function testCleanSpecifyCallbacks()
    {
        $this->afterSpecify(function() {
            $this->user = "davert";
        });
        $this->cleanSpecify();
        $this->specify("user should be davert", function() {
            $this->user = "jon";
        });
        $this->assertNull($this->user);


    }

}
