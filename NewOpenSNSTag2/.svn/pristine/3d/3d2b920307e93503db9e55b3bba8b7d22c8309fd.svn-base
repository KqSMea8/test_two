<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 15-4-28
 * Time: 上午11:30
 * @author 郑钟良<zzl@ourstu.com>
 */

namespace Live\Controller;


use Admin\Model\MemberModel;
use Common\Lib\WX\JSSDK;
use News\Model\NewsModel;
use Think\Controller;
use Live\Model\LiveRoomModel;

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

    public function detail()
    {

        //是否展示广告图片、推荐信息和评论信息等
        $isShowMore = true;
        if(!empty($_SERVER["HTTP_AD"]) && $_SERVER["HTTP_AD"] == 1){
            $this->assign("isShow","no");
        }else{
            $isShowMore = false;
            $this->assign("isShow","show");
        }

        //查询资讯信息
        $aId=I('id',0,'intval');
        if (!($aId && is_numeric($aId))) {
            $this->error(L('_ERROR_ID_'));
        }

        $info=$this->newsModel->getData($aId);

        if($info["category"] == 40 || $info["category_two"] ==40 || $info["category_three"] == 40){

            $this->assign("commentTitle","最新建议");
        }else{

            $this->assign("commentTitle","最新评论");
        }

        if($info['dead_line']<=time()&&!check_auth('News/Index/edit',$info['uid'])){
            $this->error(L('_ERROR_EXPIRE_'));
        }

        $author=query_user(array('uid','space_url','nickname','avatar64','signature'),$info['uid']);
        $author['news_count']=$this->newsModel->where(array('uid'=>$info['uid']))->count();

        //获取模板
        if (!empty($info['detail']['template'])) {
            //已定制模板
            $tmpl = 'Index/tmpl/'.$info['detail']['template'];
        } else {
            //使用默认模板
            $tmpl = 'Index/tmpl/detail';
        }

        $this->_category($info['category']);
		$info["updateTimeText"] = date("m月d日",$info["update_time"]);

        //更新浏览数
        $map = array('id' => $aId);
        $this->newsModel->where($map)->setInc('view');

        if($isShowMore){

            //查询news_below_advertising位置上的广告
            $advInfo = [];
            $advs = M("adv")->alias("adv")
                ->field("adv.*")
                ->join("jpgk_adv_pos as pos on adv.pos_id = pos.id")
                ->where(["pos.name" => "news_below_advertising","adv.status" => 1,"adv.start_time"=>["lt",time()],"adv.end_time" => ["egt",time()]])
                ->order("adv.create_time desc")
                ->limit(0,1)
                ->select();

            if(!empty($advs)){
                //取出一条广告，放到资讯详情下面
                $adv = $advs[0];
                $advData = json_decode($adv["data"],true);
                //查询广告的图片
                $picture = M('Picture')->where(array('status' => 1))->getById($advData["pic"]);
                if(!empty($picture)){
                    $advInfo = [
                        "openUrl" => $adv["url"],
                        "openMethod" => $advData["target"],
                        "imageUrl" => "http://image.lz517.com/catering".$picture["path"],
                        "advId" => $adv["id"]
                    ];
                }
            }


            //获取推荐的资讯
            $tjNewsWhereMap =  ["id" => ["neq",$aId],"status" => 1,"dead_line" => ["gt",time()],"publish_time" => ["lt",time()]];
            if(!empty($info["category_three"])){
                $tjNewsWhereMap["category_three"] = $info["category_three"];
            }else if($info["category_two"]){
                $tjNewsWhereMap["category_two"] = $info["category_two"];
            }else{
                $tjNewsWhereMap["category"] = $info["category"];
            }

            $tjNews = [];
            $tuiJianNews = $this->newsModel->where($tjNewsWhereMap)->order("create_time desc")->limit(0,3)->select();

            foreach($tuiJianNews as $new){

                $tjNews[] = [

                    "id" => $new["id"],
                    "title" => mb_strlen($new["title"])>22?mb_substr($new["title"],0,22)."……":$new["title"],
                    "view" => $new["view"],
                    "comment" => $new["comment"],
                    "collection" => $new["collection"],
                    "newsId" => $new["id"],
                    "source" => $new["source"],
                    "cover" => $new["cover"],
                    "url" => U("/News/Index/detail",["id" => $new["id"]])
                ];
            }
            //查询推荐资讯的封面
            if(!empty($tjNews)){
                $coverIdArr = array_column($tjNews,"cover");
                $covers = M('picture')->where(["id" => ["in",$coverIdArr]])->select();
                $covers = useFieldAsArrayKey($covers,"id");
                foreach($tjNews as &$new){
                    if(empty($covers[$new["cover"]])){
                        $new["coverUrl"] = "http://{$_SERVER['HTTP_HOST']}/Public/images/news_content_list_item_bg.png";
                    }else{
                        $new["coverUrl"] = "http://image.lz517.com/catering".$covers[$new["cover"]]["path"];
                    }
                }
            }
            $this->assign("tjNews",$tjNews);
        }

        //微信分享的相关信息
        $wxjssdk = new JSSDK();
        $signPackage = $wxjssdk->getSignPackage();

        if($aId == 803){
            $this->assign("shareImg","http://opensns.lz517.com/share.png");
            $this->assign("shareDesc","请餐饮人积极参与投票，并转发微信群、朋友圈，获得更多同仁的支持，表达我们共同的心声。");
        }else{
            $this->assign("shareImg","http://opensns.lz517.com/share2.png");
//            $this->assign("shareDesc","请餐饮人积极参与投票，并转发微信群、朋友圈，获得更多同仁的支持，表达我们共同的心声。");
            $this->assign("shareDesc",str_replace("&nbsp;","",$info["description"]));
        }

        //查询顶部的轮播banner
        list($bannerData,$bannerNewData) = getTopBanner();
        $this->assign("topBanners",$bannerData);

        /* 模板赋值并渲染模板 */
        $this->assign('author',$author);
        $this->assign('info', $info);
        $this->setTitle('{$info.title|text} —— '.L("_MODULE_"));
        $this->setDescription('{$info.description|text} ——'.L("_MODULE_"));
        $this->assign("adv",$advInfo);
        $this->assign("newsId",$aId);
        $this->assign("signPackage",$signPackage);
        $this->display($tmpl);
    }

	public function detailH5()
	{
		$aId=I('id',0,'intval');

		/* 标识正确性检测 */
		if (!($aId && is_numeric($aId))) {
			$this->error(L('_ERROR_ID_'));
		}

		$info=$this->newsModel->getData($aId);
//print_r($info);die();
		if($info['dead_line']<=time()&&!check_auth('News/Index/edit',$info['uid'])){
			$this->error(L('_ERROR_EXPIRE_'));
		}
		$author=query_user(array('uid','space_url','nickname','avatar64','signature'),$info['uid']);
		$author['news_count']=$this->newsModel->where(array('uid'=>$info['uid']))->count();
		/* 获取模板 */
		if (!empty($info['detail']['template'])) { //已定制模板
			$tmpl = 'Index/tmpl/'.$info['detail']['template'];
		} else { //使用默认模板
			$tmpl = 'Index/tmpl/detail';
		}

		$this->_category($info['category']);

		/* 更新浏览数 */
		$map = array('id' => $aId);
		$this->newsModel->where($map)->setInc('view');
		/* 模板赋值并渲染模板 */
		$this->assign('author',$author);
		$this->assign('info', $info);
		$this->setTitle('{$info.title|text} —— '.L("_MODULE_"));
		$this->setDescription('{$info.description|text} ——'.L("_MODULE_"));
		$this->display($tmpl);
	}

    public function edit()
    {
        $this->_needLogin();
        if(IS_POST){
            $this->_doEdit();
        }else{
            $aId=I('id',0,'intval');
            if($aId){
                $data=$this->newsModel->getData($aId);
                if(!check_auth('News/Index/edit',-1)){
                    if($data['uid']==is_login()){
                        if($data['status']==1){
                            $this->error(L('_ERROR_EDIT_DENY_'));
                        }
                    }else{
                        $this->error(L('_ERROR_EDIT_LIMIT_'));
                    }
                }
                $this->assign('data',$data);
            }else{
                $this->checkAuth('News/Index/add',-1,L('_ERROR_CONTRIBUTION_LIMIT_'));
            }
            $title=$aId?L('_EDIT_'):L('_ADD_');
            $category=$this->newsCategoryModel->getCategoryList(array('status'=>1,'can_post'=>1),1);
            $this->assign('category',$category);
            $this->assign('title',$title);
        }
        $this->assign('tab','create');
        $this->display();
    }

    private function _doEdit()
    {
        $aId=I('post.id',0,'intval');
        $data['category']=I('post.category',0,'intval');

        if($aId){
            $data['id']=$aId;
            $now_data=$this->newsModel->getData($aId);
            if(!check_auth('News/Index/edit',-1)){
                if($now_data['uid']==is_login()){
                    if($now_data['status']==1){
                        $this->error(L('_ERROR_EDIT_DENY_'));
                    }
                }else{
                    $this->error(L('_ERROR_EDIT_LIMIT_'));
                }
            }
            $category=$this->newsCategoryModel->where(array('status'=>1,'id'=>$data['category']))->find();
            if($category){
                if($category['can_post']){
                    if($category['need_audit']&&!check_auth('Admin/News/setNewsStatus')){
                        $data['status']=2;
                    }else{
                        $data['status']=1;
                    }
                }else{
                    $this->error(L('_ERROR_CONTRIBUTION_DENY_'));
                }
            }else{
                $this->error(L('_ERROR_NONE_'));
            }
            $data['template']=$now_data['detail']['template']?:'';
        }else{
            $this->checkAuth('News/Index/add',-1,L('_ERROR_CONTRIBUTION_LIMIT_'));
            $this->checkActionLimit('add_news','News',0,is_login(),true);
            $data['uid']=get_uid();
            $data['sort']=$data['position']=$data['view']=$data['comment']=$data['collection']=0;
            $category=$this->newsCategoryModel->where(array('status'=>1,'id'=>$data['category']))->find();
            if($category){
                if($category['can_post']){
                    if($category['need_audit']&&!check_auth('Admin/News/setNewsStatus')){
                        $data['status']=2;
                    }else{
                        $data['status']=1;
                    }
                }else{
                    $this->error(L('_ERROR_CONTRIBUTION_DENY_'));
                }
            }else{
                $this->error(L('_ERROR_NONE_'));
            }
            $data['template']='';
        }
        $data['title']=I('post.title','','text');
        $data['cover']=I('post.cover',0,'intval');
        $data['description']=I('post.description','','text');
        $data['dead_line']=I('post.dead_line','','text');
        if($data['dead_line']==''){
            $data['dead_line']=2147483640;
        }else{
            $data['dead_line']=strtotime($data['dead_line']);
        }
        $data['source']=I('post.source','','text');
        $data['content']=I('post.content','','filter_content');

        if(!mb_strlen($data['title'],'utf-8')){
            $this->error(L('_TIP_TITLE_EMPTY_'));
        }
        if(mb_strlen($data['content'],'utf-8')<20){
            $this->error(L('_TIP_CONTENT_LENGTH_'));
        }

        $res=$this->newsModel->editData($data);
        $title=$aId?L('_EDIT_'):L('_ADD_');
        if($res){
            if(!$aId){
                $aId=$res;
                if($category['need_audit']&&!check_auth('Admin/News/setNewsStatus')){
                    $this->success($title.L('_TIP_SUCCESS_').cookie('score_tip').L('_TIP_AUDIT_'),U('News/Index/detail',array('id'=>$aId)));
                }
            }
            $this->success($title.L('_TIP_SUCCESS_').cookie('score_tip'),U('News/Index/detail',array('id'=>$aId)));
        }else{
            $this->error($title.L('_TIP_FAIL_').$this->newsModel->getError());
        }
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

    //分享直播页
    public function liveShare($id = ''){

        if($id){
            $liveData = (new LiveRoomModel())->where(['id'=>$id])->find();
            $liveData['cover_url'] = 'http://image.lz517.com/catering'.$liveData['cover_url'];
            $this->assign('liveData',$liveData);

//            $memberData = (new MemberModel())->where(['uid'=>$liveData['uid']])->find();
//
//            $member['nickname'] = $memberData['nickname'];
//            $member['brand'] = $memberData['brand'];
//            $member['job'] = $memberData['job'];
//            $this->assign('member',$member);
        }

        $this->display('Index/tmpl/liveShare');
    }
} 