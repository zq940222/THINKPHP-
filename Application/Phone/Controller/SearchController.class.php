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
class SearchController extends Controller {

    /**
     * 显示页面
     * @param HttpRequest $request
     * @return html
     * @author Antonia
     */
    public function index () {
      if (!empty($_GET["cid"])) {
          switch ($_GET["cid"]) {
            case '1':
              $cate_name = "手机回收";
              break;
            case '2':
              $cate_name = "平板回收";
              break;
            case '3':
              $cate_name = "笔记本回收";
              break;
            case '5':
              $cate_name = "数码相机回收";
              break;            
            default:
              # code...
              break;
          }
        $this->assign("cid",$_GET["cid"]);
        $this->assign("cate_name",$cate_name);
      }else{
        $this->assign("cid",1);
        $this->assign("cate_name","手机回收");
      }

      !empty($_GET["cid"]) && $filters["cate_id"] = array('like','%'.remove_xss(intval($_GET["cid"])).'%');
      $brand_list = M("Brands")->cache(true)
                               ->where($filters)
                               ->order("sort asc,add_time desc")
                               ->field("brand_id,brand_name,brand_logo")
                               ->select();                      
      $this->assign("brand_list", $brand_list);
      $this->display();
    }

    /**
     * 简单检索
     *
     * @param html $request
     *
     * @return html
     * @author Antonia
     */
    public function Search()
    {
      $type_list = D("Types")->cache(true)
                             ->order("has_sold desc")
                             ->limit(17)
                             ->field("type_name,type_id")
                             ->select();
      unset($type_list[0]);
      if ($_SESSION["history"]) {
        $this->assign("history",$_SESSION["history"]);
      }
      $this->assign("list",$type_list);
      $this->display();
    }

    /**
     * 返回搜索
     *
     * @param ajax $request
     *
     * @return html
     * @author Antonia
     */
    public function ajax_list()
    {   
        $key = remove_xss($_POST['key']);
        $cate_id = remove_xss(intval($_POST["cid"]));
        $Model = new \Think\Model();
        if(!empty($_POST["bid"])){
          $bid = remove_xss(intval($_POST["bid"]));
          $list = $Model->query("select * from `yhg_types` where `brand_id` = $bid and cate_id = $cate_id order by `sort` asc");
        }else{
          $list = $Model->query("select * from `yhg_types` where trim(replace(`type_name`,' ','')) like trim(replace('%$key%',' ','')) and cate_id = $cate_id order by `sort` asc");
        }
        $this->assign("list",$list);
        $this->display();
    }




        
}