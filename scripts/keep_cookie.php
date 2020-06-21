<?php

require __DIR__ . '/../vendor/autoload.php';

use Medoo\Medoo;
use WeiyunBackend\Common\C;

$db = new Medoo([
    'database_type' => 'sqlite',
    'database_file' => C::SQLITE_FILE_PATH . C::CTCLOUD_SQLITE_DATA_FILE,
    'option' => []
]);

$cookie = getConfig($db, 'cookie');
if (empty($cookie)) {
    print 'empty cookie';
    return;
}
$newCookie = renewCookie($cookie);
if ($newCookie !== '') {
    updateConfig($db, ['value' => $newCookie], 'cookie');
    print $newCookie;
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

function updateConfig(Medoo $db, $toUpdate, $key)
{
    try {
        $db->update(
            'config', $toUpdate, ['name' => $key]
        );
    } catch (\Exception $e) {
    }
}

function renewCookie($cookie)
{
    $url = 'https://www.weiyun.com/disk';
    $domain = '';
    if (preg_match_all('/^https:\/\/(.*?)(\/.*?|$)/', $url, $match)) {
        $domain = $match[1][0];
    }
    $headerArr = [
        'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8',
        'Accept-Encoding' => 'compress',
        'Accept-Language' => 'zh-CN,zh;q=0.9',
        'Cache-Control' => 'max-age=0',
        'Cookie' => $cookie,
        'Referer' => 'https://www.weiyun.com/',
        'Upgrade-Insecure-Requests' => '1',
        'User-Agent' => 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/64.0.3282.119 Safari/537.36',
    ];

    $cookieArr = [];
    $cookieSpl = explode(';', $cookie);
    foreach ($cookieSpl as $c) {
        $cArr = explode('=', $c);
        $cookieArr[trim($cArr[0])] = trim($cArr[1]);
    }
    $cookieJar = \GuzzleHttp\Cookie\CookieJar::fromArray($cookieArr, $domain);
    $reqOptions = [
        'allow_redirects' => false,
        'headers' => $headerArr,
        'cookies' => $cookieJar,
    ];

    $client = new \GuzzleHttp\Client(['verify' => false]);
    $res = $client->get($url, $reqOptions);
    $cookies = $res->getHeader('Set-Cookie');
    foreach ($cookies as $rspC) {
        if (preg_match_all('/wyctoken=(.*?)(;|$)/', $rspC, $m)) {
            $ret = $m[1][0];
            if (preg_match_all('/wyctoken=(.*?)(;|$)/', $cookie, $m)) {
                $oldStr = $m[1][0];
                $replacement = 'wyctoken=' . $ret;
                if (($len = strlen($oldStr)) > 0 && $oldStr[$len - 1] === ';') {
                    $replacement .= ';';
                }
                return preg_replace('/wyctoken=(.*?)(;|$)/', $replacement, $cookie);
            }
            break;
        }
    }
    return '';
}

function mathRandom()
{
    return (mt_rand() / mt_getrandmax() * 1) . rand(10, 99);
}