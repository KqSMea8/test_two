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
use Admin\Model\PictureModel;
use Classroom\Model\ClassCategoryModel;
use Classroom\Model\ClassModel;
use Classroom\Model\ClassSourceModel;

class ClassroomController extends AdminController{

    protected $newsModel;
    protected $newsDetailModel;
    protected $newsCategoryModel;
    protected $payServerModel;
    protected $NewsGoodsModel;
    protected $NewsOrderModel;
    protected $NewsGoodsImageModel;
    protected $pictureModel;

    protected $classCategoryModel;
    protected $classModel;
    protected $classSourceModel;

    function _initialize()
    {
        parent::_initialize();
        $this->newsModel = D('News/News');
        $this->newsDetailModel = D('News/NewsDetail');
        $this->newsCategoryModel = D('News/NewsCategory');
        $this->payServerModel = D('Pay/PayServer');
        $this->NewsGoodsModel = D('News/NewsGoods');
        $this->NewsOrderModel = D('News/NewsOrder');
        $this->NewsGoodsImageModel = D('News/NewsGoodsImageModel');
        $this->pictureModel = new PictureModel();

        $this->classCategoryModel = new ClassCategoryModel();
        $this->classModel = new ClassModel();
        $this->classSourceModel = D('Classroom/ClassSourceModel');
    }

    public function classCategory()
    {
        //显示页面
        $builder = new AdminTreeListBuilder();

        $tree = $this->classCategoryModel->getTree(0, 'id,name as title,sort,pid,status');

        $builder->title('课程分类管理')
            ->buttonNew(U('Classroom/add'))
            ->data($tree)
            ->setLevel(2)
            ->setSource('classRoom')
            ->display();
    }

    /**分类添加
     * @param int $id
     * @param int $pid
     */
    public function add($id = 0, $pid = 0)
    {
        $title=$id?L('_EDIT_'):L('_ADD_');
        if (IS_POST) {

            //参数验证
            $error = "";
            if(empty($_POST["name"])){

                $error .= "分类名称必填！";
            }

            if(!empty($error)){

                $this->error($error);
            }

            //拼接图片链接
            if(I('post.icon_id')){
                $iconData = $this->pictureModel->where(['id'=>I('post.icon_id')])->find();
                $_POST['icon_path'] = $iconData['path'] ;
            }

            if ($cate = $this->classCategoryModel->editData($_POST)) {
                S('SHOW_EDIT_BUTTON',null);
                $this->success($title.L('_SUCCESS_'), U('Classroom/classCategory'));
            } else {
                $this->error($title.L('_FAIL_').$this->classCategoryModel->getError());
            }
        } else {
            $builder = new AdminConfigBuilder();

            if ($id != 0) {
                $data = $this->classCategoryModel->find($id);
            }

            if($pid!=0){
                $categorys = $this->classCategoryModel->where(array('id'=>$pid,'status'=>array('egt',0)))->select();
            }

            $opt = array();
            foreach ($categorys as $category) {
                $opt[$category['id']] = $category['name'];
            }
            $builder->title($title.L('_CATEGORY_'))
                ->data($data)
                ->keyId()->keyText('name', L('_TITLE_'))
                ->keySelect('pid',L('_FATHER_CLASS_'), L('_FATHER_CLASS_SELECT_'), array('0' =>L('_TOP_CLASS_')) + $opt)->keyDefault('pid',$pid)
                ->keyInteger('sort',L('_SORT_'))->keyDefault('sort', 0)
                ->keyStatus()->keyDefault('status',1)
                ->keySingleImage('icon_id',L('图片'))
                ->buttonSubmit(U('Classroom/add'))->buttonBack()
                ->display();
        }
    }

    /**
     * 设置分类状态：删除=-1，禁用=0，启用=1
     * @param $ids
     * @param $status
     */
    public function setStatus($ids, $status)
    {

        !is_array($ids)&&$ids=explode(',',$ids);
        if(in_array(1,$ids)){
            $this->error(L('_ERROR_CANNOT_'));
        }
        if($status==0||$status==-1){
            $map['id']=array('in',$ids);
            $this->classCategoryModel->where($map)->setField('category',1);
        }

        //删除分类时 所属课程也会删除
        if($status == -1){
            $where['category_one'] = $ids;
            $where['category_two'] = $ids;
            $where['_logic'] = 'OR';
            $this->classModel->where($where)->save(['status'=>-1]);
        }

        $builder = new AdminListBuilder();
        $builder->doSetStatus('classCategory', $ids, $status);
    }
    //分类管理end

    //课程列表start
    public function classList($page=1,$r=20)
    {
        $aCate=I('cate',0,'intval');
        if($aCate){
            $cates=$this->classCategoryModel->getCategoryList(array('pid'=>$aCate));
            if(count($cates)){
                $cates=array_column($cates,'id');
                $cates=array_merge(array($aCate),$cates);
                $map['category']=array('in',$cates);
            }else{
                $map['category']=$aCate;
            }
        }

        $map['status']=['neq',-1];

        //HQ修改 过滤咨询显示列表数据
        if(session('user_auth')['uid']){
            $uid = session('user_auth')['uid'];
            $map['uid'] = $uid;
            //查看uid当前用户是否为管理员
            if($uid == 1){
                unset($map['uid']);
            }
        }

//        $positions=$this->_getPositions(1);
        list($list,$totalCount)=$this->classModel->getListByPage($map,$page,'update_time desc','*',$r);

        $category=$this->classCategoryModel->getCategoryList(array('status'=>array('egt',0)),1);
        $category=array_combine(array_column($category,'id'),$category);

        $sourceData = (new ClassSourceModel())->getList(['status'=>1]);
        $sourceData = array_combine(array_column($sourceData,'id'),$sourceData);

        foreach($list as &$val){

            //拼接课程来源
//            if($val['source_id'] == 1){
//                $val['classSource'] = '勺子课堂';
//            }elseif($val['source_id'] == 2){
//                $val['classSource'] = '筷子课堂';
//            }

            //拼接课程来源数据
            if($sourceData[$val['source_id']]){
                $val['classSource'] = $sourceData[$val['source_id']]['name'];
            }

            $val['categoryOne'] = '';
            $val['categoryTwo'] = '';
            //拼接分类数据
            if($category[$val['category_one']]){
                $val['categoryOne'] = $category[$val['category_one']]['name'];
            }

            if($val['category_two']){
                $val['categoryTwo'] = $category[$val['category_two']]['name'];
            }

        }

        unset($val);

        $optCategory=$category;
        foreach($optCategory as &$val){
            $val['value']=$val['name'];
        }

        unset($val);

        $builder=new AdminListBuilder();
        $builder->title('课程列表')
            ->data($list)
            ->setSelectPostUrl(U('Admin/News/index'))
//            ->select('','cate','select','','','',array_merge(array(array('id'=>0,'value'=>L('_EVERYTHING_'))),$optCategory))
//            ->select('课程状态','classStatus','select','','','',[['id'=>0,'value'=>'已下架'],['id'=>1,'value'=>'已上架']])
//            ->search("课程名称",'className','text','')
            ->buttonNew(U('classroom/editClass'))
            ->keyId()->keyUid()->keyText('title','课程标题')->keyText('categoryOne','课程一级类别')->keyText('categoryTwo','课程二级类别')->keyText('classSource','课程来源')->keyText('click_rate','点击量')->keyText('price','课程价格(元)')
            ->keyNewsStatus()->keyCreateTime()
            ->keyDoActionEdit('Classroom/editClass?id=###')
            ->button('启用', ['class' => 'btn ajax-post tox-confirm', 'data-confirm' => '您确定要启用数据吗?', 'url' => U('Classroom/disableClass?status=1'), 'target-form' => 'ids'])
            ->button('禁用', ['class' => 'btn ajax-post tox-confirm', 'data-confirm' => '您确定要禁用数据吗?', 'url' => U('Classroom/disableClass?status=0'), 'target-form' => 'ids'])
            ->button('删除', ['class' => 'btn ajax-post tox-confirm', 'data-confirm' => '您确定要删除数据吗?', 'url' => U('Classroom/delClass?status=-1'), 'target-form' => 'ids']);

        $builder->pagination($totalCount,$r)
            ->display();
    }

    //上线/下线
    public function disableClass(){
        $classId = I('ids');
        $status = I('get.status');

        $return = '';
        if($classId){
            $return = $this->classModel->where(['id'=>['IN',$classId]])->save(['status'=>$status,'update_time'=>Date('Y-m-d H:i:s')]);
        }
        if($return){
            $this->success('操作成功');
        }else{
            $this->success('操作失败');
        }

    }

    //删除商品
    public function delClass(){
        $classId = I('ids');
        $status = I('get.status');

        $return = '';
        if($classId){
            $return = $this->classModel->where(['id'=>['IN',$classId]])->save(['status'=>$status,'update_time'=>Date('Y-m-d H:i:s')]);
        }
        if($return){
            $this->success('操作成功');
        }else{
            $this->success('操作失败');
        }
    }

    public function editClass()
    {
        $aId=I('id',0,'intval');
        $title=$aId?L('_EDIT_'):L('_ADD_');
        if(IS_POST){

            //参数验证
            $error = "";
            if(empty($_POST['title'])){

                $error .= "课程名称不可为空";
            }

//            if(empty($_POST['price'])){
//
//                $error .= "课程价格不可为空";
//            }

            if(empty($_POST['source_id'])){

                $error .= "课程资源不可为空";
            }


            if(!empty($error)){

                $this->error($error);
            }
            $aId&&$data['id']=$aId;
            $data['uid']=get_uid();
            $data['title']=I('post.title');
            $data['category_one']=I('post.one_level_category');
            $data['category_two']=I('post.two_level_category');
            $data['type']=I('post.type');
            $data['url']=I('post.url');
            if(I('post.source_id') == '勺子课堂'){
                $source = (new ClassSourceModel())->where(['name'=>'勺子课堂','status'=>1])->find();
                $data['source_id']=$source['id'];
            }else{
                $data['source_id']=I('post.source_id');
            }
            $data['cover_id']=I('post.cover_id');

            $data['is_banner']=I('post.is_banner');
            if(I('post.is_banner') == 1){
                $data['banner_id']=I('post.banner_id');
            }

            if(I('post.price') == 0){
                $data['price'] = 0;
            }else{
                $data['price'] = I('post.classPrice');
            }

            $data['status']=1;
            if($result=$this->classModel->editData($data)){

                S('news_home_data',null);
                $aId=$aId?$aId:$result;


                $this->success($title.L('_SUCCESS_'),U('Classroom/classlist'));
            }else{
                $this->error($title.L('_SUCCESS_'),$this->classModel->getError());
            }
        }else{

            if($aId){
                $data=$this->classModel->find($aId);

                $this->assign("dsparam",[
                    'one_level_category' => $data['category_one'],
                    'two_level_category' => $data['category_two']
                ]);

            }

            $category=$this->classCategoryModel->getCategoryList(array('status'=>array('egt',0)),1);
            $options=[];
            foreach($category as $val){
                $options[$val['id']]=$val['title'];
            }

            $sourceData = (new ClassSourceModel())->getList(['status'=> 1]);
            $sourceOptions=[];
            foreach($sourceData as $val){
                $sourceOptions[$val['id']]=$val['name'];
            }

            $builder=new AdminConfigBuilder();

            if(is_administrator()){
                $builder->title($title.'课程')
                    ->data($data)
                    ->keyId()
                    ->keyReadOnly('uid',L('_PUBLISHER_'))->keyDefault('uid',get_uid())
                    ->keyText('title','课程名称')
    //                ->keySelect('category',L('_CATEGORY_'),'',$options)
                    ->keyOthers("category","选择分类",null,"Classroom/Test/test",["module" => "Classroom","html" => "selectcategory"])
                    ->keySelect('source_id','课程来源','',$sourceOptions)
                    ->keyText('url','资源链接')
                    ->keyClassRadio('price','课程费用(选择付费时,金额填写到下栏文本框)','',[0=>'免费',1=>'付费'])->keyDefault('price',0)
                    ->keySingleImage("cover_id","课程封面","图片格式为jpg、png格式；图片大小在1MB以内，分辨率640*640")
                    ->keyRadio('type','放置板块','',[1=>'推荐课程',2=>'热门课程'])->keyDefault('type',1)
                    ->keyRadio('is_banner','banner设置','',[0=>'不是',1=>'是'])->keyDefault('is_banner',0)
                    ->keySingleImage("banner_id","banner图片","图片格式为jpg、png格式；图片大小在1MB以内，分辨率640*640")

                    ->group(L('_BASIS_'),'id,uid,title,category,source_id,url,price,cover_id,type,is_banner,banner_id')

                    ->buttonSubmit()->buttonBack()
                    ->GXPTdisplay();
            }else{
                $builder->title($title.'课程')
                    ->data($data)
                    ->keyId()
                    ->keyReadOnly('uid',L('_PUBLISHER_'))->keyDefault('uid',get_uid())
                    ->keyText('title','课程名称')
                    //                ->keySelect('category',L('_CATEGORY_'),'',$options)
                    ->keyOthers("category","选择分类",null,"Classroom/Test/test",["module" => "Classroom","html" => "selectcategory"])
                    ->keyReadOnly('source_id','课程来源')->keyDefault('source_id','勺子课堂')
                    ->keyText('url','资源链接')
                    ->keyClassRadio('price','课程费用(选择付费时,金额填写到下栏文本框)','',[0=>'免费',1=>'付费'])->keyDefault('price',0)
                    ->keySingleImage("cover_id","课程封面","图片格式为jpg、png格式；图片大小在1MB以内，分辨率640*640")

                    ->group(L('_BASIS_'),'id,uid,title,category,source_id,url,price,cover_id')

                    ->buttonSubmit()->buttonBack()
                    ->GXPTdisplay();
            }

        }
    }

    //*************************************************课程开始**************************************************
    public function selectCategoryLevelOne(){

        if (IS_AJAX){
            $pid = I('pid');  //默认的省份id

            if( !empty($pid) ){
                //$map['id'] = $pid;
            }
            $map["pid"] = 0;
            $map["status"] = 1;
            $list = $this->classCategoryModel->where($map)->select();

            $data = "<option value =''>-全部-</option>";
            foreach ($list as $k => $vo) {
                $data .= "<option ";
                if( $pid == $vo['id'] ){
                    $data .= " selected ";
                }
                $data .= " value ='" . $vo['id'] . "'>" . $vo['name'] . "</option>";
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

                $list = $this->classCategoryModel->where($map)->select();
            }

            $data = "<option value =''>-全部-</option>";
            foreach ($list as $k => $vo) {
                $data .= "<option ";
                if( $cid == $vo['id'] ){
                    $data .= " selected ";
                }
                $data .= " value ='" . $vo['id'] . "'>" . $vo['name'] . "</option>";
            }
            $this->ajaxReturn($data);
        }
    }

    //*************************************************课程结束**************************************************

    //*************************************************课程来源开始**************************************************

    //来源列表start
    public function sourceList($page=1,$r=20)
    {
        $map = [];
        $map['status']=['neq',-1];

        list($list,$totalCount)=(new ClassSourceModel())->getListByPage($map,$page,'update_time desc','*',$r);

        foreach($list as &$val){
            $val['statusText'] = $val['status'] == 1 ? '启用' : ($val['status'] == 0 ? '禁用' : '');
        }

        $builder=new AdminListBuilder();
        $builder->title('来源列表')
            ->data($list)
            ->buttonNew(U('Classroom/editSource'))
            ->button(L('_DELETE_'), ['class' => 'btn ajax-post tox-confirm', 'data-confirm' => '您确定要删除数据吗?', 'url' => U('Classroom/delSource?id=###'), 'target-form' => 'ids'])
            ->keyId()
            ->keyText('name','名称')
            ->keyText('statusText','状态')
            ->keyCreateTime()
            ->keyUpdateTime()
            ->keyDoActionEdit('Classroom/editSource?id=###')
            ->keyDoActionEdit('Classroom/disableSource?id=###','启用/禁用');

        $builder->pagination($totalCount,$r)
            ->display();
    }

    /**来源添加
     * @param int $id
     */
    public function editSource($id = 0)
    {
        $title=$id?L('_EDIT_'):L('_ADD_');
        if (IS_POST) {

            //参数验证
            $error = "";
            if(empty($_POST["name"])){

                $error .= "名称必填！";
            }

            if(!empty($error)){

                $this->error($error);
            }

            if ($cate = (new ClassSourceModel())->editData(I('post.'))) {
                S('SHOW_EDIT_BUTTON',null);
                $this->success($title.L('_SUCCESS_'), U('Classroom/sourceList'));
            } else {
                $this->error($title.L('_FAIL_').(new ClassSourceModel())->getError());
            }
        } else {
            $builder = new AdminConfigBuilder();

            $data = [];
            if ($id != 0) {
                $data = (new ClassSourceModel())->find($id);
            }

            $builder->title($title.L('来源'))
                ->data($data)
                ->keyId()->keyText('name', L('_TITLE_'))
                ->keySelect('status','状态',null,[1 => '启用', 0 => '禁用'])->keyDefault('status',1)
                ->buttonSubmit(U('Classroom/editSource'))->buttonBack()
                ->display();
        }
    }

    //删除来源数据
    public function delSource(){

        $Ids = I('post.ids');
        if($Ids){
            (new ClassSourceModel())->where(['id'=>['in',$Ids]])->save(['status' => '-1']);
        }

        $this->success('删除成功');
    }

    //启用禁用来源数据
    public function disableSource(){

        $Id = I('get.id');

        if($Id){
            $jobData = (new ClassSourceModel())->where(['id'=>$Id])->find();

            $status = '';
            $jobData['status'] == '1' ? $status = '0' : ($jobData['status'] == '0' ? $status = '1':'');

            (new ClassSourceModel())->where(['id'=>$Id])->save(['status'=>$status,'update_time'=>time()]);
        }

        $this->redirect('Classroom/sourceList');
    }

}