<?php
/**
 * 机型搜索控制器
 * PHP version 5
 * @package   ehuigou PC 2.0
 * @author    Antonia <471699715@qq.com>
 * @copyright 2016 Antonia
 */
namespace Home\Controller;
use Think\Controller;
class SearchController extends Controller {
     /**
     * 首页
     * @param HttpRequest $request
     * @return html
     * @author Antonia 
     */
    public function index()
    {   
    	$brand_id=I('get.bid','','htmlspecialchars'); 
		  $cate_id=I('get.tid','','htmlspecialchars');
		  if (intval($_GET["tid"])) {
        $tid = intval($_GET["tid"]);
        $where['cate_id'] = array('like','%,'.$tid.',%');
      }
      if ($brand_id) {
        $brand_name = M("Brands")->field("brand_name")->find($brand_id);
      }
      $brand_list = M("Brands")->order("sort asc,add_time desc")
                               ->field("brand_id,brand_name,brand_logo")
                               ->limit(32)
                               ->where($where)
                               ->select();
      $filters = array();
      if (!empty($_GET['key'])) {
        if ($_GET["type_id"]==2) {
          !empty($_GET['key']) && $filters['type_name'] = $_GET["key"];
        }else{
          $key = $this->mb_str_split(trim($_GET["key"]));
          for ($i=0; $i <count($key) ; $i++) {
            $map[] = array("like","%".$key[$i]."%");
          }
          !empty($_GET['key']) && $filters['type_name'] = $map;
        }
      }
      !empty($_GET['tid']) && $filters['cate_id'] = intval($_GET["tid"]);
      $filters["is_state"] = 1;
      !empty($_GET['bid']) && $filters['brand_id'] = intval($_GET["bid"]);
      $model = D("Types");
      $count = $model->where($filters)
                     ->count();
      $Page  =  new \Think\Page($count,11);
      $list = $model->order('sort asc,add_time desc')
                    ->limit($Page->firstRow.','.$Page->listRows)
                    ->where($filters)
                    ->select();
      $map = array();
      for ($i=0; $i < 15; $i++) { 
        $id.=mt_rand(0,2350).",";
      }
      $map["is_state"] = 1;
      $type_list = D("Types")->where($map)
                             ->order("sort asc")
                             ->limit(24)
                             ->select();
      $seo_array=$this->seo_change($cate_id,$brand_id);
      if (empty($list)) {
        $this->assign("display",1);
      }
      !empty($_GET["p"]) ? $p = $_GET["p"] : $p = 0;
      $this->assign("type_list",$type_list);
      $this->assign("brand_name",$brand_name);
      $this->assign('count', $count);
      $this->assign('list',$list);
      $this->assign('page',$Page->show());
      $this->assign('p',$p);
		  $this->assign("keyword", remove_xss($_GET["key"]));
      $this->assign("bid", intval($_GET["bid"]));
      $this->assign("tid", intval($_GET["tid"]));
		  $this->assign('tag1_url',$seo_array[0]);
		  $this->assign('seo',$seo_array[1]);
		  $this->assign('tag2_url',$seo_array[2]);
		  $this->assign("brand_list", $brand_list);
      $this->display();
    }

    /**
     * UTF-8字符串截取
     * @param HttpRequest $request
     * @return array
     * @author Antonia
     */
    protected function mb_str_split($str,$split_length=1,$charset="UTF-8"){
      if(func_num_args()==1){
        return preg_split('/(?<!^)(?!$)/u', $str);
      }
      if($split_length<1)return false;
      $len = mb_strlen($str, $charset);
      $arr = array();
      for($i=0;$i<$len;$i+=$split_length){
        $s = mb_substr($str, $i, $split_length, $charset);
        $arr[] = $s;
      }
      return $arr;
    }

    /**
     * seo
     * @param HttpRequest $request
     * @return array
     * @author Antonia
     */
	public function seo_change($cate_id,$brand_id,$type_id=0){   //判断页面层数 做出seo输出判断
	    if($cate_id>0){
		     switch($cate_id){  
			     case '1':
			     $data[0]="> <a href='/search/index/tid/1/bid/0.html' style='color:#666;'>手机回收</a>";
				 $cate_name='手机';
			     break;
			     case '2':
			     $data[0]="> <a href='/search/index/tid/2/bid/0.html' style='color:#666;'>平板电脑回收</a>";
				 $cate_name='平板电脑';
			     break;
			     case '3':
			     $data[0]="> <a href='/search/index/tid/3/bid/0.html' style='color:#666;'>笔记本电脑回收</a>";
				 $cate_name='笔记本电脑';
			     break;
			     case '5':
			     $data[0]="> <a href='/search/index/tid/5/bid/0.html' style='color:#666;'>数码相机回收</a>";
				 $cate_name='数码相机 ';
			     break;
			}
		}
	    if($cate_id>0&&$brand_id<1){
		    switch($cate_id){
				case '1':
				$data[1]=C('HUIGOU_CATE_SEO');
				break;
				case '2':
				$data[1]=$this->seo_changes('手机','平板电脑',C('HUIGOU_CATE_SEO'));
				$cate_name='平板电脑';
				break;
				case '3':
				$data[1]=$this->seo_changes('手机','笔记本电脑',C('HUIGOU_CATE_SEO'));
				$cate_name='笔记本电脑';
				break;
				case '5':
				$data[1]=$this->seo_changes('手机','数码相机',C('HUIGOU_CATE_SEO'));
				$cate_name='数码相机 ';
				break;
			}		
		}elseif($cate_id<1&&$brand_id<1){  
		    $data[0]=$cate_id;
			$data[1]=C('HUIGOU_HOME_SEO');
		}elseif($brand_id>0){
		    $where_b['brand_id']=$brand_id;
		    $brand_name = M("Brands")->where($where_b)
                                    ->getField('brand_name');
            switch ($cate_id) {
                case '1':
                $brand_name.="手机";
                break;
                case '2':
                $brand_name.="平板";
                break;
                case '3':
                $brand_name.="笔记本";
                break;
                case '5':
                $brand_name.="相机";
                break;
            }
            if (!empty($type_id)) {
                $type_info = D("Types")->field("type_name")
                                       ->find($type_id);
                $data[3] = "><a href='/item/index/id/".$type_id."'style='color:#666;'>".$type_info["type_name"]."回收</a>";
                // $brand_name .=$type_info["type_name"];
            }
            $brand_name .="回收";
		    $data[2]="> <a href='/search/index/tid/".$cate_id."/bid/".$brand_id.".html' style='color:#666;'>".$brand_name."</a>";
        if (!empty($type_id)) {
          $brand_types_name =$type_info["type_name"];
            switch ($cate_id) {
                case '1':
                $brand_types_name.="手机";
                break;
                case '2':
                $brand_types_name.="平板";
                break;
                case '3':
                $brand_types_name.="笔记本";
                break;
                case '5':
                $brand_types_name.="相机";
                break;
            }          
          $brand_types_name .="回收";
        }

		    $data[1]=$this->seo_changes('苹果Iphone手机',$brand_types_name.$cate_name,C('HUIGOU_BRANDS_CATE_SEO'));
		}
		return $data;
	}

    /**
     * seo输出信息处理
     * @param HttpRequest $request
     * @return array
     * @author Antonia
     */	   
	public function  seo_changes($find,$replace,$seo){  
	  $seo['hl']=str_replace($find,$replace,$seo['h1']); 
		$seo['keywords']=str_replace($find,$replace,$seo['keywords']); 
		$seo['title']=str_replace($find,$replace,$seo['title']);
		$seo['description']=str_replace($find,$replace,$seo['description']);
	  return $seo;
	}
	
    /**
     * 返回搜索
     * @param ajax $request
     * @return html
     * @author Antonia
     */
    public function ajax_list()
    {   
        $key = remove_xss($_POST['key']);
        $one_key = $key;
        $key = $this->mb_str_split($key);
        for ($i=0; $i <count($key) ; $i++) {
          if (!empty($key[$i])) {
            $keys .= "trim(replace(`type_name`,' ','')) like trim(replace('%$key[$i]%',' ','')) and ";
          }
        }
        $cate_id = remove_xss(intval($_POST["cid"]));
        $bid = remove_xss(intval($_POST["bid"]));
        $Model = new \Think\Model();
        if($bid != 0){
            $cache = S("brand_".$bid."cid_".$cate_id."key_".$one_key);
            if ($cache) {
                $list = $cache;
            }else{
                $list = $Model->query("select * from `yhg_types` where $keys  brand_id = $bid and cate_id =$cate_id and is_state = 1 order by `sort` asc limit 7");
                S("brand_".$bid."cid_".$cate_id."key_".$one_key,$list,7200);
            }
        }else{
            if (!empty($cate_id)) {
              $cache = S("cid_".$cate_id."key_".$one_key);
              if ($cache) {
                  $list = $cache;
              }else{
                $list = $Model->query("select * from `yhg_types` where $keys cate_id =$cate_id and is_state = 1 order by `sort` asc limit 7");   
                S("cid_".$cate_id."key_".$one_key,$list,7200);
              }
            }else{
              $cache = S("key_".$one_key);
              if ($cache) {
                  $list = $cache;
              }else{
                $list = $Model->query("select * from `yhg_types` where $keys is_state = 1 order by `sort` asc limit 7");   
                S("key_".$one_key,$list,7200);
              }              
            }
        }
        if (empty($list)) {
            $this->assign("display",1);
        }
        $this->assign("list",$list);
        $this->display();
    }
	
}