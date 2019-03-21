<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 15-4-27
 * Time: 上午10:21
 * @author 郑钟良<zzl@ourstu.com>
 */

namespace Admin\Controller;


use Admin\Builder\AdminConfigBuilder;
use Admin\Builder\AdminListBuilder;
use Admin\Builder\AdminTreeListBuilder;
use GXPT\Model\CategoryModel;
use Gxpt\Model\CommentModel;
use Common\Model\ContentHandlerModel;
use Gxpt\Model\ProductModel;
use Live\Model\LiveRoomModel;
use News\Model\NewsCategoryModel;
use News\Model\NewsModel;
use Pay\Conf\Conf;
use Pay\Model\PayServerModel;
use Pay\Service\PayForService;
use Admin\Model\PictureModel;
use News\Model\NewsGoodsStandardModel;
use News\Model\NewsGoodsImageModel;
use Think\Model;

class NewsController extends AdminController{

    protected $newsModel;
    protected $newsDetailModel;
    protected $newsCategoryModel;
    protected $payServerModel;
    protected $NewsGoodsModel;
    protected $NewsOrderModel;
    protected $NewsGoodsImageModel;
    protected $NewsGoodsStandardModel;
    protected $pictureModel;
    protected $ShareModel;

    function _initialize()
    {
        parent::_initialize();
        $this->newsModel = D('News/News');
        $this->newsDetailModel = D('News/NewsDetail');
        $this->newsCategoryModel = D('News/NewsCategory');
        $this->payServerModel = D('Pay/PayServer');
        $this->NewsGoodsModel = D('News/NewsGoods');
        $this->NewsOrderModel = D('News/NewsOrder');
        $this->NewsGoodsImageModel = new NewsGoodsImageModel();
        $this->NewsGoodsStandardModel = new NewsGoodsStandardModel();
        $this->pictureModel = new PictureModel();
        $this->ShareModel = D('Weibo/Share');
        $this->productCategory = D('Gxpt/Category');
    }

    public function newsCategory()
    {
        //显示页面
        $builder = new AdminTreeListBuilder();

        $tree = $this->newsCategoryModel->getTree(0, 'id,title,sort,pid,status');

        $builder->title(L('_CATEGORY_MANAGER_'))
            ->suggest(L('_CATEGORY_MANAGER_SUGGEST_'))
            ->buttonNew(U('News/add'))
            ->data($tree)
            ->setLevel(3)
            ->display();
    }

    /**
     * 同步添加菜单
     * @param $pData
     * @param $cate
     * @author songbin@jpgk.com.cn
     */
    private function addMenu($pData,$cate){
        $data = [];
        $data['title'] = $pData['title'];
        $data['pid'] = 10037;
        $data['sort'] = $pData['sort'];
        $data['url'] = "Admin/News/index/cate/{$cate}";
        $data['hide'] = 0;
        $data['tip'] = '';
        $data['group'] = '资讯管理';
        $data['is_dev'] = 0;
        $data['icon'] = 'rss-sign';
        $data['module'] = 'News';

        $id = D('Menu')->add($data);
        if($id){
            //记录行为
            action_log('update_menu', 'Menu', $id, UID);
        }
    }

    /**分类添加
     * @param int $id
     * @param int $pid
     * @author 郑钟良<zzl@ourstu.com>
     */
    public function add($id = 0, $pid = 0)
    {
        $title=$id?L('_EDIT_'):L('_ADD_');
        if (IS_POST) {
            if ($cate = $this->newsCategoryModel->editData()) {

                //添加成功，同步增加菜单（只是添加 不包括编辑）
//                if(!I('post.id')){
//                    $this->addMenu($_POST,$cate);
//                }

                S('SHOW_EDIT_BUTTON',null);
                $this->success($title.L('_SUCCESS_'), U('News/newsCategory'));
            } else {
                $this->error($title.L('_FAIL_').$this->newsCategoryModel->getError());
            }
        } else {
            $builder = new AdminConfigBuilder();

            if ($id != 0) {
                $data = $this->newsCategoryModel->find($id);
            }
//            } else {
//                $father_category_pid=$this->newsCategoryModel->where(array('id'=>$pid))->getField('pid');
//                if($father_category_pid!=0){
//                    $this->error(L('_ERROR_CATEGORY_HIERARCHY_'));
//                }
//            }
            if($pid!=0){
                $categorys = $this->newsCategoryModel->where(array('id'=>$pid,'status'=>array('egt',0)))->select();
            }
            $opt = array();
            foreach ($categorys as $category) {
                $opt[$category['id']] = $category['title'];
            }
            $builder->title($title.L('_CATEGORY_'))
                ->data($data)
                ->keyId()->keyText('title', L('_TITLE_'))
                ->keySelect('pid',L('_FATHER_CLASS_'), L('_FATHER_CLASS_SELECT_'), array('0' =>L('_TOP_CLASS_')) + $opt)->keyDefault('pid',$pid)
                ->keyRadio('can_post',L('_PLAY_YN_'),'',array(0=>L('_NO_'),1=>L('_YES_')))->keyDefault('can_post',1)
                ->keyRadio('need_audit',L('_PLAY_YN_AUDIT_'),'',array(0=>L('_NO_'),1=>L('_YES_')))->keyDefault('need_audit',1)
                ->keyInteger('sort',L('_SORT_'))->keyDefault('sort',0)
                ->keyStatus()->keyDefault('status',1)
                ->buttonSubmit(U('News/add'))->buttonBack()
                ->display();
        }

    }

    /**分类添加
     * @param int $id
     * @param int $pid
     * @author 郑钟良<zzl@ourstu.com>
     */
    public function buyServer($id = 0)
    {

        if(!empty($_GET["out_trade_no"]) && $_GET["is_success"] == "T"){

            //调用支付宝查询是否支付成功

            //如果成功，处理程序逻辑
        }

        $title="购买资讯服务";
        if (IS_POST) {

            $startTime = I("post.newsStartTime");
            $endTime = I("post.newsEndTime");

            //计算购买的天数
            $days = round((strtotime($endTime)-strtotime($startTime))/86400) + 1;

            if($days < 1){

                $this->error("购买服务的天数至少为1天！");
            }

            //$money = $days * 1;
            $money = 0.01;

            $payType = I("post.payType");
            $payService = new PayForService();
            $payUrl = "";
            $openUrl = "";
            switch($payType){
                case 'alipay':
                    $returnUrl = "http://".$_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"];
                    $payUrl = $payService->buyService(get_uid(),'News',$id,$startTime,$endTime,Conf::PAY_TYPE_ALIPAY,$money,"购买资讯置顶服务",$returnUrl);
                    break;
                case 'wxpay':
                    $tmpUrl = $payService->buyService(get_uid(),"News",$id,$startTime,$endTime,Conf::PAY_TYPE_WEIXIN,$money,"购买资讯置顶服务");
                    $openUrl = "http://".$_SERVER["HTTP_HOST"].U("Pay/WxPay/payHtml")."&payUrl=".urlencode($tmpUrl);
                    break;
            }

            $this->success("请前往支付界面！",$payUrl,false,$payUrl,$openUrl);
        } else {

            //查询特定的资讯是否有置顶的服务
            $payServerModel = new PayServerModel();
            $newServer = $payServerModel->getDataPayServerOfRunning("News",$id);

            if(empty($newServer)){
                $builder=new AdminConfigBuilder();
                $builder->title($title.L('_NEWS_'))
                        ->keyHidden("newsId",$id)
                        ->keyTime('newsStartTime','置顶开始时间','','date')->keyDefault('newsStartTime',time())
                        ->keyTime('newsEndTime','置顶结束时间','','date')->keyDefault('newsEndTime',strtotime(date("Y-m-d",strtotime("+10 day"))))
                        ->keyRadio("payType","支付方式",'',['wxpay' => '微信支付','alipay' => '支付宝支付'])->keyDefault('payType','alipay')
                        ->keyLabel("description","请注意：如果您已支付成功，请刷新！！！")
                        ->group(L('_BUYNEWSSERVICE_'),'newsStartTime,newsEndTime,clickZD,payType,description')
                        ->buttonSubmit()->buttonBack()
                        ->newsDisplay();
            }else{

                $builder=new AdminConfigBuilder();
                $builder->title($title.L('_NEWS_'))
                        ->keyHidden("newsId",$id)
                        ->keyTime('newsStartTime','置顶开始时间','','date')->keyDefault('newsStartTime',$newServer["start_time"])
                        ->keyTime('newsEndTime','置顶结束时间','','date')->keyDefault('newsEndTime',$newServer["end_time"])
                        /*->keyRadio("payType","支付方式",'',['wxpay' => '微信支付','alipay' => '支付宝支付'])->keyDefault('payType','alipay')*/
                        ->keyLabel("description","请注意：该资讯还在服务中，请过期后重新购买！！！")
                        ->group(L('_BUYNEWSSERVICE_'),'newsStartTime,newsEndTime,clickZD,description')
                        ->buttonBack()
                        ->newsDisplay();
            }
        }

    }

    /**
     * 设置资讯分类状态：删除=-1，禁用=0，启用=1
     * @param $ids
     * @param $status
     * @author 郑钟良<zzl@ourstu.com>
     */
    public function setStatus($ids, $status)
    {
        !is_array($ids)&&$ids=explode(',',$ids);
        if(in_array(1,$ids)){
            $this->error(L('_ERROR_CANNOT_'));
        }
        if($status==0||$status==-1){
            $map['category']=array('in',$ids);
            $this->newsModel->where($map)->setField('category',1);
        }
        $builder = new AdminListBuilder();
        $builder->doSetStatus('newsCategory', $ids, $status);
    }
//分类管理end

    public function config()
    {
        $builder=new AdminConfigBuilder();
        $data=$builder->handleConfig();
        $default_position=<<<str
1:系统首页
2:推荐阅读
4:本类推荐
str;

        $builder->title(L('_NEWS_BASIC_CONF_'))
            ->data($data);

        $builder->keyTextArea('NEWS_SHOW_POSITION',L('_GALLERY_CONF_'))->keyDefault('NEWS_SHOW_POSITION',$default_position)
            ->keyRadio('NEWS_ORDER_FIELD',L('_FRONT_LIST_SORT_'),L('_SORT_RULE_'),array('view'=>L('_VIEWS_'),'create_time'=>L('_CREATE_TIME_'),'update_time'=>L('_UPDATE_TIME_')))->keyDefault('NEWS_ORDER_FIELD','create_time')
            ->keyRadio('NEWS_ORDER_TYPE',L('_LIST_SORT_STYLE_'),'',array('asc'=>L('_ASC_'),'desc'=>L('_DESC_')))->keyDefault('NEWS_ORDER_TYPE','desc')
            ->keyInteger('NEWS_PAGE_NUM','',L('_LIST_IN_PAGE_'))->keyDefault('NEWS_PAGE_NUM','20')

            ->keyText('NEWS_SHOW_TITLE', L('_TITLE_NAME_'), L('_HOME_BLOCK_TITLE_'))->keyDefault('NEWS_SHOW_TITLE',L('_HOT_NEWS_'))
            ->keyText('NEWS_SHOW_COUNT', L('_NEWS_SHOWS_'), L('_TIP_NEWS_ARISE_'))->keyDefault('NEWS_SHOW_COUNT',4)
            ->keyRadio('NEWS_SHOW_TYPE', L('_NEWS_SCREEN_'), '', array('1' => L('_BG_RECOMMEND_'), '0' => L('_EVERYTHING_')))->keyDefault('NEWS_SHOW_TYPE',0)
            ->keyRadio('NEWS_SHOW_ORDER_FIELD', L('_SORT_VALUE_'), L('_TIP_SORT_VALUE_'), array('view' => L('_VIEWS_'), 'create_time' => L('_DELIVER_TIME_'), 'update_time' => L('_UPDATE_TIME_')))->keyDefault('NEWS_SHOW_ORDER_FIELD','view')
            ->keyRadio('NEWS_SHOW_ORDER_TYPE', L('_SORT_TYPE_'), L('_TIP_SORT_TYPE_'), array('desc' => L('_COUNTER_'), 'asc' => L('_DIRECT_')))->keyDefault('NEWS_SHOW_ORDER_TYPE','desc')
            ->keyText('NEWS_SHOW_CACHE_TIME', L('_CACHE_TIME_'),L('_TIP_CACHE_TIME_'))->keyDefault('NEWS_SHOW_CACHE_TIME','600')

            ->group(L('_BASIC_CONF_'), 'NEWS_SHOW_POSITION,NEWS_ORDER_FIELD,NEWS_ORDER_TYPE,NEWS_PAGE_NUM')->group(L('_HOME_SHOW_CONF_'), 'NEWS_SHOW_COUNT,NEWS_SHOW_TITLE,NEWS_SHOW_TYPE,NEWS_SHOW_ORDER_TYPE,NEWS_SHOW_ORDER_FIELD,NEWS_SHOW_CACHE_TIME')
            ->groupLocalComment(L('_LOCAL_COMMENT_CONF_'),'index')
            ->buttonSubmit()->buttonBack()
            ->display();
    }


    //资讯列表start
    public function index($page=1,$r=20)
    {
        $aCate=I('cate',0,'intval');

        if($aCate){
            $cates=$this->newsCategoryModel->getCategoryList(array('id'=>$aCate));
            if($cates){
                $cateWhere['category']=$aCate;
                $cateWhere['_logic'] = 'OR';
                $map['_complex'] = $cateWhere;
            }
        }

        //搜索资讯名称
        if(I('get.newsName')){
            $map['title'] = ['like','%'.I('get.newsName').'%'];
        }

        if(I('get.newsDate')){
            $map['newsDate'] = I('get.newsDate');
        }
        $aDead=I('dead',0,'intval');
//        if($aDead){
//            $map['dead_line']=array('elt',time());
//        }else{
//            $map['dead_line']=array('gt',time());
//        }
        $aPos=I('pos',0,'intval');
        /* 设置推荐位 */
        if($aPos>0){
            $map[] = "position & {$aPos} = {$aPos}";
        }

        $map['status']=1;

        //HQ修改 过滤咨询显示列表数据
//        if(session('user_auth')['uid']){
//            $uid = session('user_auth')['uid'];
//            $map['uid'] = $uid;
//
//            //查看uid当前用户是否为管理员
//            if($uid){
//
//            }
//        }

        $positions=$this->_getPositions(1);
        list($list,$totalCount)=$this->newsModel->getListByPage($map,$page,'update_time desc','*',$r);

        //拼接资讯分享条数
        $newsShareData = $this->ShareModel->field('*,count(row_id) as countRow')->where(['app'=>'News'])->group('row_id')->select();
        $newsShareData = useFieldAsArrayKey($newsShareData,'row_id');

        foreach($list as $key=>$val){
            if($newsShareData[$val['id']]){
                $list[$key]['countRow'] = $newsShareData[$val['id']]['countRow'];
            }else{
                $list[$key]['countRow'] = 0;
            }
        }


        //查询是否购买了服务
        $newIdArr = array_column($list,"id");
        $payServerModel = new PayServerModel();
        $servers = $payServerModel->getPayServerListOfRunning("News",$newIdArr);
        $servers = useFieldAsArrayKey($servers,"row_id");

        $category=$this->newsCategoryModel->getCategoryList(['status'=>['eq',1]]);
        $category=array_combine(array_column($category,'id'),$category);

        foreach($list as &$val){

            $val['category']='['.$val['category'].'] '.$category[$val['category']]['title'];
            $val['category_two']='['.$val['category_two'].'] '.$category[$val['category_two']]['title'];
            if(isset($servers[$val["id"]])){

                $val["serverTime"] = date("Y.m.d",$servers[$val['id']]['start_time'])."--".date("Y.m.d",$servers[$val['id']]['end_time']);
            }else{

                $buyServerUrl = U("buyServer",["id" => $val["id"]]);
                $val["serverTime"] = "<a href='{$buyServerUrl}'>购买服务</a>";
            }

            //查看资讯数据是否已过期
            if($val['status'] == '1'){
                if(time()>$val['dead_line']){
                    $val['status'] = '3';
                }
            }
        }

        unset($val);

//        print_r($category);die;
        foreach($category as $key => $val){
            if($val['pid'] == 0){
                $optCategory[] = $val;
            }
        }

//        $optCategory=$category;
        foreach($optCategory as &$val){
            $val['value']=$val['title'];
        }
        unset($val);
        $builder=new AdminListBuilder();
        $builder->title(L('_NEWS_LIST_'))
            ->data($list)
            ->setSelectPostUrl(U('Admin/News/index'))
            ->select('','cate','select','','','',array_merge(array(array('id'=>0,'value'=>L('_EVERYTHING_'))),$optCategory))
            ->select('','dead','select','','','',array(array('id'=>0,'value'=>L('_NEWS_CURRENT_')),array('id'=>1,'value'=>L('_NEWS_HISTORY_'))))
            ->select(L('_RECOMMENDATIONS_'),'pos','select','','','',array_merge(array(array('id'=>0,'value'=>L('_ALL_DEFECTIVE_'))),$positions))
            ->select('发布日期：','newsDate','select','','','',array_merge([['id'=>0,'value'=>"全部"]],[["id" =>1,"value" => "今天"],["id" => 2,"value" => "昨天"],["id" => 3,"value" => "一周内"],["id" => 4,"value" => "一个月内"]]))
            ->search("资讯名称",'newsName','text','')
            ->buttonNew(U('News/editNews'))
            ->keyId()->keyUid()->keyText('title',L('_TITLE_'))->keyText('category','分类一级')->keyText('category_two','分类二级')->keyText('view','阅读量')->keyText('collection','收藏量')->keyText('comment','评论量')->keyText('countRow','转发量')->keyText('description',L('_NOTE_'))->keyText("serverTime","服务有效期")->keyText('sort',L('_SORT_'))
            ->keyNewsStatus()->keyCreateTime()->keyUpdateTime()
            ->keyDoActionEdit('News/editNews?id=###')
            ->keyDoAction('News/localcomment?id=###','评论管理');
        if(!$aDead){
            $builder->ajaxButton(U('News/setDead'),'',L('_SET_EXPIRE_'))->keyDoAction('News/setDead?ids=###',L('_SET_EXPIRE_'));
        }
        $builder->pagination($totalCount,$r)
            ->display();
    }

    //意见征集列表
    public function opinionNews($page=1,$r=20){

        $cates=$this->newsCategoryModel->where(['title'=>'意见征集','status'=>1])->find();

        if($cates){
            $cateWhere['category']=$cates['pid'];
            $cateWhere['category_two']=$cates['id'];
//            $cateWhere['_logic'] = 'OR';
            $map['_complex'] = $cateWhere;
        }

        //搜索资讯名称
        if(I('get.newsName')){
            $map['title'] = ['like','%'.I('get.newsName').'%'];
        }
        $aDead=I('dead',0,'intval');
//        if($aDead){
//            $map['dead_line']=array('elt',time());
//        }else{
//            $map['dead_line']=array('gt',time());
//        }
            $aPos=I('pos',0,'intval');
            /* 设置推荐位 */
            if($aPos>0){
                $map[] = "position & {$aPos} = {$aPos}";
            }

            $map['status']=1;

            //HQ修改 过滤咨询显示列表数据
//        if(session('user_auth')['uid']){
//            $uid = session('user_auth')['uid'];
//            $map['uid'] = $uid;
//
//            //查看uid当前用户是否为管理员
//            if($uid){
//
//            }
//        }

            $positions=$this->_getPositions(1);
            list($list,$totalCount)=$this->newsModel->getListByPage($map,$page,'update_time desc','*',$r);

            //拼接资讯分享条数
            $newsShareData = $this->ShareModel->field('*,count(row_id) as countRow')->where(['app'=>'News'])->group('row_id')->select();
            $newsShareData = useFieldAsArrayKey($newsShareData,'row_id');

            foreach($list as $key=>$val){
                if($newsShareData[$val['id']]){
                    $list[$key]['countRow'] = $newsShareData[$val['id']]['countRow'];
                }else{
                    $list[$key]['countRow'] = 0;
                }
            }


            //查询是否购买了服务
            $newIdArr = array_column($list,"id");
            $payServerModel = new PayServerModel();
            $servers = $payServerModel->getPayServerListOfRunning("News",$newIdArr);
            $servers = useFieldAsArrayKey($servers,"row_id");

            $category=$this->newsCategoryModel->getCategoryList(['status'=>['egt',0]]);
            $category=array_combine(array_column($category,'id'),$category);

            foreach($list as &$val){

                $val['category']='['.$val['category'].'] '.$category[$val['category']]['title'];
                $val['category_two']='['.$val['category_two'].'] '.$category[$val['category_two']]['title'];
                if(isset($servers[$val["id"]])){

                    $val["serverTime"] = date("Y.m.d",$servers[$val['id']]['start_time'])."--".date("Y.m.d",$servers[$val['id']]['end_time']);
                }else{

                    $buyServerUrl = U("buyServer",["id" => $val["id"]]);
                    $val["serverTime"] = "<a href='{$buyServerUrl}'>购买服务</a>";
                }

                //查看资讯数据是否已过期
                if($val['status'] == '1'){
                    if(time()>$val['dead_line']){
                        $val['status'] = '3';
                    }
                }
            }

            unset($val);

            $optCategory=$category;
            foreach($optCategory as &$val){
                $val['value']=$val['title'];
            }
            unset($val);
            $builder=new AdminListBuilder();
            $builder->title('意见征集列表')
                ->data($list)
                ->setSelectPostUrl(U('Admin/News/opinionNews'))
                ->select('发布日期：','newsDate','select','','','',array_merge(array(array('id'=>0,'value'=>"今天")),[["id" =>1,"value" => "昨天"],["id" => 2,"value" => "一周内"],["id" => 3,"value" => "一个月内"],["id" => 4,"value" => "全部"]]))
                ->search("标题",'newsName','text','')
//                ->buttonNew(U('News/editNews'))
                ->keyId()->keyUid()->keyText('title',L('_TITLE_'))->keyText('category','分类一级')->keyText('category_two','分类二级')->keyText('view','阅读量')->keyText('collection','收藏量')->keyText('comment','评论量')->keyText('countRow','转发量')->keyText('description',L('_NOTE_'))->keyText("serverTime","服务有效期")->keyText('sort',L('_SORT_'))
                ->keyNewsStatus()->keyCreateTime()->keyUpdateTime()
                ->keyDoActionEdit('News/editNews?id=###')
                ->keyDoAction('News/newsVote?id=###','投票管理');
//                ->keyDoAction('News/localcomment?id=###','评论管理');
            if(!$aDead){
                $builder->ajaxButton(U('News/setDead'),'',L('_SET_EXPIRE_'));
            }
            $builder->pagination($totalCount,$r)
                ->display();
    }

    //投票管理
    public function newsVote($page=1,$r=2){

        $id = I('get.id');
        $where = '';
        if(I('get.type')){
            $where = 'AND nv.type='.I('get.type');
        }
//        //查询资讯投票相关数据
//        $model = M('');
//        $sqlCount = "SELECT
//			  COUNT(*)
//			FROM
//			  `jpgk_news_vote` nv
//			   LEFT JOIN `jpgk_member` m
//                ON nv.`uid` = m.uid
//			WHERE nv.`news_id`=$id $where LIMIT 1";
//
//        $dataCount = $model->query($sqlCount);

        $model = M('');
        $sql = "SELECT
			  nv.`create_time`,
			  nv.`id`,
			  nv.`uid`,
			  nv.`type`,
			  m.`nickname`,
			  m.`phone`,
			  m.`company`,
			  m.`position`,
			  m.`job`,
			  m.`department`,
			  m.`brand`
			FROM
			  `jpgk_news_vote` nv
			   INNER JOIN `jpgk_member` m
                ON nv.`uid` = m.uid
			WHERE nv.`news_id`=$id $where ";

        $data = $model->query($sql);

        //查询资讯评论相关数据
        $commentModel = new CommentModel();
        $commentData = $commentModel->where(['status'=>1,'row_id'=>$id])->select();

        //计算支持与反对的数量
        $supportNum = '';
        $againstNum = '';
        foreach($data as $val){
            if($val['type'] == 1){
                $supportNum ++;
            }elseif($val['type'] == 2){
                $againstNum ++;
            }
        }

        //拼接数据
        foreach($commentData as $key => $val){
            foreach($data as $k => $v){
                if($val['uid'] == $v['uid']){

                    //拼接建议内容
                    if(!$v['content']){
                        $data[$k]['content'] = $val['content'];
                    }elseif($v['content']){
                        $data[] = [
                            'create_time' => $v['create_time'],
                            'uid' => $v['uid'],
                            'type' => $v['type'],
                            'typeText' => $v['type'] ==1 ? '支持':($v['type'] ==2 ? '反对':''),
                            'nickname' => $v['nickname'],
                            'brand' => $v['brand'],
                            'content' => $val['content'],
                        ];
                    }
                }

                //拼接观点文本
                if($v['type']==1){
                    $data[$k]['typeText'] = '支持';
                }elseif($v['type']==2){
                    $data[$k]['typeText'] = '反对';
                }
            }
        }

//        print_r(count($data));die;

        //获取资讯的标题
        $newsData = $this->newsModel->find($id);

        $attr['href'] = U('exportVoteExcel?newsID='.$id);
        $attr['class']='btn btn-ajax';

        //显示页面
        $builder = new AdminListBuilder();

        $builder->title("投票列表 | ".$newsData['title'])
            ->tips('支持数: '.$supportNum.'　　反对数: '.$againstNum)
            ->keyId()
            ->keyText('nickname','用户')
            ->keyText('phone','手机号')
            ->keyText('position','城市')
            ->keyText('brand','品牌')
            ->keyText('company','公司')
            ->keyText('department','部门')
            ->keyText('job','职业')
            ->keyText('typeText','观点')
            ->keyCreateTime('create_time','投票时间')
            ->keyText('content','建议内容')
            ->data($data)
            ->button('导出excel列表',$attr)
            ->select('观点','type','select','','','',array_merge(array(array('id'=>0,'value'=>"全部")),[["id" =>1,"value" => "支持"],["id" => 2,"value" => "反对"]]))
//			->keyMap('statusText', '是否通过审核', array(0 => '未审核', 1 => '已审核'))
//            ->pagination(count($data),$r)
            ->display();

    }

    //导出资讯投票Excel数据列表
    public function exportVoteExcel(){

        $id = I('get.newsID');
        //查询资讯投票相关数据
        $model = M('');
        $sql = "SELECT
			  nv.`create_time`,
			  nv.`id`,
			  nv.`uid`,
			  nv.`type`,
			  m.`nickname`,
			  m.`phone`,
			  m.`company`,
			  m.`position`,
			  m.`job`,
			  m.`department`,
			  m.`brand`
			FROM
			  `jpgk_news_vote` nv
			   INNER JOIN `jpgk_member` m
                ON nv.`uid` = m.uid
			WHERE nv.`news_id`=$id ";

        $data = $model->query($sql);

        //查询资讯评论相关数据
        $commentModel = new CommentModel();
        $commentData = $commentModel->where(['status'=>1,'row_id'=>$id])->select();

        //计算支持与反对的数量
        $supportNum = '';
        $againstNum = '';
        foreach($data as $val){
            if($val['type'] == 1){
                $supportNum ++;
            }elseif($val['type'] == 2){
                $againstNum ++;
            }
        }

        //拼接数据
        foreach($commentData as $key => $val){
            foreach($data as $k => $v){
                if($val['uid'] == $v['uid']){

                    //拼接建议内容
                    if(!$v['content']){
                        $data[$k]['content'] = $val['content'];
                    }elseif($v['content']){
                        $data[] = [
                            'create_time' => $v['create_time'],
                            'uid' => $v['uid'],
                            'type' => $v['type'],
                            'typeText' => $v['type'] ==1 ? '支持':($v['type'] ==2 ? '反对':''),
                            'nickname' => $v['nickname'],
                            'brand' => $v['brand'],
                            'content' => $val['content'],
                        ];
                    }
                }

            }
        }

        $newsData = M('News')->find($id);
        for($i = 0; $i < count ( $data ); $i ++) {
            array_unshift ( $data [$i], $i );
        }

        $excelData = array ();
        $title = array (
            "row" => 1,
            "count" => 15,
            "title" => $this->getExcelHeaderData()
        );

        $num = 1;
        foreach ( $data as $k => $v ) {

            $excelData [$k] ['nickname'] = $v ['nickname'];
            $excelData [$k] ['phone'] = $v ['phone'];
            $excelData [$k] ['position'] = $v ['position'];
            $excelData [$k] ['brand'] = $v ['brand'];
            $excelData [$k] ['company'] = $v ['company'];
            $excelData [$k] ['department'] = $v ['department'];
            $excelData [$k] ['job'] = $v ['job'];
            $excelData [$k] ['typeText'] = $v['type'] ==1 ? '支持':($v['type'] ==2 ? '反对':'');
            $excelData [$k] ['create_time'] = Date('Y-m-d H:i:s',$v ['create_time']);
            $excelData [$k] ['content'] = $v ['content'];
            $num ++;
        }
        $len = count ( $excelData );
        $info = array (
            "table_name_one" =>  "投票信息数据表|".$newsData['title'],
            "table_name_two" => "投票信息报表",
            "lister" => "",
            "tabletime" => date ( "Y-m-d", time () )
        );

        $optional = array (
            'colwidth' => array (
                'A' => '30',
                'B' => '15',
                'C' => '25',
                'D' => '20',
                'E' => '30',
                'F' => '20',
                'G' => '25',
                'H' => '15',
                'I' => '30',
                'J' => '66',
            ),
            'fontsize' => array (
                'A1:O' . ($len + 4) => '10'
            ),
            'colalign' => array (
                'A1:O' . ($len + 4) => '2'
            )
        );
        outputExcel ( $info, $title, $excelData, $optional );
    }

    private function getExcelHeaderData()
    {

        $return = [
            '用户',
            '手机号',
            '城市',
            '品牌',
            '公司',
            '部门',
            '职位',
            '观点',
            '投票时间',
            '建议内容',
        ];

        return $return;
    }

    //待审核列表
    public function audit($page=1,$r=20)
    {
        $aAudit=I('audit',0,'intval');
        if($aAudit==3){
            $map['status']=array('in',array(-1,2));
        }elseif($aAudit==2){
            $map['dead_line']=array('elt',time());
            $map['status']=2;
        }elseif($aAudit==1){
            $map['status']=-1;
        }else{
            $map['status']=2;
            $map['dead_line']=array('gt',time());
        }
        list($list,$totalCount)=$this->newsModel->getListByPage($map,$page,'update_time desc','*',$r);
        $cates=array_column($list,'category');
        $category=$this->newsCategoryModel->getCategoryList(array('id'=>array('in',$cates),'status'=>1),1);
        $category=array_combine(array_column($category,'id'),$category);
        foreach($list as &$val){
            $val['category']='['.$val['category'].'] '.$category[$val['category']]['title'];
        }
        unset($val);

        $builder=new AdminListBuilder();

        $builder->title(L('_AUDIT_LIST_'))
            ->ajaxButton(U("News/readNewsFromOthers"),[],"读取资讯信息")
            ->data($list)
            ->setStatusUrl(U('News/setNewsStatus'))
            ->buttonEnable(null,L('_AUDIT_SUCCESS_'))
            ->buttonModalPopup(U('News/doAudit'),null,L('_AUDIT_UNSUCCESS_'),array('data-title'=>L('_AUDIT_FAIL_REASON_'),'target-form'=>'ids'))
            ->setSelectPostUrl(U('Admin/News/audit'))
            ->select('','audit','select','','','',array(array('id'=>0,'value'=>L('_AUDIT_READY_')),array('id'=>1,'value'=>L('_AUDIT_FAIL_')),array('id'=>2,'value'=>L('_EXPIRE_AND_UNAUDITED_')),array('id'=>3,'value'=>L('_AUDIT_ALL_'))))
            ->keyId()->keyUid()->keyText('title',L('_TITLE_'))->keyText('category',L('_CATEGORY_'))->keyText('description',L('_NOTE_'))->keyText('sort',L('_SORT_'));
        if($aAudit==1){
            $builder->keyText('reason',L('_FAULT_REASON_'));
        }
        $builder->keyTime('dead_line',L('_PERIOD_TO_'))->keyCreateTime()->keyUpdateTime()
            ->keyDoActionEdit('News/editNews?id=###')
            ->pagination($totalCount,$r)
            ->display();
    }

    public function readNewsFromOthers(){

        set_time_limit(0);

        $newsModel = null;

        if(OPENSNS_ENV == "test"){

            $newsModel = new Model("test_news"," ","mysqli://visitor:root@1.119.0.244:3306/opensns");
        }else if(OPENSNS_ENV == 'product'){

            $newsModel = new Model("server_news"," ","mysqli://visitor:root@1.119.0.244:3306/opensns");
        }else{

            $newsModel = new Model("news"," ","mysqli://visitor:root@192.168.1.50:3306/gyl_erp");
        }

        $newsArr = $newsModel->where(["is_deal" => 1])->select();
        $newsCount = $newsModel->count();

        $isReadAll = true;
        if(count($newsArr) != $newsCount){

            $isReadAll = false;
        }

        $model = new Model();
        $model->startTrans();

        try{

            $picModel = M("picture");
            $snsNewModel = M("news");
            $snsDetailModel = M("news_detail");

            //处理新闻
            foreach($newsArr as $news){

                //如果isReadAll==false，就只读取当天的数据
                $date = date("Y-m-d",strtotime($news["date"]));

                if($news["source"] == "红餐网"){

                    preg_match('/([0-9]{4})-([0-9]{2})-([0-9]{2})/',$news["date"],$matches);
                    $date = $matches[0];
                }

                if($news["source"] == "勺子课堂"){

                    $date = str_replace(".","-",$news["date"]);
                }


                if(date("Y-m-d") != $date && date("Y-n-d") != $date){

                    continue;
                }

                $url = $news["cover"];
                if($news["source"] =="勺子课堂"){

                    $url = str_replace('">',"",str_replace('<img src="//','',$news["cover"]));
                }

                //添加图片
                $picId = $picModel->add(["type" => "remote","path" => "","url" => $url, "md5" => md5($url),"sha1" => sha1($url),"status" => 1,"create_time" => time()]);
                if($picId == false){

                    throw new \Exception($picModel->getDbError());
                }

                //添加jpgk_news表记录
                $newsId = $snsNewModel->add(["uid" => 1,"show_type" => 1,"title" => $news["title"],"description" => $news["descc"],
                    "category"=>32,"category_two" => 0,"category_three" => 0,"status" => 2,"reason" => "","sort" => 0,
                    "position" => 0,"cover" => $picId,"view" => 0,"comment" => 0,"collection" => 0,"dead_line" => time()+365*24*3600,
                    "source" => $news["source"],"is_show_banner" => 0,"publish_time" => time(),"create_time" => time(),"update_time" => time()]);

                if($newsId == false){

                    throw new \Exception($snsNewModel->getDbError());
                }

                $re = $snsDetailModel -> add(["news_id" => $newsId,"content" => trim($news["content"]),"template" => "detailH5"]);

                if($re == false){

                    throw new \Exception($snsDetailModel->getDbError());
                }
            }

            $newsModel->where(["id" => ["in",array_column($newsArr,"id")]])->save(["is_deal" => 2]);

            $model->commit();

            $this->success(L('_SUCCESS_TIP_'),U('News/index'));
        }catch(\Exception $e){

            $model->rollback();
            $this->error($e->getMessage(),U('News/index'));
        }
    }

    public function imgwith(){

        /*$str='<img src="//sz.wimg.cc/p/3/5/T.k.jpg">';

//        $data = preg_grep('',$str);
        $data = str_replace('">',"",str_replace('<img src="//','',$str));

        test_output_json($data);*/

        $str = '来源：职业餐饮网    作者：王平    
							2017-01-05 10:23      230';
        $str = str_replace(" ","",$str);
        $str = str_replace(" ","",$str);

        $p = '/([0-9]{4})-([0-9]{2})-([0-9]{2})/';
        $data = preg_match('/([0-9]{4})-([0-9]{2})-([0-9]{2})/',$str,$matches);

        test_output_json($matches);
    }

    /**
     * 审核失败原因设置
     * @author 郑钟良<zzl@ourstu.com>
     */
    public function doAudit()
    {
        if(IS_POST){
            $ids=I('post.ids','','text');
            $ids=explode(',',$ids);
            $reason=I('post.reason','','text');
            $res=$this->newsModel->where(array('id'=>array('in',$ids)))->setField(array('reason'=>$reason,'status'=>-1));
            if($res){
                $result['status']=1;
                $result['url']=U('Admin/News/audit');
                //发送消息
                $messageModel=D('Common/Message');
                foreach($ids as $val){
                    $news=$this->newsModel->getData($val);
                    $tip = L('_YOUR_NEWS_').'【'.$news['title'].'】'.L('_FAIL_AND_REASON_').$reason;
                    $messageModel->sendMessage($news['uid'], L('_NEWS_AUDIT_FAIL_'),$tip,  'News/Index/detail',array('id'=>$val), is_login(), 2);
                }
                //发送消息 end
            }else{
                $result['status']=0;
                $result['info']=L('_OPERATE_FAIL_');
            }
            $this->ajaxReturn($result);
        }else{
            $ids=I('ids');
            $ids=implode(',',$ids);
            $this->assign('ids',$ids);
            $this->display(T('News@Admin/audit'));
        }
    }

    public function setNewsStatus($ids,$status=1)
    {
        !is_array($ids)&&$ids=explode(',',$ids);
        $builder = new AdminListBuilder();
        S('news_home_data',null);
        //发送消息
        $messageModel=D('Common/Message');
        foreach($ids as $val){
            $news=$this->newsModel->getData($val);
            $tip = L('_YOUR_NEWS_').'【'.$news['title'].'】'.L('_AUDIT_SUCCESS_').'。';
            $messageModel->sendMessage($news['uid'],L('_NEWS_AUDIT_SUCCESS_'), $tip,  'News/Index/detail',array('id'=>$val), is_login(), 2);

            //将该数据的三级分类添加到redis中 用于前端资讯分类展示
            if(!empty($news['category_three'])){

                $redis = getRedisClient();
                $redis -> set('jpgk:catering:news:focus:categorythree',$news['category_three']);
                //设置过期时间 7天
                $redis -> expire('jpgk:catering:news:focus:categorythree',86400 * 7);
            }
        }

        //发送消息 end
        $builder->doSetStatus('News', $ids, $status);

    }

    public function editNews()
    {
        $aId=I('id',0,'intval');
        $title=$aId?L('_EDIT_'):L('_ADD_');
        if(IS_POST){

            //参数验证
            $error = "";

            //banner跳转选择直播详情页（11）时 查询直播数据
            if(I('post.banner_app') == 11){
                $liveData = (new LiveRoomModel())->where(['id'=>I("post.detailId")])->find();
                if(!$liveData){
                    $error.="详情页ID有误,请填写正确的Id！";
                }
            }

            if(empty($_POST['title'])){

                $error .= "标题不可为空！";
            }

            if(empty($_POST['show_type'])){

                $error .= "展示方式不可为空！";
            }

            if(I('post.banner_app') == 2 || I('post.banner_app') == 5 || I('post.banner_app') == 7 || I('post.banner_app') == 9 || I('post.banner_app') == 11){
                if(!I("post.detailId")){
                    $error.="banner跳转选择包含详情页时,详情页ID不可为空！";
                }
            }

            //供需详情页跳转 查看填写的详情页ID符不符合规则
            if(I('post.banner_app') == 5){

                $gxptData = (new ProductModel())->info(I("post.detailId"));

                if($gxptData['status'] != 1 || $gxptData['check_status'] != 2 ){
                    $error.="请填写状态为启用且审核通过的 供需详情页ID！";
                }
            }

            //banner跳转选择供需平台列表页
            if(I('post.banner_app') == 4){
                //产品分类ID(父分类,子分类使用逗号隔开)
                $productCateId = I('post.productCateId');
                $productCateArr = explode(',',$productCateId);
                if(!$productCateArr[0] || !$productCateArr[1]){
                    $error.= "banner跳转选择供需平台列表页时,产品分类ID请正确填写！";
                }
            }

            if(!empty($error)){

                $this->error($error);
            }

            $aId&&$data['id']=$aId;
            $data['uid']=I('post.uid',get_uid(),'intval');
            $data['title']=I('post.title','','op_t');
            $data['content']=I('post.content','','filter_content');
            $data['content'] = imageStyle($data['content']);
            $data['category']=I('post.one_level_category');
            $data['category_two']=I('post.two_level_category');
            $data['category_three']=I('post.three_level_category');

            //HQ修改 咨询添加/修改  增加展示方式字段
            $data['show_type']=I('post.show_type',1,'intval');
            $data['publish_time']=I('post.publish_time',0,'intval');

            $data['description']=I('post.description','','op_t');
            $data['cover']=I('post.cover',0,'intval');
            $data['view']=I('post.view',0,'intval');
            $data['comment']=I('post.comment',0,'intval');
            $data['collection']=I('post.collection',0,'intval');
            $data['sort']=I('post.sort',0,'intval');
            $data['dead_line']=I('post.dead_line',2147483640,'intval');
            if($data['dead_line']==0){
                $data['dead_line']=2147483640;
            }
//            $data['template']=I('post.template','','op_t');
            $data['template']='detailH5';
//            $data['status']=I('post.status',1,'intval');
            $data['status']=2;
            $data['source']=I('post.source','','op_t');
            $data['position']=0;

            if(I('post.banner_app')==0){
                $data['is_show_banner']=I('post.is_show_banner',0,'intval');
            }

            $data['is_vote']=I('post.is_vote',0,'intval');
            $position=I('post.position','','op_t');
            $position=explode(',',$position);
            foreach($position as $val){
                $data['position']+=intval($val);
            }
            $this->_checkOk($data,I('post.banner_app'));

            $result=$this->newsModel->editData($data);
            if($result){
                $aId=$aId?$aId:$result;

                //判断如果用户选择了banner跳转 进行操作
                if(I('post.banner_app')!=0){

                    //banner跳转选择供需平台列表页（4）时 查询供销分类ID
                    $oneCategory = '';
                    $twoCategory = '';
                    if(I('post.banner_app') == 4){
                        //查询分类名称
                        $oneCategory = $this->productCategory->where(['id'=>$productCateArr[0]])->find();
                        $twoCategory = $this->productCategory->where(['id'=>$productCateArr[1]])->find();
                    }

                    //banner跳转选择直播详情页（11）时 查询直播数据 TODO 加验证
                    $liveData = '';
                    $liveStatus = '';
                    if(I('post.banner_app') == 11){
                        $liveData = (new LiveRoomModel())->where(['id'=>I("post.detailId")])->find();

                        if($liveData['status'] == 1){

                            //从redis中取出直播数据 有则是直播进行中 没有判断
                            $redis = getRedisClient();
                            $temp = $redis->get('jpgk:catering:chat:token:'.$liveData['path']);
                            if($temp){
                                $liveStatus = '直播中';
                            }elseif($liveData['start_time']>=Date('Y-m-d H:i:s')){
                                $liveStatus = $liveData['start_time'].'直播开始';
                            }
                        }

                    }

                    //banner跳转选择资讯详情页（2）时 查询资讯数据
                    $newsBannerData = '';
                    if(I('post.banner_app') == 2){
                        $newsBannerData = (new NewsModel())->where(['id'=>I("post.detailId")])->find();
                    }

                    $newData['id'] = $aId;
                    $newData['is_show_banner'] = 1;

                    switch (I('post.banner_app'))
                    {
                        //'1' = '资讯主页',
                        case 1:
                            $newData['banner_app'] = 'News';
                            $newData['banner_target'] = 'Main';
                            break;
                        //'2' = '资讯详情页',
                        case 2:
                            $newData['banner_app'] = 'News';
                            $newData['banner_target'] = 'Detail?id='.I("post.detailId").'&title='.$newsBannerData['title'].'&desc='.$newsBannerData['description'];
                            break;
                        //'3' = '供需平台主页',
                        case 3:
                            $newData['banner_app'] = 'SupplyMarket';
                            $newData['banner_target'] = 'Main';
                            break;
                        //'4' = '供需平台列表页'
                        case 4:
                            $newData['banner_app'] = 'SupplyMarket';
                            $newData['banner_target'] = 'List?categoryOne='.$oneCategory['id'].'&categoryOneName='.$oneCategory['name'].'&categoryTwo='.$twoCategory['id'].'&categoryTwoName='.$twoCategory['name'].'';
                            break;
                        //'5' = '供需平台详情页'
                        case 5:
                            $newData['banner_app'] = 'SupplyMarket';
                            $newData['banner_target'] = 'Detail?'.I("post.detailId").'';
                            break;
                        //'6' = '二手市场主页'
                        case 6:
                            $newData['banner_app'] = 'SHM';
                            $newData['banner_target'] = 'Main';
                            break;
                        //'7' = '二手市场详情页'
                        case 7:
                            $newData['banner_app'] = 'SHM';
                            $newData['banner_target'] = 'Detail?'.I("post.detailId").'';
                            break;
                        //'8' = '会议主页'
                        case 8:
                            $newData['banner_app'] = 'Event';
                            $newData['banner_target'] = 'Main';
                            break;
                        //'9' = '会议详情页'
                        case 9:
                            $newData['banner_app'] = 'Event';
                            $newData['banner_target'] = 'Detail?'.I("post.detailId").'';
                            break;
                        //'10' = '直播主页'
                        case 10:
                            $newData['banner_app'] = 'Live';
                            $newData['banner_target'] = 'Main';
                            break;
                        //'11' = '直播详情页'
                        case 11:
                            $newData['banner_app'] = 'Live';
                            $newData['banner_target'] = 'Detail?id='.I("post.detailId").'&title='.$liveData['title'].'&status_show='.$liveStatus.'';
                            break;
                        //'12' = '视频主页'
                        case 12:
                            $newData['banner_app'] = 'CircleVideo';
                            $newData['banner_target'] = 'Main';
                            break;
                        //'13' = '视频详情页'
                        case 13:
                            $newData['banner_app'] = 'CircleVideo';
                            $newData['banner_target'] = 'Detail?'.I("post.detailId").'';
                            break;
                        //'14' = '线上投票主页'
                        case 14:
                            $newData['banner_app'] = 'Event';
                            $newData['banner_target'] = 'voteList';
                            break;
                        //'15' = '线上投票详情页'
                        case 15:
                            $newData['banner_app'] = 'Event';
                            $newData['banner_target'] = 'voteDetail?'.I("post.detailId").'';
                            break;
                    }
                    $newData['content'] = $data['content'];
                    $newData['template'] = $data['template'];

                    $this->newsModel->editData($newData);
                }

                S('news_home_data',null);
//                $this->success($title.L('_SUCCESS_'),U('News/editNews',array('id'=>$aId)));
                $this->success($title.L('_SUCCESS_'),U('News/index'));
            }else{
                $this->error($title.L('_SUCCESS_'),$this->newsModel->getError());
            }
        }else{

            $position_options=$this->_getPositions();
            //buyZTservice是 如果是编辑的话就显示购买咨询置顶服务
            $buyZTservice = false;
            $buyZDserver = false;
            $bannerApp = 0;
            if($aId){
                $data=$this->newsModel->find($aId);
                $detail=$this->newsDetailModel->find($aId);
                $data['content']=$detail['content'];
//                $data['template']=$detail['template'];
                $data['template']='detailH5';
                $position=array();
                foreach($position_options as $key=>$val){
                    if($key&$data['position']){
                        $position[]=$key;
                    }
                }
                $data['position']=implode(',',$position);

                //拼接banner数据
                if($data['is_show_banner'] == 1){

                    //资讯
                    if($data['banner_app']=='News'){

                        if($data['banner_target']=='Main'){
                            $data['banner_app'] = 1;
                        }elseif(explode('=',$data['banner_target'])[0]=='Detail?id'){
                            $data['banner_app'] = 2;
                            $data['detailId'] = explode('&',explode('=',$data['banner_target'])[1])[0];
                        }
                    }

                    //供需平台
                    if($data['banner_app']=='SupplyMarket'){
                        if($data['banner_target']=='Main'){
                            $data['banner_app'] = 3;
                        }elseif(explode('=',$data['banner_target'])[0]=='List?categoryOne'){
                            $targetTemp = explode("=",$data['banner_target']);
                            $data['banner_app'] = 4;
                            $data['productCateId'] = explode("&",$targetTemp[1])[0].','.explode("&",$targetTemp[3])[0];
                        }elseif(explode('?',$data['banner_target'])[0]=='Detail'){
                            $data['banner_app'] = 5;
                            $data['detailId'] = explode('?',$data['banner_target'])[1];
                        }
                    }

                    //二手市场
                    if($data['banner_app']=='SHM'){
                        if($data['banner_target']=='Main'){
                            $data['banner_app'] = 6;
                        }elseif(explode('?',$data['banner_target'])[0]=='Detail'){
                            $data['banner_app'] = 7;
                            $data['detailId'] = explode('?',$data['banner_target'])[1];
                        }
                    }

                    //会议
                    if($data['banner_app']=='Event'){
                        if($data['banner_target']=='Main'){
                            $data['banner_app'] = 8;
                        }elseif(explode('?',$data['banner_target'])[0]=='Detail'){
                            $data['banner_app'] = 9;
                            $data['detailId'] = explode('?',$data['banner_target'])[1];
                        }
                    }

                    //直播
                    if($data['banner_app']=='Live'){
                        if($data['banner_target']=='Main'){
                            $data['banner_app'] = 10;
                        }elseif(explode('=',$data['banner_target'])[0]=='Detail?id'){
                            $bannerTemp = explode('=',$data['banner_target']);
                            $data['banner_app'] = 11;
                            $data['detailId'] = explode('&',$bannerTemp[1])[0];
                        }
                    }

                    //视频
                    if($data['banner_app']=='CircleVideo'){
                        if($data['banner_target']=='Main'){
                            $data['banner_app'] = 12;
                        }elseif(explode('?',$data['banner_target'])[0]=='Detail'){
                            $data['banner_app'] = 13;
                            $data['detailId'] = explode('?',$data['banner_target'])[1];
                        }
                    }

                    //线上投票
                    if($data['banner_app']=='Event'){
                        if($data['banner_target']=='voteList'){
                            $data['banner_app'] = 14;
                        }elseif(explode('?',$data['banner_target'])[0]=='voteDetail'){
                            $data['banner_app'] = 15;
                            $data['detailId'] = explode('?',$data['banner_target'])[1];
                        }
                    }

                }

                $buyZTservice = true;

                //判断当前咨询 置顶是否存在且有效
                $payData = $this->payServerModel->getDataPayServerOfRunning('News',$aId);

                //如果有效的话排序字段不可写 只读
                $buyZDserver = $payData ? true : false;
            }
            $category=$this->newsCategoryModel->getCategoryList(array('status'=>array('egt',0)),1);
            $options=array();
            foreach($category as $val){
                $options[$val['id']]=$val['title'];
            }
            $this->assign("dsparam",[
                "one_level_category" => $data["category"],
                "two_level_category" => $data["category_two"],
                "three_level_category" => $data["category_three"]
            ]);

            //HQ添加 咨询展示方法
            $showType = [
                '1' => '短图显示',
                '2' => '长图显示',
                '3' => '纯文本显示',
            ];

            $builder=new AdminConfigBuilder();
            $builder->title($title.L('_NEWS_'))
                ->data($data)
                ->keyId()
                ->keyReadOnly('uid',L('_PUBLISHER_'))->keyDefault('uid',get_uid())
                ->keyText('title',L('_TITLE_'))
                ->keyEditor('content',L('_CONTENT_'),'','all',array('width' => '700px', 'height' => '400px'))
//                ->keySelect('category',L('_CATEGORY_'),'',$options)
                ->keyOthers("category","选择分类",null,"News/Test/test",["module" => "News","html" => "selectcategory"])
                ->keyTime('publish_time','发布时间','','datetime')->keyDefault('publish_time','')
                ->keySelect('show_type','展示方式','',$showType)

                ->keyTextArea('description',L('_NOTE_'))
                ->keySingleImage('cover',L('_COVER_'))
                ->keyInteger('view',L('_VIEWS_'))->keyDefault('view',0)
                ->keyInteger('comment',L('_COMMENTS_'))->keyDefault('comment',0)
                ->keyInteger('collection',L('_COLLECTS_'))->keyDefault('collection',0)
//                ->keyInteger('sort',L('_SORT_'))->keyDefault('sort',0)
                ->keySort('sort',L('_SORT_'),'',$buyZDserver)->keyDefault('sort',999)
                ->keyTime('dead_line',L('_PERIOD_TO_'))->keyDefault('dead_line',2147483640)
                ->keyText('template',L('_TEMPLATE_'))
                ->keyText('source',L('_SOURCE_'),L('_SOURCE_ADDRESS_'))
                ->keyCheckBox('position',L('_RECOMMENDATIONS_'),L('_TIP_RECOMMENDATIONS_'),$position_options)


                //HQ修改 咨询添加去除状态选择
                ->keyStatus()->keyDefault('status',1)

                //HQ修改 咨询增加 购买咨询置顶服务table
                ->keyTime('newsStartTime','置顶开始时间','','date')->keyDefault('newsStartTime','')
                ->keyTime('newsEndTime','置顶结束时间','','date')->keyDefault('newsEndTime','')
                ->keyReadOnly('payMoney','应付金额')
                ->keyButton('goPay','去支付',null,'去支付')
                ->keyButton('clickZD','点击置顶',null,'点击置顶')
                ->keySelect('banner_app','banner跳转模块','',[
                    '0' => '无',
                    '1' => '资讯主页',
                    '2' => '资讯详情页',
                    '3' => '供需平台主页',
                    '4' => '供需平台列表页',
                    '5' => '供需平台详情页',
                    '6' => '二手市场主页',
                    '7' => '二手市场详情页',
                    '8' => '会议主页',
                    '9' => '会议详情页',
                    '10' => '直播主页',
                    '11' => '直播详情页',
                    '12' => '视频主页',
                    '13' => '视频详情页',
                    '14' => '线上投票主页',
                    '15' => '线上投票详情页',
                ])->keyDefault('banner_app',$bannerApp)
                ->keyText('productCateId','产品分类ID(父分类,子分类使用逗号隔开),banner跳转模块选择供需平台列表页时,此项必填')
                ->keyText('detailId','详情页ID( banner跳转模块选择详情页时,此项必填)')
                ->keyRadio('is_vote','是否支持投票','',['0' => '不可投票','1' => '支持投票'])->keyDefault('is_vote','0')

                ->group(L('_BASIS_'),'id,uid,title,cover,content,category,banner_app,show_type,publish_time,detailId,productCateId')
                ->group(L('_EXTEND_'),'description,view,comment,collection,sort,dead_line,position,source,template,is_vote')
//                ->group(L('_BUYNEWSSERVICE_'),'newsStartTime,newsEndTime,clickZD',$buyZTservice)

                ->buttonSubmit()->buttonBack()
                ->GXPTdisplay();
        }
    }

    public function setDead($ids)
    {
        !is_array($ids)&&$ids=explode(',',$ids);
        $res=$this->newsModel->setDead($ids);
        if($res){
            //发送消息
            $messageModel=D('Common/Message');
            foreach($ids as $val){
                $news=$this->newsModel->getData($val);
                $tip = L('_YOUR_NEWS_').'【'.$news['title'].'】'.L('_SET_TO_EXPIRE_').'。';
                $messageModel->sendMessage($news['uid'],L('_NEWS_TO_EXPIRE_'),  $tip, 'News/Index/detail',array('id'=>$val), is_login(), 2);
            }
            //发送消息 end
            S('news_home_data',null);
            $this->success(L('_SUCCESS_TIP_'),U('News/index'));
        }else{
            $this->error(L('_OPERATE_FAIL_').$this->newsModel->getError());
        }
    }


    private function _checkOk($data=[],$bannerApp){
        if(!mb_strlen($data['title'],'utf-8')){
            $this->error(L('_TIP_TITLE_EMPTY_'));
        }

        if(!$bannerApp){
            if(mb_strlen($data['content'],'utf-8')<20){
                $this->error(L('_TIP_CONTENT_LENGTH_'));
            }
        }
        return true;
    }

    private function _getPositions($type=0)
    {
        $default_position=<<<str
1:系统首页
2:推荐阅读
4:本类推荐
str;
        $positons=modC('NEWS_SHOW_POSITION',$default_position,'News');
        $positons = str_replace("\r", '', $positons);
        $positons = explode("\n", $positons);
        $result=array();
        if($type){
            foreach ($positons as $v) {
                $temp = explode(':', $v);
                $result[] = array('id'=>$temp[0],'value'=>$temp[1]);
            }
        }else{
            foreach ($positons as $v) {
                $temp = explode(':', $v);
                $result[$temp[0]] = $temp[1];
            }
        }

        return $result;
    }


    //资讯置顶 HQ
    public function newsZD(){
        $startTime = I('post.startTime');
        $endTime = I('post.endTime');
        $newsId = I('post.newsId');
        if(!$startTime || !$endTime || !$newsId){
            $this->ajaxReturn(['status'=>false,'info'=>'参数不全']);
        }
        $data = [
            'start_time'=>strtotime($startTime),
            'end_time'=>strtotime($endTime),
            'uid'=>get_uid(),
            'row_id'=>$newsId,
            'app'=>'News',
            'status'=>1,
            'create_time'=>time(),
            'update_time'=>date('Y-m-d H:i:s'),
        ];
        $return = $this->payServerModel ->addData($data);

        if($return){
            $this->ajaxReturn(['status'=>true,'info'=>'添加成功']);
        }else{
            $this->ajaxReturn(['status'=>false,'info'=>'添加失败']);
        }
    }


    //*************************************************资讯分类开始**************************************************
    public function selectCategoryLevelOne(){

        if (IS_AJAX){
            $pid = I('pid');  //默认的省份id

            if( !empty($pid) ){
                //$map['id'] = $pid;
            }
            $map["pid"] = 0;
            $map["status"] = 1;
            $list = $this->newsCategoryModel->where($map)->select();

            $data = "<option value =''>-全部-</option>";
            foreach ($list as $k => $vo) {
                $data .= "<option ";
                if( $pid == $vo['id'] ){
                    $data .= " selected ";
                }
                $data .= " value ='" . $vo['id'] . "'>" . $vo['title'] . "</option>";
            }
            $this->ajaxReturn($data);
        }
    }

    public function selectCategoryLevelTwo(){

        if (IS_AJAX){
            $cid = I('cid');  //默认的省份id
            $pid = I("pid");

            if( !empty($pid) ){
                //$map['id'] = $pid;
            }
            $map["pid"] = $pid;
            $map["status"] = 1;
            $list = [];
            if(!empty($pid)){

                $list = $this->newsCategoryModel->where($map)->select();
            }

            $data = "<option value =''>-全部-</option>";
            foreach ($list as $k => $vo) {
                $data .= "<option ";
                if( $cid == $vo['id'] ){
                    $data .= " selected ";
                }
                $data .= " value ='" . $vo['id'] . "'>" . $vo['title'] . "</option>";
            }
            $this->ajaxReturn($data);
        }
    }

    public function selectCategoryLevelThree(){

        if (IS_AJAX){
            $did = I('did');  //默认的省份id
            $cid = I("cid");

            if( !empty($cid) ){
                //$map['id'] = $pid;
            }
            $map["pid"] = $cid;
            $map["status"] = 1;
            $list = [];
            if(!empty($cid)){

                $list = $this->newsCategoryModel->where($map)->select();
            }

            $data = "<option value =''>-全部-</option>";
            foreach ($list as $k => $vo) {
                $data .= "<option ";
                if( $did == $vo['id'] ){
                    $data .= " selected ";
                }
                $data .= " value ='" . $vo['id'] . "'>" . $vo['title'] . "</option>";
            }
            $this->ajaxReturn($data);
        }
    }

    //*************************************************资讯分类结束**************************************************

    //*************************************************价格公示开始**************************************************
    //价格公示列表
    public function newsGoods($page = 1,$r = 20){

        //查询商品信息
//        $map['productName'] = I("productName");
        $map['publishDate'] = I("publishDate");
        $map["category"] = I("category");
//        $map["companyId"] = $this->company_id;

        //查询价格公示二级分类
        $erJiC = [];
        $data = $this->newsCategoryModel->getTree(0,true,["status" => 1]);
        foreach($data as $tmpD){

            foreach($tmpD["_"] as $erTmpD){

                foreach($erTmpD["_"] as $sanTmpD){

                    $erJiC[] = ["id" => $sanTmpD["id"],"value" => $sanTmpD["title"]];
                }
            }
        }

        $map['type'] = 1;
        $map['status'] = ['neq',-1];


        //查询商品信息
        list($list,$count) = $this->NewsGoodsModel->getListByPage($map,$page,'update_time desc','*',$r);

        $category=$this->newsCategoryModel->getCategoryList(['status'=>1]);
        $category=array_combine(array_column($category,'id'),$category);

        foreach($list as &$val){
            $val['categoryText']=$category[$val['category']]['title'];
        }
        unset($val);

        $builder = new AdminListBuilder();
        $builder->title("价格公示列表")
            ->data($list)
            ->buttonNew(U('News/newsGoodsAdd'),"新增")
            ->ajaxButton(U('News/setnewsgoodsstatus?status=0&id=###'),"","禁用")
            ->ajaxButton(U('News/setnewsgoodsstatus?status=1&id=###'),"","启用")
            ->ajaxButton(U('News/setnewsgoodsstatus?status=-1&id=###'),"","删除")
            ->setSelectPostUrl(U('News/newsGoods'))
            ->select('所属分类：','category','select','','','',array_merge(array(array('id'=>0,'value'=>L('_EVERYTHING_'))),$erJiC))
            ->select('发布日期：','publishDate','select','','','',array_merge(array(array('id'=>-1,'value'=>"今天")),[["id" =>1,"value" => "昨天"],["id" => 2,"value" => "一周内"],["id" => 3,"value" => "一个月内"],["id" => 4,"value" => "全部"]]))
            ->search("商品名称",'goodsName','text','')
            ->keyId()->keyText('name','商品名称')->keyText('categoryText','商品分类')->keyCreateTime('create_time','发布时间')->keyStatus()
            ->keyDoActionEdit('News/newsGoodsAdd?id=###',"查看")
            ->pagination($count,$r)
            ->display();
    }

    //禁用/启用价格公示状态
    public function setnewsgoodsstatus($status){
        $ids = $_POST["ids"];
        if(!empty($ids)){

            $this->NewsGoodsModel->where(["id" => ['in',$_POST['ids']]])->save(["status" => $status]);
            $this->success("状态设置".L('_SUCCESS_'), U('News/newsGoods'));
        }else{

            $this->error("状态设置".L('_FAIL_')."请选择操作的对象");
        }
    }

    public function newsGoodsAdd(){

        $id = I('get.id');
        $editData = I('post.');
        $title=$id || $editData['id']?L('_EDIT_'):L('_ADD_');
        if (IS_POST) {

            //参数验证
            $error = "";
            if(empty($editData["name"])){

                $error .= "商品名称必填！";
            }

            if(empty($editData["price"])){

                $error .= "当日价格必填！";
            }

            if(empty($editData["standard"])){

                $error .= " 规格必填！";
            }

            if(empty($editData["buy_amount"])){

                $error .= " 起购量必填！";
            }

            if(empty($editData["unit"])){

                $error .= " 单位必填！";
            }

            if(empty($editData["qualityImage"])){

                $error .= " 品质标准图片必填！";
            }

            if(!empty($error)){

                $this->error($error);
            }

            $editData['type'] = 1;

            if ($cate = $this->NewsGoodsModel->editData($editData)) {

                //取出添加的资质图片信息
                $qualityImage = I("qualityImage");

                if($qualityImage){

                    //todo 添加或者维护图片关系表 jpgk_NewsGoodsImageModel
                    $rowId = $id ? $id : $cate;

                    //将关联的图片数据删除
                    $this->NewsGoodsImageModel->delData(['goods_id'=>$rowId]);

                    //重新添加图片数据
                    $picData = $this->pictureModel->where(['id'=>$qualityImage])->find();
                    $this->NewsGoodsImageModel->editData([
                        'goods_id'=>$rowId,
                        'image_url_id'=>$picData['id'],
                        'status'=>1,
                        'image_url'=>$picData['path'],
                        'create_time'=>time(),
                    ]);
                }

                $price = I("price");
                $standard = I("standard");

                if($price && $standard){

                    //todo 添加或者维护价格公示规格表 jpgk_NewsGoodsStandardModel
                    $rowId = $id ? $id : $cate;

                    //将关联的规格数据删除
                    $this->NewsGoodsStandardModel->delData(['goods_id'=>$rowId]);

                    //重新添加图片数据
                    $this->NewsGoodsStandardModel->editData([
                        'goods_id'=>$rowId,
                        'name'=>$standard,
                        'price'=>$price,
                        'status'=>1,
                        'create_time'=>time(),
                    ]);
                }

                S('SHOW_EDIT_BUTTON',null);
                $this->success($title.L('_SUCCESS_'), U('News/newsGoods'));
            } else {
                $this->error($title.L('_FAIL_').$this->NewsGoodsModel->getError());
            }
        } else {

            $builder = new AdminConfigBuilder();

            //id不等于0代表编辑
            $data = [];
            if ($id != '') {

                $data = $this->NewsGoodsModel->find($id);

                //品质标准图片
                $qualityImageNew = $this->NewsGoodsImageModel->where(['goods_id'=>$data['id']])->find();
                $data['qualityImage'] = $qualityImageNew['image_url_id'];

//                $qualityImageNew = $this->NewsGoodsImageModel->where(['goods_id'=>$data['id']])->find();

               $cate = $data['category'];

                //取出相对应的规格 拼接数据
                $standardData = $this->NewsGoodsStandardModel->getList([
                    'goods_id'=>$id,
                    'status'=>1,
                ]);

                $data['normsPrice']['price'] = $standardData[0]['price'];
                $data['normsPrice']['standard'] = $standardData[0]['name'];
            }

            $newsOnePid=$this->newsCategoryModel->oneInfo('供应商价格');
            $category=$this->newsCategoryModel->getCategoryList(['status'=>1,'pid' => $newsOnePid['id']]);
            $options=array();
            foreach($category as $val){
                $options[$val['id']]=$val['title'];
            }

            $builder->title($title."价格公示信息")
                ->data($data)
                ->keyId('id')
                ->keyText('name', '商品名称')
                ->keySelect('category',L('_CATEGORY_'),'',$options)->keyDefault('category',$cate)
                ->keyNormsPrice("normsPrice","规格及价格")
                ->keyText("buy_amount","起购量")
                ->keyText("unit","单位")
                ->keySingleImage("qualityImage","品质标准(图片格式为jpg、png格式；图片大小在1MB以内，分辨率640*640)","")
                ->keyTextArea("description","详情")

                ->group("基础信息","id,name,category,normsPrice,price,,buy_amount,unit,qualityImage,description")

                ->buttonSubmit(U('News/newsGoodsAdd'))->buttonBack()
                ->newsDisplay();
        }
    }



    //*************************************************价格公示结束**************************************************

    //************************************************资讯回复开始**************************************************
    //咨询回复列表
    public function localComment($page = 1,$r = 20){

        $newId = I('get.id');

//        $map["productName"] = I("productName");
        $map["newsId"] = $newId;


//        $map["company_id"] = $this->company_id;

        $commentModel = new CommentModel();
        list($list,$count) = $commentModel->getNewsPageList($map,$page,'coment.create_time desc','*',$r);

//        foreach($list as &$l){
//            if(!$l["isReply"]){
//                $url = U("News/zixunreply",["id" => $l["id"]]);
//                $l["operate"] = "<a href='{$url}'>回复</a>";
//            }else{
//                $url = U("News/setreplaystatus",["id" => $l["id"]]);0
//                $l["operate"] = "<a href='{$url}'>删除回复</a>";
//            }
//        }


        foreach($list as &$val){
            $val['taskName'] = '';
            if($val['pid'] != 0){
                foreach($list as $k => $v) {
                    if ($val['pid'] == $v['id']) {
                        $val['taskName'] = $v['nickname'];
                    }
                }
            }else{
                $val['taskName'] = '资讯';
            }
        }

        $newInfo = $this->newsModel->find($newId);

        $builder = new AdminListBuilder();
        $builder->title("资讯评论列表 | 资讯标题 : ".$newInfo['title']."")
            ->data($list)
            ->buttonNew(U('News/zixunreply?newsId='.$newId.''),"新增评论")
            ->ajaxButton(U("Admin/News/setreplaystatus"),"","删除评论",[])
            ->setSelectPostUrl(U('Admin/News/localComment'))
            ->select('回复情况：','status','select','','','',array_merge(array(array('id'=>0,'value'=>L('_EVERYTHING_'))),[["id" =>1,"value" => "已回复"],["id" =>2,"value" => "未回复"]]))
            ->search("产品名称",'newsName','text','')
            ->keyId()->keyText('nickname',"用户")
            ->keyText('taskName','评论对象')->keyText('content','评论内容')
            ->keyCreateTime("create_time","评论时间")
//                ->keyDoActionEdit('Gxpt/zixunreply?id=###',"回复")
            ->pagination($count,$r)
            ->display();
    }

    //新增评论功能
    public function zixunreply(){
        $newsId = I('get.newsId');
        if(IS_POST){

            $commentModel = new CommentModel();

            $reply = [
                "uid" => get_uid(),
                "app" => "News",
                "mod" => "index",
                "row_id" => I('get.id'),
                "parse" => 0,
                "content" => I("content"),
                "create_time" => time(),
                "pid" => 0,
                "status" => 1
            ];

            if($commentModel->add($reply)){

                //给用户推送信息
//                $jpush = new Lz517JPush();
//                $jpush->pushMessageForApp("您的商品咨询有了新的回复，点击查看！",["canxun".$coment["uid"]]);

                $this->success(L('_SUCCESS_'), U('News/localComment?id='.I('get.id').''));
            }else{

                $this->error(L('_FAIL_').$commentModel->getError());
            }
        }else{

            $builder = new AdminConfigBuilder();

            $builder->title("资讯评论添加")
                ->keyTextArea("content","评论内容")
                ->buttonSubmit(U("News/zixunreply",["id" => $newsId]))->buttonBack()
                ->display();
        }
    }

    //删除评论功能
    public function setreplaystatus(){

        $ids = I("ids");

        if(empty($ids) && !empty($_REQUEST["id"])){

            $ids = [$_REQUEST["id"]];
        }

        if(!empty($ids)){

            $where = [
                "pid" => ["in",$ids],
                "id" => ["in",$ids],
                '_logic'=>'or'
            ];
            $commentModel = new CommentModel();
            $commentModel->where($where)->save(["status" => -1]);

            S('SHOW_EDIT_BUTTON',null);
            $this->success(L('_SUCCESS_'), U('News/localComment'));
        }else{

            $this->error(L('_FAIL_').$this->newsModel->getError());
        }
    }


    //*************************************************资讯回复结束**************************************************
    //
    //************************************************订购列表开始**************************************************
    //订购列表
    public function newsOrderList($page = 1,$r = 20){

        $map["category"] = I("category");
        $map["goodsName"] = I("goodsName");

        list($list,$count) = $this->NewsOrderModel->getPageList($map,$page,'newsOrder.create_time desc',"newsOrder.*,goods.name,category.title,FROM_UNIXTIME(newsOrder.create_time, '%Y-%m-%d %H:%i:%S') as newsCreatTime",$r);

        //查询产品二级分类
        $erJiC = [];
        $categoryInfo = $this->newsCategoryModel->oneInfo("供应商价格");
        $data = $this->newsCategoryModel->getCategoryList(["status" => 1,"pid" =>$categoryInfo['id']]);
        foreach($data as $tmpD){

            $erJiC[] = ["id" => $tmpD["id"],"value" => $tmpD["title"]];
        }

        $builder = new AdminListBuilder();
        $builder->title("订购列表")
            ->data($list)
            ->select('商品分类：','category','select','','','',array_merge(array(array('id'=>0,'value'=>L('_EVERYTHING_'))),$erJiC))
            ->search("商品名称",'goodsName','text','')
            ->keyId()->keyText('title','分类')
            ->keyText('name','商品名称')
            ->keyText('amount','订购量（数量）')
            ->keyText('phone',"订购人电话")
            ->keyText("newsCreatTime","订购时间")
            ->pagination($count,$r)
            ->display();
    }


    //*************************************************订购列表结束**************************************************

    //*************************************************banner列表开始**************************************************

    //banner列表开始
    public function bannerList($page=1,$r=20){
        $aCate=I('cate',0,'intval');

        if($aCate){
            $cates=$this->newsCategoryModel->getCategoryList(array('id'=>$aCate));
            if($cates){
                $cateWhere['category']=$aCate;
                $cateWhere['_logic'] = 'OR';
                $map['_complex'] = $cateWhere;
            }
        }

        //搜索资讯名称
        if(I('get.newsName')){
            $map['title'] = ['like','%'.I('get.newsName').'%'];
        }
        $aDead=I('dead',0,'intval');
//        if($aDead){
//            $map['dead_line']=array('elt',time());
//        }else{
//            $map['dead_line']=array('gt',time());
//        }
        $aPos=I('pos',0,'intval');
        /* 设置推荐位 */
        if($aPos>0){
            $map[] = "position & {$aPos} = {$aPos}";
        }

        $map['status']=['neq',-1];
        $map['is_show_banner']=1;

        //HQ修改 过滤咨询显示列表数据
//        if(session('user_auth')['uid']){
//            $uid = session('user_auth')['uid'];
//            $map['uid'] = $uid;
//
//            //查看uid当前用户是否为管理员
//            if($uid){
//
//            }
//        }

        $positions=$this->_getPositions(1);
        list($list,$totalCount)=$this->newsModel->getListByPage($map,$page,'update_time desc','*',$r);

        foreach($list as $key=>&$val){

            //拼接banner数据
            if($val['is_show_banner'] == 1){

                //资讯
                if($val['banner_app']=='News'){
                    if($val['banner_target']=='Main'){
                        $val['bannerAppNew'] = '资讯主页';
                    }elseif(explode('?',$val['banner_target'])[0]=='Detail'){
                        $val['bannerAppNew'] = '资讯详情页';
                    }
                }

                //供需平台
                if($val['banner_app']=='SupplyMarket'){
                    if($val['banner_target']=='Main'){
                        $val['bannerAppNew'] = '供需平台主页';
                    }elseif(explode('=',$val['banner_target'])[0]=='List?categoryOne'){
                        $val['bannerAppNew'] = '供需平台列表页';
                    }elseif(explode('?',$val['banner_target'])[0]=='Detail'){
                        $val['bannerAppNew'] = '供需平台详情页';
                    }
                }

                //二手市场
                if($val['banner_app']=='SHM'){
                    if($val['banner_target']=='Main'){
                        $val['bannerAppNew'] = '二手市场主页';
                    }elseif(explode('?',$val['banner_target'])[0]=='Detail'){
                        $val['bannerAppNew'] = '二手市场详情页';
                    }
                }

                //会议
                if($val['banner_app']=='Event'){
                    if($val['banner_target']=='Main'){
                        $val['bannerAppNew'] = '会议主页';
                    }elseif(explode('?',$val['banner_target'])[0]=='Detail'){
                        $val['bannerAppNew'] = '会议详情页';
                    }
                }

                //直播
                if($val['banner_app']=='Live'){
                    if($val['banner_target']=='Main'){
                        $val['bannerAppNew'] = '直播主页';
                    }elseif(explode('=',$val['banner_target'])[0]=='Detail?id'){
                        $val['bannerAppNew'] = '直播详情页';
                    }
                }

                //视频
                if($val['banner_app']=='CircleVideo'){
                    if($val['banner_target']=='Main'){
                        $val['bannerAppNew'] = '视频主页';
                    }elseif(explode('?',$val['banner_target'])[0]=='Detail'){
                        $val['bannerAppNew'] = '视频详情页';
                    }
                }

                //线上投票
                if($val['banner_app']=='Event'){
                    if($val['banner_target']=='voteList'){
                        $val['bannerAppNew'] = '线上投票主页';
                    }elseif(explode('?',$val['banner_target'])[0]=='voteDetail'){
                        $val['bannerAppNew'] = '线上投票详情页';
                    }
                }
            }
        }

        unset($val);
        $builder=new AdminListBuilder();
        $builder->title('Banner列表')
            ->data($list)
            ->setSelectPostUrl(U('Admin/News/bannerList'))
            ->button('删除', ['class' => 'btn ajax-post tox-confirm', 'data-confirm' => '您确定要删除数据吗?', 'url' => U('News/delBanner?id=###'), 'target-form' => 'ids'])
            ->button('启用', ['class' => 'btn ajax-post tox-confirm', 'data-confirm' => '您确定要启用数据吗?', 'url' => U('News/disableBanner?id=###'), 'target-form' => 'ids'])
            ->button('禁用', ['class' => 'btn ajax-post tox-confirm', 'data-confirm' => '您确定要禁用数据吗?', 'url' => U('News/disableBanner?id=###'), 'target-form' => 'ids'])
            //            ->select('发布日期：','newsDate','select','','','',array_merge(array(array('id'=>0,'value'=>"今天")),[["id" =>1,"value" => "昨天"],["id" => 2,"value" => "一周内"],["id" => 3,"value" => "一个月内"],["id" => 4,"value" => "全部"]]))
            ->search("资讯名称",'newsName','text','')
            ->keyId()->keyUid()
            ->keyText('title',L('_TITLE_'))
            ->keyText('bannerAppNew','跳转目录')
            ->keyText('sort',L('_SORT_'))
            ->keyNewsStatus()->keyCreateTime()->keyUpdateTime();
        if(!$aDead){
//            $builder->ajaxButton(U('News/setDead'),'',L('_SET_EXPIRE_'))->keyDoAction('News/setDead?ids=###',L('_SET_EXPIRE_'));
        }
        $builder->pagination($totalCount,$r)
            ->display();
    }

    //删除资讯banner数据
    public function delBanner(){

        $Ids = I('post.ids');
        if($Ids){
            (new NewsModel())->where(['id'=>['in',$Ids]])->save(['status' => '-1']);
        }

        $this->success('删除成功');
    }

    //启用禁用资讯banner数据
    public function disableBanner(){
        $Ids = I('post.ids');

        if($Ids){
            $jobData = (new NewsModel())->where(['id'=>['IN',$Ids]])->find();

            $status = '';
            $jobData['status'] == '1' ? $status = '0' : ($jobData['status'] == '0' ? $status = '1':'');

            (new NewsModel())->where(['id'=>['IN',$Ids]])->save(['status'=>$status,'update_time'=>time()]);
        }

        $this->success('操作成功');
    }


}