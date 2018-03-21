<?php

namespace Tests\Unit;

use Shov\Helpers\Mixins\FillableTrait;
use PHPUnit\Framework\TestCase;

class FillableTraitTest extends TestCase
{
    /**
     * @test
     */
    public function arrayTest()
    {
        //Arrange
        $mock = $this->getMock();

        //Act
        $mock = $mock->fillBy(['foo' => 'test', 'bar' => '142', 'X_X', false]);

        //Assert
        $this->assertSame('test', $mock->foo);
        $this->assertSame(142, $mock->bar);
        $this->assertSame(['X_X', false], $mock->data);
    }

    /**
     * @test
     */
    public function objectTest()
    {
        //Arrange
        $mock = $this->getMock();

        $source = new \stdClass();
        $source->foo = 'dvwv';
        $source->bar = 142.6666666;

        //Here data is unknown key and must be stored as $host->data = ['data' => ['X_X', true]]
        $source->data = ['X_X', true];

        //Act
        $mock = $mock->fillBy($source);

        //Assert
        $this->assertSame('dvwv', $mock->foo);
        $this->assertSame(142, $mock->bar);
        $this->assertSame(['data' => ['X_X', true]], $mock->data);
    }

    /**
     * @test
     */
    public function varTest()
    {
        //Arrange
        $mock = $this->getMock();

        //Act
        $mock = $mock->fillBy('value', 'foo');
        $mock->fillBy(42, 'bar');

        //Assert
        $this->assertSame('value', $mock->foo);
        $this->assertSame(42, $mock->bar);
        $this->assertTrue(!isset($mock->data));
    }

    /**
     * @test
     */
    public function dynamicOffTest()
    {
        //Arrange
        $mock = $this->getMock();

        //Act
        $mock = $mock->fillBy(['unknownkey' => 'test', 'bar' => '142', 'X_X', false]);

        //Assert
        $this->assertSame(null, $mock->foo);
        $this->assertSame(142, $mock->bar);
        $this->assertSame(['unknownkey' => 'test', 'X_X', false], $mock->data);
    }

    /**
     * @test
     */
    public function dynamicOnTest()
    {
        //Arrange
        $mock = $this->getMock();

        //Act
        $mock = $mock->fillBy(['unknownkey' => 'test', 'bar' => '142', 'X_X', false], 'data', true);

        //Assert
        $this->assertSame(null, $mock->foo);
        $this->assertSame('test', $mock->unknownkey);
        $this->assertSame(142, $mock->bar);
        $this->assertSame(['X_X', false], $mock->data);
    }

    /**
     * @test
     */
    public function selfArrayTest() {
        //Arrange
        $mock = $this->getMock();

        //Act
        $mock = $mock->fillPropsBy(['foo' => 'test', 'bar' => '142', 'X_X', false]);

        //Assert
        $this->assertSame('test', $mock->foo);
        $this->assertSame(142, $mock->bar);
    }

    /**
     * @test
     */
    public function selfObjectMagicTest()
    {
        //Arrange
        $mock = $this->getMock();

        $source = new class () {

            public $data = [
                'X_X', true,
            ];

            public function __get($name) {
                if('foo' === $name) {
                    return 'dvwv';
                }

                if('bar' === $name) {
                    return 142.6666666;
                }

                throw new \Exception('Call to missing property');
            }
        };

        //Act
        $mock = $mock->fillPropsBy($source);

        //Assert
        $this->assertSame('dvwv', $mock->foo);
        $this->assertSame(142, $mock->bar);
    }

    /**
     * @test
     */
    public function selfVarTest()
    {
        //Arrange
        $mock = $this->getMock();

        //Act
        $mock = $mock->fillPropsBy('test');

        //Assert
        $this->assertSame('test', $mock->foo);
        $this->assertSame(0, $mock->bar);
    }

    protected function getMock()
    {
        return new class() {

            use FillableTrait;

            public $foo;

            /** @var int */
            public $bar;

            /**
             * @param int $bar
             */
            public function setBar($bar)
            {
                $this->bar = (int)$bar;
            }
        };
    }
}
