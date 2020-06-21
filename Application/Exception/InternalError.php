<?php

namespace WeiyunBackend\Exception;

class InternalError extends ApiException
{
    public function __construct($msg)
    {
        parent::__construct(500, $msg);
    }
}