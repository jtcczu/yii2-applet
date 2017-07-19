<?php

namespace Jtcczu\Applet;

use Exception;

/**
 * Class DecryptTrait
 */
trait DecryptTrait
{
    /**
     * decode text
     * 
     * @param  $text
     * @return string
     */
    protected function decode($text)
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
    protected function decrypt( $aesKey, $aesCipher, $aesIV )
    {   
        try{
            $decrypted = openssl_decrypt($aesCipher, 'aes-128-cbc', $aesKey, OPENSSL_RAW_DATA, $aesIV);
        }catch (Exception $e){
            throw new DecryptionException('Decode Base64 Error', DecryptionException::ERROR_DECODE_BASE64);
        }

        try {
            $result = $this->decode($decrypted);
        } catch (Exception $e) {
            throw new DecryptionException('Illegal buffer', DecryptionException::ERROR_ILLEGAL_BUFFER);
        }

        return $result;
    }
}
