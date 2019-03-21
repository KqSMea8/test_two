<?php
/**
 * @author  XiaohuiTian.
 * email: tianxh@jpgk.com.cn
 * QQ:2018997757 * Date: 2016/7/6
 * Time: 15:30
 */

namespace News\Widget;
use Think\Controller;


/**
 * Class NewDetail
 *
 *
 * @package News\Widget
 */
class NewDetailWidget extends Controller
{
	public function shareH5(){

		$this->display("Widget/shareH5");
	}

	public function newsCommentH5($newsId){

		$map = [];
		$map["app"] = "News";
		$map["mod"] = "index";
		$map["row_id"] = $newsId;
		$map["status"] = 1;
		$map["pid"] = 0;

		$comments = M("LocalComment")->field("id,uid,content,create_time")->where($map)->order("id desc")->limit(0,2)->select();
		$uids = array_unique(array_column($comments,"uid"));
		$commentIds = array_unique(array_column($comments,"id"));

		//增加用户信息
		$members = [];
		foreach ($uids as &$e) {
			$members[] = query_user(array('uid', 'avatar64', 'nickname', 'space_url'), $e);
		}
		$members = useFieldAsArrayKey($members,"uid");

		/*$commentMap = [];
		$commentMap["appname"] = "News";
		$commentMap["row"] = ["in",$commentIds];
		$supportDbModel = M("Support");
		$supports = $supportDbModel->field("sum(1) as sum,row as commentId")->where($commentMap)->group("row")->select();
		$supports = useFieldAsArrayKey($supports,"commentId");
		foreach($comments as &$comment){

			$comment["support"] = $supports[$comment["id"]]["sum"];
		}*/

		foreach($comments as &$comment){

			$comment["timeText"] = date("H:i",$comment["create_time"]);
		}

		$this->assign("comments",$comments);
		$this->assign("members",$members);
		$this->display("Widget/newsCommentH5");
	}
}