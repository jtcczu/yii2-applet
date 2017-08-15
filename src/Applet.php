<?php

namespace Jtcczu\Applet;

use GuzzleHttp\Client;
use Jtcczu\Applet\Decrypt\AppletDecrypt;
use yii\base\Component;
use yii\helpers\Json;
use yii\web\HttpException;

/**
 * Class Applet
 *
 * decrypt
 */
class Applet extends Component
{
    /**
     * @var string
     */
    public $appid;
    /**
     * @var string
     */
    public $secret;
    /**
     * @var string
     */
    protected $client;
    /**
     * @var string
     */
    protected $baseUrl = 'https://api.weixin.qq.com/sns';
    /**
     * @var string
     */
    protected $sessionJsonKey = 'session_key';
    /**
     * @var Session
     */
    protected $session;
    /**
     * Get session_key from server
     * 
     * @param  $code
     * @return $this
     * @throws HttpException
     */
    public function makeSession($code)
    {
        $response = $this->getClient()->get(
            $this->getSessionKeyUrl(), [
                'query' => [
                    'appid' => $this->appid,
                    'secret' => $this->secret,
                    'js_code' => $code,
                    'grant_type' => 'authorization_code'
                ]
            ]
        );
        $contents = $response->getBody()->getContents();
        $result =  Json::decode($contents);
        if (isset($result['errcode'])) {
            throw new HttpException(500, $result['errmsg']);
        }
        return $this->setSession($result);
    }

    protected function setSession($data)
    {
        $this->session = new Session($data);

        return $this;
    }

    public function getSession()
    {
        return $this->session;
    }

    /**
     * instance decrypt
     */
    public function decrypt()
    {
        return new AppletDecrypt($this->appid, $this->session->getSessionKey());
    }

    public function __call($method, $arguments)
    {
        $decrypt = $this->decrypt();
        if(method_exists($decrypt, $method)){
            return call_user_func_array([$decrypt, $method], $arguments);
        }

        throw new \BadMethodCallException("Method [$method] does not exist.");
    }

    /**
     * Get session_key server url
     * 
     * @return string
     */
    protected function getSessionKeyUrl()
    {
        return $this->baseUrl.'/jscode2session';
    }

    /**
     * Get client instance
     * 
     * @return Client
     */
    protected function getClient()
    {
        return $this->client?:new Client();
    }

    /**
     * Set client instance
     * 
     * @param  Client $client
     * @return $this
     */
    public function setClient(Client $client)
    {
        $this->client = $client;
        
        return $this;
    }
}


