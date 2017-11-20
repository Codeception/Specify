<?php

class SpecifyTest extends \SpecifyUnitTest
{
    /**
     * @specify
     */
    protected $user;

    /**
     * @specify
     */
    protected $a;

    /**
     * @specify
     */
    private $private = false;

    /**
     * not cloned
     */
    protected $b;

    public function testUserCanChangeName()
    {
        $this->user = new User();
        $this->user->name = 'davert';
        $this->specify("i can change my name", function() {
           $this->user->name = 'jon';
           $this->assertEquals('jon', $this->user->name);
        });

        $this->assertEquals('davert', $this->user->name);

        try {
            $this->specify('i can fail here but test goes on', function() {
                $this->markTestIncomplete();
            });
        } catch (\PHPUnit\Framework\IncompleteTestError $e) {
            $this->fail("should not be thrown");
        }
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
        $this->assertTrue(true);
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

    public function testDeepRevert()
    {
        $this->specify("user should be jon", function() {
            $this->user = "jon";
        });

        $this->specify("user should be davert", function() {
            $this->user = "davert";
        });

        $this->a = new TestOne();
        $this->a->prop = new TestOne();
        $this->a->prop->prop = 1;

        $this->specify("user should be davert", function() {
            $this->a->prop->prop = "davert";
        });

        $this->assertEquals(1, $this->a->prop->prop);
    }

    public function testCloneOnlySpecified()
    {
        $this->user = "bob";
        $this->b = "rob";
        $this->specify("user should be jon", function() {
            $this->user = "jon";
            $this->b = 'alice';
        });
        $this->assertEquals('bob', $this->user);
        $this->assertEquals('alice', $this->b);
    }


//    public function testFail()
//    {
//        $this->specify('this will fail', function(){
//            $this->assertTrue(false);
//        });
//
//        $this->specify('this will fail', function(){
//            $this->assertTrue(false);
//        });
//
//        $this->specify('this will fail', function(){
//            $this->assertTrue(false);
//        });
//
//        $this->specify('this will fail', function(){
//            $this->assertTrue(false);
//        });
//        $this->specify('this will fail', function(){
//            $this->assertTrue(false);
//        });
//        $this->specify('this will fail', function(){
//            $this->assertTrue(false);
//        });
//
//
//        $this->specify('this will fail too', function(){
//            $this->assertTrue(true);
//        }, ['throws' => 'Exception']);
//    }


    /**
     * @Issue https://github.com/Codeception/Specify/issues/6
     */
    function testPropertyRestore()
    {
        $this->a = new testOne();
        $this->a->prop = ['hello', 'world'];

        $this->specify('array contains hello+world', function ($testData) {
            $this->a->prop = ['bye', 'world'];
            $this->assertContains($testData, $this->a->prop);
        }, ['examples' => [
            ['bye'],
            ['world'],
        ]]);

        $this->assertEquals(['hello', 'world'], $this->a->prop);
        $this->assertFalse($this->private);
        $this->assertTrue($this->getPrivateProperty());

        $this->specify('property $private should be restored properly', function() {
            $this->private = 'i\'m protected';
            $this->setPrivateProperty('i\'m private');
            $this->assertEquals('i\'m private', $this->getPrivateProperty());
        });

        $this->assertFalse($this->private);
        $this->assertTrue($this->getPrivateProperty());
    }

    public function testExamplesIndexInName()
    {
        $name = $this->getName();

        $this->specify('it appends index of an example to a test case name', function ($idx) use ($name) {
            $name .= ' | it appends index of an example to a test case name';
            $this->assertEquals($name . ' # example ' . $idx, $this->getCurrentSpecifyTest()->getName(false));
        }, ['examples' => [
            [0, ''],
            [1, '0'],
            [2, null],
            [3, 'bye'],
            [4, 'world'],
        ]]);

        $this->specify('it does not append index to a test case name if there are no examples', function () use ($name) {
            $name .= ' | it does not append index to a test case name if there are no examples';
            $this->assertEquals($name, $this->getCurrentSpecifyTest()->getName(false));

            $this->specify('nested specification without examples', function () use ($name) {
                $this->assertEquals($name . ' nested specification without examples', $this->getCurrentSpecifyTest()->getName(false));
            });

            $this->specify('nested specification with examples', function () use ($name) {
                $this->assertEquals($name . ' nested specification with examples # example 0', $this->getCurrentSpecifyTest()->getName(false));
            }, ['examples' => [
                [null]
            ]]);
        });
    }

    public function testNestedSpecify()
    {
        $name = $this->getName();

        $this->specify('user', function() use ($name) {
            $name .= ' | user';
            $this->specify('nested specification', function () use ($name) {
                $name .= ' nested specification';
                $this->assertEquals($name, $this->getCurrentSpecifyTest()->getName(false));
            });

        });
    }

    public function testBDDStyle()
    {
        $name = $this->getName();

        $this->describe('user', function() use ($name) {
            $name .= ' | user';
            $this->it('should be ok', function () use ($name) {
                $name .= ' should be ok';
                $this->assertEquals($name, $this->getCurrentSpecifyTest()->getName(false));
            });
            $this->should('be ok', function () use ($name) {
                $name .= ' should be ok';
                $this->assertEquals($name, $this->getCurrentSpecifyTest()->getName(false));
            });
        });
    }

    public function testMockObjectsIsolation()
    {
        $mock = $this->createMock(get_class($this));
        $mock->expects($this->once())->method('testMockObjectsIsolation');
        $mock->testMockObjectsIsolation();

        $this->specify('this should not fail', function () {
            $mock = $this->createMock(get_class($this));
            $mock->expects($this->never())->method('testMockObjectsIsolation')->willReturn(null);
        });

    }

    /**
     * @dataProvider someData
     */
    public function testSpecifyAndDataProvider($param)
    {
        $this->specify('should assert data provider', function () use ($param) {
            $this->assertGreaterThan(0, $param);
        });
    }

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



}

class TestOne
{
    /** @specify  */
    public $prop;
}

class User
{
    public $name;
}