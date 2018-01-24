<?php
/**
 * 属性选择控制器
 * PHP version 5
 * @package   ehuigou phone 2.0
 * @author    Antonia <471699715@qq.com>
 * @copyright 2016 Antonia
 */
namespace Phone\Controller;
use Think\Controller;
class ItemController extends Controller {

    /**
     * 非电脑类别显示页
     * @param HttpRequest $request
     * @return html
     * @author Antonia
     */
    public function index () {
        $id = intval($_GET['id']);
        
        $type_info = D("Types")->cache(true)
                                ->field("type_id,cate_id,type_name,type_photo,left_formula_id,right_formula_id,highestprice")
                                ->find($id);
        
        if ($type_info["cate_id"] == 3) {
            $this->redirect('index1', array('id' => $_GET["id"]));
        }
        
        if(empty($type_info)) {
            redirect(__APP__."/404.html");
        }
        
        $left_list = D("Params")->cache(true)
                                ->where("formula_id=".$type_info["left_formula_id"])
                                ->field("parameter_id,parameter_name")
                                ->relation(true)
                                ->order("sort asc")
                                ->select();
        
        $right = D("Featureviews")->cache(true)
                                    ->where("formula_id=".$type_info["right_formula_id"])
                                    ->field("interface_id,interface_key,interface_name,is_multi")
                                    ->order("sort asc")
                                    ->select();
        
        $this->assign("type_info", $type_info);
        $this->assign("left_list",$left_list); 
        $this->assign("right_list",$right);
        $this->display();
    }


    /**
     * 电脑类别显示页
     *
     * @param HttpRequest $request
     *
     * @return html
     * @author Antonia
     */
    public function index1 () {
        $id = intval($_GET['id']);

        $type_info = D("Types")->cache(true)
                                ->field("type_id,cate_id,type_name,type_photo,left_formula_id,right_formula_id,highestprice")
                                ->find($id);

        if(empty($type_info)) {
            redirect(__APP__."/404.html");
        }

        $right = D("Featureviews")->cache(true)
                                  ->where("formula_id=".$type_info["right_formula_id"])
                                  ->field("interface_id,interface_key,interface_name,is_multi")
                                  ->order("sort asc")
                                  ->select();
        $right_list = array();

        $right_parpm_model = D("Featureparams");
        foreach ($right as $r) {

            $right_ids = explode(',', trim($r["interface_key"],','));
            $map = array("right_id" => array("in", $right_ids));
            $r["param_list"] = $right_parpm_model->cache(true)
                                                 ->where($map)
                                                 ->field("right_id,right_name,paraphrase")
                                                 ->order("field(right_id,".implode(",",$right_ids).")")
                                                 ->select();
            $right_list[] = $r;

        }

        $this->assign("conflist",C("CONFLIST")); 
        $this->assign("type_info", $type_info);
        $this->assign("right_list",$right_list);
        $this->display("index1");
    }





    /**
     * ajax获取公式
     *
     * @param HttpRequest $request
     *
     * @return html
     * @author Antonia  
     */
    public function get_list()
    {
        $model = D("Congtions");
        $right_parpm_model = D("Featureparams");
        if ($_POST["tid"] == 1) {

            $filters["parameter_id"] = $_POST["rid"];
            $list = $model -> where($filters)->select();

        }else{

            $filters["interface_id"] = $_POST["rid"];
            $right = D("Featureviews")->where($filters)
                                    ->field("interface_id,interface_key,interface_name,is_multi")
                                    ->order("sort asc")
                                    ->find();
            $right_ids = explode(',', trim($right["interface_key"],','));
            $map = array("right_id" => array("in", $right_ids));
            $list = $right_parpm_model->where($map)
                                        ->field("right_id,right_name")
                                        ->order("field(right_id,".implode(",",$right_ids).")")
                                        ->select();
            $this->assign("list_type",1);
        }

        $this->assign("list",$list);
        $this->display("list");
    }



    /**
     * 获取电脑配置列表
     *
     * @param HttpRequest $request
     *
     * @return ajax
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
     * 加入订单
     *
     * @param HttpRequest $request
     *
     * @return html
     * @author Antonia
     */
    public function addCart() {
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
        $data["type_id"] = $type_info["type_id"];

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

        $cart["user_param"] = serialize($data);
        $cart["user_price"] = $count;
        $cart_id = D("Order_cart")->add($cart);
        if (!empty($cart_id)) {
            $_SESSION["cart_id"] = $cart_id;
            $data = array();
            $data['status'] = 1;
            $data['info'] = 'success';
            $this->ajaxReturn($data,'JSON');
        }else{
            $data = array();
            $data['status'] = 0;
            $data['info'] = "参数缺失,请重试";
            $this->ajaxReturn($data,'JSON');
        }

    }

}