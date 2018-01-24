<?php
namespace Think;

class ChuanglanSmsApi {

	//创蓝发送短信接口URL, 如无必要，该参数可不用修改
			private $api_send_url = ;
			private $api_balance_query_url = ;
			private $api_account = ;
			private $api_password = ;

	/**1
	 * 发送短信
	 *
	 * @param string $mobile 手机号码
	 * @param string $msg 短信内容
	 * @param string $needstatus 是否需要状态报告
	 * @param string $product 产品id，可选
	 * @param string $extno   扩展码，可选
	 */
	public function sendSMS($data) {
		//创蓝接口参数
		//
		$postArr = array (
				          'account' => $this->api_account,
				          'pswd' =>  $this->api_password,
				          'msg' => $data["msg"], 	
				          'mobile' => $data["mobile"],
				          'needstatus' => false,
				          'product' => "349312826",
				          'extno' => "044528"
                     );
		$result = $this->curlPost( 'http://222.73.117.158/msg/HttpBatchSendSM', $postArr);
		return $result;
	}
	

	/**2
	 * 发送短信  营销短信接口
	 *
	 * @param string $mobile 手机号码
	 * @param string $msg 短信内容
	 * @param string $needstatus 是否需要状态报告
	 * @param string $product 产品id，可选
	 * @param string $extno   扩展码，可选
	 */
	public function sendSMS1($data) {
		//创蓝接口参数
		//
		$postArr = array (
				          'account' => "bjsjck",
				          'pswd' =>  "Bj666666",
				          'msg' => "恭喜您-手机回收下单成功，获得一次幸运大转盘抽奖活动资格，点击http://m.ehuigou.com/public/explain，关注易回购微信公众号，了解活动详情。退订回TD", 	
				          'mobile' => $data["mobile"],
				          'needstatus' => false,
                     );
		$result = $this->curlPost( 'http://222.73.117.169/msg/HttpBatchSendSM', $postArr);
		return $result;
	}
	





	/**
	 * 查询额度
	 *
	 *  查询地址
	 */
	public function queryBalance() {
		//查询参数
		$postArr = array ( 
		          'account' => $this->api_account,
		          'pswd' => $this->api_password,
		);
		$result = $this->curlPost($this->api_send_url, $postArr);
		return $result;
	}

	/**
	 * 处理返回值
	 * 
	 */
	public function execResult($result){
		$result=preg_split("/[,\r\n]/",$result);
		return $result;
	}

	/**
	 * 通过CURL发送HTTP请求
	 * @param string $url  //请求URL
	 * @param array $postFields //请求参数 
	 * @return mixed
	 */
	private function curlPost($url,$postFields){
		$postFields = http_build_query($postFields);
		$ch = curl_init ();
		curl_setopt ( $ch, CURLOPT_POST, 1 );
		curl_setopt ( $ch, CURLOPT_HEADER, 0 );
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt ( $ch, CURLOPT_URL, $url );
		curl_setopt ( $ch, CURLOPT_POSTFIELDS, $postFields );
		$result = curl_exec ( $ch );
		curl_close ( $ch );
		return $result;
	}
	
	// 魔术获取
	public function __get($name){
		return $this->name;
	}
	
	//魔术设置
	public function __set($name,$value){
		$this->name=$value;
	}
}
