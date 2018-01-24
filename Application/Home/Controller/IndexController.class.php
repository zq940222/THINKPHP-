<?php
/**
 * 首页控制器
 * PHP version 5
 * @package   ehuigou PC 2.0
 * @author    Antonia <471699715@qq.com>
 * @copyright 2016 Antonia
 */
namespace Home\Controller;
use Think\Controller;
class IndexController extends Controller{
    /**
     * 首页
     *
     * @param HttpRequest $request
     *
     * @return html
     * @author Antonia 
     */
    public function index()
    {
            $str = 'a:2:{s:4:"left";a:3:{i:0;s:19:"颜色:玫瑰金色";i:1;s:25:"购买渠道:大陆国行";i:2;s:17:"存储容量:128G";}s:5:"right";a:5:{i:0;s:31:"边框背板:有磕碰或掉漆";i:1;s:28:"屏幕外观:屏幕有划痕";i:2;s:22:"浸液情况:无浸液";i:3;s:22:"拆修情况:无拆修";i:4;s:31:"屏幕性能:屏幕性能完好";}}';
       dump(unserialize($str)); die;

        if (empty($_SESSION["city"])) {
            $model = D("Users");
            $city = $model->get_city();
            $_SESSION["city"] = $city["city"];
        }
        $type_list = D("Types")->cache(true)
                               ->order("count_sale desc")
                               ->limit(5)
                               ->field("type_name,type_id")
                               ->select();
        $this->assign("list",$type_list);
        $this->display();
    }
    /**
     * app介绍页
     * @param HttpRequest $request
     * @return html
     * @author Antonia 
     */
    public function app_list()
    {
        $this->display();
    }

    /**
     * app介绍页
     * @param HttpRequest $request
     * @return html
     * @author Antonia 
     */
    public function app_lists()
    {
        $this->display();
    }

    /**
     * 微信通知
     * @param HttpRequest $request
     * @return html
     * @author Antonia 
     */
    public function get_Wechat()
    {
        $result = $GLOBALS['HTTP_RAW_POST_DATA'];

        if (empty($result)) {
            return false;
            die;
        }else{
            $model = D("Orders");
            $orderssss["right_new_list"] = $result;
            $orderssss["order_id"] = 9;
            $model->save($orderssss);
            $pos = strpos($result, 'xml');
            $obj=simplexml_load_string($result,'SimpleXMLElement', LIBXML_NOCDATA);
            if(is_object($obj)){
                $obj=get_object_vars($obj);
            }
            if (!empty($obj) && $obj["result_code"] == "SUCCESS") {
                if ($obj["attach"] == "微信退款") {
                    $logs = M("order_handle_logs");
                    $map["order_sn"] = $obj["out_trade_no"];
                    $map["is_del"] = 0;
                    // $map["pay_mode"] = 2;
                    $rs = $model->where($map)->lock(true)->find();
                    if (!empty($rs)) {
                        $map = array();
                        $map["order_id"] = $rs["order_id"];
                        $map["type"] = 18;
                        $info = $logs->order("add_time desc")->where($map)->find();
                        if (!empty($info)) {
                            $time = time();
                            $times = $time-$info["add_time"];
                            if ($times < 300) {
                                return false; 
                                die;                     
                            }
                        }
                        $price  = $obj["cash_fee"] /100;
                        $map["remark"] = "通过微信退款".$price."元";
                        $map["add_time"] = time();
                        $logs_id = $logs->add($map);  
                        if (!empty($logs_id)) {
                            $pay_model = D("WeChatplay");
                            $date["order_id"] =$rs["order_id"].",";
                            $date["pay_price"] =$price;
                            $date["pay_time"] = time();
                            $date["pay_sn"] = $pay_model->get_pay_sn();
                            $date["payment_no"] = $obj["transaction_id"];
                            $date["payment_time"] = $obj["time_end"];
                            $date["merchant"] = $rs["affiliation_firm"];
                            $date["pay_state"] = 2;
                            $pay_id = $pay_model->add($date);
                            if(!empty($pay_id)){
                                $filters = array();
                                $filters["order_id"] = $rs["order_id"];
                                $base["pay_state"] = 0;
                                $base["pay_price"] = 0;
                                $order_save = $model->where($filters)->save($base);
                                if (!empty($order_save)) {
                                    $data = "
                                        <xml>
                                          <return_code><![CDATA[SUCCESS]]></return_code>
                                          <return_msg><![CDATA[OK]]></return_msg>
                                        </xml>
                                    ";
                                    return $data;   
                                }else{
                                    return fasle;
                                    die;
                                }
                            }else{
                                return false;
                                die;
                            }
                        }else{
                            return false; 
                            die;
                        }                   
                    }else{
                        return false;
                        die;
                    }
                }else if($obj["attach"] == "商户收款"){
                    $price  = $obj["cash_fee"] /100;
                    $pay_model = D("WeChatplay");
                    $date["pay_price"] =$price;
                    $date["pay_time"] = time();
                    $date["pay_sn"] = $pay_model->get_pay_sn();
                    $date["payment_no"] = $obj["transaction_id"];
                    $date["payment_time"] = $obj["time_end"];
                    $date["pay_state"] = 3;
                    $pay_id = $pay_model->add($date);
                    $data = "
                        <xml>
                            <return_code><![CDATA[SUCCESS]]></return_code>
                            <return_msg><![CDATA[OK]]></return_msg>
                        </xml>
                    ";
                    return $data;   
                }else{
                    return false;
                    die;
                }                
            }else{
                return false;
                die;
            }            
        } 
    }

    /**
     * 支付宝通知
     * @param HttpRequest $request
     * @return html
     * @author Antonia 
     */
    public function get_Alipay()
    {   
        if (!empty($_POST) && $_POST["notify_type"] == "batch_trans_notify") {
            $model = M("alipay");
            $order_model = D("Orders");
            $log_model = M("order_handle_logs");
            $map["batch_no"] = $_POST["batch_no"];  
            $pay_list = $model->where($map)->find();
            if (empty($pay_list)) {
                return false;
            }
            $data = array();
            $data["id"] = $pay_list["id"]; 
            $data["notify_time"] = $_POST["notify_time"];
            $data["success_details"] = $_POST["success_details"];
            $data["fail_details"] =$_POST["fail_details"];
            $model-> where($map)->save($data);
            $success_details = explode("|",$_POST["success_details"]);

            for ($i=0; $i <count($success_details) ; $i++) {
                if (!empty($success_details[$i])) {
                    $success = explode("^",$success_details[$i]);
                    $maps["order_sn"] = $success[0];
                    $list = $order_model->where($maps)->find();
                    if (!empty($list)) {
                        $maps["order_id"] = $list["order_id"];
                        $order_model-> where($maps)->setField('pay_state','1');
                        $datas["order_id"] = $list["order_id"];
                        $datas["type"] = 19;
                        $datas["remark"] = "于".$success[7]."支付宝完成付款，收款人为:".$success[1]."帐号的".$success[2]."付款金额为:".$success[3]."支付宝内部流水单号为:".$success[6];
                        $datas["add_time"] = time();
                        $rs = $log_model->add($datas);
                    }
                } 
            }
            $fail_details = explode("|",$_POST["fail_details"]);
            for ($i=0; $i <count($fail_details) ; $i++) { 
                if (!empty($fail_details[$i])) {
                    $fail = explode("^",$fail_details[$i]);
                    $maps["order_sn"] = $fail[0];
                    $list = $order_model->where($maps)->find();
                    if (!empty($list)) {
                        $maps["order_id"] = $list["order_id"];
                        $order_model-> where($maps)->setField('pay_state','0');
                        $datas["order_id"] = $list["order_id"];
                        $datas["type"] = 19;
                        $datas["remark"] = "于".$fail[7]."支付宝付款失败，失败原因为".$fail[4]."支付宝内部流水单号为:".$fail[6];
                        $datas["arr_time"] = time();
                        $log_model->add();
                    }
                }
            }
            echo "success";
        }
    }


    /**
     * 预约上门
     * @param HttpRequest $request
     * @return html
     * @author Antonia 
     */
    public function order_visit()
    {
        $this->display();
    }

    /**
     * 保存上门信息
     * @param HttpRequest $request
     * @return html
     * @author Antonia 
     */
    public function add_visit()
    {
        $list = remove_xss($_POST);
        $data["is_new"] = remove_xss(intval($_GET["id"]));
        $data["recycle_list"]  = remove_xss($_POST["recycle_list"]);
        $data["user_phone"] = remove_xss($_POST["user_phone"]);
        $data["user_name"] = remove_xss($_POST["user_name"]);
        $data["user_location"] = remove_xss($_POST["user_location"]);
        $data["add_time"] = time();
        if ($data["is_new"] == 2) {
            $data["new_phone"] = remove_xss($_POST["new_phone"]);
        }
        $rs = M("recycle")->add($data);
        if ($rs) {
            echo "<meta http-equiv='Content-Type' content='text/html; charset=UTF-8' />";
            echo "<script>";
            echo "alert('预约成功,请等待工作人员上门');";
            echo "window.location.href='http://www.ehuigou.com'";
            echo "</script>";
        }
    }

    /**
     * 门店加盟
     * @param HttpRequest $request
     * @return html
     * @author Antonia
     */
    public function to_join()
    {
        $province_model = M("province");
        $province_list = $province_model->select();
        $this->assign("province",$province_list);
        $this->display();
    }

    /**
     * 企业帐户注册接口
     * @param HttpRequest $request
     * @return html
     * @author Antonia
     */
    public function business_account(){
        $data["business_name"] = remove_xss($_POST["business_name"]);
        $data["provinceid"] = remove_xss($_POST["provinceid"]);
        $data["cityid"] = remove_xss($_POST["cityid"]);
        $data["user_name"] = remove_xss($_POST["user_name"]);
        $data["user_phone"] = remove_xss($_POST["user_phone"]);
        $data["add_time"] = time();
        $model = M("business_account");
        $rs = $model->add($data);
        if($rs){
            $info =  $this->_upload();
            $img_model = M("photo");
            foreach($info as $file){
                $data["url"] = $file['savename'];
                $data["business_id"] = $rs;
                $result = $img_model->add($data);
            }
            if ($result) {
                $NT = new \Think\ChuanglanSmsApi();
                $result = $NT->sendSMS_warn();
                $data = array();
                $data['status'] = 1;
                $this->ajaxReturn($data,'JSON');
                die;
            }else{
                $data = array();
                $data['status'] = 0;
                $data["info"] = "添加数据失败";
                $this->ajaxReturn($data,'JSON');
                die;
            }
        }else{
            $data = array();
            $data['status'] = 0;
            $data["info"] = "添加数据失败";
            $this->ajaxReturn($data,'JSON');
            die;
        }
    }

    /**
     * 文件上传
     *
     * @param HttpRequest $request
     *
     * @return json string
     * @author Antonia  
     */
    protected function _upload() {
        $upload = new \Think\Upload();
        $upload->maxSize  = 0;
        $upload->exts     = array('jpg', 'gif', 'png', 'jpeg', 'amr');
        $upload->savePath = 'Public/Uploads/';
        $upload->saveName = 'com_create_guid'; 
        $upload->autoSub  = true;
        $upload->subName  = array('date','Ymd');
        $info   =   $upload->upload();
        if(!$info) {
            $re['statusCode'] = "300";
            $re['message'] = $upload->getError();
            echo json_encode($re);
            exit;
        } else {
            return $info;
        }
    }

    /**
     * 商户帐户注册接口
     * @param HttpRequest $request
     * @return html
     * @author Antonia
     */
    public function personal_account(){

        $data["store_name"] = remove_xss($_POST["store_name"]);
        $data["provinceid"] = remove_xss($_POST["provinceid"]);
        $data["cityid"] = remove_xss($_POST["cityid"]);
        $data["person_name"] = remove_xss($_POST["person_name"]);
        $data["person_phone"] = remove_xss($_POST["person_phone"]);
        $data["add_time"] = time();
        $data["store_type"] = remove_xss($_POST["store_type"]);
        $data["detail_add"] = remove_xss($_POST["detail_add"]);
        $model = M("personal_account");
        $rs = $model->add($data);
        if($rs){
            $NT = new \Think\ChuanglanSmsApi();
            $result = $NT->sendSMS_warn();
            $data = array();
            $data['status'] = 1;
            $this->ajaxReturn($data,'JSON');
            die;
        }else{
            $data = array();
            $data['status'] = 0;
            $data["info"] = "添加数据失败";
            $this->ajaxReturn($data,'JSON');
            die;
        }
    }


     /**
     * 根据品牌id获取对应的手机和平板  ajax
     * @param HttpRequest $request
     * @return html
     * @author song
     * 2016/4/15
     */
    public function  get_product(){
        $types = D('types');
        $brand = D('brands');
        $bid = I('post.brandid');
        if($bid=='brand'){
            $where['_string'] = 'FIND_IN_SET(1,cate_id)';
            $where2['_string'] = 'FIND_IN_SET(2,cate_id)';
            $where3['_string'] = 'FIND_IN_SET(3,cate_id)';
            $where4['_string'] = 'FIND_IN_SET(5,cate_id)';
             $list = $brand ->cache(true)
                            ->field('brand_name,brand_id')
                            ->order('sort asc,add_time desc')
                            ->where($where)
                            ->select();
            $list2 = $brand ->cache(true)
                            ->field('brand_name,brand_id')
                            ->order('sort asc,add_time desc')
                            ->where($where2)
                            ->select();
            $list3 = $brand ->cache(true)
                            ->field('brand_name,brand_id')
                            ->order('sort asc,add_time desc')
                            ->where($where3)
                            ->select();
            $list4 = $brand ->cache(true)
                            ->field('brand_name,brand_id')
                            ->order('sort asc,add_time desc')
                            ->where($where4)
                            ->select();
            foreach($list as $key=>$val){
                $list[$key]['cate_id']=1;
            }
            foreach ($list2 as $k=> $v) {
                $v['cate_id']=2;
                $list[] = $v;
            }
            foreach ($list3 as $k=> $v) {
                $v['cate_id']=3;
                $list[] = $v;
            }
            foreach ($list4 as $k=> $v) {
                $v['cate_id']=5;
                $list[] = $v;
            }
            $this->ajaxReturn($list);
        }else{
            $bn = $brand->where('brand_id='.$bid)->find();
            $where = "brand_id=$bid and cate_id=1";
            $where2 = "brand_id=$bid and cate_id=2";
            $where3 = "brand_id=$bid and cate_id=3";
            $where4 = "brand_id=$bid and cate_id=5";
             $list = $types ->cache(true)
                            ->field('type_name,type_id,brand_id,cate_id')
                            ->order('sort asc,add_time desc')
                            ->limit(0,7)
                            ->where($where)
                            ->select();
             $list2 = $types->cache(true) 
                            ->field('type_name,type_id,brand_id,cate_id')
                            ->order('sort asc,add_time desc')
                            ->limit(0,7)
                            ->where($where2)
                            ->select();   
             $list3 = $types->cache(true) 
                            ->field('type_name,type_id,brand_id,cate_id')
                            ->order('sort asc,add_time desc')
                            ->limit(0,7)
                            ->where($where3)
                            ->select(); 
             $list4 = $types->cache(true) 
                            ->field('type_name,type_id,brand_id,cate_id')
                            ->order('sort asc,add_time desc')
                            ->limit(0,7)
                            ->where($where4)
                            ->select();                         
            foreach ($list2 as $k=> $v) {
                $list[] = $v;
            }
            foreach ($list3 as $k=> $v) {
                $list[] = $v;
            }
            foreach ($list4 as $k=> $v) {
                $list[] = $v;
            }
            for ($i=0; $i <count($list); $i++) { 
                $list[$i]["type_name"] = explode("（",$list[$i]["type_name"])[0];
                $list[$i]['brand_name'] = explode('（',$bn['brand_name'])[0] ;
            }
            $this->ajaxReturn($list);
        }
    }
}