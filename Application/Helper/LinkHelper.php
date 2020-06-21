<?php

namespace WeiyunBackend\Helper;

use WeiyunBackend\Common\C;
use WeiyunBackend\Common\D;

class LinkHelper
{
    public static function getRealLinkByFileRecord($fileRecord)
    {
        $file = self::getFileInfoByFilename($fileRecord);
        if (empty($file)) {
            return [];
        } else {
            return self::getDownloadLinkByFileInfo($file['pDirKey'], $file['fileId']);
        }
    }

    private static function mathRandom()
    {
        return (mt_rand() / mt_getrandmax() * 1) . rand(10, 99);
    }

    private static function getFileInfoByFilename($filename)
    {
        $file = [];
        if (strlen($filename) >= 3) {
            $pDirId = C::WEIYUN_ROOT_PDIR_KEY;
            $dirId = C::WEIYUN_ROOT_DIR_KEY;
            for ($i = 0; $i <= 3; $i++) {
                if (strlen($dirId) === 0) {
                    break;
                }
                if ($i >= 3) {
                    $file = self::getFileInfoFromDirListByFilename(self::getListJson($pDirId, $dirId), $filename, true);
                } else {
                    $listJsonRet = self::getListJson($pDirId, $dirId);
                    $pDirId = $dirId;
                    $dir = self::getFileInfoFromDirListByFilename($listJsonRet, $filename[$i], false);
                    $dirId = $dir['fileId'];
                }
            }
        }

        return $file;
    }

    private static function getListJson($pDirId, $dirId)
    {
        $d = D::getInstance();
        $cookie = $d->getConfig('cookie');
        if (empty($cookie)) {
            return [];
        }

        $sKey = '';
        $tk = '';
        if (preg_match_all('/skey=(.*?)(;|$)/', $cookie, $m)) {
            $sKey = $m[1][0];
        }
        if (preg_match_all('/wyctoken=(.*?)(;|$)/', $cookie, $m)) {
            $tk = $m[1][0];
        }

        $url = 'https://www.weiyun.com/webapp/json/weiyunQdisk/DiskDirBatchList?refer=chrome_windows&g_tk=' . $tk . "&r=" . self::mathRandom();
        $headerArr = [
            'accept' => 'application/json, text/plain, */*',
            'accept-encoding' => 'compress',
            'accept-language' => 'zh-CN,zh;q=0.9',
            'content-length' => '671',
            'content-type' => 'application/json;charset=UTF-8',
            'connection' => 'close',
            'cookie' => $cookie,
            'origin' => 'https://www.weiyun.com',
            'referer' => 'https://www.weiyun.com/disk',
            'user-agent' => C::BROWSER_USER_AGENT,
        ];
        $seq = (integer)(time() . '' . rand(1000000, 9999999));
        $content = json_encode([
            'req_header' => json_encode([
                'seq' => $seq,
                'type' => 1,
                'cmd' => 2209,
                'appid' => 30013,
                'version' => 3,
                'major_version' => 3,
                'minor_version' => 3,
                'fix_version' => 3,
                'wx_openid' => '',
                'user_flag' => 0,
            ]),
            'req_body' => json_encode([
                'ReqMsg_body' => [
                    'ext_req_head' => [
                        'token_info' => [
                            'token_type' => 0,
                            'login_key_type' => 1,
                            'login_key_value' => $sKey,
                        ],
                        'language_info' => [
                            'language_type' => 2052,
                        ]
                    ],
                    '.weiyun.DiskDirBatchListMsgReq_body' => [
                        'pdir_key' => $pDirId,
                        'dir_list' => [
                            [
                                'dir_key' => $dirId,
                                'get_type' => 0,
                                'start' => 0,
                                'count' => 100,
                                'sort_field' => 2,
                                'reverse_order' => false,
                                'get_abstract_url' => true,
                                'get_dir_detail_info' => true,
                            ]
                        ]
                    ]
                ]
            ])
        ]);
//        print $content;
//        die();

        $domain = '';
        if (preg_match_all('/^https:\/\/(.*?)(\/.*?|$)/', $url, $match)) {
            $domain = $match[1][0];
        }

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
            'body' => $content,
        ];

        $client = new \GuzzleHttp\Client(['verify' => false]);
        $res = $client->post($url, $reqOptions);

        $json = json_decode($res->getBody()->getContents(), true);
        if (empty($json)) {
            $json = [];
        }
        return $json;
    }

    private static function getFileInfoFromDirListByFilename($json, $filename, $isFile)
    {
        $file = [];
        if (!isset($json['data']['rsp_body']['RspMsg_body']['dir_list'][0])) {
            return $file;
        }
        $rspBody = $json['data']['rsp_body']['RspMsg_body']['dir_list'][0];
        if ($isFile) {
            $arrKeyName = 'file_list';
            $objKeyName = 'filename';
            $resultKeyName = 'file_id';
        } else {
            $arrKeyName = 'dir_list';
            $objKeyName = 'dir_name';
            $resultKeyName = 'dir_key';
        }
        $fileListArr = $rspBody[$arrKeyName];
        $foundObj = null;
        for ($i = 0; $i < sizeof($fileListArr); $i++) {
            if ($fileListArr[$i][$objKeyName] === $filename) {
                $foundObj = $fileListArr[$i];
                break;
            }
        }

        if ($foundObj !== null) {
            $file['fileId'] = $foundObj[$resultKeyName];
            $file['pDirKey'] = $rspBody['pdir_key'];
        }
        return $file;
    }

    private static function getDownloadLinkByFileInfo($pDirId, $fileId)
    {
        $link = [];
        $d = D::getInstance();
        $cookie = $d->getConfig('cookie');
        if (empty($cookie)) {
            return $link;
        }

        $sKey = '';
        $tk = '';
        if (preg_match_all('/skey=(.*?)(;|$)/', $cookie, $m)) {
            $sKey = $m[1][0];
        }
        if (preg_match_all('/wyctoken=(.*?)(;|$)/', $cookie, $m)) {
            $tk = $m[1][0];
        }

        $url = 'https://www.weiyun.com/webapp/json/weiyunQdiskClient/DiskFileBatchDownload?refer=chrome_windows&g_tk=' . $tk . "&r=" . self::mathRandom();
        $headerArr = [
            'accept' => 'application/json, text/plain, */*',
            'accept-encoding' => 'compress',
            'accept-language' => 'zh-CN,zh;q=0.9',
            'content-length' => '564',
            'content-type' => 'application/json;charset=UTF-8',
            'connection' => 'close',
            'cookie' => $cookie,
            'origin' => 'https://www.weiyun.com',
            'referer' => 'https://www.weiyun.com/disk',
            'user-agent' => C::BROWSER_USER_AGENT,
        ];
        $seq = (integer)(time() . '' . rand(1000000, 9999999));
        $content = json_encode([
            'req_header' => json_encode([
                'seq' => $seq,
                'type' => 1,
                'cmd' => 2402,
                'appid' => 30013,
                'version' => 3,
                'major_version' => 3,
                'minor_version' => 3,
                'fix_version' => 3,
                'wx_openid' => '',
                'user_flag' => 0,
            ]),
            'req_body' => json_encode([
                'ReqMsg_body' => [
                    'ext_req_head' => [
                        'token_info' => [
                            'token_type' => 0,
                            'login_key_type' => 1,
                            'login_key_value' => $sKey,
                        ],
                        'language_info' => [
                            'language_type' => 2052,
                        ]
                    ],
                    '.weiyun.DiskFileBatchDownloadMsgReq_body' => [
                        'file_list' => [
                            [
                                'file_id' => $fileId,
                                'pdir_key' => $pDirId
                            ]
                        ],
                        'download_type' => 0
                    ]
                ]
            ])
        ]);

        $domain = '';
        if (preg_match_all('/^https:\/\/(.*?)(\/.*?|$)/', $url, $match)) {
            $domain = $match[1][0];
        }

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
            'body' => $content,
        ];

        $client = new \GuzzleHttp\Client(['verify' => false]);
        $res = $client->post($url, $reqOptions);

        $json = json_decode($res->getBody()->getContents(), true);
        if (isset($json['data']['rsp_header']['retcode']) && $json['data']['rsp_header']['retcode'] === 0) {
            $theFile = $json['data']['rsp_body']['RspMsg_body']['file_list'][0];
            $link['cookieName'] = $theFile['cookie_name'];
            $link['cookieValue'] = $theFile['cookie_value'];
            $link['downloadLink'] = $theFile['https_download_url'];
            $link['currentCookie'] = $cookie;
        }
        return $link;
    }
}