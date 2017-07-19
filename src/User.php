<?php

namespace Jtcczu\Applet;

/**
 * class User
 * 
 * @property string $openId
 * @property string $nickName
 * @property integer $gender
 * @property string $city
 * @property string $province
 * @property string $country
 * @property string $avatarUrl
 * @property string $unionId
 * @property array $watermark
 */
class User
{
    protected $attributes;
    
    public function __construct($attributes = [])
    {
        $this->attributes = $attributes;
    }
    
    public function getAttribute($name, $default=null)
    {
        return isset($this->attributes[$name]) ? $this->attributes[$name] : $default;
    }
    
    public function __get($name)
    {
        return $this->getAttribute($name);
    }
    
    public function getAttributes()
    {
        return $this->attributes;
    }
    
    public function setAttributes()
    {
        return $this->attributes;
    }
}

