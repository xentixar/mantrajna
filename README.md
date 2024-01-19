# Mantrajna Documentation

Mantrajna is a PHP library designed to provide a flexible and powerful way to manipulate and query tabular data from various sources, such as CSV, JSON, and XML files. It allows users to perform operations like filtering, sorting, formatting, and updating data seamlessly.

## Table of Contents

- [Installation](#installation)
- [Getting Started](#getting-started)
- [Supported Operations](#supported-operations)
  - [Read Data](#read-data)
  - [Filtering and Sorting](#filtering-and-sorting)
  - [Formatting](#formatting)
  - [Updating Data](#updating-data)
  - [Output Operations](#output-operations)
- [Examples](#examples)
- [Contributing](#contributing)
- [License](#license)

## Installation

To use Mantrajna in your project, you can install it using [Composer](https://getcomposer.org/):

```bash
composer require xentixar/mantrajna
```

## Getting Started

Here's a basic example of using Mantrajna to read a CSV file and perform some operations:

```php
<?php

use Xentixar\Mantrajna\Unicorn;

// Create an instance of Mantrajna Unicorn
$mantrajna = new Unicorn();

// Read data from a CSV file
$mantrajna->readCsv('example.csv');

// Filter data where the 'age' column is greater than 25
$mantrajna->where(['age', '>', 25]);

// Sort the data based on the 'name' column in descending order
$mantrajna->sort('name', 'DESC');

// Execute all the instruction
// Before using print or get you have to call run function to save all the changes.
$mantrajna->run();

// Print the resulting data
$mantrajna->print();
```

## Supported Operations

### Read Data

#### CSV

```php
$mantrajna->readCsv('example.csv', ',');
```

#### JSON

```php
$mantrajna->readJson('example.json');
```

#### XML

```php
$mantrajna->readXml('example.xml');
```

### Filtering and Sorting

#### Filtering

```php
$mantrajna->where(['age', '>', 25]);
```

#### Sorting

```php
$mantrajna->sort('name', 'DESC');
```

### Formatting

#### Set Column Info

```php
$mantrajna->setColumnInfo('birthdate:datetime:Y-m-d', 'score:double');
```

#### Format

```php
$mantrajna->format('birthdate:Y-m-d');
```

### Updating Data

#### Update

```php
$mantrajna->update(['age', 'score'], [30, 95.5]);
```

### Output Operations

#### Select Columns

```php
$mantrajna->selectColumns('name', 'age');
```

#### Print Data

```php
$mantrajna->print();
```

#### Output to JSON

```php
$mantrajna->dumpJson('output.json');
```

#### Output to XML

```php
$mantrajna->dumpXml('output.xml');
```

#### Output to CSV

```php
$mantrajna->dumpCsv('output.csv', ',');
```

## Examples

For more examples and use cases, refer to the [examples](examples) directory in the Mantrajna GitHub repository.

## Contributing

Contributions are welcome! If you find any issues or have suggestions, please open an [issue](https://github.com/xentixar/mantrajna/issues) or submit a [pull request](https://github.com/xentixar/mantrajna/pulls).

## License

Mantrajna is open-source software licensed under the [MIT license](LICENSE). Feel free to use and modify it as per your project requirements.