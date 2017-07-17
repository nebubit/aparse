# AParse

## About AParse
A command line tool for PHP developers to analyze Apache log file.

## Requirements
* PHP version 5.4.0 or greater

## Installation

```shell
composer global require "nebubit/aparse=*"
```
Make sure you have the composer bin dir in your PATH. The default value is ~/.composer/vendor/bin/, but you can check the value that you need to use by running composer global config bin-dir --absolute. Then appending this path to your shell environment file by running following command and restart your terminal.

**For bash**
```shell
echo 'export PATH="$PATH:$HOME/.composer/vendor/bin"' >> ~/.bashrc
```
**For Z shell**
```shell
echo 'export PATH="$PATH:$HOME/.composer/vendor/bin"' >> ~/.zshrc
```


## Steps for Query
* Select a file.

```shell
use access-file-name.log
```

* Query

```php
$db->select('c1', 'c2')->get(3)
```

## Examples
**Using GROUP BY and COUNT to get a aggregation result.**

```php
$db->select('*')->count('c3')->group('c3')->get(3)
```
**Grouping result with filters.**
```php
$db->select('c1', 'c2')->count('c3')->where(['c3'=>'400'])->group('c3')->get(3)
```

## Terms

The "c" in select fields stands for column.

