<?php
/**
 * Copyright 2017 Dmitriy Simushev
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace JustBlackBird\JmsSerializer\StrictJson\Tests;

use Doctrine\Common\Annotations\AnnotationReader;
use JMS\Serializer\Construction\UnserializeObjectConstructor;
use JMS\Serializer\Context;
use JMS\Serializer\EventDispatcher\EventDispatcher;
use JMS\Serializer\Exception\RuntimeException;
use JMS\Serializer\Handler\ArrayCollectionHandler;
use JMS\Serializer\Handler\HandlerRegistry;
use JMS\Serializer\Handler\PhpCollectionHandler;
use JMS\Serializer\Metadata\Driver\AnnotationDriver;
use JMS\Serializer\Naming\CamelCaseNamingStrategy;
use JMS\Serializer\Naming\SerializedNameAnnotationStrategy;
use JMS\Serializer\Serializer;
use JustBlackBird\JmsSerializer\StrictJson\StrictJsonDeserializationVisitor;
use Metadata\MetadataFactory;
use PHPUnit\Framework\TestCase;
use PhpCollection\Map;

class DeserializationTest extends TestCase
{
    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @dataProvider provideCorrectStrings
     */
    public function testCorrectStringDeserialization($content, $expected_value)
    {
        $this->assertSame($expected_value, $this->deserialize($content, 'string'));
    }

    public function provideCorrectStrings()
    {
        return [
            ['""', ''],
            ['"foo"', 'foo'],
        ];
    }

    /**
     * @dataProvider provideInvalidStrings
     */
    public function testInvalidStringDeserialization($invalid_string)
    {
        $this->expectException(RuntimeException::class);
        $this->deserialize($invalid_string, 'string');
    }

    public function provideInvalidStrings()
    {
        return [
            ['1'],
            ['4.20'],
            ['true'],
            ['false'],
            ['null'],
            ['[]'],
            ['{}'],
        ];
    }

    /**
     * @dataProvider provideCorrectBooleans
     */
    public function testCorrectBooleanDeserialization($content, $expected_value)
    {
        $this->assertSame($expected_value, $this->deserialize($content, 'boolean'));
    }

    public function provideCorrectBooleans()
    {
        return [
            ['false', false],
            ['true', true],
        ];
    }

    /**
     * @dataProvider provideInvalidBooleans
     */
    public function testInvalidBooleanDeserialization($invalid_boolean)
    {
        $this->expectException(RuntimeException::class);
        $this->deserialize($invalid_boolean, 'boolean');
    }

    public function provideInvalidBooleans()
    {
        return [
            ['0'],
            ['1'],
            ['42'],
            ['8.74'],
            ['null'],
            ['"foo"'],
            ['""'],
            ['"true"'],
            ['"false"'],
            ['"0"'],
            ['"1"'],
            ['"yes"'],
            ['"no"'],
            ['[]'],
            ['{}'],
        ];
    }

    /**
     * @dataProvider provideCorrectIntegers
     */
    public function testCorrectInteger($content, $expected_result)
    {
        $this->assertSame($expected_result, $this->deserialize($content, 'integer'));
    }

    public function provideCorrectIntegers()
    {
        return [
            ['-78', -78],
            ['0', 0],
            ['32', 32],
        ];
    }

    /**
     * @dataProvider provideInvalidIntegers
     */
    public function testInvalidInteger($invalid_integer)
    {
        $this->expectException(RuntimeException::class);
        $this->deserialize($invalid_integer, 'integer');
    }

    public function provideInvalidIntegers()
    {
        return [
            ['null'],
            ['false'],
            ['true'],
            ['""'],
            ['"foo"'],
            ['"42"'],
            ['42.0'],
            ['{}'],
            ['[]'],
        ];
    }

    /**
     * @dataProvider provideCorrectFloats
     */
    public function testCorrectFloat($content, $expected_value)
    {
        $this->assertSame($expected_value, $this->deserialize($content, 'float'));
    }

    /**
     * @dataProvider provideCorrectFloats
     */
    public function testCorrectDouble($content, $expected_value)
    {
        $this->assertSame($expected_value, $this->deserialize($content, 'double'));
    }

    public function provideCorrectFloats()
    {
        return [
            ['-2.10', -2.1],
            ['0.0', 0.0],
            ['42.5', 42.5],
            ['4e2', 400.0],
            ['2e+2', 200.0],
            ['8e-2', 0.08],
            ['1.2E2', 120.0],
            // Treat integers as a subset of float. It's the only exception of
            // strict parsing.
            ['50', 50.0],
        ];
    }

    /**
     * @dataProvider provideInvalidFloats
     */
    public function testInvalidFloat($invalid_float)
    {
        $this->expectException(RuntimeException::class);
        $this->deserialize($invalid_float, 'float');
    }

    /**
     * @dataProvider provideInvalidFloats
     */
    public function testInvalidDouble($invalid_double)
    {
        $this->expectException(RuntimeException::class);
        $this->deserialize($invalid_double, 'double');
    }

    public function provideInvalidFloats()
    {
        return [
            ['null'],
            ['false'],
            ['true'],
            ['""'],
            ['"foo"'],
            ['"1.0"'],
            ['"14"'],
            ['{}'],
            ['[]'],
        ];
    }

    private function deserialize($content, $type, Context $context = null)
    {
        return $this->serializer->deserialize($content, $type, 'json', $context);
    }

    protected function setUp()
    {
        $factory = new MetadataFactory(new AnnotationDriver(new AnnotationReader()));

        $handler_registry = new HandlerRegistry();
        $handler_registry->registerSubscribingHandler(new PhpCollectionHandler());
        $handler_registry->registerSubscribingHandler(new ArrayCollectionHandler());

        $naming_strategy = new SerializedNameAnnotationStrategy(new CamelCaseNamingStrategy());
        $deserialization_visitors = new Map(array(
            'json' => new StrictJsonDeserializationVisitor($naming_strategy),
        ));

        $this->serializer = new Serializer(
            $factory,
            $handler_registry,
            new UnserializeObjectConstructor(),
            new Map(),
            $deserialization_visitors,
            new EventDispatcher()
        );
    }
}
