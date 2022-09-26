<?php

namespace Synthetic;

use PHPUnit\Framework\Assert;
use Illuminate\Support\Traits\Macroable;

class TestableSynthetic
{
    use Macroable { __call as macroCall; }

    protected $target;
    protected $snapshot;
    protected $data;
    protected $effects;

    function __construct($target) {
        $return = app('synthetic')->synthesize($target);

        $this->target = $target;
        $this->snapshot = $return['snapshot'];
        $this->data = $this->extractData($this->snapshot['data']);
        $this->effects = $return['effects'];
    }

    function set($key, $value)
    {
        $return = app('synthetic')->update($this->snapshot, [$key => $value], $calls = []);

        $this->target = $return['target'];
        $this->snapshot = $return['snapshot'];
        $this->data = $this->extractData($this->snapshot['data']);
        $this->effects = $return['effects'];

        return $this;
    }

    function call($method, $params)
    {
        $return = app('synthetic')->update($this->snapshot, [$key => $value], $calls = []);

        $this->target = $return['target'];
        $this->snapshot = $return['snapshot'];
        $this->data = $this->extractData($this->snapshot['data']);
        $this->effects = $return['effects'];

        return $this;
    }

    function extractData($payload) {
        $value = $this->isSyntheticTuple($payload) ? $payload[0] : $payload;

        if (is_array($value)) {
            foreach ($value as $key => $child) {
                $value[$key] = $this->extractData($child);
            }
        }

        return $value;
    }

    function isSyntheticTuple($payload) {
        return is_array($payload)
            && count($payload) === 2
            && isset($payload[1]['s']);
    }

    function assert($value)
    {
        Assert::assertTrue(value($value, $this->data, $this->target));

        return $this;
    }

    function assertEquals($expected, $value)
    {
        Assert::assertEquals($expected, value($value, $this->data, $this->target));

        return $this;
    }

    public function __get($property)
    {
        //
    }

    public function __call($method, $params)
    {
        if (static::hasMacro($method)) {
            return $this->macroCall($method, $params);
        }

        //

        return $this;
    }
}
