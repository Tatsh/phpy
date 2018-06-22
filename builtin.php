<?php
namespace PythonBuiltins;

class TypeError extends \Exception {
}

class ValueError extends \Exception {
}

/**
 * Checks if an interable has all truthy values.
 *
 * @return boolean
 */
function all(iterable $x)
{
    foreach ($x as $v) {
        if (!$v) {
            return false;
        }
    }
    return true;
}

/**
 * Checks if an interable has at least one truthy value.
 *
 * @return boolean
 */
function any(iterable $x)
{
    foreach ($x as $v) {
        if ($v) {
            return true;
        }
    }
    return false;
}

/**
 * Returns ASCII representation of an object.
 *
 * @return string
 */
function ascii($obj)
{
    return repr($obj);
}

/**
 * Converts a number to base 2.
 *
 * @return string
 */
function bin(int $int)
{
    return base_convert($int, 10, 2);
}

/**
 * Returns if a value is truthy or falsey.
 *
 * @return boolean
 */
function bool($x)
{
    return (bool) $x;
}

/**
 * Do not instantiate this class directly. Use the `bytearray()` function.
 */
class bytearray implements \ArrayAccess, \Countable
{
    private $source;
    private $encoding;
    private $errors;

    public function __construct(
        $source = null,
        ?string $encoding = null,
        ?string $errors = null
    ) {
        $this->encoding = $encoding;
        $this->errors = $errors;

        if ($source && $source instanceof \Traversable) {
            $this->source = iterator_to_array($source, false);
        } else if (is_string($source)) {
            $this->source = $source ? str_split($source) : [];
            if (!$encoding) {
                throw new TypeError('String argument without an encoding');
            }
        } else {
            $this->source = $source ?: [];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function count(): int
    {
        return count($this->source);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetSet($i, $v)
    {
        $s = (string) $v;
        if (strlen($s) > 1 || !strlen($s)) {
            throw new ValueError(sprintf('Value %s is invalid', $v));
        }
        $this->source[$i] = $s;
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet($i): string
    {
        return $this->source[$i];
    }

    /**
     * {@inheritdoc}
     */
    public function offsetExists($i): bool
    {
        return isset($this->source[$i]);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset($i)
    {
        unset($this->source[$i]);
        $this->source = array_values(array_filter($this->source, function ($x) {
            return (bool) $x;
        }));
    }

    /**
     * Returns a string of hexadecimal numbers.
     *
     * @return string
     */
    public function hex()
    {
        return join('', array_map('dechex', array_map('ord', $this->source)));
    }

    /**
     * Convert from a hexadecimal string to create an instance.
     *
     * @param string Hexadecimal string.
     *
     * @return bytearray
     */
    public static function fromhex(string $str)
    {
        $str = preg_replace('/\s+/', '', $str);
        $len = strlen($str);
        if (($len % 2) != 0) {
            throw new \InvalidArgumentException('String length should be an even number');
        }

        $s = new bytearray();
        for ($i = 0; $i < $len; $i += 2) {
            $val = strtolower($str[$i] . $str[$i + 1]);
            if (!preg_match('/^[0-9a-f]+/', $val)) {
                throw new ValueError(
                    sprintf('Non-hexadecimal number found: %s', $val)
                );
            }
            $s[$i] = chr(hexdec($val));
        }

        return $s;
    }

    public function __toString() {
        return join('', $this->source);
    }
}

/**
 * Creates a `bytearray` instance.
 *
 * @param mixed       $source
 * @param string|null $encoding
 * @param string|null $errors
 *
 * @return bytearray
 */
function bytearray($source = null, $encoding = null, $errors = null)
{
    return new bytearray($source, $encoding, $errors);
}

/**
 * Takes an iterable of tuples and returns a hash with the first values as
 * keys.
 *
 * @return array
 */
function dict(iterable $arr): array
{
    $ret = [];
    foreach ($arr as list($key, $value)) {
        $ret[$key] = $value;
    }
    return $ret;
}

/**
 * Returns available methods for an value.
 *
 * @return array
 */
function dir($val = null): array
{
    if (!$val || !is_object($val)) {
        return [];
    }

    $ret = array_merge(
        sorted(get_class_methods($val)),
        sorted(array_keys(get_object_vars($val)))
    );

    return $ret;
}

/**
 * Converts an iterable to tuples with index as the first value.
 *
 * @param iterable $sequence
 * @param integer  $start Starting index.
 *
 * @return iterable
 */
function enumerate(iterable $sequence, int $start = 0)
{
    $n = $start;
    foreach ($sequence as $elem) {
        yield [$n, $elem];
        $n++;
    }
}

/**
 * Filter function.
 *
 * @param callable $func     Function to call. If not specified, then this will
 *                           return an iterable with only truthy values.
 * @param iterable $iterable
 *
 * @return iterable
 */
function filter(?callable $func, iterable $iter)
{
    if ($func) {
        foreach ($iter as $item) {
            if ($func($item)) {
                yield $item;
            }
        }

        return;
    }

    foreach ($iter as $item) {
        if ($item) {
            yield $item;
        }
    }
}

/**
 * Get an attribute by name.
 *
 * @param mixed  $obj
 * @param string $name         Key.
 * @param mixed  $defaultValue Default if key does not exist.
 */
function getattr($obj, string $name, $defaultValue = null)
{
    $vars = get_class_vars(get_class($obj));
    if (isset($vars[$name])) {
        return $vars[$name];
    }
    return $defaultValue;
}

/**
 * Convert an integer to a hexadecimal string.
 */
function hex(int $x)
{
    $neg = '';
    if ($x < 0) {
        $neg = '-';
        $x = abs($x);
    }
    return sprintf('%s0x%x', $neg, $x);
}

/**
 * Unique object ID.
 *
 * @return string
 */
function id($obj)
{
    return spl_object_hash($obj);
}

/**
 * Map function. Arguments after the iterable are passed into the callback.
 */
function map(callable $func, iterable $iterable, ...$args)
{
    foreach ($iterable as $item) {
        yield $func($item, ...$args);
    }
}

/**
 * Convert an integer to a octal string.
 */
function oct(int $n)
{
    return '0o' . base_convert($n, 10, 8);
}

/**
 * Returns string representation of an object.
 */
function repr($obj)
{
    return (string) $obj;
}

/**
 * Generator which yields values in reverse.
 *
 * @param iterable $seq
 * @param integer  $count Count of values to avoid unnecessary iteration.
 */
function reversed(iterable $seq, $count = null)
{
    if (!$count) {
        $count = count($seq);
    }
    $start = $count - 1;
    for ($i = $start; $i >= 0; $i--) {
        yield $seq[$i];
    }
}

/**
 * Slice class. Use the slice() function to instantiate.
 */
class slice
{
    public $start = 0;
    public $step = 1;
    public $stop;
}

/**
 * Generate a slice object. Can be called with 1, 2, or 3 arguments.
 * If 1 argument, sets stop to the first argument.
 * If 2/3 arguments, the signature is `slice($start, $stop, $step = 1)`.
 *
 * @return slice
 */
function slice($start)
{
    $args = func_get_args();
    if (count($args) === 1) {
        $slice = new slice();
        $slice->stop = $start;

        return $slice;
    }

    $slice = new slice();
    $slice->start = $start;
    $slice->stop = $args[1];
    $slice->step = isset($args[2]) ? $args[2] : 1;

    return $slice;
}

/**
 * Sorts an iterable.
 *
 * @param iterable      $iter
 * @param callable|null $key     Comparator function.
 * @param boolean       $reverse If true, return values reversed.
 *
 * @return array
 */
function sorted(iterable $iter, ?callable $key = null, ?bool $reverse = false)
{
    $length = count($iter);
    if ($length <= 1) {
        return $iter;
    }

    if (is_object($iter)) {
        $iter = iterator_to_array($iter, false);
    }
    else {
        $iter = array_values($iter);
    }

    // select an item to act as our pivot point, since list is unsorted first
    // position is easiest
    $pivot = $iter[0];

    // declare our two arrays to act as partitions
    $left = $right = [];

    // loop and compare each item in the array to the pivot value, place item
    // in appropriate partition
    for($i = 1; $i < $length; $i++) {
        if (!$key) {
            if ($iter[$i] < $pivot) {
                $left[] = $iter[$i];
            } else {
                $right[] = $iter[$i];
            }
        } else {
            if ($key($iter[$i], $pivot) < 0) {
                $left[] = $iter[$i];
            } else {
                $right[] = $iter[$i];
            }
        }
    }

    // use recursion to now sort the left and right lists
    return array_merge(sorted($reverse ? $right : $left, $key, $reverse),
                       [$pivot],
                       sorted($reverse ? $left : $right, $key, $reverse));
}

/**
 * Returns string representation of an object.
 */
function str($obj)
{
    return (string) $obj;
}

/**
 * Sums all values of an interable.
 *
 * @param iterable $iter  Iterable.
 * @param integer  $start Starting index.
 *
 * @return integer
 */
function sum(iterable $iter, int $start = 0)
{
    $ans = 0;
    $len = count($iter);
    for ($i = $start; $i < $len; $i++) {
        $ans += $iter[$i];
    }
    return $ans;
}

/**
 * Takes any amount of iterables and 'aligns' them.
 *
 * @return array
 */
function zip(...$iterables)
{
    $sentinel = (object) [];
    $iterators = new \ArrayObject();
    $result = [];

    foreach ($iterables as $obj) {
        if ($obj instanceof \Iterator) {
            $iterators[] = $obj;
        }
        else {
            $iterators[] = new \ArrayIterator($obj);
        }
    }

    $iterators = $iterators->getIterator();

    foreach ($iterators as $k => $it) {
        foreach ($it as $i => $val) {
            $result[$i][$k] = $val;
        }
    }

    return $result;
}
