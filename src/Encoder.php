<?php

namespace Fpay\Xid;

class Encoder
{
    const ENCODED_LEN = 20;

    const DECODED_LEN = 15;

    const RAW_LEN = 12;

    // encoding stores a custom version of the base32 encoding with lower case letters.
    const ENCODING = "0123456789abcdefghijklmnopqrstuv";

    private static $dec = null;

    public static function getDec()
    {
        if (!static::$dec) {
            $dec = array_fill(0, 256, 0xff);

            $l = strlen(static::ENCODING);
            for ($i = 0; $i < $l; $i++) {
                $dec[ord(static::ENCODING[$i])] = $i;
            }

            static::$dec = $dec;
        }

        return static::$dec;
    }

    /**
     * encode
     *
     * @param array $id
     * @return array
     */
    public static function encode($id)
    {
        $dst = [];

        $dst[0] = self::ENCODING[$id[0] >> 3];
        $dst[1] = self::ENCODING[($id[1] >> 6) & 0x1f | ($id[0] << 2) & 0x1f];
        $dst[2] = self::ENCODING[($id[1] >> 1) & 0x1f];
        $dst[3] = self::ENCODING[($id[2] >> 4) & 0x1f | ($id[1] << 4) & 0x1f];
        $dst[4] = self::ENCODING[$id[3] >> 7 | ($id[2] << 1) & 0x1f];
        $dst[5] = self::ENCODING[($id[3] >> 2) & 0x1f];
        $dst[6] = self::ENCODING[$id[4] >> 5 | ($id[3] << 3) & 0x1f];
        $dst[7] = self::ENCODING[$id[4] & 0x1f];
        $dst[8] = self::ENCODING[$id[5] >> 3];
        $dst[9] = self::ENCODING[($id[6] >> 6) & 0x1f | ($id[5] << 2) & 0x1f];
        $dst[10] = self::ENCODING[($id[6] >> 1) & 0x1f];
        $dst[11] = self::ENCODING[($id[7] >> 4) & 0x1f | ($id[6] << 4) & 0x1f];
        $dst[12] = self::ENCODING[$id[8] >> 7 | ($id[7] << 1) & 0x1f];
        $dst[13] = self::ENCODING[($id[8] >> 2) & 0x1f];
        $dst[14] = self::ENCODING[($id[9] >> 5) | ($id[8] << 3) & 0x1f];
        $dst[15] = self::ENCODING[$id[9] & 0x1f];
        $dst[16] = self::ENCODING[$id[10] >> 3];
        $dst[17] = self::ENCODING[($id[11] >> 6) & 0x1f | ($id[10] << 2) & 0x1f];
        $dst[18] = self::ENCODING[($id[11] >> 1) & 0x1f];
        $dst[19] = self::ENCODING[($id[11] << 4) & 0x1f];

        return $dst;
    }

    /**
     * decode
     *
     * @param array $src
     * @return array
     */
    public static function decode($src)
    {
        $id = [];
        $dec = static::getDec();

        $id[0] = $dec[$src[0]]<<3 | $dec[$src[1]]>>2;
        $id[1] = $dec[$src[1]]<<6 | $dec[$src[2]]<<1 | $dec[$src[3]]>>4;
        $id[2] = $dec[$src[3]]<<4 | $dec[$src[4]]>>1;
        $id[3] = $dec[$src[4]]<<7 | $dec[$src[5]]<<2 | $dec[$src[6]]>>3;
        $id[4] = $dec[$src[6]]<<5 | $dec[$src[7]];
        $id[5] = $dec[$src[8]]<<3 | $dec[$src[9]]>>2;
        $id[6] = $dec[$src[9]]<<6 | $dec[$src[10]]<<1 | $dec[$src[11]]>>4;
        $id[7] = $dec[$src[11]]<<4 | $dec[$src[12]]>>1;
        $id[8] = $dec[$src[12]]<<7 | $dec[$src[13]]<<2 | $dec[$src[14]]>>3;
        $id[9] = $dec[$src[14]]<<5 | $dec[$src[15]];
        $id[10] = $dec[$src[16]]<<3 | $dec[$src[17]]>>2;
        $id[11] = $dec[$src[17]]<<6 | $dec[$src[18]]<<1 | $dec[$src[19]]>>4;

        // keep the lowest 8 bits
        foreach ($id as $i => $v) {
            $id[$i] = $v & 0x00ff;
        }

        return $id;
    }
}
