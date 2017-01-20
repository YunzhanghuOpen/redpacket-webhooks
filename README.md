# 红包消息推送v1.1.0

## 版本历史
v1.1.0 2017年01月04日

* 增加消息类型：红包过期退回、身份证审核结果

v1.0.2 2016年09月13日

* 充值类型通知增加了支付渠道

v1.0.1 2016年09月12日

* 充值类型通知增加了流水号
* 增加了子文档《详细的通知数据示例.txt》

v1.0 2016年09月09日

* 针对商户（开发者）的消息推送服务
* 增加对所属商户（SaaS 平台）的通知，通过 appid 识别下属商户

## 说明

云账户对用户的请求数据处理完成后，会将结果以服务器主动通知的方式通知给商户网站。
这些处理结果数据就是服务器异步通知参数。

示例：

curl -X __POST__ -H "Content-Type: __application/json__" -d '{"notify_id":"14732279660721952","uid":"foo01","partner":"123456","appid":"abcdefg","trade_status":"RECHARGE_SUCCESS","sign":"25892892e2806066fa852381c2d7707ee9d25c4eb91e529881dd101e849edecf","data":"{\"amount\":\"1.00\",\"datetime\":\"2016-09-08 12:21:44\",\"ref\":\"151120185800437765\"}","create_time":"2016-09-12 18:30:54","notify_time":"2016-09-12 19:36:59"}' "https://foo.bar/getnotice/stuff"

> 注意：data 的内容经过 json encode，请解析后使用。


## 通知触发条件

|触发条件（交易状态）|描述|
|-----------------|------------|
|RECEIVE_SUCCESS  |抢到红包     |
|SEND_SUCCESS     |发出红包     |
|RECHARGE_SUCCESS |充值成功     |
|REBACK_SUCCESS   |退回成功     |
|IDVERIFY_RESULT  |身份证审核结果|
|WITHDRAW_SUCCESS |提现成功     |

## 通知参数

### 通知参数的数据格式

```js
{
    notify_id: '14732279660721953',  # 消息编号
    uid: 'foo01',                    # 商户平台下用户ID
    partner: 123456,                 # 商户编号
    appid: '8a48b5514fd49643014ff3', # 商户在 SaaS 平台的 appid
    trade_status: 'SEND_SUCCESS',    # 交易状态（触发条件）
    sign: '86faa539781bb20ba5d7182e00c760cd1dbfcfc612fd800e05727e0f2d2c875c',
    data: {                          # 具体的业务参数，以发送红包为例
        id: '1604051506e9e4c591859a2016488e794a44b533',
        message: '恭喜发财',                            
        recipient: 'userid001',                        
        amount: '1.00',                                
        groupid: '',                                   
        count: 1                                       
    },
    create_time: '2016-09-06 12:26:17', # 消息产生的日期时间
    notify_time: '2016-09-06 15:26:17'  # 消息发出的日期时间
}
```
### 具体的业务参数

#### 1 抢到红包 RECEIVE_SUCCESS

```js
data: {
    id: '1604051506e9e4c591859a2016488e794a44b533', # 红包ID
    myamount: 0.01                                  # 我抢到的钱
},
```

#### 2 发出红包 SEND_SUCCESS

```js
data: {
    id: '1604051506e9e4c591859a2016488e794a44b533', # 红包ID
    message: '恭喜发财',                             #  红包留言
    recipient: 'userid001',                         # 接收人ID
    amount: '1.00',                                 # 红包金额
    groupid: '',                                    # 群ID
    count: 1                                        # 红包数量
},
```

#### 3 充值成功 RECHARGE_SUCCESS

```js
data: {
    amount: '1.00',                      # 充值金额
    datetime: '2016-09-08 12:21:44',     # 充值时间
    ref: '151120185800437765',           # 充值流水号         
    channel: 'JDPAY',                    # 支付渠道，取值[JDPAY,ALIPAY,WECHAT]
},
```

#### 4 退回成功 REBACK_SUCCESS

```js
data: {
    id: '1604051506e9e4c591859a2016488e794a44b533', # 红包ID
    amount: '1.00',                                 # 红包金额
    reback_amount: '1.00',                          # 退回金额
    groupid: '',                                    # 群ID
    count: 1                                        # 红包数量
    rest_count: 1                                   # 未领取数量
},
```

#### 5 身份证审核结果 IDVERIFY_RESULT

```js
data: {
    name: '张三',                         # 姓名
    card_no: '110101198104130234',       # 身份证号
    status: 1,                           # 审核结果，取值[0未验证 1已验证 2验证失败 3审核中]
    message: '审核通过',                  # 审核说明，举例 “请上传您本人的身份证照片”
},
```

#### 5 提现成功 WITHDRAW_SUCCESS

```js
data: {
    amount: '100.1',                         # 提现金额
    datetime: '2017-01-20 11:49:04',         # 提现日期
},
```

## 商户通知参数合法性验证

当云账户处理完成后会把数据结果反馈给商户，商户获得这些数据时，必须进行如下处理。

### 验证签名

1. 待签名参数集合
    
    除了 sign 及 sign_type 以外，获得的参数集合都是要参与签名的。
    一般情况下，通知里的参数都是有值的，空值的参数不会出现。

1. 组装
    1. 排序

        除 sign、sign_type 字段外，所有参数按照字段名（KEY）的ASCII码从小到大排序
        
    2. 拼接

        排序后所有字段以 key1=value1&key2=value2 格式进行拼接
        
1. 调用签名验证函数 HMAC

    ```php
    # appkey 为云账户提供的通讯密钥
    $hash = hmac('sha256', $message, $appkey);
    ```
    
1. 签名示例

    * [demo](./sign_demo.php)
    
    * [更多用例](./详细的通知数据示例.txt)

### 数据处理注意事项

1. 商户方需验证通知数据中的 uid 为商户系统中的用户ID；
1. 校验通知中的 partner 是否为商户方本身；
1. 商户方可以依据 notify_id 过滤重复的通知。

## 通知频率

程序执行完后必须打印输出 __success__。如果商户反馈给云账户的字符不是 __success__ 这7个字符，云账户服务器会不断重发通知，直到超过24小时22分钟。
一般情况下，25小时以内完成8次通知（通知的间隔频率一般是：4m,10m,10m,1h,2h,6h,15h）

