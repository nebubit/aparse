## Notice

THIS PROJECT IS NOT FINISHED. PLEASE DO NOT USE THIS TOOL.

## How to Install

composer install "nebubit/aparse" --globally

## How to Use


## Examples
#### Using GROUP BY and COUNT to get a aggregation result

$db->select('*')->count('c3')->groupBy('c3')->get(3)

$db->select('c1', 'c2')->get(3)

$db->select('c1', 'c2')->count('c3')->where(['c3'=>'400'])->group('c3')->get(3)

$db->select('c1', 'c2')->count('c3')->group('c3')->get(3)



##Terms

The "c" in select fields stands for column.

