<?php

namespace AjCastro\ScribeTdd\Tests\ParamParsers;

class BodyParamParser
{
    public static function parse(array $data)
    {
        $processArray = function ($array, string $prefix = '') use (&$processArray) {
            $result = [];
            foreach ($array as $key => $value) {
                if (is_scalar($value)) {
                    $result[$prefix . $key] = [
                        'type' => gettype($value),
                        'description' => '',
                        'example' => $value,
                        'required' => false,
                    ];
                } elseif (is_array($value) && !\Illuminate\Support\Arr::isAssoc($value)) {
                    $result[$prefix . $key] = [
                        'type' => gettype(head($value)) . '[]',
                        'description' => '',
                        'example' => $value,
                        'required' => false,
                    ];
                } elseif (is_array($value)) {
                    $result += $processArray($value, "$prefix$key.");
                }
            }

            return $result;
        };
        return $processArray($data);
    }
}
