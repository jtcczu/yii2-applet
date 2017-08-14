<?php

namespace Jtcczu\Applet;

class Session
{
    protected $data;

    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    public function get($key, $default=null)
    {
        return $this->has($key) ? $this->data[$key] : $default;
    }

    public function has($key)
    {
        return array_key_exists($key, $this->data);
    }

    public function getOpenId()
    {
        return $this->get('openid');
    }
    
    public function getSessionKey()
    {
        return $this->get('session_key');
    }

    public function getUnionid()
    {
        return $this->get('unionid');
    }

}