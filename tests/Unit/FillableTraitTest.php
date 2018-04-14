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
    public function selfArrayTest()
    {
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
    public function selfArrayPartialTest()
    {
        //Arrange
        $mock = $this->getMock();
        $mock->bar = 143;

        //Act
        $mock = $mock->fillPropsBy(['foo' => 'test', 'X_X', false]);

        //Assert
        $this->assertSame('test', $mock->foo);
        $this->assertSame(143, $mock->bar);
    }

    /**
     * @test
     */
    public function selfObjectMagicTest()
    {
        //Arrange
        $mock = $this->getMock();

        $source = new class ()
        {

            public $data = [
                'X_X', true,
            ];

            public function __get($name)
            {
                if ('foo' === $name) {
                    return 'dvwv';
                }

                if ('bar' === $name) {
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

    /**
     * @test
     */
    public function onlyTest()
    {
        $mocks = [];

        //Arrange
        $mock = $this->getMock();

        $arr = ['foo' => 'test', 'bar' => '142', 'X_X', false];
        $obj = (object)$arr;

        //Act
        $mocks[] = (clone $mock)
            ->only(['bar'])
            ->fillBy($arr);

        $mocks[] = (clone $mock)
            ->only(['bar'])
            ->fillPropsBy($arr);

        $mocks[] = (clone $mock)
            ->only(['bar'])
            ->fillBy($obj);

        $mocks[] = (clone $mock)
            ->only(['bar'])
            ->fillPropsBy($obj);

        $mocks[] = (clone $mock)
            ->only('bar')
            ->fillBy($arr);

        $mocks[] = (clone $mock)
            ->only('bar')
            ->fillPropsBy($arr);

        $mocks[] = (clone $mock)
            ->only('bar')
            ->fillBy($obj);

        $mocks[] = (clone $mock)
            ->only('bar')
            ->fillPropsBy($obj);

        //Assert
        foreach ($mocks as $mock) {
            $this->assertTrue(empty($mock->foo));
            $this->assertSame(142, $mock->bar);
        }
    }

    /**
     * @test
     */
    public function onlyAfterSkipTest()
    {
        $mocks = [];

        //Arrange
        $mock = $this->getMock();

        $arr = ['foo' => 'test', 'bar' => '142', 'X_X', false];
        $obj = (object)$arr;

        //Act
        $mocks['a1'] = (clone $mock)
            ->only(['bar'])
            ->skipQuery()
            ->fillBy($arr);

        $mocks['o1'] = (clone $mock)
            ->only(['bar'])
            ->skipQuery()
            ->fillPropsBy($arr);

        $mocks['o2'] = (clone $mock)
            ->only(['bar'])
            ->skipQuery()
            ->fillBy($obj);

        $mocks['o3'] = (clone $mock)
            ->only(['bar'])
            ->skipQuery()
            ->fillPropsBy($obj);

        //Assert
        foreach ($mocks as $key => $mock) {
            $this->assertSame('test', $mock->foo);
            $this->assertSame(142, $mock->bar);

            if (substr($key, 0, 1) === 'a') {
                $this->assertSame(['X_X', false], $mock->data);
            }
        }
    }

    /**
     * @test
     */
    public function excludeTest()
    {
        $mocks = [];

        //Arrange
        $mock = $this->getMock();

        $arr = ['foo' => 'test', 'bar' => '142', 'X_X', false];
        $obj = (object)$arr;

        //Act
        $mocks[] = (clone $mock)
            ->exclude(['bar'])
            ->fillBy($arr);

        $mocks[] = (clone $mock)
            ->exclude(['bar'])
            ->fillPropsBy($arr);

        $mocks[] = (clone $mock)
            ->exclude(['bar'])
            ->fillBy($obj);

        $mocks[] = (clone $mock)
            ->exclude(['bar'])
            ->fillPropsBy($obj);

        $mocks[] = (clone $mock)
            ->exclude('bar')
            ->fillBy($arr);

        $mocks[] = (clone $mock)
            ->exclude('bar')
            ->fillPropsBy($arr);

        $mocks[] = (clone $mock)
            ->exclude('bar')
            ->fillBy($obj);

        $mocks[] = (clone $mock)
            ->exclude('bar')
            ->fillPropsBy($obj);

        //Assert
        foreach ($mocks as $mock) {
            $this->assertTrue(empty($mock->bar));
            $this->assertSame('test', $mock->foo);
        }
    }

    /**
     * @test
     */
    public function excludeAfterSkipTest()
    {
        $mocks = [];

        //Arrange
        $mock = $this->getMock();

        $arr = ['foo' => 'test', 'bar' => '142', 'X_X', false];
        $obj = (object)$arr;

        //Act
        $mocks['a1'] = (clone $mock)
            ->exclude(['bar'])
            ->skipQuery()
            ->fillBy($arr);

        $mocks['o1'] = (clone $mock)
            ->exclude(['bar'])
            ->skipQuery()
            ->fillPropsBy($arr);

        $mocks['o2'] = (clone $mock)
            ->exclude(['bar'])
            ->skipQuery()
            ->fillBy($obj);

        $mocks['o3'] = (clone $mock)
            ->exclude(['bar'])
            ->skipQuery()
            ->fillPropsBy($obj);

        //Assert
        foreach ($mocks as $key => $mock) {
            $this->assertSame('test', $mock->foo);
            $this->assertSame(142, $mock->bar);

            if (substr($key, 0, 1) === 'a') {
                $this->assertSame(['X_X', false], $mock->data);
            }
        }
    }

    /**
     * @test
     */
    public function onlyExcludeTest()
    {
        $mocks = [];

        //Arrange
        $mock = $this->getMock();

        $arr = ['foo' => 'test', 'bar' => '142', 'X_X', false];
        $obj = (object)$arr;

        //Act
        $mocks[] = (clone $mock)
            ->exclude(['bar', 'foo'])
            ->only(['bar'])
            ->fillBy($arr);

        $mocks[] = (clone $mock)
            ->exclude(['bar'])
            ->only(['bar'])
            ->fillPropsBy($arr);

        $mocks[] = (clone $mock)
            ->exclude(['bar'])
            ->only(['bar'])
            ->fillBy($obj);

        $mocks[] = (clone $mock)
            ->exclude(['bar', 'foo'])
            ->only(['bar'])
            ->fillPropsBy($obj);

        //Assert
        foreach ($mocks as $mock) {
            $this->assertTrue(empty($mock->bar));
            $this->assertTrue(empty($mock->foo));
            $this->assertTrue(!property_exists($mock, 'data'));
        }
    }

    /**
     * @test
     */
    public function afterQueryTest()
    {
        $mocks = [];

        //Arrange
        $mock = $this->getMock();

        $arr = ['foo' => 'test', 'bar' => '142', 'X_X', false];
        $obj = (object)$arr;

        $arr2 = ['foo' => 'test2', 'bar' => '1422', 'X_X2', true];
        $obj2 = (object)$arr2;

        //Act
        $mocks[] = (clone $mock)
            ->only(['bar'])
            ->fillBy($arr)
            ->fillBy($arr2);

        $mocks[] = (clone $mock)
            ->only(['bar'])
            ->fillPropsBy($arr)
            ->fillPropsBy($arr2);

        $mocks[] = (clone $mock)
            ->only(['bar'])
            ->fillBy($obj)
            ->fillBy($obj2);

        $mocks[] = (clone $mock)
            ->only(['bar'])
            ->fillPropsBy($obj)
            ->fillPropsBy($obj2);

        $mocks[] = (clone $mock)
            ->only('bar')
            ->fillBy($arr)
            ->fillBy($arr2);

        $mocks[] = (clone $mock)
            ->only('bar')
            ->fillPropsBy($arr)
            ->fillPropsBy($arr2);

        $mocks[] = (clone $mock)
            ->only('bar')
            ->fillBy($obj)
            ->fillBy($obj2);

        $mocks[] = (clone $mock)
            ->only('bar')
            ->fillPropsBy($obj)
            ->fillPropsBy($obj2);

        $mocks[] = (clone $mock)
            ->exclude(['foo'])
            ->fillBy($arr)
            ->fillBy($arr2);

        $mocks[] = (clone $mock)
            ->exclude(['foo'])
            ->fillPropsBy($arr)
            ->fillPropsBy($arr2);

        $mocks[] = (clone $mock)
            ->exclude(['foo'])
            ->fillBy($obj)
            ->fillBy($obj2);

        $mocks[] = (clone $mock)
            ->exclude(['foo'])
            ->fillPropsBy($obj)
            ->fillPropsBy($obj2);

        $mocks[] = (clone $mock)
            ->exclude('foo')
            ->fillBy($arr)
            ->fillBy($arr2);

        $mocks[] = (clone $mock)
            ->exclude('foo')
            ->fillPropsBy($arr)
            ->fillPropsBy($arr2);

        $mocks[] = (clone $mock)
            ->exclude('foo')
            ->fillBy($obj)
            ->fillBy($obj2);

        $mocks[] = (clone $mock)
            ->exclude('foo')
            ->fillPropsBy($obj)
            ->fillPropsBy($obj2);

        //Assert
        foreach ($mocks as $mock) {
            $this->assertSame('test2', $mock->foo);
            $this->assertSame(1422, $mock->bar);
        }
    }

    /**
     * @test
     * @dataProvider wrongsOnlyQueryTestDataProvider
     */
    public function wrongsOnlyQueryTest($value)
    {
        //Assert
        $this->expectException(\InvalidArgumentException::class);

        //Act
        $this->getMock()->only($value);
    }

    public function wrongsOnlyQueryTestDataProvider()
    {
        return [
            [666],
            [666.666],
            [false],
        ];
    }

    /**
     * @test
     * @dataProvider wrongsExcludeQueryTestDataProvider
     */
    public function wrongsExcludeQueryTest($value)
    {
        //Assert
        $this->expectException(\InvalidArgumentException::class);

        //Act
        $this->getMock()->exclude($value);
    }

    public function wrongsExcludeQueryTestDataProvider()
    {
        return [
            [666],
            [666.666],
            [false],
        ];
    }

    /**
     * @test
     */
    public function getSourceValueWithGetter()
    {
        //Arrange
        $mock = $this->getMock();
        $source = new class ()
        {
            public $foo = 'xxx';
            protected $bar = 42;

            /**
             * @return mixed
             */
            public function getBar()
            {
                return $this->bar * 2;
            }

            /**
             * @param $_
             * @param $__
             * @return mixed
             */
            public function __call($_, $__)
            {
                return 'O_O';
            }
        };

        //Act
        $mock->fillPropsBy($source);

        //Assert
        $this->assertSame('xxx', $mock->foo);
        $this->assertSame(84, $mock->bar);

    }

    protected function getMock()
    {
        return new class()
        {

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
