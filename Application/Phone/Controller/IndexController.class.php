<?php
/**
 * 首页控制器
 * PHP version 5
 * @package   ehuigou phone 2.0
 * @author    Antonia <471699715@qq.com>
 * @copyright 2016 Antonia
 */
namespace Phone\Controller;
use Think\Controller;
class IndexController extends Controller {
    /**
     * 首页
     * @param HttpRequest $request
     * @return html
     * @author Antonia
     */
    public function index () {
        $ad_list = M("Ads")->cache(true)
                            ->order("sort asc,add_time desc")
                            ->field("ad_name,ad_url,ad_img")
                            ->select();
        $this->assign("ad_list", $ad_list);
        $this->display();
    }
}