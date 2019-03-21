<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 15-4-28
 * Time: 上午11:30
 * @author 郑钟良<zzl@ourstu.com>
 */

namespace CircleVideo\Controller;


use Admin\Model\MemberModel;
use Common\Lib\WX\JSSDK;
use News\Model\NewsModel;
use Think\Controller;
use Live\Model\LiveRoomModel;
use CircleVideo\Model\CircleVideoModel;
use CircleVideo\Model\CircleVideoDetailModel;

class IndexController extends Controller{

    protected $newsModel;
    protected $newsDetailModel;
    protected $newsCategoryModel;
    protected $liveModel;

    function _initialize()
    {
        if(isset($_POST['keywords'])){
            $_GET['keywords']=json_encode(trim($_POST['keywords']));
        }
        $keywords=$_GET['keywords'];

        $this->newsModel = D('News/News');
        $this->newsDetailModel = D('News/NewsDetail');
        $this->newsCategoryModel = D('News/NewsCategory');
        $this->liveModel = new LiveRoomModel();

        $tree = $this->newsCategoryModel->getTree(0,true,array('status' => 1));
        $this->assign('tree', $tree);
        $menu_list['left'][]=array( 'title' => L('_HOME_'), 'href' => U('News/Index/index'),'tab'=>'home');
        foreach ($tree as $category) {
            $menu = array('tab' => 'category_' . $category['id'], 'title' => $category['title'], 'href' => U('News/index/index', array('category' => $category['id'],'keywords'=>$keywords)));
            if ($category['_']) {
                $menu['children'][] = array( 'title' => L('_EVERYTHING_'), 'href' => U('News/index/index', array('category' => $category['id'],'keywords'=>$keywords)));
                foreach ($category['_'] as $child)
                    $menu['children'][] = array( 'title' => $child['title'], 'href' => U('News/index/index', array('category' => $child['id'],'keywords'=>$keywords)));
            }
            $menu_list['left'][] = $menu;
        }
        $menu_list['right']=array();
        if(is_login()){
            $menu_list['right'][]=array('tab' => 'myNews', 'title' => L('_MY_CONTRIBUTIONS_'), 'href' =>U('News/index/my'));
        }

        $show_edit=S('SHOW_EDIT_BUTTON');
        if($show_edit===false){
            $map['can_post']=1;
            $map['status']=1;
            $show_edit=$this->newsCategoryModel->where($map)->count();
            S('SHOW_EDIT_BUTTON',$show_edit);
        }
        if($show_edit){
            $menu_list['right'][]=array('tab' => 'create', 'title' => '<i class="icon-edit"></i>'. L('_CONTRIBUTIONS_'), 'href' =>is_login()?U('News/index/edit'):"javascript:toast.error('".L('_LOG_TIP_')."')");
            $menu_list['right'][]=array('type'=>'search', 'input_title' => L('_INPUT_TIP_'),'input_name'=>'keywords','from_method'=>'post', 'action' =>U('News/index/index'));
        }
        $menu_list['first']=array( 'title' => L('_NEWS_'));

        $this->assign('tab','home');
        $this->assign('sub_menu', $menu_list);

    }

    public function index($page=1)
    {
        if(json_decode($_GET['keywords'])!=''){
            $keywords=json_decode($_GET['keywords']);
            $this->assign('search_keywords',$keywords);
            $map['title|description']=array('like','%'.$keywords.'%');
        }else{
            $_GET['keywords']=null;
        }
        /* 分类信息 */
        $category = I('category',0,'intval');
        $current='';
        if($category){
            $this->_category($category);
            $cates=$this->newsCategoryModel->getCategoryList(array('pid'=>$category,'status'=>1));
            if(count($cates)){
                $cates=array_column($cates,'id');
                $cates=array_merge(array($category),$cates);
                $map['category']=array('in',$cates);
            }else{
                $map['category']=$category;
            }
            $now_category=$this->newsCategoryModel->find($category);
            $cid=$now_category['pid']==0?$now_category['id']:$now_category['pid'];
            $current='category_' . $cid;
        }
        $map['dead_line']=array('gt',time());
        $map['status']=1;

        $order_field=modC('NEWS_ORDER_FIELD','create_time','News');
        $order_type=modC('NEWS_ORDER_TYPE','desc','News');
        $order='sort desc,'.$order_field.' '.$order_type;

        /* 获取当前分类下资讯列表 */
        list($list,$totalCount) = $this->newsModel->getListByPage($map,$page,$order,'*',modC('NEWS_PAGE_NUM',20,'News'));
        foreach($list as &$val){
            $val['user']=query_user(array('space_url','nickname'),$val['uid']);
        }
        unset($val);
        /* 模板赋值并渲染模板 */
        $this->assign('list', $list);
        $this->assign('category', $category);
        $this->assign('totalCount',$totalCount);
        $current= ($current==''?'home':$current);
        $this->assign('tab',$current);
        $this->display();
    }

    public function my($page=1)
    {
        $this->_needLogin();
        $map['uid']=get_uid();
        /* 获取当前分类下资讯列表 */
        list($list,$totalCount) = $this->newsModel->getListByPage($map,$page,'update_time desc','*',modC('NEWS_PAGE_NUM',20,'News'));
        foreach($list as &$val){
            if($val['dead_line']<=time()){
                $val['audit_status']= '<span style="color: #7f7b80;">'.L('_EXPIRE_').'</span>';
            }else{
                if($val['status']==1){
                    $val['audit_status']='<span style="color: green;">'.L('_AUDIT_SUCCESS_').'</span>';
                }elseif($val['status']==2){
                    $val['audit_status']='<span style="color:#4D9EFF;">'.L('_AUDIT_READY_').'</span>';
                }elseif($val['status']==-1){
                    $val['audit_status']='<span style="color: #b5b5b5;">'.L('_AUDIT_FAIL_').'</span>';
                }
            }

        }
        unset($val);
        /* 模板赋值并渲染模板 */
        $this->assign('list', $list);
        $this->assign('totalCount',$totalCount);

        $this->assign('tab','myNews');
        $this->display();
    }

	public function detailH5()
	{
        $aId=I('id',0,'intval');

        //是否为分享打开
        if(!empty($_SERVER["HTTP_AD"]) && $_SERVER["HTTP_AD"] == 1){
            $this->assign("isApp","yes");
        }else{
            $this->assign("isApp","no");
        }

		/* 标识正确性检测 */
		if (!($aId && is_numeric($aId))) {
			$this->error(L('_ERROR_ID_'));
		}

        $info=(new CircleVideoModel())->getData($aId);

        $info["updateTimeText"] = date("m月d日",$info["update_time"]);
		/* 获取模板 */
		if (!empty($info['detail']['template'])) { //已定制模板
			$tmpl = 'Index/tmpl/'.$info['detail']['template'];
		}else{
            $this->error(L('未定制模板'));
        }

		$this->_category($info['category']);

		/* 更新浏览数 */
		$map = ['id' => $aId];
        (new CircleVideoModel())->where($map)->setInc('view_count');
		/* 模板赋值并渲染模板 */
		$this->assign('info', $info);
		$this->setTitle('{$info.title|text} —— '.L("_MODULE_"));
		$this->setDescription('{$info.description|text} ——'.L("_MODULE_"));
		$this->display($tmpl);
	}

    private function _category($id=0)
    {
        $now_category=$this->newsCategoryModel->getTree($id,'id,title,pid,sort',array('status'=>1));
        $this->assign('now_category',$now_category);
        return $now_category;
    }
    private function _needLogin()
    {
        if(!is_login()){
            $this->error(L('_TIP_LOGIN_'));
        }
    }

}