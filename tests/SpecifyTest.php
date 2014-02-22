<?php
require_once __DIR__.'/../vendor/autoload.php';

class SpecifyTest extends \PHPUnit_Framework_TestCase {

    use Codeception\Specify;

    protected $user;
    protected $a;

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

    public function testExceptions()
    {
        $this->specify('user is invalid', function() {
            throw new Exception;
        }, ['throws' => 'Exception']);

        $this->specify('user is invalid', function() {
            throw new RuntimeException;
        }, ['throws' => 'RuntimeException']);

        $this->specify('user is invalid', function() {
            throw new RuntimeException;
        }, ['throws' => new RuntimeException()]);

        $this->specify('i can handle fails', function() {
            $this->fail("Ok, I'm failing");
        }, ['throws' => 'fail']);
    }

    /**
     * @expectedException RuntimeException
     */
    public function testFailWhenUnexpectedExceptionHappens()
    {
        $this->specify('i bubble exception up if no throws is defined', function() {
            throw new RuntimeException;
        });
    }

    public function testExamples()
    {
        $this->specify('specify may contain examples', function($a, $b) {
            $this->assertEquals($b, $a*$a);
        }, ['examples' => [
            ['2', '4'],
            ['3', '9']
        ]]);
    }

    function testOnlySpecifications()
    {
        $this->specify('should be valid');
    }

    public function testDeepCopy()
    {
        $this->a = new TestOne();
        $this->a->prop = new TestOne();
        $this->a->prop->prop = 1;
        $this->specify('nested object can be changed', function() {
            $this->assertEquals(1, $this->a->prop->prop);
            $this->a->prop->prop = 2;
            $this->assertEquals(2, $this->a->prop->prop);
        });
        $this->assertEquals(1, $this->a->prop->prop);
        
    }

}

class TestOne
{
    public $prop;
}