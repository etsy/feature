[![Build Status](https://travis-ci.org/PabloJoan/feature.svg?branch=master)](https://travis-ci.org/PabloJoan/feature)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/PabloJoan/feature/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/PabloJoan/feature/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/PabloJoan/feature/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/PabloJoan/feature/?branch=master)
[![GitHub license](https://img.shields.io/github/license/PabloJoan/feature.svg)](https://github.com/PabloJoan/feature/blob/master/LICENSE)

Requires PHP 8.0 and above.

# Installation

```bash
composer require pablojoan/feature
```

# Basic Usage

```php

use PabloJoan\Feature\Feature; // Import the namespace.

$config = [
    'foo' => [
        'enabled' => [
            'variant1' => 100, //100% chance this variable will be chosen
            'variant2' => 0 //0% chance this variable will be chosen
        ]
    ],
    'bar' => [
        'enabled' => [
            'variant1' => 25, //25% chance this variable will be chosen
            'variant2' => 25, //25% chance this variable will be chosen
            'variant3' => 50 //50% chance this variable will be chosen
        ],
        'bucketing' => 'id' //same id string will always return the same variant
    ]
];

$feature = new Feature($config);

$feature->isEnabled('foo');   // true
$feature->variant('foo');     // 'variant1'
```

For a quick summary and common use cases, please read the rest of this README.

# Feature API

Feature flagging API used for operational rampups and A/B testing.

The Feature API is how we selectively enable and disable features at a very fine
grain as well as enabling features for a percentage of users for operational
ramp-ups and for A/B tests.
A feature can be completely enabled, completely disabled, or something in
between and can comprise a number of related variants.

The two main API entry points are:
```php
    $feature->isEnabled('my_feature')
```
which returns true when `my_feature` is enabled and, for multi-variant features:
```php
    $feature->variant('my_feature')
```
which returns the name of the particular variant which should be used.

The single argument to each of these methods is the name of the
feature to test.

A typical use of `$feature->isEnabled` for a single-variant feature
would look something like this:
```php
    if ($feature->isEnabled('my_feature')) {
        // do stuff
    }
```
For a multi-variant feature, we can determine the appropriate code to run for
each variant with something like this:
```php
    switch ($feature->variant('my_feature')) {
      case 'foo':
          // do stuff appropriate for the 'foo' variant
          break;
      case 'bar':
          // do stuff appropriate for the 'bar' variant
          break;
    }
```

## Configuration cookbook

There are a number of common configurations so before I explain the complete
syntax of the feature configuration stanzas, here are some of the more common
cases along with the most concise way to write the configuration.

### A totally enabled feature:
```php
    $server_config['foo'] = ['enabled' => 100];
```
### A totally disabled feature:
```php
    $server_config['foo'] = ['enabled' => 0];
```
### Feature with winning variant turned on for everyone
```php
    $server_config['foo'] = ['enabled' => ['blue_background' => 100]];
```
### Single-variant feature ramped up to 1% of users.
```php
    $server_config['foo'] = ['enabled' => 1];
```
### Multi-variant feature ramped up to 1% of users for each variant.
```php
    $server_config['foo'] = [
       'enabled' => [
           'blue_background'   => 1,
           'orange_background' => 1,
           'pink_background'   => 1,
       ],
    ];
```
### Enabled for 10% of regular users.
```php
    $server_config['foo'] = [
       'enabled' => 10
    ];
```
### Feature ramped up to 1% of requests, bucketing at random rather than by id
```php
    $server_config['foo'] = [
       'enabled' => 1,
       'bucketing' => 'random'
    ];
```
### Feature ramped up to 40% of requests, bucketing by id rather than at random
```php
    $server_config['foo'] = [
       'enabled' => 40,
       'bucketing' => 'id'
    ];
```
### Single-variant feature in 50/50 A/B test
```php
    $server_config['foo'] = ['enabled' => 50];
```
### Multi-variant feature in A/B test with 20% of users seeing each variant (and 40% left in control group).
```php
    $server_config['foo'] = [
       'enabled' => [
           'blue_background'   => 20,
           'orange_background' => 20,
           'pink_background'   => 20
       ],
    ];
```
## Configuration details

Each feature’s config stanza controls when the feature is enabled and what
variant should be used when it is.

The value of a feature config stanza is an array with a number of special
keys, the most important of which is `'enabled'`.

In its full form, the value of the `'enabled'` property an array whose keys are
names of variants and whose values are the percentage of requests that should
see each variant.

As a shorthand to support the common case of a feature with only one variant,
`'enabled'` can also be specified as a percentage from 0 to 100.

The remaining feature config property is `'bucketing'`. Bucketing specifies 
how users are bucketed when a feature is enabled for only a percentage of users.
The default value, `'random'`, causes each request to be bucketed independently,
meaning that the same user will be in different buckets on different requests.
This is typically used for features that should have no user-visible effects
but where we want to ramp up something like the switch from master to shards
or a new version of JS code.

The bucketing value `'id'`, causes bucketing to be based on the given id.

## Errors

There are a few ways to misuse the Feature API or misconfigure a feature that
may be detected. (Some of these are not currently detected but may be in the
future.)

  1. Setting `'enabled'` to numeric value less than 0 or greater than 100.

  2. Setting the percentage value of a variant in `'enabled'` to a value less
     than 0 or greater than 100.

  3. Setting `'enabled'` such that the sum of the variant percentages is greater
     than 100.

  4. Setting `'enabled'` to a non-numeric, non-array value.

  5. Setting `'bucketing'` to `'id'` and not providing an id string to the
     `$feature->variant` or the `$feature->isEnabled` function.
