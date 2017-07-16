<?php
namespace Jtcczu\Applet\Traits;

use Exception;

trait UseDecrypt
{
    /**
     * 解密的错误码对应的错误信息
     * @var array
     */
    public static $cryptError = [
        41001 => 'encodingAesKey 非法',
        41002 => 'iv 非法',
        41003 => 'aes 解密失败',
        41004 => '解密后得到的buffer非法',
        41005 => 'base64加密失败',
        41016 => 'base64解密失败'
    ];

    /**
     * 检验数据的真实性，并且获取解密后的明文.
     * @param $session_key wx.login获取到的session_key
     * @param $encryptedData string 加密的用户数据
     * @param $iv string 与用户数据一同返回的初始向量
     * @throws Exception
     * @return int 成功0，失败返回对应的错误码
     */
    public function decryptData( $session_key ,$encryptedData, $iv )
    {
        if (strlen($session_key) != 24) {
            throw new Exception(self::$cryptError[41001]);
        }
        $aesKey=base64_decode($session_key);

        if (strlen($iv) != 24) {
            throw new Exception(self::$cryptError[41002]);
        }
        $aesIV=base64_decode($iv);

        $aesCipher=base64_decode($encryptedData);

        $result = $this->decrypt($aesKey,$aesCipher,$aesIV);

        if ($result[0] != 0) {
            throw new Exception($result[0]);
        }

        $dataObj=json_decode( $result[1] );
        if( $dataObj  == NULL )
        {
            throw new Exception(self::$cryptError[41003]);
        }
        if( $dataObj->watermark->appid != $this->appid )
        {
            throw new Exception(self::$cryptError[41003]);
        }
        return $result[1] ? json_decode($result[1],true) : null;
    }


    /**
     * 对解密后的明文进行补位删除
     * @param decrypted 解密后的明文
     * @return 删除填充补位后的明文
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
     * 对密文进行解密
     * @param string $sessionKey
     * @param string $aesCipher 需要解密的密文
     * @param string $aesIV 解密的初始向量
     * @return string 解密得到的明文
     */
    public function decrypt( $sessionKey,$aesCipher, $aesIV )
    {   
        try{
            $decrypted = openssl_decrypt($aesCipher,'AES-128-CBC',$sessionKey,OPENSSL_RAW_DATA,$aesIV);
            
        }catch (Exception $e){
            return array(self::$cryptError[41004], null);
        }

        try {
            $result = $this->decode($decrypted);

        } catch (Exception $e) {
            //print $e;
            return array(self::$cryptError[41003], null);
        }
        return array(0, $result);
    }
}
