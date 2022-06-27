<?php

namespace Fpay\Xid;

/**
 * Generator generates new Xid object
 */
class Generator
{
    /**
     * @var int
     */
    private $objectIdCounter;

    /**
     * @var array
     */
    private $machineId;

    /**
     * @var int
     */
    private $pid;

    /**
     * @var boolean
     */
    private static $_instance;

    public function __construct($options = [])
    {
        if (!array_key_exists("cli", $options)) {
            $options["cli"] = php_sapi_name() == 'cli';
        }

        if (!array_key_exists("apcu", $options)) {
            $options["apcu"] = function_exists('apcu_inc');
        }

        $this->machineId = $this->getMachineId();
        $this->pid = posix_getpid();
        $this->objectIdCounter = $this->objectIdGenerator($this->pid, $options);
    }

    /**
     * Do the generation job
     *
     * @return Xid
     */
    public function generate()
    {
        $now = time();
        $id = [];

        $id[0] = ($now >> 24) & 0xff;
        $id[0] = ($now >> 24) & 0xff;
        $id[1] = ($now >> 16) & 0xff;
        $id[2] = ($now >> 8) & 0xff;
        $id[3] = ($now) & 0xff;

        $id[4] = $this->machineId[0];
        $id[5] = $this->machineId[1];
        $id[6] = $this->machineId[2];

        $id[7] = ($this->pid >> 8) & 0xff;
        $id[8] = ($this->pid) & 0xff;

        $i = $this->objectIdCounter->send(null);

        $id[9] = ($i >> 16) & 0xff;
        $id[10] = ($i >> 8) & 0xff;
        $id[11] = ($i) & 0xff;

        return new Xid($id);
    }

    /**
     * Creates a new Xid instance
     *
     * @return Xid
     */
    public static function create()
    {
        if (!static::$_instance) {
            static::$_instance = new Generator();
        }

        return static::$_instance->generate();
    }

    /**
     * Reads an ID from its string representation
     *
     * @param string $str
     * @throws Exception
     * @return Xid
     */
    public static function fromString($str)
    {
        if (strlen($str) !== Encoder::ENCODED_LEN) {
            throw new Exception("invalid id");
        }

        $dec = Encoder::getDec();

        $src = array_map(function($v) {
            return ord($v);
        }, str_split($str));

        foreach ($src as $c) {
            if ($dec[$c] === 0xff) {
                throw new Exception("invalid id");
            }
        }

        $id = Encoder::decode($src);
        return new Xid($id);
    }

    /**
     * Get the machine id. If it fails to get the host name, uses openssl to generate a random name.
     *
     * @return array
     */
    private function getMachineId()
    {
        $name = gethostname();
        if ($name === false) {
            return $this->ord(random_bytes(3));
        } else {
            return $this->ord(substr(md5($name, true), 0, 3));
        }
    }

    /**
     * Split input string into ASCII array
     *
     * @param string $str
     * @param array
     */
    private function ord($str)
    {
        return array_map(function($v) {
            return ord($v);
        }, str_split($str));
    }

    /**
     * Get a counter.
     *
     * @param int $pid
     * @param array $options
     * @return \Generator
     */
    private function objectIdGenerator($pid, $options)
    {
        // we are in cli mode, there is no need to share counter
        if ($options["cli"]) {
            return $this->loopCounter();
        }

        // share counter across requests in the same php-fpm process
        if ($options["apcu"]) {
            return $this->apcuCounter($pid);
        }

        // if there is no apcu extension, fallback to simple looping
        return $this->loopCounter();
    }

    /**
     * Create a counter generator which uses simple looping to generate id.
     *
     * @return \Generator
     */
    private function loopCounter() {
        for ($i = mt_rand(); ; $i++) {
            yield $i;
        }
    }

    /**
     * Create a counter generator which uses apcu to share counter across requests.
     *
     * @return \Generator
     */
    private function apcuCounter($pid) {
        $key = "xid.counter:{$pid}";
        if (!apcu_exists($key)) {
            apcu_store($key, mt_rand());
        }

        while (true) {
            $i = apcu_fetch($key);
            yield $i;
            apcu_inc($key, 1);
        }
    }
}
