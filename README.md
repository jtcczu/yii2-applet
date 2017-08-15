# Yii2 Applet Extension

Yii2小程序组件 获取用户信息和会话密钥


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
 $applet = Yii::$app->applet->makeSession($code); 
 $applet->getUser($encryptedData, $iv); //返回用户信息
 $applet->checkSignature($rawData, $signature); //数据签名校验

 $session = $applet->getSession(); 
 $session->getOpenid(); //openid
 $session->getSessionKey(); //session_key
 $session->getUnionid(); //unionid            
```

微信小程序api文档
https://mp.weixin.qq.com/debug/wxadoc/dev/api/api-login.html#wxloginobject

## 快速开始

小程序代码
```
wx.login({
        success: function (login) {
          wx.getUserInfo({
            success: function (res) {
              wx.request({
                url: 'xxx',
                method : 'post',
                data:{
                    code : login.code,
                    rawData : res.rawData,
                    signature : res.signature,
                    encryptedData : res.encryptedData,
                    iv : res.iv
                },
                dataType : 'json',
                success : function(res){
                }
              })
            }
          })
        }
      })
```

后端代码

```
public function actionTest(){    
  $contents = file_get_contents('php://input');
  $data = json_decode($contents,true);
  $user = Yii::$app->applet
                   ->makeSession($data['code'])
                   ->getUser($data['encryptedData'],$data['iv']);
}                   
```







