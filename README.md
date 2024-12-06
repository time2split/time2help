# Time2Help

[![License: MIT](https://img.shields.io/badge/License-MIT-green.svg)](https://opensource.org/licenses/MIT)
[![Latest Stable Version](https://poser.pugx.org/time2split/time2help/v)](https://packagist.org/packages/time2split/time2help)
[![Latest Unstable Version](https://poser.pugx.org/time2split/time2help/v/unstable)](https://packagist.org/packages/time2split/time2help)

Time2Help is an utility library made in the first place to be shared across all Time2Split's projects.
Nevertheless, its functionnalities may be used in any php project.

## Installation

The library is distributed as a Composer project.

```bash
$ composer require time2split/time2help
```

## Documentation

 * [API documentation](https://time2split.net/php-time2help/)

## Features

The library serves 3 main domains:
 1. container
 2. inputs/outputs (IO)
 3. tests

### Container

The container domain provides functionalities to works on different type of containers.
It's purpose is
 1. to facilitate operations through iteration on each container entry (key => value) and
 2. provides implementations of usefull data structure. 

Iterating through an iterable to do some operations on each item appears to be a common task in a program.
The library provides functionnalities to works on
[arrays](https://time2split.net/php-time2help/classes/Time2Split-Help-Arrays.html),
and more generally on
[iterables](https://time2split.net/php-time2help/classes/Time2Split-Help-Iterables.html)
(which have the advantage to be able to work on efficient [Generators](https://www.php.net/manual/en/class.generator.php)).

For instance, an iterating function can be:
to iterate in reverse order,
iterate only on key,
filter the entries to be iterated,
apply an operation to each entries of a container,
flip the entry key and value;
and any composition of the provided functions.
(Consult the API documentation if interested to know all the existing functions.)

The container functions return mainly [\Iterator](https://www.php.net/manual/en/class.iterator.php) instances, that internally are more specifically [\Generator](https://www.php.net/manual/en/class.generator.php).
Generators have the advantage to be space-efficient: they just return an entry through an iteration without the need to store all the entries to be returned.
All iteration operations on containers return iterators.
Moreover it's so easy to get an array from an iterable with [\iterator_to_array()](https://www.php.net/manual/en/function.iterator-to-array.php) that the library as no interest in returning less space-efficient array values.

Tree-shaped structured are also covered for
[arrays](https://time2split.net/php-time2help/classes/Time2Split-Help-ArrayTrees.html) and
[iterables](https://time2split.net/php-time2help/classes/Time2Split-Help-IterableTrees.html).


Concerning the second purpose of the container domain,
the library provides implementations for the
[Set](https://time2split.net/php-time2help/classes/Time2Split-Help-Set.html) and
[Optional](https://time2split.net/php-time2help/classes/Time2Split-Help-Optional.html)
data-structures
and more basic functionnalities on
[lists](https://time2split.net/php-time2help/classes/Time2Split-Help-ArrayLists.html) (array with integer keys).

### IO

The IO domain provides 3 utility classes.
 - [CharPredicates](https://time2split.net/php-time2help/classes/Time2Split-Help-CharPredicates.html)
 Functions to create char predicate closures.
 - [IO](https://time2split.net/php-time2help/classes/Time2Split-Help-IO.html)
 Functions for inputs/outputs.
 - [Streams](https://time2split.net/php-time2help/classes/Time2Split-Help-Streams.html)
 Functions for stream resource.

Streams and Char predicate where made mainly to facilitate the writing of parsers.
CharPredicate permits to generate char predicate closures.
Streams provides functions for reading on php resource streams.

IO provides functions to works with the filesystem and the php output buffer, and also somme facilities to launch an external cli command and retrieves the output and the errors.

### tests

The tests domain was mad to facilitate the writting of unit test with PHPUnit.
It provides 2 classes
 1. [Producer](https://time2split.net/php-time2help/classes/Time2Split-Help-Tests-DataProvider-Producer.html)
 2. [Provided](https://time2split.net/php-time2help/classes/Time2Split-Help-Tests-DataProvider-Provided.html)

The use case is to
 1. Write an iterable of Provided, that can be viewed as an incomplete database of arguments to be send to a unit test method
 2. Make the complete iterable of dataset to be returned by a [DataProvider](https://docs.phpunit.de/en/11.0/writing-tests-for-phpunit.html#data-providers) with
a space-efficient [cartesian product](https://time2split.net/php-time2help/classes/Time2Split-Help-Provided.html#method_merge)
between multiple Provided databases.

The advantage of this workflow is that it's easy to conceptualise the different database to write and to compose them.
The cartesian product generate automatically a PHPUnit dataset's name and arguments,
and is able to dynamically generate a data when a specific database argument is selected by the cartesian product.
The cartesian product implementation uses internally a Generator.
That Generator do not stores the all cartesian product in a container, but it generates each dataset only when needed by a test method. 
