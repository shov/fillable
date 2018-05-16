<?php

namespace Tests\Unit;

use Shov\Helpers\Mixins\FillableTrait;
use PHPUnit\Framework\TestCase;

class SnakeCaseVarsTest extends TestCase
{
    /**
     * @test
     */
    public function arrayTest()
    {
        //Arrange
        $mock = $this->getMock();

        //Act
        $mock = $mock->fillBy(['foo_bar' => '66', 'bazBan' => '142', 'X_X', false]);

        //Assert
        $this->assertSame(66, $mock->foo_bar);
        $this->assertSame(142.0, $mock->bazBan);
        $this->assertSame(['X_X', false], $mock->data);
    }

    /**
     * @test
     */
    public function selfArrayTest()
    {
        //Arrange
        $mock = $this->getMock();

        //Act
        $mock = $mock->fillPropsBy(['foo_bar' => '66', 'bazBan' => '142', 'X_X', false]);

        //Assert
        $this->assertSame(66, $mock->foo_bar);
        $this->assertSame(142.0, $mock->bazBan);
    }

    protected function getMock()
    {
        return new class()
        {

            use FillableTrait;

            /** @var int */
            public $foo_bar;

            /** @var float */
            public $bazBan;


            /**
             * @param int $foo_bar
             * @return self
             */
            public function setFooBar(int $foo_bar)
            {
                $this->foo_bar = $foo_bar;
                return $this;
            }

            /**
             * @param float $bazBan
             * @return self
             */
            public function setBazBan(float $bazBan)
            {
                $this->bazBan = $bazBan;
                return $this;
            }

        };
    }
}