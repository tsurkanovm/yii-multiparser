<?php
/**
 * Created by PhpStorm.
 * User: Cibermag
 * Date: 27.08.2015
 * Time: 13:36
 */

namespace yii\multiparser;

// @todo add comments
class Encoder
{
    /** @var out encoding charset */
    public static $out_charset = 'UTF-8';
    /** @var out encoding charset */
    public static $in_charset = 'windows-1251';

    public static function encodeFile($in_charset, $out_charset, $filePath)
    {

        $old_content = file_get_contents($filePath);
        $encode_content = self::encodeString( $old_content, $in_charset, $out_charset );
        $file = @fopen($filePath, "w");
        fwrite($file, $encode_content);
        @fclose($file);
    }

    public static function encodeArray( $array, $in_charset = '', $out_charset = '')
    {
        if ($in_charset)
        self::$in_charset = $in_charset;

        if ($out_charset)
        self::$out_charset = $out_charset;

        $result = array_map(
            function ($value) {

                return self::encodeString( $value, self::$in_charset, self::$out_charset );

            },
            $array);

        return $result;
    }

    public static function encodeString( $source, $in_charset = '', $out_charset = '' ){

        if ($in_charset)
            self::$in_charset = $in_charset;

        if ($out_charset)
            self::$out_charset = $out_charset;

        return iconv( self::$in_charset, self::$out_charset, $source );

    }
}