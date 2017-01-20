<?php

# 商户配置
$partner = '123456';
$key = 'f83b33ce0ffe16cdea8c23eea259b724';

# 通知的数据
$input = '{"notify_id":170120122136236688,"uid":"duid","partner":"123456","appid":"123456","trade_status":"RECHARGE_SUCCESS","sign":"1f87b3b1a6f39e1e9471483dfab0f84044633de41cf01aaa8730c323317aad0f","data":"{\"amount\":11.1,\"datetime\":\"2017-01-20 12:21:36\",\"ref\":\"3333\",\"channel\":\"JDPAY\"}","create_time":"2017-01-20 12:21:36","notify_time":"2017-01-20 12:21:40"}';

# 参数列表
$params = json_decode($input, true);
echo sprintf("参数列表: %s \n", json_encode($params));

# 签名原始串
ksort($params);

$signStr = '';
foreach($params as $k => $val) {
	if ($k != 'sign' && $k != 'sign_type' && $val != '') {
		$signStr .= $k . '=' . $val . '&';
	}
}
$signStr = substr($signStr, 0, -1);
echo sprintf("签名原始串: %s \n", $signStr);

# 签名结果
$hash = hash_hmac('sha256', $signStr, $key);
echo sprintf("hmac 签名结果: %s \n", $hash);

# 比较签名
if ($hash == $params['sign']) {
	echo sprintf("签名匹配 \n");
} else {
	echo sprintf("签名不匹配 \n");
}
