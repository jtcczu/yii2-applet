<?php
namespace Jtcczu\Yii\Applet;

use GuzzleHttp\Client;
use Jtcczu\Yii\Applet\Traits\UseDecrypt;
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
    use UseDecrypt;
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

    protected $session;

    public function getSessionFromServer($code)
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

        $this->session = $result[$this->sessionJsonKey];

        return $this;
    }

    public function getUserFromDecrypt($encryptedData, $iv)
    {
        if (strlen($this->session) != 24) {
            throw new DecryptionException('Illegal Aeskey',DecryptionException::ERROR_ILLEGAL_AESKEY);
        }

        if (strlen($iv) != 24) {
            throw new DecryptionException('Illegal Iv',DecryptionException::ERROR_ILLEGAL_IV);
        }

        $aesKey = base64_decode($this->session);

        $aesIV = base64_decode($iv);

        $aesCipher = base64_decode($encryptedData);

        $result = $this->decrypt($aesKey,$aesCipher,$aesIV);

        $dataArr = Json::decode($result,true);

        if(is_null($dataArr)){
            throw new DecryptionException('Illegal Buffer',DecryptionException::ERROR_ILLEGAL_BUFFER);
        }

        if( $dataArr['watermark']['appid'] != $this->appid ){
            throw new DecryptionException('Illegal Buffer',DecryptionException::ERROR_ILLEGAL_BUFFER);
        }
        return $dataArr;
    }

    public function getSession()
    {
        return $this->session;
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


