<?php

namespace WeiyunBackend\Common;


class C
{
    const DATABASE_TYPE_MYSQL = 'mysql';
    const DATABASE_TYPE_SQLITE = 'sqlite';
    const SELECT_DATABASE_TYPE = self::DATABASE_TYPE_SQLITE;
    const GET_REAL_LINK_AUTH_CODE = 'wy-code';
    const WEIYUN_ROOT_PDIR_KEY = '8e2de25af0a739ae12b58dcd423dce4a';
    const WEIYUN_ROOT_DIR_KEY = '8e2de25aa787049c27e93f3ac64a3fed';
    const BROWSER_USER_AGENT = 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/64.0.3282.119 Safari/537.36';
    const SQLITE_FILE_PATH = __DIR__ . '/../../sqlite-data/';
    const CTCLOUD_SQLITE_DATA_FILE = 'weiyun_api.sqlite';
}