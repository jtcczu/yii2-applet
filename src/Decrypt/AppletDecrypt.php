<?php

namespace Jtcczu\Applet\Decrypt;

use yii\helpers\Json;

class AppletDecrypt
{
    protected $appid;

    protected $sessionKey;

    public function __construct($appid, $sessionKey)
    {
        $this->appid = $appid;
        $this->sessionKey = $sessionKey;
    }

    public function getUser($encryptedData, $iv)
    {
        if (strlen($this->sessionKey) != 24) {
            throw new DecryptionException('Illegal Aeskey', DecryptionException::ERROR_ILLEGAL_AESKEY);
        }

        if (strlen($iv) != 24) {
            throw new DecryptionException('Illegal Iv', DecryptionException::ERROR_ILLEGAL_IV);
        }

        list($aesKey, $aesIV, $aesCipher) = array_map('base64_decode', [$this->sessionKey, $iv, $encryptedData]);

        $result = self::decrypt($aesKey, $aesCipher, $aesIV);

        $userArray = Json::decode($result);

        if(is_null($userArray)) {
            throw new DecryptionException('Illegal Buffer', DecryptionException::ERROR_ILLEGAL_BUFFER);
        }

        if($userArray['watermark']['appid'] != $this->appid ) {
            throw new DecryptionException('Illegal Buffer', DecryptionException::ERROR_ILLEGAL_BUFFER);
        }

        return $userArray;
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
        return sha1($rawData.$this->sessionKey) === $signature;
    }

    /**
     * decode text
     *
     * @param  $text
     * @return string
     */
    public static function decode($text)
    {
        $pad = ord(substr($text, -1));
        if ($pad < 1 || $pad > 32) {
            $pad = 0;
        }
        return substr($text, 0, (strlen($text) - $pad));
    }

    /**
     * decrypt data
     *
     * @param $aesKey
     * @param $aesCipher
     * @param $aesIV
     * @return string
     * @throws DecryptionException
     */
    public static function decrypt( $aesKey, $aesCipher, $aesIV )
    {
        try{
            $decrypted = openssl_decrypt($aesCipher, 'aes-128-cbc', $aesKey, OPENSSL_RAW_DATA, $aesIV);
        }catch (Exception $e){
            throw new DecryptionException('Decode Base64 Error', DecryptionException::ERROR_DECODE_BASE64);
        }

        try {
            $result = self::decode($decrypted);
        } catch (Exception $e) {
            throw new DecryptionException('Illegal buffer', DecryptionException::ERROR_ILLEGAL_BUFFER);
        }

        return $result;
    }
}