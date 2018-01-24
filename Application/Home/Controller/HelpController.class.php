<?php
/**
 * 帮助中心
 * PHP version 5
 * @package   ehuigou PC 2.0
 * @author    Antonia <471699715@qq.com>
 * @copyright 2016 Antonia
 */
namespace Home\Controller;
use Think\Controller;
class HelpController extends Controller {

    /**
     * 显示页面
     * @param HttpRequest $request
     * @return html
     * @author Antonia
     */
    public function index()
    {   
        if ($_GET["cid"] == 2) {
            $map["affiliation_firm"] = array("in","1,21,22,24,25,26,27,28,29,30,32");
            $model = D("Stores");
            $list = $model->cache(true)
                          ->where($map)
                          ->field("store_area")
                          ->group("store_area")
                          ->select();
            for ($i=0; $i <count($list) ; $i++) { 
                $province = M("province")->where("provinceID ='".$list[$i]["store_area"]."'")->cache(true)->find();
                $list[$i]["province"] = $province;
            }
            $this->assign("province",$list);
        }
        $this->assign("cid",$_GET["cid"]);
        $this->assign("bid",!empty($_GET["bid"])?$_GET["bid"] :1);
        $this->display();
    }

    /**
     * 显示页面
     * @param HttpRequest $request
     * @return html
     * @author Antonia
     */
    public function ajax_city()
    {   
        if (empty($_POST)) {
            $data['status'] = 0;
            $data['info'] = '参数错误，请刷新页面后重试';
            $this->ajaxReturn($data,'JSON');
        }
        !empty($_POST["id"]) && $map["store_area"] = $_POST["id"];
        $map["affiliation_firm"] = array("in","1,21,22,24,25,26,27,28,29,30,32");
        $model = D("Stores");
        $list = $model->cache(true)
                      ->where($map)
                      ->field("store_province")
                      ->group("store_province")
                      ->select();
        for ($i=0; $i <count($list) ; $i++) { 
            $cityid .= $list[$i]["store_province"].",";
        }
        $filters["cityID"] = array("in",$cityid);
        $city = M("city")->where($filters)->select();
        $data['status'] = 1;
        $data['data'] = $city;
        $this->ajaxReturn($data,'JSON');
    }

    /**
     * 显示页面
     * @param HttpRequest $request
     * @return html
     * @author Antonia
     */
    public function ajax_area()
    {   
        if (empty($_POST)) {
            $data['status'] = 0;
            $data['info'] = '参数错误，请刷新页面后重试';
            $this->ajaxReturn($data,'JSON');
        }
        !empty($_POST["id"]) && $map["store_province"] = $_POST["id"];
        $map["affiliation_firm"] = array("in","1,21,22,24,25,26,27,28,29,30,32");
        $model = D("Stores");
        $list = $model->cache(true)
                      ->where($map)
                      ->field("store_city")
                      ->group("store_city")
                      ->select();
        for ($i=0; $i <count($list) ; $i++) { 
            $areaid .= $list[$i]["store_city"].",";
        }
        $filters["areaID"] = array("in",$areaid);
        $area = M("area")->where($filters)->select();
        $data['status'] = 1;
        $data['data'] = $area;
        $this->ajaxReturn($data,'JSON');
    }

    /**
     * [加入我们]
     * @param HttpRequest $request
     * @return html
     * @author Antonia
     */
    public function joinUs() {
        $this->display();
    }

    /**
     * [联系我们]
     * @param HttpRequest $request
     * @return html
     * @author Antonia
     */
    public function Us() {
        $this->display();
    }

    /**
     * [关于我们]
     * @param HttpRequest $request
     * @return html
     * @author Antonia
     */
    public function aboutUs() {
        $this->display();
    }

    /**
     * 获取地区门店列表
     * @param HttpRequest $request
     * @return html
     * @author Antonia
     */
    public function getStoreList() {
        !empty($_POST["rid"]) && $filters["store_city"] = intval($_POST["rid"]);
        !empty($_POST["store_name"]) && $filters["store_name"] = array("like","%".$_POST["store_name"]."%");
        $filters["affiliation_firm"] = array("in","1,21,22,24,25,26,27,28,29,30,32");
        $store_model = D("Stores");
        $stores = $store_model->where($filters)
                                ->field("store_id,store_name,store_addr,store_lbs,store_phone")
                                ->select();
        $this->assign("store_list", $stores);
        $this->display("store_page");
    }
}