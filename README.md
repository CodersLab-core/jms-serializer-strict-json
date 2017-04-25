# JMS Serializer Strict JSON [![Build Status](https://travis-ci.org/JustBlackBird/jms-serializer-strict-json.svg)](https://travis-ci.org/JustBlackBird/jms-serializer-strict-json)

> Deserialize JSON using strict types validation

## Installation

Run in the command line:

```shell
composer require justblackbird/jms-serializer-strict-json
```

## Usage

Use the `StrictJsonDeserializationVisition` from the package instead of JMSSerializer built in `JsonDeserializationVisitior`.

For example, if you use the following code to instantiate JMS serializer:

```php
use JMS\Serializer\SerializerBuilder;

$serializer = SerializerBuilder::create()->build();
```

You shold change it to something like:

```php
use JMS\Serializer\Naming\CamelCaseNamingStrategy;
use JMS\Serializer\Naming\SerializedNameAnnotationStrategy;
use JMS\Serializer\SerializerBuilder;
use JustBlackBird\JmsSerializerStrictJson\StrictJsonDeserializationVisitor;

$naming_strategy = new SerializedNameAnnotationStrategy(new CamelCaseNamingStrategy());
$serializer = SerializerBuilder::create()
    ->setPropertyNamingStrategy($naming_strategy)
    ->setDeserializationVisitor('json', new StrictJsonDeserializationVisitor($naming_strategy))
    ->build();
```

Then use the serializer as you used to.

## License

[Apache 2.0](http://www.apache.org/licenses/LICENSE-2.0) (c) Dmitriy Simushev
