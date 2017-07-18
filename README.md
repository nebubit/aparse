<h1 align="center">AParse</h1>

<p align="center">
<a href="https://travis-ci.org/nebubit/aparse"><img src="https://travis-ci.org/nebubit/aparse.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/nebubit/aparse"><img src="https://poser.pugx.org/nebubit/aparse/d/total.svg" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/nebubit/aparse"><img src="https://poser.pugx.org/nebubit/aparse/v/stable.svg" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/nebubit/aparse"><img src="https://poser.pugx.org/nebubit/aparse/license.svg" alt="License"></a>
</p>

## About AParse
A command line tool for PHP developers to analyze Apache log file. You may ask We already have sed, awk and the others. Why did you still make this tool? The syntax of those tools are too unreadable. A lot of time will be spent on reading the reference to achieve a very simple aim, such as counting the status code. 

## Requirements
* PHP version 5.5 or greater

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
**Using GROUP BY and COUNT to get an aggregation result.**

```php
$db->select('*')->count('c3')->group('c3')->get(3)
```
**Grouping result with WHERE conditions.**
```php
$db->select('c1', 'c2')->count('c3')->where(['c3'=>'400'])->group('c3')->get(3)
```

## Terms

The "c" in select fields stands for column.

