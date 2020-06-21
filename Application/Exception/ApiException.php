<?php

namespace WeiyunBackend\Exception;


class ApiException extends \Exception
{
    protected $httpStatus;

    public function __construct($httpStatus, $msg)
    {
        parent::__construct($msg);
        $this->httpStatus = $httpStatus;
    }

    public function getHttpStatus()
    {
        return $this->httpStatus;
    }
}