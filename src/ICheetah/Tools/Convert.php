<?php

namespace ICheetah\Tools;

class Convert
{
    
    
    //=============== Type name convertor ===================
    public static function typeNameConvert($strName)
    {
        $retVal = null;
        switch (strtolower($strName)) {
            case "int":
            case "integer":
                $retVal = Core::VAR_INT;
                break;
            case "string":
                $retVal = Core::VAR_STRING;
                break;
            case "float":
            case "double":
                $retVal = Core::VAR_FLOAT;
                break;
            case "bool":
            case "boolean":
                $retVal = Core::VAR_BOOL;
                break;
        }
        
    }
    //=============== Type name convertor ===================

    
    public static function toInt ($value)
    {
        if (!is_int($value)){
            $value = self::numberUnformat($value);
            $value = filter_var($value, FILTER_SANITIZE_NUMBER_INT);
            return intval($value);
        } else {
            return $value;
        }
    }
    
    public static function toInteger ($value)
    {
        return self::toInt($value);
    }
    
    public static function toFloat($value)
    {
        if (!is_float($value)){
            $value = filter_var ($value, FILTER_SANITIZE_NUMBER_FLOAT);
            return floatval($value);
        } else {
            return $value;
        }
    }

    public static function toString ($value)
    {
        if (!is_string($value)){
            return strval($value);
        } else {
            return $value;
        }        
        return strval($value);
    }
    
    public static function toBool ($value)
    {
        return (bool)$value;
    }
    
    public static function toBoolean ($value)
    {
        return self::toBool($value);
    }

    public static function toSafeHTML ($value)
    {
        return htmlentities($value, ENT_QUOTES, "UTF-8", false);
    }
    
    public static function toHTML ($value)
    {
        return html_entity_decode($value, ENT_QUOTES, "UTF-8");        
    }
    
    public static function toRange ($value, $min, $max)
    {
        $value = self::toInt($value);
        if ($value < $min) {
            $value = $min;
        } elseif ($value > $max) {
            $value = $max;
        }
        return $value;
    }
    
    public static function toAlphaNumeric ($value)
    {
        $regExpr = new RegExpr(RegExpr::ALPHA_NUMERIC);
        $matches = array();
        $regExpr->match($value, false, $matches);
        return $matches[0];
    }
    
    /**
     * Converts a value to int and then string.
     * @param int $value
     * @return string
     */
    public static function toStringInt ($value)
    {
        return self::toString(self::toInt($value));
    }

    public static function toArray ($value)
    {
        if (!$value) return array();
        if (is_array($value)) {
            return $value;
        } else {
            return array($value);
        }
    }
    
    public static function toUTF8 ($value)
    {
        if (function_exists("mb_convert_encoding")){
            if (is_array($value)){
                $value = array_map(array(self, "toUTF8"), $value);
            } else {
                mb_convert_encoding($value, "UTF-8");                
            }
        }
    }

    public static function strToArray ($strValue, $delimiter="\n")
    {
        if (!$strValue) return array();
        $strValue = trim($strValue);
        $list = explode($delimiter, $strValue);
        $newList = array();
        foreach ($list as $value) {
            $itemArr = explode("=", $value);
            if (count($itemArr) < 2) {
                $newList[] = $itemArr[0];
            } else {
                $itemKey = trim($itemArr[0]);
                $itemValue = trim($itemArr[1]);
                $newList[$itemKey] = $itemValue;
            }
        }
        return $newList;
    }
    
    /**
    * (PHP 5 &gt;= 5.2.0, PECL json &gt;= 1.2.0)<br/>
    * Returns the JSON representation of a value
    * @link http://php.net/manual/en/function.json-encode.php
    * @param mixed $value <p>
    * The <i>value</i> being encoded. Can be any type except
    * a resource.
    * </p>
    * <p>
    * This function only works with UTF-8 encoded data.
    * </p>
    * @param int $options [optional] <p>
    * Bitmask consisting of <b>JSON_HEX_QUOT</b>,
    * <b>JSON_HEX_TAG</b>,
    * <b>JSON_HEX_AMP</b>,
    * <b>JSON_HEX_APOS</b>,
    * <b>JSON_NUMERIC_CHECK</b>,
    * <b>JSON_PRETTY_PRINT</b>,
    * <b>JSON_UNESCAPED_SLASHES</b>,
    * <b>JSON_FORCE_OBJECT</b>,
    * <b>JSON_UNESCAPED_UNICODE</b>.
    * </p>
    * @return string a JSON encoded string on success or <b>FALSE</b> on failure.
    */
    public static function jSonEncode ($value)
    {
        return json_encode($value, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);
    }
    
    /**
    * (PHP 5 &gt;= 5.2.0, PECL json &gt;= 1.2.0)<br/>
    * Decodes a JSON string
    * @link http://php.net/manual/en/function.json-decode.php
    * @param string $json <p>
    * The <i>json</i> string being decoded.
    * </p>
    * <p>
    * This function only works with UTF-8 encoded data.
    * </p>
    * @param bool $assoc [optional] <p>
    * When <b>TRUE</b>, returned objects will be converted into
    * associative arrays.
    * </p>
    * @param int $depth [optional] <p>
    * User specified recursion depth.
    * </p>
    * @param int $options [optional] <p>
    * Bitmask of JSON decode options. Currently only
    * <b>JSON_BIGINT_AS_STRING</b>
    * is supported (default is to cast large integers as floats)
    * </p>
    * @return mixed the value encoded in <i>json</i> in appropriate
    * PHP type. Values true, false and
    * null (case-insensitive) are returned as <b>TRUE</b>, <b>FALSE</b>
    * and <b>NULL</b> respectively. <b>NULL</b> is returned if the
    * <i>json</i> cannot be decoded or if the encoded
    * data is deeper than the recursion limit.
    */
    public static function jSonDecode ($value, $assoc = false, $depth = 512, $options = 0)
    {
        return json_decode($value, $assoc, $depth, $options);
    }
    
    public static function toPersianDigit($text, $numberFormat = false)
    {
        $text = $numberFormat? self::numberFormat($text) : $text;
        $text = self::toString($text);
        $en_num = array("0","1","2","3","4","5","6","7","8","9");
        $fa_num = array("۰","۱","۲","۳","۴","۵","۶","۷","۸","۹");
        return str_replace($en_num, $fa_num, $text);
    }
    
    public static function numberFormat($number, $decimals = 0, $dec_point = ".", $thousands_sep = ",")
    {
        $text = self::toString($number);
        return number_format($number, $decimals, $dec_point, $thousands_sep);
    }
    
    public static function numberUnformat($text, $thousands_sep = ",")
    {
        $text = self::toString($text);
        return str_replace($thousands_sep, "", $text);
    }
    
    public static function quoteStringValues(array $values)
    {
        foreach ($values as $key => $value) {
            if (is_string($value)) {
                $values[$key] = "'$value'";
            }
        }
        return $values;
    }
    
}

?>