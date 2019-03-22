<?php

namespace Brezgalov\GmapsApiClient\Decorators;

use Brezgalov\ApiWrapper\IResourceDecorator;

class SslVerifyPeerDecorator implements IResourceDecorator
{
    /**
     * @var bool
     */
    public $value;

    /**
     * SslVerifyPeerDecorator constructor.
     * @param bool $value = false
     */
    public function __construct($value = false)
    {
        $this->value = (bool)$value;
    }

    /**
     * decorate curl resourse with CURLOPT_SSL_VERIFYPEER
     * @param $ch
     */
    public function decorate(&$ch)
    {
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $this->value);
    }
}