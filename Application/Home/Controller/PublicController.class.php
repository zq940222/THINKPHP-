<?php
/**
 * 公共控制器
 * PHP version 5
 * @package   ehuigou PC 2.0
 * @author    Antonia <471699715@qq.com>
 * @copyright 2016 Antonia
 */
namespace Home\Controller;
use Think\Controller;
class PublicController extends Controller {

   /**
     * 活动页面1
     * @param HttpRequest $request
     * @return html
     * @author Antonia
     */
    public function huodong1()
    {
        $this->assign("region_list", C("STORE_REGION_LIST"));
        $this->display();
    }

    /**
     * [意见反馈]
     * @param HttpRequest $request
     * @return html
     * @author Antonia
     */
    public function feedback(){
        $this->display();
    }

    /**
     * [意见反馈添加]
     * @param HttpRequest $request
     * @return html
     * @author Antonia
     */
    public function add_feedback(){
        $model = M("feedback");
        $data["relation"] = $_POST["relation"];
        $data["comment"] = $_POST["comment"];
        $data["add_time"] = time();
        $rs = $model->add($data);
        if ($rs) {
            $data= array();
            $data["status"] = 1;
            $this->ajaxReturn($data,'json');
        }
    }

   /**
     * 企业回收
     * @param HttpRequest $request
     * @return html
     * @author Antonia
     */
    public function enterprise()
    {
        $this->display();
    }

    /**
     * 保存企业回收信息
     *
     * @param HttpRequest $request
     *
     * @return html
     * @author Antonia
     */
    public function send_enterprise()
    {
        $verify = new \Think\Verify();
        $rs = $verify->check($_POST["verify"]);
        if(!$rs) {
            $data['status'] = 0;
            $data['info'] = "验证码错误，请刷新验证码后重试";
            $this->ajaxReturn($data,'JSON');
        }

        $model = M("enterprise");
        $data = array();
        $data["enterprise_list"] = remove_xss($_POST["enterprise_list"]);
        $data["enterprise_name"] = remove_xss($_POST["enterprise_name"]);
        $data["user_name"] = remove_xss($_POST["user_name"]);
        $data["user_phone"] = remove_xss($_POST["user_phone"]);
        $data["enterprise_name"] = remove_xss($_POST["enterprise_name"]);
        $data["add_time"] = time();
        $rs = $model->add($data);
        if ($rs) {
            $data['status'] = 1;
            $data['info'] = "提交成功,请保持电话畅通，我们将竭诚为您服务";
            $this->ajaxReturn($data,'JSON');
        }else{
            $data['status'] = 0;
            $data['info'] = "提交失败,请联系克服人员";
            $this->ajaxReturn($data,'JSON');
        }
    }

    /**
     * 生成验证码
     *
     * @param HttpRequest $request
     *
     * @return Image
     * @author Fee
    */
    Public function verify(){
        $Verify = new \Think\Verify();
        $Verify->fontSize = 15;
        $Verify->length = 4;
        $Verify->useNoise = false;
        $Verify->useCurve = false;
        $Verify->entry();
    }
}
?>