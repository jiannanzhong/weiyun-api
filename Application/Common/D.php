<?php

namespace WeiyunBackend\Common;

use WeiyunBackend\Exception\InternalError;
use Medoo\Medoo;

class D
{
    private static $instance = null;
    /** @var Medoo $db */
    private $db;

    private function __construct()
    {
        global $app;
        $this->db = $app->getContainer()->get('database');
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new D();
        }
        return self::$instance;
    }

    public function getConfig($key)
    {
        try {
            $config = $this->db->get(
                'config', 'value', ['name' => $key]
            );
            if (empty($config)) {
                $config = '';
            }
            return $config;
        } catch (\Exception $e) {
            throw new InternalError($e->getMessage());
        }
    }

}