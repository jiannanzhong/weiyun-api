<?php

use WeiyunBackend\Common\C;
use Slim\App;

return function (App $app) {
    $container = $app->getContainer();

    // view renderer
    $container['renderer'] = function ($c) {
        $settings = $c->get('settings')['renderer'];
        return new \Slim\Views\PhpRenderer($settings['template_path']);
    };

    // monolog
    $container['logger'] = function ($c) {
        $settings = $c->get('settings')['logger'];
        $logger = new \Monolog\Logger($settings['name']);
        $logger->pushProcessor(new \Monolog\Processor\UidProcessor());
        $logger->pushHandler(new \Monolog\Handler\StreamHandler($settings['path'], $settings['level']));
        return $logger;
    };

    // database
    $container['database'] = function ($c) {
        if (C::SELECT_DATABASE_TYPE === C::DATABASE_TYPE_MYSQL) {
            $cfg = $c->get('settings')['db'];
            return new Medoo\Medoo([
                'database_type' => 'mysql',
                'database_name' => $cfg['name'],
                'server' => $cfg['host'] . ':' . $cfg['port'],
                'username' => $cfg['user'],
                'password' => $cfg['pass'],
                'option' => array(
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                ),
            ]);
        } else {
            return new Medoo\Medoo([
                'database_type' => 'sqlite',
                'database_file' => C::SQLITE_FILE_PATH . C::CTCLOUD_SQLITE_DATA_FILE,
                'option' => []
            ]);
        }
    };

    // Error Handler
    $container['errorHandler'] = function ($c) {
        return function ($req, $rsp, $e) {
            if (is_subclass_of($e, 'ay\\gs\\except\\GSApiException')) {
                $httpStatus = $e->getHttpStatus();
            } else {
                $httpStatus = 500;
            }

            //debug_print_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
            //print_r(str_replace('/path/to/code/', '', $e->getTraceAsString()));

            //print_r($e->getTraceAsString());
            // TODO 这样会导致出错时的返回（JSON）多了调用堆栈输出，不能被解析！
            // 要加个单元测试的判断！

            return $rsp
                ->withStatus($httpStatus)
                ->withJson(array(
                    'err_http_code' => $httpStatus,
                    'err_msg' => $e->getMessage(),
                ));
        };
    };
};
