This code consists of ports of most of the [built-in functions](https://docs.python.org/3.6/library/functions.html) from Python. This code does not implement Python type semantics.

Install with Composer.

# Usage in code

```php
<?php
use PythonBuiltins as py;

// Check for truthy values, useful for filter() and related functions
$hasTruthies = py\any([false, null, '', '0']);
$isAllTruth = py\all([true, true, true]);

// Convert tuples to hash
$hash = py\dict([['a', 1], ['b', 2]]);

// sorted()
class X {
    public function sortBySomeCriteria(iterable $iter): array {
        return py\sorted($iter, function ($a, $b) {
            if ($a->field[0] === '9') {
                return -1;
            }
            return $b->field[0] - $a->field[0];
        });
    }
}

// `slice` object as a standard way to specify ranges without calling `range()` // at the call site
function hello(array $arr, ?py\slice $sl): array {
    if ($sl) {
        $range = range($sl->start, $sl->stop, $sl->end);
        $newArr = [];
        foreach ($range as $i) {
            if (!isset($arr[$i])) {
                break;
            }
            $newArr[] = $arr[$i];
        }
    }
    // ...
}
$sl = slice(2); // stop at 2nd index
hello([1, 2, 3, 4], $sl); // [1, 2]
```

# Contributing

This project is on [GitLab](https://gitlab.com/Tatsh/phpy). File issues and merge requests there.

# Test suite

From the project root:

```bash
./vendor/bin/phpunit --coverage-html coverage
```
