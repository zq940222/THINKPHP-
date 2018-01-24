<?php
/**
 * 属性选择器
 * PHP version 5
 * @package   ehuigou PC 2.0
 * @author    Antonia <471699715@qq.com>
 * @copyright 2016 Antonia
 */
namespace Home\Controller;
use Home\Controller\Search;
class ItemController extends SearchController {
    /**
     * ajax获取右公式界面
     * @param HttpRequest $request
     * @return html
     * @author Antonia
     */
    public function index()
    {   
        $id =$_GET["id"];
        $type_info = D("Types")->field("type_id,cate_id,brand_id,type_name,type_photo,left_formula_id,right_formula_id,highestprice")
                               ->find($id);
        $seo_array = $this->seo_change($type_info["cate_id"],$type_info["brand_id"],$type_info["type_id"]);
        if($type_info["cate_id"] == 3 || $type_info["cate_id"] == 4) {
            $map["type_id"] = $type_info["type_id"];
            $conflist = array_keys(C("CONFLIST"));
            $field = implode(",", $conflist);
            $list = M("type_confs")->where($map)->field($field)->select();
            foreach ($list as $l) {
                foreach ($l as $k => $v) {
                    if(!in_array($v ,$conf_data[$k])) {
                        $conf_data[$k][] = $v;
                    }
                }
            }
            $this->assign("conf_data",$conf_data); 
            $this->assign("conflist",C("CONFLIST")); 
        } else {
            $left_list = D("Params")->where("formula_id=".$type_info["left_formula_id"])
                                    ->field("parameter_id,parameter_name")
                                    ->relation(true)
                                    ->order("sort asc")
                                    ->select();
            $this->assign("left_list",$left_list); 
        }
        $right = D("Featureviews")->where("formula_id=".$type_info["right_formula_id"])
                                    ->field("interface_id,interface_key,interface_name,is_multi")
                                    ->order("sort asc")
                                    ->select();
        $right_list = array();
        $right_parpm_model = D("Featureparams");
        foreach ($right as $r) {
            $right_ids = explode(',', trim($r["interface_key"],','));
            $map = array("right_id" => array("in", $right_ids));
            $r["param_list"] = $right_parpm_model->where($map)
                                                    ->field("right_id,right_name,paraphrase")
                                                    ->order("field(right_id,".implode(",",$right_ids).")")
                                                    ->select();

            $right_list[] = $r;
        }
        $this->assign("right_list",$right_list);


        $trend = M("trend")->where("type_id ='".$id."'")->find();
        $this->assign("last",count($right_list)); 
        $this->assign("type_info",$type_info);
        $this->assign('tag1_url',$seo_array[0]);
        $this->assign('seo',$seo_array[1]);
        $this->assign('tag2_url',$seo_array[2]);
        $this->assign('tag3_url',$seo_array[3]);
        $this->assign("trend",$trend);
        $this->display();
    }

    /**
     * 获取配置列表
     *
     * @param HttpRequest $request
     *
     * @return html
     * @author Antonia
     */
    public function getConfList() {
        if(empty($_POST["data"]) || empty($_POST["tid"])) {
            $data['status'] = 0;
            $data['info'] = '参数缺失';
            $data['data'] = array();
            $this->ajaxReturn($data,'JSON');
        }
        $data = json_decode($_POST["data"]);

        $map = array();
        $map["type_id"] = intval($_POST["tid"]);

        $conflist = array_keys(C("CONFLIST"));

        $field = implode(",", $conflist);

        if(!empty($data)) {
            $fields = array();
            foreach ($data as $d) {
                $conf_field = $d->tid;
                $fields[] = $conf_field;
                $map[$conf_field] = $d->name;
            }
            $field = implode(",", array_diff($conflist,$fields));
        }

  
        $list = M("type_confs")->where($map)->field($field)->select();

        $conf_data = array();

        foreach ($list as $l) {
            foreach ($l as $k => $v) {
                if(!in_array($v ,$conf_data[$k])) {
                    $conf_data[$k][] = $v;
                }
            }
        }
        $data = array();
        $data['status'] = 1;
        $data['info'] = 'success';
        $data['data'] = $conf_data;
        $this->ajaxReturn($data,'JSON');
    }
    /**
     * 加入订单车
     * @param HttpRequest $request
     * @return html
     * @author Antonia  
     */
    public function Cart() {
        if(empty($_POST["cart_id"])) {
            $data['status'] = 0;
            $data['info'] = '参数缺失,请重试';
            $this->ajaxReturn($data,'JSON');
        }
        $data["cart_id"] = intval($_POST["cart_id"]);
        $data["cart_state"] = 0;
        $rs = M("order_cart")->save($data);
        if ($rs) {
            $data=array();
            $data['status'] = 1;
            $this->ajaxReturn($data,'JSON');
        }else{
            $data['status'] = 1;
            $data['info'] = "参数错误";
            $this->ajaxReturn($data,'JSON');
        }
    }
    /**
     * 估价留存
     * @param HttpRequest $request
     * @return html
     * @author Antonia  
     */
    public function addCarts() {
        if(empty($_POST["tid"])) {
            $data['status'] = 0;
            $data['info'] = '参数缺失,请重试';
            $this->ajaxReturn($data,'JSON');
        }
        $id = intval($_POST["tid"]);
        $type_info = D("Types")->field("type_id,cate_id")->find($id);
        if(empty($type_info)) {
            $data['status'] = 0;
            $data['info'] = '该机型不存在,请重试';
            $this->ajaxReturn($data,'JSON');
        }
        $data = array();
        $data["type_id"] = $id;
        if($type_info["cate_id"] == 3 || $type_info["cate_id"] == 4) {
            $conf = json_decode($_POST["conf"]);
            $conf_data = array();
            foreach ($conf as $c) {
                $conf_data[$c->id] = $c->name;
            }
            $data["conf"] = $conf_data;
        } else {
            $data["left"] = explode(",", rtrim($_POST["leftstr"],","));
        }
        $data["right"] = explode(",", rtrim($_POST["rightstr"],","));
        $count = D("Orders")->getPrice($id,$data);
        if(!empty($_SESSION["mobile"])){
            $cart["user_phone"] = $_SESSION["mobile"];
        }else if (empty($_COOKIE["visitor"])) {
            $visitor = D("Orders")->getID();
            setcookie('visitor',$visitor,0,'/','.ehuigou.com');
            $cart["purchaser"] = $visitor;
        }else{
            $cart["purchaser"] = $_COOKIE["visitor"];
        }
        $cart["type_id"] = $id;
        $cart["add_time"] = time();
        $cart["user_param"] = serialize($data);
        $cart["user_price"] = $count;
        $cart["cart_state"] = 1;
        $cart_id = M("order_cart")->add($cart);
        $data = array();
        $data['status'] = 1;
        $data['info'] = $count;
        $data["msg"] = $cart_id;
        $this->ajaxReturn($data,'JSON');
    }

}