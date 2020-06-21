<?php

namespace WeiyunBackend\Exception;


class EmptyReqException extends InvalidReqException
{
    public function __construct()
    {
        parent::__construct('request is empty');
    }
}