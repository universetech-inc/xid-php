<?php

namespace Fpay\Xid;

class Xid
{
    /**
     * @var array
     */
    public $value;

    /**
     * __construct
     *
     * @param array $id
     */
    public function __construct($id)
    {
        $this->value = $id;
    }

    /**
     * pid
     *
     * @return int
     */
    public function pid()
    {
        return ($this->value[7] << 8 | $this->value[8]);
    }

    /**
     * counter
     *
     * @return int
     */
    public function counter()
    {
        return ($this->value[9] << 16 | $this->value[10] << 8 | $this->value[11]);
    }

    /**
     * machine
     *
     * @return string
     */
    public function machine()
    {
        return implode(array_map(function($v) {
            return chr($v);
        }, array_slice($this->value, 4, 3)));
    }

    /**
     * time
     *
     * @return int
     */
    public function time()
    {
        return ($this->value[0] << 24 | $this->value[1] << 16 | $this->value[2] << 8 | $this->value[3]);
    }

    /**
     * Encode to base32 hex string
     *
     * @return string
     */
    public function encode()
    {
        $text = Encoder::encode($this->value);
        return implode($text);
    }

    public function __toString()
    {
        return $this->encode();
    }
}
