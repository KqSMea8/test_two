<?php
/**
 * Created by PhpStorm.
 * User: HQ
 * Date: 17-6-20
 * Time: PM5:41
 */

namespace Admin\Controller;

use Admin\Builder\AdminConfigBuilder;
use Admin\Builder\AdminListBuilder;
use Admin\Builder\AdminTreeListBuilder;
use Event\Model\EventAttendModel;
use Event\Model\EventModel;
use OnlineVote\Model\EventVoteModel;
use OnlineVote\Model\EventVoteItemModel;
use OnlineVote\Model\EventUserVoteModel;

class OnlineVoteController extends AdminController
{
	protected $eventModel;
	protected $eventTypeModel;

	function _initialize()
	{
		$this->eventModel = D('Event/Event');
		$this->eventTypeModel = D('Event/EventType');
		$tree = $this->eventTypeModel->where(array('status' => 1))->select();
		$this->assign('tree', $tree);
		parent::_initialize();
	}

	public function config()
	{
		$admin_config = new AdminConfigBuilder();
		$data = $admin_config->handleConfig();

		$admin_config->title(L('_EVENT_BASIC_CONF_'))->data($data)
			->keyBool('NEED_VERIFY', L('_EVENT_CREATE_AUDIT_'), L('_AUDIT_DEFAULT_NO_NEED_'))
			->keyBool('SHENHE_SEND_WEIBO', L('_EVENT_AUDIT_SEND_FRESH_'), L('_EVENT_AUDIT_SEND_FRESH_DEFAULT_'))->keyDefault('SHENHE_SEND_WEIBO', 0)
			->keyText('EVENT_SHOW_TITLE', L('_TITLE_NAME_'), L('_HOME_BLOCK_TITLE_'))->keyDefault('EVENT_SHOW_TITLE', '热门活动')
			->keyText('EVENT_SHOW_COUNT', '显示活动的个数', '只有在网站首页模块中启用了活动模块之后才会显示')->keyDefault('EVENT_SHOW_COUNT', 4)
			->keyRadio('EVENT_SHOW_TYPE', '活动的删选范围', '', array('1' => L('_BG_RECOMMEND_'), '0' => L('_EVERYTHING_')))->keyDefault('EVENT_SHOW_TYPE', 0)
			->keyRadio('EVENT_SHOW_ORDER_FIELD', L('_SORT_VALUE_'), L('_TIP_SORT_VALUE_'), array('view_count' => L('_VIEWS_'), 'create_time' => L('_DELIVER_TIME_'), 'update_time' => L('_UPDATE_TIME_'), 'attentionCount' => '报名人数'))->keyDefault('EVENT_SHOW_ORDER_FIELD', 'view_count')
			->keyRadio('EVENT_SHOW_ORDER_TYPE', L('_SORT_TYPE_'), L('_TIP_SORT_TYPE_'), array('desc' => L('_COUNTER_'), 'asc' => L('_DIRECT_')))->keyDefault('EVENT_SHOW_ORDER_TYPE', 'desc')
			->keyText('EVENT_SHOW_CACHE_TIME', L('_CACHE_TIME_'), L('_TIP_CACHE_TIME_'))->keyDefault('EVENT_SHOW_CACHE_TIME', '600')
			->group(L('_EVENT_BASIC_CONF_'), 'NEED_VERIFY,SHENHE_SEND_WEIBO')->group(L('_HOME_SHOW_CONF_'), 'EVENT_SHOW_COUNT,EVENT_SHOW_TITLE,EVENT_SHOW_TYPE,EVENT_SHOW_ORDER_TYPE,EVENT_SHOW_ORDER_FIELD,EVENT_SHOW_CACHE_TIME')
			->groupLocalComment(L('_LOCAL_COMMENT_CONF_'), 'event')
			->buttonSubmit('', L('_SAVE_'));
		$admin_config->display();
	}

	public function index($page = 1, $r = 10, $id = 0)
	{
        $eventName = I("get.eventName");
        if($eventName){
            $map['title'] = ['like','%'.$eventName.'%'];
        }

		//读取列表
		$map['status'] = ['neq',-1];
		$model = $this->eventModel;

//        $aCate=I('cate',1001,'intval');
        $aCate = 1001;

        if($aCate){
            $cates=$this->eventTypeModel->where(['id'=>$aCate])->find();
            if($cates){
                $cateWhere['type_id']=$aCate;
                $cateWhere['_logic'] = 'OR';
                $map['_complex'] = $cateWhere;
            }
        }

        //分类条件搜索
//        $category=$this->eventTypeModel->where(['status'=>['eq',1]])->select();
//        $category=array_combine(array_column($category,'id'),$category);

        //查询活动数据
		$list = $model->where($map)->page($page, $r)->order('status desc,sort asc')->select();
        $totalCount = $model->where($map)->count();

        //查询今日用户报名数
        $nowTime = time();
        foreach ($list as &$oneObject) {

//            //拼接活动是否结束
			$oneObject['eTime'] = (($nowTime > $oneObject['eTime']) ? '已结束(' : '进行中(') . date('Y-m-d', $oneObject['eTime']) . ')';
            $oneObject['sTime'] = Date('Y-m-d H:i:s',$oneObject['sTime']);
            $oneObject['deadline'] = Date('Y-m-d H:i:s',$oneObject['deadline']);

            $oneObject['vote_html'] = '<a href="' . U('OnlineVote/voteList?eventId=' . $oneObject['id']) . '">' . $oneObject['title'] . '</a>';
		}

		unset($li);
        //拼接活动列表分类搜索
        $optCategory = '';
        foreach($optCategory as &$val){
            $val['value']=$val['title'];
        }

		//显示页面
		$builder = new AdminListBuilder();

		$attr['class'] = 'btn ajax-post';
		$attr['target-form'] = 'ids';

        $builder->title(L('线上投票列表'))
//            ->setStatusUrl(U('setEventContentStatus'))
//            ->select('','cate','select','','','',array_merge([['id'=>0,'value'=>"全部"]],[['id'=>1,'value'=>'报名中'],['id'=>2,'value'=>'报名完成'],['id'=>3,'value'=>'已结束']]))
            ->buttonNew(U('admin/OnlineVote/addHuoDong'), '添加活动')
            ->button(L('_ENABLE_'), ['class' => 'btn ajax-post tox-confirm', 'data-confirm' => '您确定要启用数据吗?', 'url' => U('OnlineVote/saveOnlineVoteStatus?status=1'), 'target-form' => 'ids'])
            ->button(L('_DISABLE_'), ['class' => 'btn ajax-post tox-confirm', 'data-confirm' => '您确定要禁用数据吗?', 'url' => U('OnlineVote/saveOnlineVoteStatus?status=0'), 'target-form' => 'ids'])
            ->button(L('_DELETE_'), ['class' => 'btn ajax-post tox-confirm', 'data-confirm' => '您确定要删除数据吗?', 'url' => U('OnlineVote/saveOnlineVoteStatus?status=-1'), 'target-form' => 'ids'])
            ->keyId()
            ->keyText('vote_html','活动标题')
            ->keyStatus()
            ->keyText('sort','排序')
            ->keyCreateTime()
            ->keyUpdateTime()
            ->keyText('deadline','投票结束时间')
            ->keyText('eTime','活动状态')
            ->data($list)
            ->keyDoActionEdit('OnlineVote/edit?id=###')
            ->pagination($totalCount, $r)
            ->search("标题",'eventName','text','')
            ->display();
	}

    public function saveOnlineVoteStatus(){

        $ids = $_POST["ids"];
        if(!empty($ids)){

            (new EventModel())->where(["id" => ['in',$_POST['ids']]])->save(["status" => I('get.status')]);
            $this->success("状态设置".L('_SUCCESS_'), U('OnlineVote/index'));
        }else{

            $this->error("状态设置".L('_FAIL_')."请选择操作的对象");
        }
    }

    //修改奖项状态
    public function saveEventVoteStatus(){

        $ids = $_POST["ids"];
        $eventId = I('get.eventId');
        $page = I('get.page');

        if(!empty($ids)){

            (new EventVoteModel())->where(["id" => ['in',$_POST['ids']]])->save(["status" => I('get.status')]);
            $this->success("状态设置".L('_SUCCESS_'), U('OnlineVote/votelist?eventId='.$eventId.'&page='.$page.''));
        }else{

            $this->error("状态设置".L('_FAIL_')."请选择操作的对象");
        }
    }

    //复制数据
    public function copyOnlineVote(){

        $eventId = I('get.eventId');
        $page = I('get.page');

        //循环复制数据
        foreach(I('ids') as $val){

            $voteModel = (new EventVoteModel());
            $itemModel = (new EventVoteItemModel());

            //1.查询eventVote数据
            $voteData = $voteModel->where(['id'=>$val])->find();

            //2.查询投票候选数据
            $itemData = $itemModel->where(['event_vote_id'=>$val])->select();

//            print_r($itemData);die;
            $returnOne = '' ;
            $returnTwo = '';
            if($voteData){
                //开启事务
                $voteModel->startTrans();

                //添加奖项数据
                unset($voteData['id']);
                $returnOne = $voteModel->add($voteData);

                //循环拼接 奖项候选数据
                $itemArr = [];
                foreach($itemData as $key2 => &$val2){

                    unset($val2['id']);
                    $val2['event_vote_id'] = $returnOne;
                    $itemArr[] = $val2;
                }

                // 添加候选数据入库
                $returnTwo = $itemModel->addAll($itemArr);
                if($returnOne && $returnTwo){

                    //只有$returnOne 和  $returnTwo  都执行成功是才真正执行上面的数据库操作
                    $voteModel->commit();

                    $this->success("数据复制设置".L('_SUCCESS_'), U('OnlineVote/votelist?eventId='.$eventId.'&page='.$page.''));
                }else{

                    //  条件不满足，回滚
                    $voteModel->rollback();

                    $this->error("状态设置".L('_FAIL_')."请选择操作的对象");
                }
            }
        }
        exit;
    }

	public function edit()
	{
        $id = I('get.id');
		$event_content = D('Event')->where(array('id' => $id))->find();
        if (!$event_content) {
            $this->error('404 not found');
        }

        $this->assign('content', $event_content);
		$this->setTitle(L('') . L('_DASH_') . L('_MODULE_'));
		$this->setKeywords(L('_EDIT_') . L('_COMMA_') . L('_MODULE_'));

		$this->display();
	}

	public function addHuoDong()
	{
		$this->display();
	}

    //查看投票详情列表
	public function showVote($id,$page=1,$r=20)
	{
//        if(I('get.selectDate')){
//            $map['selectDate']=I('get.selectDate');
//        }

        $map['vote_id'] = $id;
        list($list,$totalCount)=(new EventVoteModel())->getListByPage($map,$page,'create_time desc','*',$r);

        //修改空值为0
        foreach($list as &$val){
            if(empty($val['userNum'])){
                $val['userNum'] = 0;
            }
            if(empty($val['voteNum'])){
                $val['voteNum'] = 0;
            }
        }

        $attr['href'] = U('exportExcel?voteID='.$id);
        $attr['class']='btn btn-ajax';

        //查询活动标题
        $eventData = (new  EventVoteModel())->where(['id'=>$id])->find();

		//显示页面
		$builder = new AdminListBuilder();

		$builder->title("奖项标题 | 《".$eventData['title']."》")
			->keyId()
            ->data($list)
			->keyText('name','候选名称')
			->keyText('voteNum','得票数')
            ->keyText('userNum','活动参与投票人数')
            ->button('导出excel列表',$attr);
        $builder->pagination($totalCount,$r)
            ->display();
	}

    /**
     * 导出报名excel数据
     */
    public function exportExcel(){

        $id = I('get.voteID');
        $map['vote_id'] = $id;
        $data=(new EventVoteModel())->getList($map,'create_time desc','*');

        $eventData = (new  EventVoteModel())->where(['id'=>$id])->find();

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

            $excelData [$k] ['name'] = $v ['name'];
            $excelData [$k] ['voteNum'] = empty($v ['voteNum']) == true  ? 0 : $v ['voteNum'];
            $excelData [$k] ['userNum'] = empty($v ['userNum']) == true  ? 0 : $v ['userNum'];
//            $excelData [$k] ['create_time'] = Date('Y-m-d H:i:s',$v ['create_time']);
            $num ++;
        }
        $len = count ( $excelData );
        $info = array (
            "table_name_one" =>  "投票信息数据表|".$eventData['title'],
            "table_name_two" => "投票信息报表",
            "lister" => "",
            "tabletime" => date ( "Y-m-d", time () )
        );

        $optional = array (
            'colwidth' => array (
                'A' => '40',
                'B' => '30',
                'C' => '30',
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
            '候选名称',
            '得票数',
            '活动参与投票人数',
        ];

        return $return;
    }

	/**
	 * 发布活动
	 * autor:HQ
	 */

    public function doPost($id = 0, $cover_id = 0, $title = '',$explain = '', $sTime = '', $eTime = '',$deadline = '',$sort = '',$type_id = 0,$pid = 0)
    {
        $error = '';
        if (!is_login()) {
            $error.= L('_ERROR_LOGIN_');
        }
        if (!$cover_id) {
            $error.= L('_ERROR_COVER_').'！';
        }
        if (trim(op_t($title)) == '') {
            $error.= L('_ERROR_TITLE_').'！';
        }
        if (trim(op_h($explain)) == '') {
            $error.= L('_ERROR_CONTENT_').'！';
        }
        if (trim($sort) == '') {
            $error.= L('排序不可为空').'！';
        }
        if(!$sTime){
            $error.= '请选择开始时间！';
        }
        if(!$eTime){
            $error.= '请选择结束时间！';
        }
        if($deadline < $sTime){
            $error.= '投票截止时间不可小于开始时间！';
        }
        if($deadline > $eTime){
            $error.= '投票截止时间不可大于开始时间！';
        }
        if ($sTime > $eTime) {
            $error.= L('_ERROR_TIME_START_').'！';
        }

        if(!empty($error)){
            $this -> error($error);
        }

        //拼接分类id
        if ($type_id == 0) {
            $type_id == 1001;
        }

        $content = D('Event')->create();
        if (!mb_strlen($content['description'], 'utf-8')) {
            $content['description'] = msubstr(trimall(op_t($content['explain'])), 0, 200);
        }

        $content['explain'] = filter_content($content['explain']);
        $content['title'] = op_t($content['title']);
        $content['sTime'] = strtotime($content['sTime']);
        $content['eTime'] = strtotime($content['eTime']);
        $content['cover_id'] = $cover_id;
        $content['sort'] = $sort;
        $content['type_id'] = intval(1001);
        $content['deadline'] = strtotime($deadline);

        $content['update_time'] = time();

        if ($id) {
            $content_temp = D('Event')->find($id);

            $pid = $this->eventTypeModel->find($type_id)['pid'];
            $pid = $pid?$pid:$type_id;
//            $this->checkActionLimit('add_event', 'event', $id, is_login(), true);
            $content['uid'] = $content_temp['uid']; //权限矫正，防止被改为管理员

            $rs = D('Event')->save($content);

            if ($rs!==false) {
                action_log('add_event', 'event', $id, is_login());
                if(!empty($pid)){

                    $this->success(L('_SUCCESS_SETTING_'), U('onlineVote/index?id='.$pid),true);
                }else{
                    $this->success(L('_SUCCESS_SETTING_'), U('onlineVote/index?id='.$type_id),true);
                }
            } else {
                $this->error(L('_ERROR_OPERATION_FAIL_').L('_EXCLAMATION_'),'');
            }
        } else {
            $content['create_time'] = time();
            $content['status'] = 1;
            $content['uid'] = is_login() ;
            $content['attentionCount'] = 0;
            $content['signCount'] = 0;

            $rs = (new EventModel())->add($content);

            if ($rs) {

                if(!empty($pid)){

                    $this->success(L('_SUCCESS_SETTING_'), U('onlineVote/index?id='.$pid),true);
                }else{
                    $this->success(L('_SUCCESS_SETTING_'), U('onlineVote/index?id='.$type_id),true);
                }
            } else {
                $this->error(L('_ERROR_OPERATION_FAIL_').L('_EXCLAMATION_'));
            }

        }
    }

    /**
     * 发布奖项数据
     * autor:HQ
     */
    public function voteDoPost($id = 0, $cover_id = 0,$awardsTitle = '',$limitPerTime = '',$rule = '',$deadline = '',$type_id = 0,$pid = 0)
    {
        if (!is_login()) {
            $this->error(L('_ERROR_LOGIN_'));
        }
        if (trim(op_t($awardsTitle)) == '') {
            $this->error('奖项标题不可为空！');
        }
        if (trim($limitPerTime) == '') {
            $this->error('每人每次票数限制不可为空！');
        }
        if (trim($rule) == '') {
            $this->error('投票规则说明不可为空！');
        }
        if ($type_id == 0) {
            $type_id == 1001;
        }

//        if ($deadline == '') {
//            $this->error(L('_ERROR_DEADLINE_'));
//        }

        foreach(I('post.') as $key => $val){

            //判断候选数据传输过来了 且不等于0
            if(stripos($key,'itemName')!== false){
                $itemNum = substr($key,8);

                //候选数据判断不可为空
                if(!I('post.itemName'.$itemNum)){
                    $this->error('企业或个人名称不可为空');
                }
                if(!I('post.itemDesc'.$itemNum)){
                    $this->error('候选介绍不可为空');
                }elseif(mb_strlen(I('post.itemDesc'.$itemNum),'UTF8') > 20){
                    $this->error('候选介绍不可大于20字');
                }

                if(!I('post.sort'.$itemNum)){
                    $this->error('排序不可为空');
                }
                if(!I('post.itemReason'.$itemNum)){
                    $this->error('入围理由不可为空');
                }
                if(!I('post.pictureValue'.$itemNum)){
                    $this->error('候选数据图片不可为空');
                }
            }
        }


        //拼接投票表(event_vote)的数据
        $eventVote['id'] = $id;
        $eventVote['event_id'] = I('post.eventType');
        $eventVote['type'] = I('post.type');
        $eventVote['title'] = I('post.awardsTitle');
        $eventVote['limit_per_time'] = I('post.limitPerTime');
        $eventVote['rule'] = I('post.rule');
//        $eventVote['deadline'] = strtotime($deadline);
        if ($id) {

            //添加投票数据
            $rs = (new EventVoteModel())->editData($eventVote);

            //将候选数据拆分出来
            $itemData = [];
            foreach (I('post.') as $key => $val) {

                //判断候选数据传输过来了
                if (stripos($key, 'itemName') !== false) {

                    //截取候选名称后缀
                    $itemNum = substr($key, 8);

                    //添加候选数据
                    $itemReturn = (new EventVoteItemModel())->editData(
                        [
                            'id' => I('post.itemId' . $itemNum),
                            'name' => I('post.itemName' . $itemNum),
                            'description' => I('post.itemDesc' . $itemNum),
                            'reason' => I('post.itemReason' . $itemNum),
                            'sort' => I('post.sort' . $itemNum),
                            'picture_id' => I('post.pictureValue' . $itemNum),
                            'status' => 1,
                            'event_vote_id' => $id,
                        ]);
                }
            }

            if ($rs!==false) {
                action_log('add_event', 'event', $id, is_login());
                if(!empty($pid)){

                    $this->success(L('_SUCCESS_SETTING_'), U('onlineVote/index?id='.$pid),true);
                }else{
                    $this->success(L('_SUCCESS_SETTING_'), U('onlineVote/index?id='.$type_id),true);
                }
            } else {
                $this->error(L('_ERROR_OPERATION_FAIL_').L('_EXCLAMATION_'),'');
            }
        } else {
            //拼接投票表(event_vote)的数据
            $eventVote['event_id']= I('post.eventType');
            $eventVote['type'] = I('post.type');
            $eventVote['title'] = I('post.awardsTitle');
            $eventVote['limit_per_time'] = I('post.limitPerTime');
            $eventVote['rule'] = I('post.rule');
//            $eventVote['deadline'] = strtotime($deadline);

            //添加投票数据
            $rs = (new EventVoteModel())->editData($eventVote);

            if ($rs) {

                //将候选数据拆分出来
                $itemData = [];
                foreach(I('post.') as $key => $val){

                    //判断候选数据传输过来了
                    if(stripos($key,'itemName')!== false){

                        //截取候选名称后缀
                        $itemNum = substr($key,8);

                        //添加候选数据
                        $itemReturn = (new EventVoteItemModel()) -> editData(
                            [
                                'name' => I('post.itemName'.$itemNum),
                                'description' => I('post.itemDesc'.$itemNum),
                                'reason' => I('post.itemReason'.$itemNum),
                                'sort' => I('post.sort'.$itemNum),
                                'picture_id' => I('post.pictureValue'.$itemNum),
                                'status' => 1,
                                'event_vote_id' => $rs,
                            ]);
                    }
                }

                if(!empty($pid)){

                    $this->success(L('_SUCCESS_SETTING_'), U('onlineVote/index?id='.$pid),true);
                }else{
                    $this->success(L('_SUCCESS_SETTING_'), U('onlineVote/index?id='.$type_id),true);
                }
            } else {
                $this->error(L('_ERROR_OPERATION_FAIL_').L('_EXCLAMATION_'));
            }

        }
    }

    //删除候选数据 功能
    public function delItemDataForItemId(){
        $itemId = I('post.itemId');

        //修改数据为删除状态
        (new EventVoteItemModel())->where(['id'=>$itemId])->save(['status'=>-1]);

        //删除投票数据
        (new EventUserVoteModel())->where(['vote_item_id'=>$itemId])->delete();
    }


    //显示奖项列表
    public function voteList($page = 1, $r = 10, $eventId = 0){

        //读取列表
        $model = (new EventVoteModel());

        $eventData = '';
        if($eventId){
            $eventData=$this->eventModel->where(['id'=>$eventId])->select();
            if($eventData){
                $cateWhere['event_id']=$eventId;
                $cateWhere['_logic'] = 'OR';
                $map['_complex'] = $cateWhere;
            }
        }else{
            $where = [
//                'status'=>['neq',-1],
                'type_id'=>1001
            ];
            $eventData = $this->eventModel->field('id,title')->where($where)->select();
        }
        $eventData = useFieldAsArrayKey($eventData,'id');

        $map['status'] = ['neq',-1];
        //查询活动数据
        $list = $model->where($map)->page($page, $r)->order('event_id desc')->select();
        $totalCount = $model->where($map)->count();

        if($list){
            foreach ($list as &$oneObject) {

                //拼接上投票名称
                if($eventData[$oneObject['event_id']]){
                    $oneObject['eventName'] = $eventData[$oneObject['event_id']]['title'];
                }

                //拼接活动是否结束
                $oneObject['deadline'] = Date('Y-m-d H:i:s',$oneObject['deadline']);
            }

        }

        unset($li);
        //拼接活动列表分类搜索
        $optCategory = '';
        foreach($optCategory as &$val){
            $val['value']=$val['title'];
        }

        //显示页面
        $builder = new AdminListBuilder();

        $attr['class'] = 'btn ajax-post';
        $attr['target-form'] = 'ids';

        $builder->title(L('奖项列表'))
            ->buttonNew(U('OnlineVote/addVote?eventId='.$eventId.''), '添加奖项')
            ->button(L('_ENABLE_'), ['class' => 'btn ajax-post tox-confirm', 'data-confirm' => '您确定要启用数据吗?', 'url' => U('OnlineVote/saveEventVoteStatus?status=1&eventId='.$eventId.'&page='.$page.''), 'target-form' => 'ids'])
            ->button(L('_DISABLE_'), ['class' => 'btn ajax-post tox-confirm', 'data-confirm' => '您确定要禁用数据吗?', 'url' => U('OnlineVote/saveEventVoteStatus?status=0&eventId='.$eventId.'&page='.$page.''), 'target-form' => 'ids'])
            ->button(L('_DELETE_'), ['class' => 'btn ajax-post tox-confirm', 'data-confirm' => '您确定要删除数据吗?', 'url' => U('OnlineVote/saveEventVoteStatus?status=-1&eventId='.$eventId.'&page='.$page.''), 'target-form' => 'ids'])
            ->button('复制', ['class' => 'btn ajax-post tox-confirm', 'data-confirm' => '您确定要复制数据吗?', 'url' => U('OnlineVote/copyOnlineVote?eventId='.$eventId.'&page='.$page.''), 'target-form' => 'ids'])
            ->keyId()
            ->keyText('title','奖项标题')
            ->keyStatus()
            ->keyText('eventName','所属活动')
            ->keyCreateTime()
            ->keyUpdateTime()
//            ->keyText('deadline','投票结束时间')
            ->data($list)
            ->keyDoActionEdit('OnlineVote/editVote?id=###')
            ->keyLink('查看票数', '查看票数', 'Admin/OnlineVote/showVote?id=###')
            ->pagination($totalCount, $r)
            ->display();
    }

    public function editVote()
    {
        $id = I('get.id');
//        $event_content = D('Event')->where(array('id' => $id))->find();
//        if (!$event_content) {
//            $this->error('404 not found');
//        }
        $eventVote = D('EventVote')->where(['id' => $id])->find();
        if($eventVote){

            if($eventVote['event_id']) {
                $this->assign('eventiid',$eventVote['event_id']);
            }

            $event_content['voteId'] = $eventVote['id'];
            $event_content['awardsTitle'] = $eventVote['title'];
            $event_content['limitPerTime'] = $eventVote['limit_per_time'];
            $event_content['rule'] = $eventVote['rule'];
            $event_content['deadline'] = $eventVote['deadline'];
            $event_content['type'] = $eventVote['type'];

            //查询候选名单数据
            $EventVoteItemModel = (new EventVoteItemModel());
            $eventVoteItem = $EventVoteItemModel->alias("voteItem")
                ->field("voteItem.*,pic.path as imgUrl")
                ->join("INNER JOIN jpgk_picture as pic on voteItem.picture_id = pic .id")
                ->where(['voteItem.event_vote_id'=>$eventVote['id'],'voteItem.status'=>1])
                ->select();

            if($eventVoteItem){
                $this->assign('eventVoteItem',json_encode($eventVoteItem));
            }
        }

        //获取页面所属活动数据
        $eventData = $this->eventModel->field('id,title')->where(['type_id' => 1001])->select();

        $this->assign('eventData',$eventData);

        //拼接下拉列表
        $select = [1 => '仅一次',2 => '每天一次'];

        $this->assign('select', $select);
        $this->assign('content', $event_content);
        $this->setTitle(L('') . L('_DASH_') . L('_MODULE_'));
        $this->setKeywords(L('_EDIT_') . L('_COMMA_') . L('_MODULE_'));

        $this->display();
    }

    public function addVote()
    {
        $eventId = I('eventId');
        if($eventId) {
            $this->assign('eventiid',$eventId);
        }

        $where['type_id'] = 1001;

        $eventData = $this->eventModel->field('id,title')->where($where)->select();
        $eventData = useFieldAsArrayKey($eventData,'id');

        $this->assign('eventData',$eventData);

        $this->display();
    }

    //后台投票项入围理由专用
    public function editor($id = 'myeditor', $name = 'content',$default='',$width='100%',$height='200px',$config='',$style='',$param='')
    {
        //后台投票管理入围理由专用
        if($config == 'reason'){
            $config=" toolbars:[['source','|','bold','italic','underline','fontsize','forecolor','justifyleft','fontfamily','insertimage']]";
        }
        empty($param['zIndex']) && $param['zIndex'] = 977;
        $config.=(empty($config)?'':',').'zIndex:'.$param['zIndex'];
        is_bool(strpos($width,'%')) && $config.=',initialFrameWidth:'.str_replace('px','',$width);
        is_bool(strpos($height,'%')) && $config.=',initialFrameHeight:'.str_replace('px','',$height);
        $config.=',autoHeightEnabled: false';
        cookie('video_get_info',U('Core/Public/getVideo'));

        $html = '';

        if(\Think\Hook::get('adminEditor') && MODULE_NAME == 'Admin'){
            $html .= "<label class='textarea'>
            <textarea name=".$name." id=".$id.">$default</textarea>
            </label>";

            hook('adminEditor', ['id'=>$id,'value'=>$default]);
        }elseif(\Think\Hook::get('editor')){

            $html .= "<label class='textarea'>
            <textarea name=".$name." id=".$id.">$default</textarea>
            </label>";

            hook('editor', array('id'=>$id,'value'=>$default));
        }else{
            $html .= "<script type='text/plain' name=".$name." id=".$id." style='width:".$width.";height:".$height.";'".$style.">$default</script>";
            $html .= "
            <script>
                var  ue_".$id.";
                $(function (){
                    var config = {".$config.",'topOffset':$('#nav_bar').height()+$('#sub_nav').height()+5};
                    ue_".$id." = UE.getEditor('".$id."',config);
                })
            </script>";

        }

        echo $html;
    }
}
