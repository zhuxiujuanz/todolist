<?php
namespace Home\Controller;
use Think\Controller;
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Methods:GET, POST, PUT,OPTIONS, DELETE");
header("Access-Control-Allow-Headers:DNT,X-Mx-ReqToken,Keep-Alive,User-Agent,X-Requested-With,If-Modified-Since,Cache-Control,Content-Type, Accept-Language, Origin, Accept-Encoding");
class IndexController extends Controller
{
    public function index()
    {
        $user   = M('user');
        $count = $user->count();
        $Page = new \Think\Page($count,3);
        $nowPage = isset($_GET['page'])?$_GET['page']:1;
         $list = $user->page($nowPage.','.$Page->listRows)->select();
         $this->ajaxReturn(array('data'=>$list,'total'=>intval($count), 'status'=>200));
    }

    public function login()
    {
        $user          = M('user');
        $where['user'] = $_POST['user'];
        $where['pwd']  = $_POST['pwd'];
        $res           = $user->where($where)->find();
        if ($res) {
            $this->ajaxReturn(array('data' => $res, 'status' => 200, 'msg' => '登录成功'));
        } else {
            $this->ajaxReturn(array('status' => 201, 'msg' => '用户名或密码不正确'));
        }

    }

    public function register()
    {
        $user          = M('user');
        $where['username'] = $_POST['username'];
        $this->showLOG($_POST);
        $res           = $user->where($where)->find();
        if ($res) {
            $this->ajaxReturn(array('status' => 201, 'msg' => '用户名不能重复'));
        } else {
            $result = $user->add($_POST);
            if ($result) {
                $this->ajaxReturn(array('data' => $result, 'status' => 200, 'msg' => '注册成功'));
            } else {
                $this->ajaxReturn(array('status' => 201, 'msg' => '注册失败'));
            }
        }
    }
  public function update()
  {
    $user          = M('user');
    $where['id'] = $_GET['id'];
    $data = I('put.');
    $res = $user->where($where)->save($data);
    if ($res) {
      $this->ajaxReturn(array('status' => 200, 'msg' => '修改成功'));
    }else{
      $this->ajaxReturn(array('status' => 201, 'msg' => '修改失败'));

    }

  }
    public function delete()
    {
      $user          = M('user');
      $where['id'] = $_GET['id'];
      $res = $user->where($where)->delete();
      $this->showLOG($res);
      if ($res) {
        $this->ajaxReturn(array('status' => 200, 'msg' => '删除成功'));
      }else{
        $this->ajaxReturn(array('status' => 201, 'msg' => '删除失败'));

      }

    }
    public function showLOG($e)
    {
      $trace = debug_backtrace();
      $title = '['.date('Y-m-d H:i:s').']'.str_replace($_SERVER['DOCUMENT_ROOT'],' ',$trace[0]['file']);
      file_put_contents("log.log", var_export($title, true) . "\r\n", FILE_APPEND);
      file_put_contents("log.log", var_export($e, true) . "\r\n", FILE_APPEND);
    }

}
