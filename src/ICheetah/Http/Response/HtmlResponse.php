<?php

namespace ICheetah\Http\Response;

class HtmlResponse extends Response
{
    public function __construct($content = "", $status = 200, array $headers = array())
    {
        parent::__construct($content, $status, $headers);
    }
}