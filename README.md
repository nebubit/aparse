#AParse

##About AParse
A command line tool for PHP developers to analyze Apache log file.

##Requirements
* PHP version 5.4.0 or greater

##Installation

composer global install "nebubit/aparse"

##Steps for Query
* Select a file.

```shell
use access-file-name.log
```

* Query

```php
$db->select('c1', 'c2')->get(3)
```

##Examples
**Using GROUP BY and COUNT to get a aggregation result.**

```php
$db->select('*')->count('c3')->groupBy('c3')->get(3)
```
**Grouping result with filters.**
```php
$db->select('c1', 'c2')->count('c3')->where(['c3'=>'400'])->group('c3')->get(3)
```

##Terms

The "c" in select fields stands for column.

