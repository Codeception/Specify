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
            $this->fail('ups');
        });
        $this->assertTrue(true);
    }

}
