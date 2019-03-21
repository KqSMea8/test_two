<?php
namespace Home\Controller;

//use Common\Cls\WrapController;
use Common\Lib\MobileDetect;
//use System\Service\AppVersionService;
//use Wechat\Service\WechatService;
use User\Model\AppDownloadModel;

use Think\Controller;

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015-04-28
 * Time: 11:50
 */
class AppDownloadController extends Controller
{

	//苹果版APP下载地址
	private $appIosLink = 'https://itunes.apple.com/us/app/餐讯网/id1137017065?l=zh&ls=1&mt=8';

	/**
	 * app扫描的接口
	 * @param $os 　可以为空，代表操作系统的类型
	 * @return null
	 */
	public function index($os = null)
	{
		$mobileDetect = new MobileDetect();
		if (empty($os)) {

			if ($mobileDetect -> isiPhone()) {

                $this -> appDownloadForAndroidAndIos('iPhone','ios');
			} else if ($mobileDetect -> isAndroidOS()) {

                $this -> appDownloadForAndroidAndIos('Android','and');
			} else {

				$this->display();
			}
		} elseif ('and' == $os) {

            $this -> appDownloadForAndroidAndIos('Android','and');
		} elseif ('ios' == $os) {

            $this -> appDownloadForAndroidAndIos('iPhone','ios');
			exit;
		} else {

			$this->display();
		}
	}

	/**
	 * 下载导航界面
	 */
	public function download()
	{
		$this->display();
	}

	public function introduce()
	{
		$this->display();
	}

	/**
	 * 微信服务号下载时的链接。用户在菜单中点击链接，会展示一个操作提示界面，使用浏览器打开时，会跳转到下载界面
	 * @author tianxiaohui
	 * @lastedittime 2014年9月23日10:41:17
	 */
	public function downloadApp()
	{
		$userAgent = $_SERVER['HTTP_USER_AGENT'];
		$mobileDetect = new Mobile_Detect();
		if (($mobileDetect->isiPhone())) {

			$version = $mobileDetect->version('iPhone');
			if (!strpos($userAgent, 'MicroMessenger')) {

				//LogApp("ios", $version);
				header('Location:' . $this->appIosLink);
			} else {

				$this->display("download");
			}
		} else if ($mobileDetect->isAndroidOS()) {

			$version = $mobileDetect->version('Android');
			//LogApp("and", $version);
			$this->display("download");
		} else {

			$this->display();
		}
	}

	private function logApp($type, $version)
	{
		$log = new LogApp();
		$conent = "##IPAddress:" . $_SERVER['REMOTE_ADDR'] . ";Version:{$version};DownloadTime:" . date("Y-m-d H:i:s", time()) . "\r\n";
		$log->writeAppLog($type, $conent);
	}

	//下载apk
	private function downloadApk($filePath)
	{
        set_time_limit(C('MAX_RUN_TIME'));
		$ContentLength = filesize($filePath);
		if (!$ContentLength) {

			header('Content-Type:text/html;charset=utf-8');
			die('文件无效');
		}
		header('Content-Type: application/vnd.android.package-archive');
		header('Content-Disposition: attachment;filename=lz517.apk');
		header('Accept-Ranges: bytes');
		header('Content-Transfer-Encoding: binary');
		header('Content-Length: ' . $ContentLength);
		header('Cache-Control: max-age=0');

		$fopen = fopen($filePath, 'r');
		while ($cache = fread($fopen, '1024')) {
			echo $cache;
		}

		fclose($fopen);
		exit;
	}

    /**
     * android 和 ios app下载
     * @param string $phoneType 手机型号
     * @param string $SystemType 系统型号
     * @author  HQ
     */
    private function appDownloadForAndroidAndIos($phoneType,$SystemType)
    {
        //获取用户手机型号
        $mobileDetect = new MobileDetect();
        $version = $mobileDetect -> version($phoneType);

        //给页面返回最新的app安装包
        $this -> assign('AppDownloadUrl','http://opensns.lz517.com/Uploads/App/餐讯网.apk');

        $this -> assign('os',$SystemType);

		//获取js安装包

        $this->appId = 'wxd24c5ec0166b2c74';
        $this->timestamp = time();
        $this->nonceStr = $this->createNonceStr();
        $this->signature = $package ['signature'];

        $this->display("download");
    }

    /**
     * 获取16位随机字符串
     *
     * @param number $length
     * @return string
     */
    private function createNonceStr($length = 16)
    {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }

    /**
     * 添加App下载数
     */
    public function addAppXZNum(){
        $status = I('post.status');

        //查询今日有没有添加的数据 如果存在就在原有的数据上叠加 反之新增
        $appData = (new AppDownloadModel())->where(['create_time'=>['LIKE','%'.Date('Y-m-d').'%']])->find();

        if($appData){
            //status = 1时 记录用户点击下载按钮数
            //status = 2时 记录用户进入下载页面数
			//status = 3时 记录用户扫码进入数
            $data = [];
            if($status == 1){
                $data = [
                    'id'=>$appData['id'],
                    'click_num'=>$appData['click_num']+1,
                ];
            }elseif($status == 2){
                $data = [
                    'id'=>$appData['id'],
                    'download_num'=>$appData['download_num']+1,
                ];
            }elseif($status == 3){
                $data = [
                    'id'=>$appData['id'],
                    'sweep_num'=>$appData['sweep_num']+1,
                ];
            }

            (new AppDownloadModel())->editData($data);
        }else{
            //status = 1时 记录用户点击下载按钮数
            //status = 2时 记录用户进入下载页面数
            //status = 3时 记录用户扫码进入数
            $data = [];
            if($status == 1){
                $data = [
                    'click_num'=>1,
                    'create_time'=>Date('Y-m-d'),
                ];
            }elseif($status == 2){
                $data = [
                    'download_num'=>1,
                    'create_time'=>Date('Y-m-d'),
                ];
            }elseif($status == 3){
                $data = [
                    'sweep_num'=>1,
                    'create_time'=>Date('Y-m-d'),
                ];
            }
            (new AppDownloadModel())->editData($data);
        }

        echo true;
    }

    //linkedme服务端配置
    //用来唤醒APP
    public function linkedme(){
        $this->display();
    }

}


