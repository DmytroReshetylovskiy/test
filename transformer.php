<?php

/**
 * @param string $partOfAddress
 * @return string
 */
function checkRegion(string $partOfAddress): string
{
    $value = '';

    // [0] => "Одеська обл." || "111, Одеська обл.", [1] => "Одеська ", [2] => "обл"
    if (preg_match("#[,?]? ?(\W+).?(обл).?#", $partOfAddress, $match)) {
        $value = trim($match[1]);
    }

    // [0] => "обл. Одеська, місто Одеса,", [1] => "обл ", [2] => "Одеська", [3] => " місто Одеса"
    if (preg_match("#(обл). ?(\D+),(.*),#", $partOfAddress, $match) && empty($value)) {
        $value = trim($match[2]);
    }

    return $value;
}

/**
 * @param string $partOfAddress
 * @return string
 */
function checkDistrict(string $partOfAddress): string
{
    $value = '';

    // [0] => "	 Іллічівський район", [1] => "Іллічівський ", [2] => "район"
    if (preg_match("# ?(\W+) ?(район)#", $partOfAddress, $match)) {
        $value = trim($match[1]);
    }

    // [0] => "район Іллічівський.", [1] => "район ", [2] => "Іллічівський"
    if (preg_match("#(район) ?(\W+)\.#", $partOfAddress, $match) && empty($value)) {
        $value = trim($match[2]);
    }

    return $value;
}

/**
 * @param string $partOfAddress
 * @return string
 */
function checkStreet(string $partOfAddress): string
{
    $value = '';

    // [0] => "	 ВУЛ.СЕРЕДНЯ б", [1] => "ВУЛ ", [2] => "СЕРЕДНЯ", [3] => "б"
    if (preg_match("#,? ?(ВУЛИЦЯ|ВУЛ|вул|вулиця).? ?(\D+) (\D)#", $partOfAddress, $match)) {
        $value = trim($match[2]);
    }

    // [0] => " Іллічівський район СЕРЕДНЯ ВУЛИЦЯ", [1] => " Іллічівський район ", [2] => "СЕРЕДНЯ", [3] => "ВУЛИЦЯ"
    if (preg_match("#(.*),? ? (\D+) ?(ВУЛИЦЯ|ВУЛ|вул|вулиця)#", $partOfAddress, $match) && empty($value)) {
        $value = trim($match[2]);
    }

    return $value;
}

/**
 * @param string $partOfAddress
 * @return string
 */
function checkNum(string $partOfAddress): string
{
    $value = '';

    // [0] => " кв. 5", [1] => "кв ", [2] => "5"
    if (preg_match("# ?(кв|КВ|квартира|КВАРТИРА).? ?(\d+)#", $partOfAddress, $match)) {
        $value = trim($match[2]);
    }

    // [0] => " 29 кв.", [1] => "29", [2] => "кв"
    if (preg_match("# ?(\d+) ?(кв|КВ|квартира|КВАРТИРА).?#", $partOfAddress, $match) && empty($value)) {
        $value = trim($match[1]);
    }

    return $value;
}

/**
 * @param string $partOfAddress
 * @return string
 */
function checkHouse(string $partOfAddress): string
{
    $value = '';

    // [0] => "	 буд. 29", [1] => "буд ", [2] => "29"
    if (preg_match("#,? ?(буд|будинок|БУД|БУДИНОК).? ?(\d+)#", $partOfAddress, $match)) {
        $value = trim($match[2]);
    }

    // [0] => " 29 буд", [1] => "29", [2] => "буд"
    if (preg_match("# ?(\d+)? ?(буд|будинок|БУД|БУДИНОК)#", $partOfAddress, $match) && empty($value)) {
        $value = trim($match[1]);
    }

    return $value;
}

/**
 * @param string $partOfAddress
 * @return string
 */
function checkPlace(string $partOfAddress): string
{
    $value = '';

    // [0] => "місто Одеса,", [1] => "місто  ", [2] => "Одеса"
    if (preg_match("#(місто ) ?(.*),?#", $partOfAddress, $match)) {
        $value = trim($match[2]);
    }

    // [0] => ", Одеса місто,", [1] => "Одеса", [2] => "місто"
    if (preg_match("#, (\W+).?(місто),#", $partOfAddress, $match) && empty($value)) {
        $value = trim($match[1]);
    }

    return $value;
}

/**
 * @param string $address
 * @return array
 */
function helper(string $address): array
{
    $parts = [];
    /** @var array $explodeAddress */
    $explodeAddress = explode(',', $address);
    if (is_array($explodeAddress)) {
        foreach ($explodeAddress as $address)
        {
            if (preg_match("#(обл)#", $address)) {
                $parts['region'] = checkRegion($address);
            }

            if (preg_match("#(місто)#", $address)) {
                $parts['place'] = checkPlace($address);
            }

            if (preg_match("#(район)#", $address)) {
                $parts['district'] = checkDistrict($address);
            }

            if (preg_match("#(ВУЛ|вул|ВУЛИЦЯ|вулиця)#", $address)) {
                $parts['street'] = checkStreet($address);
            }

            if (preg_match("#(буд|будинок|БУД|БУДИНОК)#", $address)) {
                $parts['house'] = checkHouse($address);
            }
//
            if (preg_match("#(кв|КВ|квартира|КВАРТИРА)#", $address)) {
                $parts['num'] = checkNum($address);
            }
        }
    }

    return $parts;
}

/**
 * @param string $address
 * @return void
 */
function transform(string $address): void
{
    $parts = helper($address);

    $result = [
        'parts' => $parts,
    ];

    echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;
}

//example "Одеська обл., місто Одеса, Іллічівський район ВУЛ.СЕРЕДНЯ буд. 29 кв. 5"
transform($argv[1]);