<?php

class ArrayServices
{

    public static function assignElemByKeyToTheArray(array $array, array $elem, string|int $keyToAssign, string|int $keyToWriteIn = 0): array
    {
        $elemWasFinded = false;

        if (!isset( $elem[$keyToAssign]) ) {
            throw new Exception('Wrong key in searching element in assignElemByKeyToTheArray function');
        }
        foreach ($array as $key => $value) {
            if ( $key == $elem[$keyToAssign] ) {
                $array[$key][$elem[$keyToWriteIn]] = [
                    'groupInfo' => $elem
                ];
                $elemWasFinded = true;
                break;
            }

            if (!empty($value) && is_array($value)) { // is_array по идеи должно быть лишним
                list($array[$key], $elemWasFinded) = self::assignElemByKeyToTheArray($array[$key], $elem, $keyToAssign, $keyToWriteIn);
                if ($elemWasFinded) break;
            }
        }

        return [$array, $elemWasFinded];
    }

    public static function addArrayToTheKey(array $array, array $elem, $keyInElemToSearchInArray, $keyToWriteIn): array
    {
        $wasFinded = false;
        
        foreach ($array as $key => $value) {

            if ( $key == $elem[$keyInElemToSearchInArray] ) {
                $array[$key][$keyToWriteIn][] = $elem;
                $wasFinded = true;
                break;
            }

            if ( is_array($value) && !empty($value) ) {
                [$array[$key], $wasFinded] = self::addArrayToTheKey($array[$key], $elem, $keyInElemToSearchInArray, $keyToWriteIn);
                if ($wasFinded) break;
            }
        }

        return [$array,$wasFinded];
    }

    public static function getPartOfArrayByKeyRecursive(array $array, array $keys, $searchingValue, string|int $firstKeyOfArray = null, bool $arrayWasFinded = false, $realKeys = []): array
    {

        $arrayToReturn = [];
        foreach ($array as $keyInArray => $valueInArray) {
            foreach ($keys as $key) {
                if ($arrayWasFinded) break 2;
                if ( is_array($valueInArray) && array_key_exists($key, $valueInArray) ) {
                    if (count($keys) == 1) {

                        if ($valueInArray[$key] == $searchingValue) {
                            $firstKeyOfArray !== null ? $arrayToReturn[$firstKeyOfArray] = $array : $arrayToReturn[] = $array;
                            $arrayWasFinded = true;
                        }
                    } else {
                        if (is_array($array[$keyInArray])) {
                            list($arrayToReturn, $arrayWasFinded) = self::getPartOfArrayByKeyRecursive($array[$keyInArray], array_slice($keys, 1), $searchingValue, $keyInArray, $arrayWasFinded, $realKeys);
                        } else {
                            return [ [] ,false];
                        }
                    }
                } else {
                    if (is_array($array[$keyInArray])){
                        list($arrayToReturn, $arrayWasFinded) = self::getPartOfArrayByKeyRecursive([$keyInArray => $array[$keyInArray]], $realKeys, $searchingValue, $keyInArray, $arrayWasFinded, $realKeys);
                    }
                }
                break;
            }
        }

        return [$arrayToReturn, $arrayWasFinded];
    }

    public static function getCertainValuesFromArrayRecursive(array $array, int|string $keyToExtract = null, array $return = []): array
    {
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $return = self::getCertainValuesFromArrayRecursive($value, $keyToExtract, $return);
            } else {
                if ($key == $keyToExtract) {
                    $return[] = $value;
                }
            }
        }
        return $return;
    }

    public static function arrayKeysRecursive($array) {
        $keys = array_keys($array);
        foreach ($array as $i)
            if (is_array($i))
            $keys = array_merge($keys, self::arrayKeysRecursive($i));
        
        return $keys;
    }
}