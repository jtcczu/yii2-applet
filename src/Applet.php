<?php

namespace Jtcczu\Applet;

use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;
use yii\base\Component;
use yii\helpers\Json;
use yii\web\HttpException;

/**
 * Class Applet
 */
class Applet extends Component
{
    use DecryptTrait;
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
     * @var string
     */
    protected $session;

    /**
     * Get session_key from server
     * 
     * @param  $code
     * @return $this
     * @throws HttpException
     */
    public function getSessionFromServer($code)
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

        $result = $this->parseJson($response);

        if (isset($result['errcode'])) {
            throw new HttpException(500, $result['errmsg']);
        }

        $this->session = $result[$this->sessionJsonKey];

        return $this;
    }

    /**
     * Get userinfo by decrypt
     * 
     * @param  $encryptedData
     * @param  $iv
     * @return mixed
     * @throws DecryptionException
     */
    public function getUserByDecrypt($encryptedData, $iv)
    {
        if (strlen($this->session) != 24) {
            throw new DecryptionException('Illegal Aeskey', DecryptionException::ERROR_ILLEGAL_AESKEY);
        }

        if (strlen($iv) != 24) {
            throw new DecryptionException('Illegal Iv', DecryptionException::ERROR_ILLEGAL_IV);
        }

        $aesKey = base64_decode($this->session);

        $aesIV = base64_decode($iv);

        $aesCipher = base64_decode($encryptedData);

        $result = $this->decrypt($aesKey, $aesCipher, $aesIV);

        $dataArr = Json::decode($result, true);

        if(is_null($dataArr)) {
            throw new DecryptionException('Illegal Buffer', DecryptionException::ERROR_ILLEGAL_BUFFER);
        }

        if($dataArr['watermark']['appid'] != $this->appid ) {
            throw new DecryptionException('Illegal Buffer', DecryptionException::ERROR_ILLEGAL_BUFFER);
        }
        return $dataArr;
    }

    /**
     * check signature is equal 
     * 
     * @param  $rawData
     * @param  $signature
     * @return bool
     */
    public function checkSignature($rawData, $signature)
    {
        return sha1($rawData.$this->session) === $signature;
    }

    /**
     * Get session_key
     * 
     * @return string
     */
    public function getSession()
    {
        return $this->session;
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

    /**
     * parse json to array
     * 
     * @param  ResponseInterface $response
     * @return mixed
     */
    protected function parseJson(ResponseInterface $response)
    {
        $contents = $response->getBody()->getContents();
        
        return Json::decode($contents);
    }

}


