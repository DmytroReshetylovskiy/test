<?php

require_once 'config/databases.php';

//connect to databases
$mysql = new mysqli($db['host'], $db['username'], $db['password'], $db['name']);

if ($mysql->connect_errno) {
    exit("Не вышло соединиться с бд: " . $mysql->connect_error . PHP_EOL);
}

$filePath = "file/17.1-EX_XML_EDR_UO_28.11.2019.xml";
if (!file_exists($filePath)) {
    exit("Не удалось открыть файл по пути " . $filePath . PHP_EOL);
}

$stream = simplexml_load_file($filePath);
$iterations = 0;

$mysql->begin_transaction(MYSQLI_TRANS_START_READ_WRITE);

while ($data = $stream->RECORD[$iterations]) {

    $name = $mysql->real_escape_string($data->NAME);
    $shortName = !empty($data->SHORT_NAME) ? $mysql->real_escape_string($data->SHORT_NAME) : null ;
    $edrpou = (string)$data->EDRPOU;
    $address = $mysql->real_escape_string($data->ADDRESS);
    $boss = !empty($data->BOSS) ? $mysql->real_escape_string($data->BOSS) : null;
    $kved = !empty($data->KVED) ? $mysql->real_escape_string($data->KVED) : null;
    $stan = (string)$data->STAN;

    $insertResult = $mysql->query("INSERT INTO `edr_uo` (
        `name`,
        `short_name`,
        `edrpou`,
        `address`,
        `boss`,
        `kved`,
        `stan`
    ) VALUES (
        '$name',
        '$shortName',
        '$edrpou',
        '$address',
        '$boss',
        '$kved',
        '$stan'
    )");

    if ($insertResult) {
        $edr_uo_id = $mysql->insert_id;

        if ($data->FOUNDERS->count()) {
            $founderI = 0;

            while ($data->FOUNDERS->FOUNDER[$founderI]) {
                $title = $mysql->real_escape_string($data->FOUNDERS->FOUNDER[$founderI]);

                if (!empty($title)) {
                    $re = $mysql->query("INSERT INTO `founders` (
                        `erd_uo_id`,
                        `title`
                    ) VALUES (
                        '$edr_uo_id',
                        '$title'
                    )");
                }

                $founderI++;
            }

        }

        if(($iterations % 5) == 0) {
            $mysql->commit();
        }
    } else {
        printf('Не вышло записать ' . $name);
    }

    $iterations++;
}

$mysql->commit();
$mysql->close();