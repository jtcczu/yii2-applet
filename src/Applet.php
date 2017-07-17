<?php
namespace Jtcczu\Yii\Applet;

use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;
use yii\base\Component;
use yii\helpers\Json;
use yii\web\HttpException;

/**
 * $user = Yii::$app->applet->getSessionKey($code)->decryptData(); 
 * 返回Userinterface的实现
 * $user->getOpenid();
 * $user->getNickName();
 *
 * @package Jtcczu\Applet
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

    protected $baseUrl = 'https://api.weixin.qq.com/sns';

    protected $sessionJsonKey = 'session_key';

    public function getSessionKey($code)
    {
        $response = $this->getClient()->get($this->getSessionKeyUrl(), [
            'appid' => $this->appid,
            'secret' => $this->secret,
            'js_code' => $code,
            'grant_type' => 'authorization_code'
        ]);

        $result = $this->parseJson($response);

        if (isset($result['errcode'])) {
            throw new HttpException(500, $result['errmsg']);
        }

        return $result[$this->sessionJsonKey];
    }
    
    protected function getSessionKeyUrl()
    {
        return $this->baseUrl.'/jscode2session';
    }

    protected function getClient()
    {
        return $this->client?:new Client();
    }
    
    public function setClient(Client $client)
    {
        $this->client = $client;
        
        return $this;
    }
    
    protected function parseJson(ResponseInterface $response)
    {
        $contents = $response->getBody()->getContents();
        
        return Json::decode($contents);
        
    }

}


