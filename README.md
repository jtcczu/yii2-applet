# Yii2 Applet Extension

Yii2小程序组件 获取用户信息和会话密钥（session_key）


## 环境需求

- PHP >= 5.5

## 安装

```
composer require jtcczu/yii2-applet
```

## 配置

```
return [
    //...
    'components' => [
        'applet' => [
          'class' => 'Jtcczu\Applet\Applet',
          'appid' => 'APPID',
          'secret' => 'SECRET'
        ]
    ]
]

```

## 使用

```
 $user = Yii::$app->applet
                  ->getSessionFromServer($code)
                  ->getUserByDecrypt($encryptedData,$iv);
 $user->openId;  //openId
 $user->nickName; //昵称
 $user->gender; //性别
 ...
 //登录凭证code获取session_key
 Yii::$app->applet->getSessionFromServer($code);
 //加密数据encryptedData对称解密
 Yii::$app->applet->getUserByDecrypt($encryptedData,$iv);
 //签名校验
 Yii::$app->applet->checkSignature($rawData, $signature);
 
```

微信小程序api文档
https://mp.weixin.qq.com/debug/wxadoc/dev/api/api-login.html#wxloginobject







