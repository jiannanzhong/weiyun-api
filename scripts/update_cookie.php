<?php

require __DIR__ . '/../vendor/autoload.php';

use Medoo\Medoo;
use WeiyunBackend\Common\C;

$cookieData = require_once('cookie_data.php');

$cookie = $cookieData['cookie'];

$db = new Medoo([
    'database_type' => 'sqlite',
    'database_file' => C::SQLITE_FILE_PATH . C::CTCLOUD_SQLITE_DATA_FILE,
    'option' => []
]);

updateConfig($db, ['value' => $cookie], 'cookie');

print_r([
    'cookie' => getConfig($db, 'cookie')
]);

function updateConfig(Medoo $db, $toUpdate, $key)
{
    try {
        $db->update(
            'config', $toUpdate, ['name' => $key]
        );
    } catch (\Exception $e) {
    }
}

function getConfig(Medoo $db, $key)
{
    try {
        $config = $db->get(
            'config', 'value', ['name' => $key]
        );
        if (empty($config)) {
            $config = '';
        }
        return $config;
    } catch (\Exception $e) {
        return '';
    }
}