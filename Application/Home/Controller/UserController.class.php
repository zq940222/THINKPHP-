<?php
/**
 * 会员控制器
 * PHP version 5
 * @package   ehuigou PC 2.0
 * @author    Antonia <471699715@qq.com>
 * @copyright 2016 Antonia
 */
namespace Home\Controller;
use Think\Controller;
class UserController extends Controller{
    /**
     * 登录
     *
     * @param HttpRequest $request
     *
     * @return html
     * @author Antonia
     */
	public function login(){
		if(!empty($_SESSION['mobile'])){
			$this->redirect('index');
			die();
		}
		$this->display();
	}

    /**
     * 注册
     *
     * @param HttpRequest $request
     *
     * @return html
     * @author Antonia
     */
	public function register(){
		$this->display();
	}

    /**
     * [个人中心]
     * @param  HttpRequest $request
     * @return html
     * @author Antonia  
     */
    public function index()
    {
    	$this->get_user();
    	$this->display();
    }

    /**
     * [订单中心]
     * @param  HttpRequest $request
     * @return html
     * @author Antonia  
     */
    public function order()
    {
    	$this->get_user();
        $map["user_phone"] = $_SESSION["mobile"];
        $deal_list = D("Deal")->where($map)->order("add_time desc")->relation(true)->select();
        $model = D("Orders");
        $array = array();
        $array1 = array();
        for ($i=0; $i <count($deal_list) ; $i++) { 
            $order_id = unserialize($deal_list[$i]["order_sn"]);
            $filters["order_id"] = array("in",$order_id);
            $deal_list[$i]["order"] = $model->where($filters)->relation(true)->select();
            $deal_list[$i]["order_count"] = count($deal_list[$i]["order"]);
            if (empty($deal_list[$i]["order"])) {
                unset($deal_list[$i]);
            }
            for ($k=0; $k <count($deal_list[$i]["order"]) ; $k++) { 
                if ($deal_list[$i]["order"][$k]["order_type"] == 1 && $deal_list[$i]["order"][$k]["order_state"] >4  && $deal_list[$i]["order"][$k]["is_del"] == 0) {
                    $array1[$i] =  $deal_list[$i];
                }else if($deal_list[$i]["order"][$k]["order_type"] != 1 && $deal_list[$i]["order"][$k]["order_state"] >7 && $deal_list[$i]["order"][$k]["is_del"] == 0){
                    $array1[$i] =  $deal_list[$i];
                }else if($deal_list[$i]["order"][$k]["is_del"] == 0){
                    $array[$i] =  $deal_list[$i];
                }                
            }
        }
        $this->assign("deal_list1",$array);
        $this->assign("deal_list2",$array1);
        $this->assign("deal_list",$deal_list);
    	$this->display();
    }

    /**
     * [订单详细]
     * @param  HttpRequest $request
     * @return html
     * @author Antonia  
     */
    public function order_list()
    {
        $this->get_user();
        $id = $_GET["id"];
        if (empty($id)) {
            $this->redirect('index');
            die();
        }
        $model = D("Orders");
        $map["user_phone"] = $_SESSION["mobile"];
        $deal_list = D("Deal")->relation(true)->where($map)->find($id);
        $filters["order_id"] = array("in",unserialize($deal_list["order_sn"]));
        $deal_list["order"] = $model->where($filters)->relation(true)->select();
        for ($i=0; $i <count($deal_list["order"]); $i++) { 
            $user_param = unserialize($deal_list["order"][$i]["conf_param"]);
            $conf_list = $model->getConfList($user_param["type_id"],$user_param["conf"],$user_param["left"],$user_param["right"]);
            $conf_list = unserialize($conf_list);
            for ($k=0; $k <count($conf_list["left"]) ; $k++) { 
                $deal_list["order"][$i]["left"][$k] = explode(":",$conf_list["left"][$k]);
            }
            for ($k=0; $k <count($conf_list["right"]) ; $k++) { 
                $deal_list["order"][$i]["right"][$k] = explode(":",$conf_list["right"][$k]);
            }
        }    
        $this->assign("deal_list",$deal_list);
        $this->display();
    }

    /**
     * [安全中心]
     * @param  HttpRequest $request
     * @return html
     * @author Antonia  
     */
    public function safety()
    {
    	$type = 1;
    	$this->get_user($type);
    	$this->display();
    }

    /**
     * [常用地址]
     * @param  HttpRequest $request
     * @return html
     * @author Antonia  
     */
    public function location()
    {
    	$this->get_user();
    	$map["user_phone"] = $_SESSION["mobile"];
		$result=D('Address')->where($map)->find();
		$result = $this->location_list($result);
		$province_model = M("province");
	    $province_list = $province_model->cache(true)->select();
	    $this->assign("province_list",$province_list);
		$this->assign("location",$result);
    	$this->display();
    }

    /**
     * [收款帐号]
     * @param  HttpRequest $request
     * @return html
     * @author Antonia  
     */
    public function pay_account()
    {
    	$this->get_user();
    	$model = D("Pay");
    	$map["user_phone"] = $_SESSION["mobile"];
    	$pay_list = $model->where($map)->select();
    	$this->assign("pay_list",$pay_list);
    	$this->display();
    }

    /**
     * [帐户余额]
     * @param  HttpRequest $request
     * @return html
     * @author Antonia  
     */
    public function user_account()
    {
    	$this->get_user();
    	$model = D("Pay");
    	$map["user_phone"] = $_SESSION["mobile"];
    	$pay_list = $model->where($map)->select();
    	$this->assign("pay_list",$pay_list);
    	$this->display();
    }

    /**
     * [取消或恢复订单]
     * @param  HttpRequest $request
     * @return html
     * @author Antonia  
     */
    public function operation()
    {
        $this->get_user();
        if (!empty($_POST["type"])) {
            $model = D("Orders");
            if ($_POST["type"] == 1 ) {
                $map["id"] = $_POST["id"];
                $deal_list = D("Deal")->where($map)->find();
                if (empty($deal_list)) {
                    $data['status'] = 0;
                    $data['info'] = '无效操作';
                    $this->ajaxReturn($data,'JSON');  
                }
                $order_id = unserialize($deal_list["order_sn"]);
                $order_handle_logs = M('order_handle_logs');

                for ($i=0; $i <count($order_id) ; $i++) {
                    $order_msg = $model->find($order_id[$i]);
                    $price = unserialize($order_msg["conf_param"]);
                    $data["order_price"] = D("Orders")->getPrice($price["type_id"],$price);
                    $data["order_id"] = $order_id[$i];
                    $data["is_del"] = 0;
                    $rs = $model->save($data);
                    $data = array();
                    $data["order_id"]     = $order_id[$i];
                    $data["type"]         = 12;
                    $data["remark"]       = "用户恢复订单";
                    $data["add_time"] = time();
                    $order_handle_logs->add($data);
                }
                if (!empty($rs)) {
                    $map["is_del"] = 0;
                    $rs = D("Deal")->save($map);
                    if (!empty($rs)) {
                        $data = array();
                        $data['status'] = 1;
                        $data['info'] = '操作成功';
                        $this->ajaxReturn($data,'JSON');
                    }
                }else{
                    $data = array();
                    $data['status'] = 0;
                    $data['info'] = '无效操作';
                    $this->ajaxReturn($data,'JSON'); 
                }
            }elseif ($_POST["type"] == 2 ) {
                $map["id"] = $_POST["id"];
                $deal_list = D("Deal")->where($map)->find();
                if (empty($deal_list)) {
                    $data['status'] = 0;
                    $data['info'] = '无效操作';
                    $this->ajaxReturn($data,'JSON');  
                }
                $order_id = unserialize($deal_list["order_sn"]);
                $array = array(1=>"我不想回收了",2=>"信息填写错误，重新评估",3=>"其他");
                $order_handle_logs = M('order_handle_logs');
                for ($i=0; $i <count($order_id) ; $i++) { 
                    $data["order_id"] = $order_id[$i];
                    $data["is_del"] = 1;
                    $rs = $model->save($data);
                    $data = array();
                    $data["order_id"]     = $order_id[$i];
                    $data["type"]         = 12;
                    $data["remark"]       = $array[$_POST["reason"]];
                    $data["add_time"] = time();
                    $order_handle_logs->add($data);
                }
                if (!empty($rs)) {
                    $map["is_del"] = 1;
                    $rs = D("Deal")->save($map);
                    if (!empty($rs)) {
                        $data = array();
                        $data['status'] = 1;
                        $data['info'] = '操作成功';
                        $this->ajaxReturn($data,'JSON');
                    }
                }else{
                    $data = array();
                    $data['status'] = 0;
                    $data['info'] = '无效操作';
                    $this->ajaxReturn($data,'JSON'); 
                }
            }else{
                $data['status'] = 0;
                $data['info'] = '无效操作';
                $this->ajaxReturn($data,'JSON');                  
            }
        }else{
            $data['status'] = 0;
            $data['info'] = '无效操作';
            $this->ajaxReturn($data,'JSON');                
        }
    }

    /**
     * [删除收款帐号]
     * @param  vivo $request
     * @return array
     * @author Antonia  
     */
    public function delete_pay_account()
    {
    	if (empty($_POST["pay_id"])) {
    		$data = array();
		    $data['status'] = 0;
		    $data['info'] = '参数错误，请重试';
		    $this->ajaxReturn($data,'JSON');
    	}
    	$model = D("Pay");
    	$rs = $model->delete($_POST["pay_id"]);
    	if ($rs) {
	 		$data = array();
		    $data['status'] = 1;
		    $data['info'] = '操作成功';
		    $this->ajaxReturn($data,'JSON');
    	}else{
    		$data['status'] = 0;
		    $data['info'] = '参数错误，请重试';
		    $this->ajaxReturn($data,'JSON');	
    	}
    }

    /**
     * [修改收款帐号]
     * @param  vivo $request
     * @return array
     * @author Antonia  
     */
    public function save_pay_account()
    {
    	$this->get_user();
    	$model = D("Pay");
    	if (!empty($_POST["pay_id"])) {
            if ($vo = $model->create()) {
                $rs = $model->where($map)->save($vo);
            }else{
                $data = array();
                $data['status'] = 0;
                $data['info'] = '参数错误，请重试';
                $this->ajaxReturn($data,'JSON');
            }
    	}else{
    		if ($vo = $model->create()) {
 				$rs = $model->where($map)->add($vo);
    		}else{
    			$data = array();
		        $data['status'] = 0;
		        $data['info'] = '参数错误，请重试';
		        $this->ajaxReturn($data,'JSON');
    		}
    	}
    	if ($rs) {
	 		$data = array();
		    $data['status'] = 1;
		    $data['info'] = '操作成功';
		    $this->ajaxReturn($data,'JSON');
    	}else{
    		$data = array();
		    $data['status'] = 0;
		    $data['info'] = '参数错误，请重试';
		    $this->ajaxReturn($data,'JSON');
    	}
    }

    /**
     * [修改地址信息]
     * @param  vivo $request
     * @return array
     * @author Antonia  
     */
    public function save_location()
    {	
    	$this->get_user();
    	$model = D('Address');
    	if ($vo = $model->create()) {
    		$map["user_phone"] = $_SESSION["mobile"];
    		$address = $model->where($map)->find();
    		$vo = array_merge($vo,$_POST);
    		if (!empty($address)) {
    			$vo["address_id"] = $address["address_id"];
 				$rs = $model->save($vo);
    		}else{
    			$vo["user_phone"] = $_SESSION["mobile"];
    			$vo["address_default"] = 1;
 				$rs = $model->where($map)->add($vo);
    		}
 			if ($rs) {
	 			$data = array();
		        $data['status'] = 1;
		        $data['info'] = '操作成功';
		        $this->ajaxReturn($data,'JSON');
 			}else{
		        $data = array();
		        $data['status'] = 0;
		        $data['info'] = '参数错误，请重试';
		        $this->ajaxReturn($data,'JSON');
    		}
    	}else{
		        $data = array();
		        $data['status'] = 0;
		        $data['info'] = '参数错误，请重试';
		        $this->ajaxReturn($data,'JSON');
    	}
    }
    /**
     * [返回地址信息]
     * @param  void $request
     * @return array
     * @author Antonia  
     */
    public function location_list($data)
    {
    	!empty($data["provinceid"]) && $data["province"] = M('Province')->where('provinceid='.$data["provinceid"])->getField('province');
		!empty($data["cityid"]) && $data["city"] = M('City')->where('cityid='.$data["cityid"])->getField('city');
		!empty($data["areaid"]) && $data["area"] = M('Area')->where('areaid='.$data["areaid"])->getField('area');
    	return $data;
    }

    /**
     * [修改用户手机]
     * @param  vivo $request
     * @return array
     * @author Antonia  
     */
    public function save_phone()
    {
        $map["user_phone"] = $_SESSION["mobile"];
        $data["user_phone"] = $_POST["user_phone"];
        $model = D("Users");
        $result = $this->check_codes($_POST["code"]);
        if (!empty($result)) {
            $rs = $model->where($map)->save($data);
        }else{
            $data = array();
            $data['status'] = 0;
            $data['info'] = '验证码错误请重新发送';
            $this->ajaxReturn($data,'JSON');            
        }
        if ($rs) {
            $_SESSION["mobile"] = $data["user_phone"];
            $data = array();
            $data['status'] = 1;
            $data['info'] = '已成功修改';
            $this->ajaxReturn($data,'JSON');
        }else{
            $data = array();
            $data['status'] = 0;
            $data['info'] = '参数错误，请重试';
            $this->ajaxReturn($data,'JSON');
        }
    }

    /**
     * [修改用户密码]
     * @param  vivo $request
     * @return array
     * @author Antonia  
     */
    public function save_pswd()
    {
        $map["user_phone"] = $_SESSION["mobile"];
        $data["user_password"] = $_POST["password"];
        $model = D("Users");
        $rs = $model->where($map)->save($data);
        if ($rs) {
            $data = array();
            $data['status'] = 1;
            $data['info'] = '已成功修改';
            $this->ajaxReturn($data,'JSON');
        }else{
            $data = array();
            $data['status'] = 0;
            $data['info'] = '参数错误，请重试';
            $this->ajaxReturn($data,'JSON');
        }
    }
    /**
     * [修改用户基本资料]
     * @param  vivo $request
     * @return array
     * @author Antonia  
     */
    public function save_user()
    {
    	$map["user_phone"] = $_SESSION["mobile"];
    	$data["user_name"] = $_POST["name"];
    	$data["user_sex"] = $_POST["sex"];
    	$model = D("Users");
    	$rs = $model->where($map)->save($data);
    	if ($rs) {
    		$_SESSION["user_name"] = $data["user_name"];
	    	$data = array();
	        $data['status'] = 1;
	        $data['info'] = '已成功修改';
	        $this->ajaxReturn($data,'JSON');
    	}else{
	        $data = array();
	        $data['status'] = 0;
	        $data['info'] = '保存成功';
	        $this->ajaxReturn($data,'JSON');
    	}
    }
    /**
     * [获取用户信息]
     * @param  vivo $request
     * @return array
     * @author Antonia  
     */
    public function get_user($type="")
    {
		$_POST = I('post.','','htmlspecialchars');
		$_GET = I('get.','','htmlspecialchars');
    	$this->login_redirect();
    	$map["user_phone"] = $_SESSION["mobile"];
    	$model = D("Users");
    	$user_data = $model->where($map)->find();
        $filters["is_del"] = 0;
        $filters["user_phone"] = $_SESSION["mobile"];
        $order_model = D("Orders");
        $coutn_order = $order_model->where($filters)->count();
        $count = $coutn_order * 60000;
        $pay_model = D("pay");
        $count_pay = $pay_model->where($filters)->count();
    	switch ($type) {
    		case 1:
    			$user_data["user_phone"] = substr($user_data["user_phone"],0,3)."****".substr($user_data["user_phone"],-4,4);
    			break;
    		default:
    			break;
    	}
        $this->assign("count_pay",$count_pay);
        $this->assign("count",$count);
        $this->assign("coutn_order",$coutn_order);
    	$this->assign("user_data",$user_data);
    	return $user_data;
    }

	/**
     * 注册验证
     * @param HttpRequest $request
     * @return html
     * @author Antonia
     */
	public function check_register(){
		$post=I('post.','','htmlspecialchars');	
		if (empty($_POST)) {
            $data['status'] = 0;
            $data['info'] = '参数错误,请刷新页面重试';
            $this->ajaxReturn($data,'JSON');
        }
        $result = $this->check_codes($_POST["code"]);
        if (!empty($result)) {
        	$users = D("Users");
        	$user['user_phone'] = $_POST['user_phone'];
            $rs = $users->where($user)->find();
            if (!empty($rs)) {
            	$data['status'] = 0;
	            $data['info'] = '帐号已经存在，请直接登录';
	            $this->ajaxReturn($data,'JSON');
            }
            $user["user_phone"] = $_POST['user_phone'];
            $user['last_login_time'] = time();
            $user['add_time'] = time();
            $user['is_membeer'] =1;
            $rs = $users->add($user);
            if (!empty($rs)) {
            	$_SESSION['mobile'] = $user['user_phone'];
	           	$_SESSION["user_name"]  = $user['user_phone'];
	           	$data['status'] = 1;
	            $data['info'] = '注册成功';
	            $this->ajaxReturn($data,'JSON');
            }
        }else{
        	$data['status'] = 2;
	        $data['info'] = '手机验证码错误,请重试';
	        $this->ajaxReturn($data,'JSON');
        }
	}
    /**
     * [确认用户身份信息]
     * @param JSON   
     * @return AJAX
     * @author Antonia  
     */
	public function ajax_check_verify(){
        if (empty($_POST)) {
            $data['status'] = 0;
            $data['info'] = '参数错误,请刷新页面重试';
            $this->ajaxReturn($data,'JSON');
        }
        $post=I('post.','','htmlspecialchars');
        if(!empty($_POST["verify"])){
	        $code = $_POST["verify"];
	        $verify = new \Think\Verify();
	        $result = $verify->check($code);	
        }else{
        	$result = 1;
        }
        if ($result) {
	        $data["mobile"] = remove_xss($_POST["user_phone"]);
	        if (!empty($_SESSION["old_user_phone"]) && empty($_POST["type"])) {
	            if ($_SESSION["old_user_phone"] == $data["mobile"]) {//记录一致,同一人下单
	                $data = array();
	                $data['status'] = 2;
	                $data['info'] = "验证通过,请继续填写";
	                $this->ajaxReturn($data,'JSON');
	            }else{
	                session('old_user_phone',null);//清空上一个电话的留存
	            }
	        }
	        $code = rand(100000,999999);
	        $_SESSION["phone_code"] = $code;
	        $data["msg"] = "您好,此次提交的验证码为:".$code;
	        $rs = $this->Note_verify($data);
	        if ($rs) {
	        	$data = array();
	            $data['status'] = 1;
	            $data['info'] = '短信发送成功';
	            $this->ajaxReturn($data,'JSON');	
	        }
        }else{
            $data['status'] = 3;
            $data['info'] = '验证码错误请重试';
            $this->ajaxReturn($data,'JSON');	
        }
	}
    /**
     *
     * [短信接口 description]
     * @param JSON $value [description]
     * @return AJAX
     * @author Antonia  
     */
    public function Note_verify($data)
    {
        $NT = new \Think\ChuanglanSmsApi();
        $result = $NT->sendSMS($data);
        return $result;
    }

    /**
     * 登录检测
     * @param HttpRequest $request
     * @return html
     * @author Antonia
     */
	public function checkLogin(){
		$post=I('post.','','htmlspecialchars');	
		$model = D("Users");
		$map=array();
		$map["user_phone"] = $_POST["user_phone"];
		$users= $model->where($map)->find();
		if (empty($users)){
            $data['status'] = 0;
            $data['info'] = '用户不存在,请先注册';
            $this->ajaxReturn($data,'JSON');
            die;
		}else{
			if ($_POST["password"]) {
				if($_POST["password"] != $users["user_password"]){
		            $data['status'] = 0;
		            $data['info'] = '用户帐号或密码错误,请检查后重试';
		            $this->ajaxReturn($data,'JSON');
		            die;
	           	}
                $result = 1;
			}else{
				$result = $this->check_codes($_POST["code"]);
			}
			if ($result) {
	            if ($_POST["Cookie"]) {
					setcookie('mobile',$users['user_phone'] ,time()+864000,'/','.ehuigou.com');
	            }else{
	            	setcookie('mobile',$users['user_phone'] ,time()+3600,'/','.ehuigou.com');
	            }
	            $_SESSION['mobile'] = $users['user_phone'];
	           	$_SESSION["user_name"]  = $users["user_name"];
		        $data['status'] = 1;
		        $data['info'] = '';
		        $this->ajaxReturn($data,'JSON');
		        die;
			}else{
		        $data['status'] = 2;
		        $data['info'] = '验证码错误,请重试';
		        $this->ajaxReturn($data,'JSON');
		        die;
			}
		}
	}
	

	/**
     * [验证短信验证码是否正确]
     * @param JSON   
     * @return AJAX
     * @author Antonia  
     */
    public function check_codes($user_check="")
    {   
        if (!empty($_POST["check_types"]) && $_POST["check_types"] == 2) {
            $pass = remove_xss($_POST["check_codes"]);
            $code = $_SESSION["phone_code"];
            if (empty($code)) {
                $data['status'] = 0;
                $data['info'] = '验证码错误,请重新获取';
                $this->ajaxReturn($data,'JSON');
                die;
            }else{
                if ($code != $pass) {
                    session('phone_code',null);
                    $data['status'] = 2;
                    $data['info'] = '验证码错误,请重新获取';
                    $this->ajaxReturn($data,'JSON');
                    die;
                }else{
                    $_SESSION["old_user_phone"] = $_POST["user_phone"];
                    $data['status'] = 1;
                    $data['info'] = '验证成功';
                    $this->ajaxReturn($data,'JSON');
                    die;
                }                
            }
        }
        if (empty($user_check)) {
            return false;
        }
        $code = $_SESSION["phone_code"];
        if (empty($code)) {
            return false;
        }
        $pass = remove_xss($user_check);
        if ($code != $pass) {
            session('phone_code',null);
            return false;
        }else{
            $_SESSION["old_user_phone"] = $_POST["user_phone"];
            return true;
        }
    }

    /**
     * [判断登陆状态]
     * @param JSON   
     * @return AJAX
     * @author Antonia  
     */
    public function  login_redirect(){   
        if(empty($_SESSION['mobile'])){
            $this->redirect('/user/login');
            die();
        }
    }

    /**
     * 易购商城用户api 
     * @param HttpRequest $request
     * @return html
     * @author Antonia
     */
    public function get_shopuser()
    {   
        $_POST = I('post.','','htmlspecialchars');
        $map["user_phone"] = $_POST["user_phone"];
        $user_name = D("Users")->where($map)->find();
        $result=D('Address')->where($map)->find();
        $results = $this->location_list($result);
        $results["user_name"] =$user_name["user_name"];
        $results["price"] =0;
        echo json_encode($results);
    }

    /**
     * 退出登录
     * @param HttpRequest $request
     * @return html
     * @author Antonia
     */
	public function Quit_login()
	{
        setcookie('mobile',$users['user_phone'] ,time()-864000,'/','.ehuigou.com');
        session('mobile',null);
		session('user_name',null);
		header("Content-type:text/html;charset=utf-8");
		echo "<script>alert('退出成功');";
		echo "window.location.href = 'http://www.ehuigou.com';";
		echo "</script>";
		die;
	}
	
}
