<?php
/*
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

namespace JustBlackBird\JmsSerializerStrictJson\Tests\Exception;

use JMS\Serializer\DeserializationContext;
use JustBlackBird\JmsSerializerStrictJson\Exception\TypeMismatchException;
use PHPUnit\Framework\TestCase;

class TypeMismatchExceptionTest extends TestCase
{
    public function testStaticConstructorWithoutContext()
    {
        $expected_type = 'foo';
        $actual_value = 'bar';

        $exception = TypeMismatchException::fromValue($expected_type, $actual_value);

        $this->assertInstanceOf(TypeMismatchException::class, $exception);
        $this->assertSame('Expected foo, but got string: "bar"', $exception->getMessage());
    }

    public function testStaticConstructorWithTopLevelContext()
    {
        $expected_type = 'foo';
        $actual_value = 'bar';
        $context = $this->getContextMock();
        $context->expects($this->atLeastOnce())
            ->method('getCurrentPath')
            ->willReturn([]);

        $exception = TypeMismatchException::fromValue($expected_type, $actual_value, $context);

        $this->assertInstanceOf(TypeMismatchException::class, $exception);
        $this->assertSame(
            'Expected foo, but got string: "bar"',
            $exception->getMessage()
        );
    }

    public function testStaticConstructorWithThirdLevelContext()
    {
        $expected_type = 'foo';
        $actual_value = 'bar';
        $context = $this->getContextMock();
        $context->expects($this->atLeastOnce())
            ->method('getCurrentPath')
            ->willReturn(['asd', 'qwe']);

        $exception = TypeMismatchException::fromValue($expected_type, $actual_value, $context);

        $this->assertInstanceOf(TypeMismatchException::class, $exception);
        $this->assertSame(
            'Expected property "asd.qwe" to be foo, but got string: "bar"',
            $exception->getMessage()
        );
    }

    private function getContextMock()
    {
        return $this->getMockBuilder(DeserializationContext::class)
            ->disableOriginalConstructor()
            ->getMock();
    }
}
