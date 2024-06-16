<?php

use Lib\DB;

class ProductRepository
{
    public static function index(int $group): array
    {
        list($parentsConnectedWithChild, $parentGroups, $childGroups, $mainGroup) = self::sortGroups($group);

        // кажется, что при небольших объемах лучше 1 запросом вытащить все продукты и отсортировать, если позволяют ограничения по памяти, а не отделять запрос на подсчет товаров и сами товары.
        // весь экшен вынести из репа
        $productsGroupSort = self::countProductsByGroupsAll();

        // количество товаров в родительских группах
        foreach ($parentsConnectedWithChild as $parentsId => $parentsChildids) {
            $count = self::countProductsAccordingToGroups($parentsChildids, $productsGroupSort);
            foreach ($parentGroups as &$parentGroup) {
                if ($parentGroup['id'] == $parentsId) {
                    $parentGroup['count'] = $count;
                    break;
                }
            }
        }

        //количество товаров в 1 дочерних группах
        foreach ($childGroups as $keyOfChildGroup => $childGroup) {
            foreach ($childGroup as $key => $value) {
                if ($key == 'allIds') {
                    $count = self::countProductsAccordingToGroups($value, $productsGroupSort);
                    $childGroups[$keyOfChildGroup]['count'] = $count;
                    break;
                }
            }
        }

        $searchProductsWithIds = [$mainGroup['id']];

        //количество товаров в искомой группе
        $mainGroup['count'] = self::countProductsAccordingToGroups([$mainGroup['id']], $productsGroupSort);
        foreach ($childGroups as $key => $childGroup) {
            $searchProductsWithIds = array_merge($searchProductsWithIds, $childGroup['allIds']);
            unset($childGroups[$key]['allIds']);
            $mainGroup['count'] += $childGroup['count'];
        }

        $products = self::getProductsByGroups($searchProductsWithIds);

        return [$parentGroups, $childGroups, $mainGroup, $products];

    }

    private static function countProductsAccordingToGroups(array $groups, array $productGroups): int
    {
        $count = 0;
        foreach ($productGroups as $productGroup) {
            if (in_array($productGroup['id_group'], $groups)) {
                $count += $productGroup['count'];
            }
        }
        return $count;
    }

    // не тут должно быть
    private static function sortGroups(int $group): array
    {

        // это возможно закэшировать c какими-то флагами
        $tree = self::buildTreeOfGroups();

        list($childGroupsAndSearching) = ArrayServices::getPartOfArrayByKeyRecursive($tree, ['groupInfo', 'id'], $group, null, false, ['groupInfo', 'id']);

        if (empty($childGroupsAndSearching)) {
            throw new Exception('Wrong group');
        }

        //getting parent groupsAndAllChildsOfIt
        list($parentGroups) = self::getParentGroups($group, $tree);
        $parentGroupsIds = array_column($parentGroups, 'id');
        list($parentsConnectedWithChild) = !empty($parentGroupsIds) ? self::getChildsForGroup($parentGroupsIds, $tree) : [[], []];

        //getting first children and main group
        $mainGroup = [];
        $childGroups = [];
        foreach ($childGroupsAndSearching as $groups) {
            foreach ($groups as $key => $value) {
                if ($key == 'groupInfo') {
                    $groupsForChildQuery[] = $value['id'];
                    $mainGroup = [
                        'id' => $value['id'],
                        'name' => $value['name']
                    ];
                    continue;
                } else {
                    if (is_array($value)) { // По идее должно быть лишним
                        foreach ($value as $keyOfValue => $valueOfValue) {
                            if ($keyOfValue == 'groupInfo') {
                                $childGroups[$valueOfValue['id']] = [
                                    'id' => $valueOfValue['id'],
                                    'name' => $valueOfValue['name'],
                                    'allIds' => [$valueOfValue['id']]
                                ];
                            } else {
                                if (is_array($valueOfValue)) {
                                    $childGroups[array_key_last($childGroups)]['allIds'] = array_merge($childGroups[array_key_last($childGroups)]['allIds'], ArrayServices::getCertainValuesFromArrayRecursive($valueOfValue, 'id'));
                                }
                            }
                        }
                    }
                }
            }
        }

        return [$parentsConnectedWithChild, $parentGroups, $childGroups, $mainGroup];
    }

    // не тут должно быть
    private static function getParentGroups(int $group, array $tree, array $parentGroups = [], $find = false): array
    {
        foreach ($tree as $key => $value) {
            if ($key == $group) {
                $find = true;
                break;
            }
            if (!empty($value) && is_array($value)) {    // сомнительно
                $lastKey = array_key_last($value);
                foreach ($value as $keyOfValue => $valueOfValue) { // нет времени
                    if ($keyOfValue == 'groupInfo') {
                        $parentGroups[] = [
                            'id' => $valueOfValue['id'], 
                            'name' => $valueOfValue['name']
                        ];
                    } else {
                        list($parentGroups, $find) = self::getParentGroups($group, [$keyOfValue => $valueOfValue], $parentGroups);
                        if ($find) break 2;
                    }
                    if ($lastKey == $keyOfValue && !$find) {
                        array_pop($parentGroups);
                    }
                }
            }
        }

        return [$parentGroups, $find];
    }

    // не тут должно быть
    private static function getChildsForGroup( array $parents, array $tree, array $arrayToReturn = []): array
    {
        foreach ($tree as $key => $value) {
            if (empty($parents)) break;
            if (in_array($key, $parents)) {
                $arrayToReturn[$key] =  ArrayServices::getCertainValuesFromArrayRecursive($value, 'id');
                unset($parents[array_search($key, $parents)]);
            }
            if (!empty($value) && is_array($value)){
                foreach ($value as $keyOfValue => $valueOfValue) {
                    if (empty($parents)) break;
                    if (is_array($valueOfValue)) {
                        list($arrayToReturn, $parents) = self::getChildsForGroup($parents, [$keyOfValue => $valueOfValue], $arrayToReturn);
                    }
                }
            }
        }

        return [$arrayToReturn, $parents];
    }

    private static function getElemFromBuffer(array &$buffer, array &$groups, array $group): void
    {
        $find = false;
        foreach ($buffer as $key => $groupBuffer) {
            if ($groupBuffer['id_parent'] == $group['id']) {
                $find = true;
                $groups[] = $groupBuffer;
                unset($buffer[$key]);
            }
        }
        if ($find) {
            self::getElemFromBuffer($buffer, $groups, $group);
        }
    }

    private static function buildTreeOfGroups(): array
    {
        $groupsFromBd = DB::getQuery("SELECT * FROM `groups` ORDER BY `id_parent` ASC");
        $groups = [];
        $groupsBuffer = [];

        //если вдруг каким-то образом материнская группа создалась позже нашей группы
        $count = 0;
        foreach ($groupsFromBd as $group) {
            $count++;
            if ($group['id'] == DEFAULT_GROUP) {
                $groups[] = $group;
                continue;
            }
            in_array($group['id_parent'], array_column($groups, 'id')) ? $groups[]=$group : $groupsBuffer[] = $group;
            self::getElemFromBuffer($groupsBuffer, $groups, $group);
        }

        $treeOfGroups = [DEFAULT_GROUP => []];
        $mainGroup = null;
        foreach ($groups as $key => $group) {
            if ($group['id'] == DEFAULT_GROUP) {
                $mainGroup = $group;
                unset($groups[$key]);
                break;
            }
        }
        if (empty($mainGroup)) {
            throw new Exception('Wrong main group');
        }
        $treeOfGroups[DEFAULT_GROUP] = [
            'groupInfo' => $mainGroup
        ];
        foreach ($groups as $group) {
            list($treeOfGroups) = ArrayServices::assignElemByKeyToTheArray($treeOfGroups, $group, 'id_parent', 'id');
        }
        return $treeOfGroups;
    }

    private static function getProductsByGroups(array $groups): array
    {
        $placeholders = str_repeat ('?, ',  count ($groups) - 1) . '?';
        return DB::getQuery("SELECT name FROM `products` WHERE id_group IN ($placeholders)", $groups);
    }


    private static function countProductsByGroups(array $groups): array
    {
        $placeholders = str_repeat ('?, ',  count ($groups) - 1) . '?';
        return DB::getQuery("SELECT id_group, COUNT(*) as count FROM `products` WHERE id_group IN ($placeholders) GROUP BY id_group", $groups);
    }
    
    private static function countProductsByGroupsAll(): array
    {
        return DB::getQuery("SELECT id_group, COUNT(*) as count FROM `products` GROUP BY id_group");
    }
}