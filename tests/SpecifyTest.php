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

    function testMultiBeforeCallback()
    {
        $this->beforeSpecify(function() {
            $this->user = "davert";
        });
        $this->beforeSpecify(function() {
            $this->user .= "jon";
        });
        $this->specify("user should be davertjon", function() {
            $this->assertEquals('davertjon', $this->user);
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

    function testMultiAfterCallback()
    {
        $this->afterSpecify(function() {
            $this->user = "davert";
        });
        $this->afterSpecify(function() {
            $this->user .= "jon";
        });
        $this->specify("user should be davertjon", function() {
            $this->user = "jon";
        });
        $this->assertEquals('davertjon', $this->user);
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

    public function testExceptionsWithMessages()
    {
        $this->specify('user is invalid', function() {
            throw new Exception("test message");
        }, ['throws' => ['Exception', 'test message']]);

        $this->specify('user is invalid', function() {
            throw new RuntimeException("test message");
        }, ['throws' => ['RuntimeException', 'test message']]);

        $this->specify('user is invalid', function() {
            throw new RuntimeException("test message");
        }, ['throws' => [new RuntimeException(), "test message"]]);

        $this->specify('i can handle fails', function() {
            $this->fail("test message");
        }, ['throws' => ['fail', 'test message']]);

        $this->specify('ignores an empty message', function() {
            $this->fail("test message");
        }, ['throws' => ['fail']]);

        $this->specify('mixed case exception messages', function() {
            throw new RuntimeException("teSt mESSage");
        }, ['throws' => ['RuntimeException', 'Test MessaGE']]);
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

    public function testConfiguration()
    {
        $this->specifyConfig()
            ->ignore('user');

        $this->specify("user should be jon", function() {
            $this->user = "jon";
        });

        $this->specifyConfig()
            ->ignore(['user']);

        $this->specify("user should be davert", function() {
            $this->user = "davert";
        });

        $this->a = new TestOne();
        $this->a->prop = new TestOne();
        $this->a->prop->prop = 1;

        $this->specifyConfig()
            ->shallowClone('a');

        $this->specify("user should be davert", function() {
            $this->a->prop->prop = "davert";
        });

        $this->assertEquals("davert", $this->a->prop->prop);
    }

    public function testCloneOnly()
    {
        $this->specifyConfig()
            ->cloneOnly('user');

        $this->user = "bob";
        $this->a = "rob";
        $this->specify("user should be jon", function() {
            $this->user = "jon";
            $this->a = 'alice';
        });
        $this->assertEquals('bob', $this->user);
        $this->assertEquals('alice', $this->a);
    }

    /**
     * @Issue https://github.com/Codeception/Specify/issues/6
     */
    function testPropertyRestore()
    {
        $this->testOne = new testOne();
        $this->testOne->prop = ['hello', 'world'];

        $this->specify('array contains hello+world', function ($testData) {
            $this->testOne->prop = ['bye', 'world'];
            $this->assertContains($testData, $this->testOne->prop);
        }, ['examples' => [
            ['bye'],
            ['world'],
        ]]);
        $this->assertEquals(['hello', 'world'], $this->testOne->prop);
    }

//    public function testFail()
//    {
//        $this->specify('this will fail', function(){
//            $this->assertTrue(false);
//        });
//
//        $this->specify('this will fail too', function(){
//            echo "executed";
//            $this->assertTrue(true);
//        }, ['throws' => 'Exception']);
//    }

}

class TestOne
{
    public $prop;
}