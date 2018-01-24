<?php
/**
 * 订单控制器
 * PHP version 5
 * @package   ehuigou PC 2.0
 * @author    Antonia <471699715@qq.com>
 * @copyright 2016 Antonia
 */
namespace Home\Controller;
use Think\Controller;
class OrderController extends Controller {
    /**
     * 下单页面
     * @param HttpRequest $request
     * @return html
     * @author Antonia
    */
    public function index() {
        $_GET=I('get.','','htmlspecialchars'); 
        $filters["is_end"] = 0;
        if ($_GET["type"] == 2) {
            $data["cart_id"] = array("in",$_GET["id"]);
            $data["cart_state"] = 0;
            $rs = M("order_cart")->save($data);
        }
        $filters["cart_state"] = 0;
        $filters["cart_id"] = array("in",$_GET["id"]);
        $cart_model = D("Order_cart");
        $cart_list = $cart_model->where($filters)->relation(true)->select();
        for ($i=0; $i <count($cart_list); $i++) { 
            $data = unserialize($cart_list[$i]["user_param"]);
            $cart_list[$i]["user_price"] = D("Orders")->getPrice($data["type_id"],$data);
            $cart_model->where("cart_id ='".$cart_list[$i]["cart_id"]."'")->setField("user_price",$cart_list[$i]["user_price"]);
            $conf_list = D("Orders")->getConfList($data["type_id"],$data["conf"],$data["left"],$data["right"]);
            $conf_list = unserialize($conf_list);
            for ($k=0; $k <count($conf_list["left"]) ; $k++) { 
                $cart_list[$i]["left"][$k] = explode(":",$conf_list["left"][$k]);
            }
            for ($k=0; $k <count($conf_list["right"]) ; $k++) { 
                $cart_list[$i]["right"][$k] = explode(":",$conf_list["right"][$k]);
            }
        }
        $count_price = $cart_model->where($filters)->Sum('user_price');
        $count = count($cart_list);
        if (empty($_SESSION["city"])) {
            $model = D("Users");
            $city = $model->get_city();
            $_SESSION["city"] = $city["city"];
        }
        if ($_SESSION["city"] == "北京") {
            $this->assign("stores",1);
        }
        $province_model = M("province");
        $province_list = $province_model->cache(true)->select();
        $city_model = M("city");
        $city_list = $city_model->cache(true)->select();
        $array = array();
        for ($i=0; $i <count($city_list) ; $i++) {
            $array[$city_list[$i]["fatherid"]][] = $city_list[$i];
        }
        $store_model = D("Stores");
        $map["affiliation_firm"] = 1;
        $store_list = $store_model->where($map)->select();
        $this->get_date();
        $this->assign("count",$count);
        $this->assign("count_price",$count_price);
        $this->assign("cart_list",$cart_list);
        $this->assign("store_list",$store_list);
        $this->assign("province_list",$province_list);
        $this->assign("city_list",$array);
        $this->assign("city",$_SESSION["city"]);
        $this->display();
    } 
    /**
     * 提交订单
     * @param ajax $request
     * @return array
     * @author Antonia
    */
    public function send() {
        $_POST=I('post.','','htmlspecialchars'); 
        $result = $this->check_codes($_POST["user_check"]);
        if (empty($result)) {
            $data = array();
            $data['status'] = 0;
            $data['info'] = "手机验证码错误,请重试";
            $this->ajaxReturn($data,'JSON');
        }
        $filters["is_end"] = 0;
        $filters["cart_state"] = 0;
        $filters["cart_id"] = array("in",$_POST["cart_ids"]);
        $cart_model = D("Order_cart");
        $model = D("Orders");
        $types = D("Types");
        $seller = M("seller");

        $list = $cart_model->where($filters)->relation(true)->select();
        for ($i=0; $i <count($list); $i++) { 
            $list[$i]["user_param"] = unserialize($list[$i]["user_param"]);
            $list[$i]["user_price"] = $model->getPrice($list[$i]["user_param"]["type_id"],$list[$i]["user_param"]);
            $cart_model->where("cart_id ='".$list[$i]["cart_id"]."'")->setField("user_price",$list[$i]["user_price"]);
        }
        $count_price = $cart_model->where($filters)->Sum('user_price');
        $count_list = count($list);
        $users = D("Users");
        $filter["user_phone"]= $_POST["user_phone"];
        $type_map["type_name"] = array("like","%智能%");
        $type_fieles_list = $types->where($type_map)->field("type_id")->select();
        for ($i=0; $i <count($type_fieles_list); $i++) { 
            $type_fieles_id[] = $type_fieles_list[$i]["type_id"];
        }
        $type_fieles_id[] = "1618"; 
        $count_next = 0;
        for ($i=0; $i <count($list) ; $i++) { 
            if($vo=$model->create()){
                $order_data = $list[$i]["user_param"];
                $id = intval($order_data["type_id"]);
                //防止恶意提交
                if (!in_array($id,$type_fieles_id)) {
                    $map = array();
                    $map["user_phone"] = $_POST['user_phone'];
                    $map["add_time"] = array("GT",strtotime(date('Y-m-d')));
                    $map["type_id"] = $id;
                    $map_list = $model->where($map)->field("order_id")->select();
                    $map_count = count($map_list);
                    $max_count = $seller->field("max_count")->find($vo['affiliation_firm']);
                    if ($map_count == $max_count["max_count"] || $map_count > $max_count["max_count"]) {
                        $data['status'] = 0;
                        $data['info'] = "您已下单成功".$count_next."件，今天此机器交易已经超限，请联系管理员";
                        $this->ajaxReturn($data,'JSON');
                        die;                    
                    }
                }
                $types->where("type_id='".$id."'")->setInc('Has_sold');
                $types_highestprice = $types->field("highestprice")->find($id);
                $vo["old_price"] = $types_highestprice["highestprice"];
                $vo["type_id"] = $id;
                $vo["order_price"] = $list[$i]["user_price"];
                $vo["order_state"] = 1;
                $vo["conf_param"] = serialize($order_data);
                $vo["conf_list"] = $model->getConfList($id,$order_data["conf"],$order_data["left"],$order_data["right"]);
                $vo["affiliation_firm"] = 1;
                unset($vo["cart_ids"]);
                $id = $model->add($vo);
                if ($id) {
                    $count_next +=1;
                    $cart_model->where("cart_id ='".$list[$i]["cart_id"]."'")->setField("is_end",1);
                    $info[] = $id;
                }
            }else {
                $data = array();
                $data['status'] = 0;
                $data['info'] = $model->getError();
                $this->ajaxReturn($data,'JSON');
            }
        }
        if($count_list == $count_next) {
            $data = array();
            $data["user_phone"] = $vo["user_phone"];
            $data["user_name"] = $vo["user_name"];
            $data["order_price"] = $count_price;
            $data["order_count"] = $count_list;
            $data["order_sn"] = serialize($info);
            $data["deal_type"] = $vo["order_type"];
            $data["count"] = $data["order_count"];
            $data["store_id"]= $vo["store_id"];
            $data["post_state"] = 1;
            $deal_model = D("Deal");
            $deal_model->create($data);
            $rs = $deal_model->add();
            $filter = array();
            $filter["user_phone"]= $vo["user_phone"];
            $rs = $users->where($filter)->find();
            if ($rs) {
                $user['last_login_ip'] =get_client_ip();
                $user['last_login_time'] = time();
                $users->where($filter)->save($user);
                $users->where($filter)->setInc('count_price',$total+$count_price);
                $users->where($filter)->setInc('count_next');
            }else{
                $user['user_name'] = $vo['user_name'];
                $user['count_price'] = $vo['order_price'];
                $user['user_id_number'] = $vo['user_id_number'];
                $user['count_next'] = 1;
                $user['last_login_time'] = time();
                $user['add_time'] = time();
                $user['last_login_ip'] =get_client_ip();
                $rs = $users->add($user);
            }
            $_SESSION['mobile'] = $vo['user_phone'];
            $_SESSION["user_name"]  = $vo["user_name"];
            setcookie('mobile',$_SESSION['mobile'] ,time()+3600,'/','.ehuigou.com');
            session("cart_count",null);
            $data = array();
            $data['status'] = 1;
            $data['info'] = "下单成功";
            $this->ajaxReturn($data,'JSON');
        }else{
            $data['status'] = 0;
            $data['info'] = $model->getError();
            $this->ajaxReturn($data,'JSON');
        }
    }
    /**
     * [验证短信验证码是否正确]
     * @param JSON   
     * @return AJAX
     * @author Antonia  
     * 
     */
    public function check_codes($user_check)
    {
        if ($_SESSION["old_user_phone"] == $_POST["user_phone"]) {
            return true;
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
     * 返回当前一周日期加星期
     * @param HttpRequest $request
     * @return vido
     * @author Antonia
    */
    public function get_date()
    {
        $weekarray=array("日","一","二","三","四","五","六");
        for ($i=0; $i <7 ; $i++) { 
            $date[$i] = date('m/d',strtotime("+$i day"))."  (星期".$weekarray[date("w",strtotime("+$i day"))].")";
        }
        $this->assign("date",$date);
    }
    /**
     * 价格查询
     * @param HttpRequest $request
     * @return html
     * @author Antonia
     */
    public function inquiry() {
        if (empty($_COOKIE["visitor"]) && empty($_SESSION["mobile"])) {
            redirect("index/index");
        }
        if(!empty($_SESSION["mobile"]) && !empty($_COOKIE["visitor"])){
            $where["user_phone"] = $_SESSION["mobile"];
            $where["purchaser"] = $_COOKIE["visitor"];
            $where['_logic'] = 'or';
            $filters["_complex"] = $where;
        }elseif (!empty($_SESSION["mobile"])) {
            $filters["user_phone"] = $_SESSION["mobile"];
        }elseif (!empty($_COOKIE["visitor"])) {
            $filters["purchaser"] = $_COOKIE["visitor"];
        }
        $filters["is_end"] = 0;
        $filters["cart_state"] = 0;
        $cart_model = D("Order_cart");
        $model = D("Orders");
        $list = $cart_model->where($filters)->order("add_time desc")->relation(true)->select();   
        $types = 2;
        for ($i=0; $i <count($list); $i++) { 
            $user_param = unserialize($list[$i]["user_param"]);
            $conf_list = $model->getConfList($user_param["type_id"],$user_param["conf"],$user_param["left"],$user_param["right"]);
            $conf_list = unserialize($conf_list);
            for ($k=0; $k <count($conf_list["left"]) ; $k++) { 
                $list[$i]["left"][$k] = explode(":",$conf_list["left"][$k]);
            }
            for ($k=0; $k <count($conf_list["right"]) ; $k++) { 
                $list[$i]["right"][$k] = explode(":",$conf_list["right"][$k]);
            }
        }    
        $count_price = $cart_model->where($filters)->Sum('user_price');
        $count = count($list);
        $_SESSION["cart_count"] = $count;
        $this->assign("count",$count);
        $this->assign("count_price",$count_price);
        $this->assign("list",$list);
        $this->display();
    }

    /**
     * 删除选定内容
     * @param AJAX $request
     * @return JSON
     * @author Antonia
    */
   public function delete() {
        $post=I('post.','','htmlspecialchars'); 
        $model  = D("Order_cart");
        if (!empty($_GET["id"])) {
            $rs = $model->delete($_GET["id"]);
            $this->redirect('inquiry');
            die;
        }
        $map = array();
        !empty($post["id"]) && $map['cart_id']  = array('in',$post["id"]);
        $result  = $model->where($map)->delete();
        if($result) {
            $data['status'] = "1";
            $data['info'] = "删除成功!";
        } else {
            $data['status'] = "0";
            $data['info'] = "删除失败，请重试!";
        }
        $this->ajaxReturn($data,'JSON');
    }
    /**
     * 下单成功提示页面
     * @param HttpRequest $request
     * @return HTML
     * @author Antonia
    */
    public function success() {
        if (empty($_SESSION['mobile'])) {
            $this->redirect("inquiry");
        }
        $model = D("Deal");
        $deal = $model->where("user_phone ='".$_SESSION['mobile']."'")->relation(true)->order("add_time desc")->find();
        $this->assign("deal",$deal);
        $this->display();
    }
    /**
     * 更新价格
     * @param AJAX $request
     * @return JSON
     * @author Antonia
    */
    public function save_price() {
        $_POST=I('post.','','htmlspecialchars'); 
        $model  = D("Order_cart");
        if (empty($_POST["id"])) {
            $data['status'] = "0";
            $data['info'] = "参数错误!";
            $this->ajaxReturn($data,'JSON');
            die;
        }
        $orders =$model->find($_POST["id"]);
        $data = unserialize($orders["user_param"]);
        $price = D("Orders")->getPrice($data["type_id"],$data);
        $data=array();
        $data["data"] = $price;
        if ($orders["user_price"] == $price) {
            $data['status'] = "3";
            $data['info'] = "";
            $this->ajaxReturn($data,'JSON');
            die;
        }
        $msg["user_price"] = $price;
        $msg["cart_id"] = $_POST["id"];
        $rs = $model->save($msg); 
        if ($orders["user_price"] > $price) {
            $data['status'] = "2";
            $data['info'] = $orders["user_price"]-$price;
        }else{
            $data['status'] = "1";
            $data['info'] = $price-$orders["user_price"];
        }
        $this->ajaxReturn($data,'JSON');
    }

    /**
     * 返回区级
     * @param HttpRequest $request
     * @return ajax
     * @author Antonia 
     */
    public function ajax_area()
    {
        if (empty($_POST)) {
            $data['status'] = 0;
            $data['info'] = '参数错误，请刷新页面后重试';
            $this->ajaxReturn($data,'JSON');
        }
        $area =M("area");
        $area_list = $area->cache(true)
                          ->where("fatherid ='".$_POST["id"]."'")
                          ->select(); 
        if (!empty($area_list)) {
            $data['status'] = 1;
            $data['data'] = $area_list;
            $this->ajaxReturn($data,'JSON');
        }else{
            $data['status'] = 0;
            $data['info'] = "获取失败，请刷新页面后重试";
            $this->ajaxReturn($data,'JSON');
        }
    }
    /**
     * 获取地区门店列表
     * @param HttpRequest $request
     * @return html
     * @author Antonia
     */
    public function getStoreList() {
        !empty($_POST["rid"]) && $filters["store_city"] = intval($_POST["rid"]);
        $filters["affiliation_firm"] = 1;
        $store_model = D("Stores");
        $stores = $store_model->where($filters)
                                ->field("store_id,store_name,store_addr,store_lbs,store_phone")
                                ->select();
        $this->assign("store_list", $stores);
        $this->display("storepage");
    }

    /**
     * 获取城市
     * @param ajax $request
     * @return array
     * @author Antonia
     */
    public function ajax_city()
    {
        if (empty($_POST)) {
            $data['status'] = 0;
            $data['info'] = '参数错误，请刷新页面后重试';
            $this->ajaxReturn($data,'JSON');
        }
        $city = M("city");
        $map["fatherID"]=$_POST["id"];
        $city_list = $city->where($map)->select(); 
        if (!empty($city_list)) {
            $data['status'] = 1;
            $data['data'] = $city_list;
            $this->ajaxReturn($data,'JSON');
        }else{
            $data['status'] = 0;
            $data['info'] = "获取失败，请刷新页面后重试";
            $this->ajaxReturn($data,'JSON');
        }
    }
}