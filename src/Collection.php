<?php

namespace Whitecube\Winbooks;

use Countable;
use ArrayAccess;
use ArrayIterator;
use IteratorAggregate;

class Collection implements ArrayAccess, IteratorAggregate, Countable
{
    /**
     * Container for the completed results
     *
     * @var array
     */
    protected $items = [];

    /**
     * The last result that has been fetched
     * (could be complete or incomplete depending
     * on the next request's first result)
     *
     * @var null|mixed
     */
    protected $fragment;

    /**
     * Get the collection's underlaying array
     *
     * @return array
     */
    public function all()
    {
        return is_null($this->fragment)
            ? $this->items
            : array_merge($this->items, [$this->fragment]);
    }

    /**
     * Insert new items and/or merge fragment in order to complete it.
     *
     * @param mixed $values
     * @return $this
     */
    public function fill($values, $expectMore = false)
    {
        if(is_a($values, static::class)) {
            $values = $values->all();
        }

        if(! is_array($values)) {
            $values = [$values];
        }

        $values = array_values($values);

        // If there is a fragment from previous request, we should
        // first check if it can be completed with the new incoming data.
        if($this->isMissingFragmentPart($values[0])) {
            $this->fragment->merge(array_shift($values));
        }

        $insertedFragment = null;

        // At this point, if there are more remaining values or
        // if we don't expect more data to come, the eventual existing
        // fragment can be added to the items array.
        if($this->fragment && (count($values) || ! $expectMore)) {
            $insertedFragment = $this->fragment;
            array_unshift($values, $this->fragment);
            $this->fragment = null;
        }

        // If there is more data to come, we'll consider the last incoming
        // element as a potential fragment that will be treated in the next
        // request iteration.
        if(! $this->fragment && $expectMore && count($values) && ($values[count($values) - 1] !== $insertedFragment)) {
            $this->fragment = array_pop($values);
        }

        $this->items = array_merge($this->items, $values);

        return $this;
    }

    /**
     * Check if the provided value can complete the current fragment.
     *
     * @param mixed $value
     * @return bool
     */
    protected function isMissingFragmentPart($value)
    {
        if(is_null($this->fragment)) {
            return false;
        }

        if(! is_a($value, get_class($this->fragment))) {
            return false;
        }

        return $this->fragment->getCode() === $value->getCode();
    }

    /**
     * Get the amount of items this collection contains
     *
     * @return int
     */
    public function count(): int
    {
        return (count($this->items) + (is_null($this->fragment) ? 0 : 1));
    }

    /**
     * Get the collection's first item
     *
     * @return null|mixed
     */
    public function first()
    {
        return $this->items[0] ?? $this->fragment ?? null;
    }

    /**
     * Get the collection's last item
     *
     * @return null|mixed
     */
    public function last()
    {
        return $this->items[count($this->items) - 1] ?? $this->fragment ?? null;
    }

    /**
     * ArrayAccess' has alias
     *
     * @param int $key
     * @return bool
     */
    public function offsetExists($key)
    {
        return array_key_exists($key, $this->items);
    }

    /**
     * ArrayAccess' set alias
     *
     * @param int $key
     * @param mixed $value
     * @return void
     */
    public function offsetSet($key, $value)
    {
        $this->items[$key] = $value;
    }

    /**
     * ArrayAccess' get alias
     *
     * @param int $key
     * @return mixed
     */
    public function offsetGet($key)
    {
        return $this->items[$key];
    }

    /**
     * ArrayAccess' remove alias
     *
     * @param int $key
     * @return void
     */
    public function offsetUnset($key)
    {
        unset($this->items[$key]);
    }

    /**
     * IteratorAggregate' accessor.
     * Get an iterator for the items.
     *
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new ArrayIterator($this->items);
    }
}
