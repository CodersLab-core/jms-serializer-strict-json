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

namespace JustBlackBird\JmsSerializerStrictJson\Exception;

use JMS\Serializer\Exception\RuntimeException;

class TypeMismatchException extends RuntimeException
{
    /**
     * A handy method for building exception instance.
     *
     * @param string $expected_type
     * @param mixed $actual_value
     * @return TypeMismatchException
     */
    public static function fromValue($expected_type, $actual_value)
    {
        return new static(sprintf(
            'Expected %s, but got %s: %s',
            $expected_type,
            gettype($actual_value),
            json_encode($actual_value)
        ));
    }
}