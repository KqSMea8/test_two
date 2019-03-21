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
use Common\Model\ContentHandlerModel;
use Goods\Model\MaintenanceModel;
use Goods\Model\UsedMarketGoodsModel;
use Goods\Model\UsedMarketOrderModel;
use Pay\Model\PayServerModel;
use Pay\Service\PayForService;
use Pay\Conf\Conf;
use Goods\Model\UsedMarketBusinessModel;

class GoodsController extends AdminController{

    protected $goodsModel;
    protected $goodsCategoryModel;
    protected $newsModel;
    protected $newsCategoryModel;
    protected $somethingPicModel;
    protected $payServerModel;
    protected $districtModel;

    function _initialize()
    {
        parent::_initialize();
        $this->goodsModel = D('Goods/UsedMarketGoods');
        $this->goodsCategoryModel = D('Goods/UsedMarketCategory');
        $this->somethingPicModel = D('Goods/SomethingPic');
        $this->payServerModel = D('Pay/PayServer');
        $this->districtModel = D('Home/District');

        $this->newsModel = D('News/News');
        $this->newsCategoryModel = D('News/NewsCategory');
    }

    //分类列表
    public function categorylist()
    {
        //显示页面
        $builder = new AdminTreeListBuilder();

        $tree = $this->goodsCategoryModel->getTree(0, 'category_id,title,sort,pid,status');

        foreach($tree as $key => $val){
            $tree[$key]['id'] = $val['category_id'];
        }

        $builder->title(L('_GOODSCATEGORY_TITLE_'))
            ->buttonNew(U('Goods/add'))
            ->setLevel(1)
            ->data($tree)
            ->goodsTreeDisplay();
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
     * @author HQ
     */
    public function add($id = 0, $pid = 0)
    {
        $title=$id?L('_EDIT_'):L('_ADD_');
        if (IS_POST) {

            $submitData = I('post.');
            $submitData['pid'] = '0';
            if($submitData['id']){
                $submitData['category_id'] = $submitData['id'];
            }
            unset($submitData['id']);

            $cate = $this->goodsCategoryModel->editData($submitData);
            if ($cate) {

                //添加成功，同步增加菜单
//                $this->addMenu($_POST,$cate);

                S('SHOW_EDIT_BUTTON',null);
                $this->success($title.L('_SUCCESS_'), U('Goods/categorylist'));
            } else {
                $this->error($title.L('_FAIL_').$this->goodsCategoryModel->getError());
            }
        } else {
            $builder = new AdminConfigBuilder();

            if ($id != 0) {
                $data = $this->goodsCategoryModel->find($id);
            } else {
                $father_category_pid=$this->goodsCategoryModel->where(['category_id'=>$pid])->getField('pid');

                if($father_category_pid!=0){
                    $this->error(L('_ERROR_CATEGORY_HIERARCHY_'));
                }
            }
            if($pid!=0){
                $categorys = $this->goodsCategoryModel->where(array('pid'=>0,'status'=>array('egt',0)))->select();
            }
            $opt = array();
            foreach ($categorys as $category) {
                $opt[$category['category_id']] = $category['title'];
            }

            $builder->title($title.L('_CATEGORY_'))
                ->data($data)
                ->keyId('category_id')->keyText('title', L('_TITLE_'))
//                ->keySelect('pid',L('_FATHER_CLASS_'), L('_FATHER_CLASS_SELECT_'), array('0' =>L('_TOP_CLASS_')) + $opt)->keyDefault('pid',$pid)
                ->keyInteger('sort',L('_SORT_'))->keyDefault('sort',0)
                ->keyCategoryStatus()->keyDefault('status',1)
                ->buttonSubmit(U('Goods/add'))->buttonBack()
                ->display();
        }

    }

    /**
     * 设置商品分类状态：删除=-1，禁用=0，启用=1
     * 1--可用，2--删除 , 3--禁用
     * @param $ids
     * @param $status
     * @author HQ
     */
    public function setStatus($ids, $status)
    {

        !is_array($ids)&&$ids=explode(',',$ids);

        $newStatus = $status=='0' ? '3' : ($status=='-1' ? '2' : ($status=='2' ? '1' :''));
        $rs = $this->goodsCategoryModel->where(['category_id' => array('in', $ids)])->save(['status' => $newStatus]);
        if ($rs === false) {
            $this->error(L('_ERROR_SETTING_') . L('_PERIOD_'));
        }

        $this->success(L('_SUCCESS_SETTING_'), U('Goods/categorylist'));
    }


//分类管理end

    //商品列表start
    public function index($page=1,$r=20)
    {
        //拼接搜索商品名称
        $goodsName = I("goodsName");
        if($goodsName){
            $map['title'] = ['like','%'.I("goodsName").'%'];
        }

        //拼接搜索商品状态
        $goodsStatus = I("goodsStatus");
        if($goodsStatus != 0){
            $map['status'] = $goodsStatus;
        }

        //拼接搜索商品分类
        $aCate=I('cate',0,'intval');
        if($aCate){
            $cates=$this->goodsCategoryModel->getCategoryList(array('pid'=>$aCate));
            if(count($cates)){
                $cates=array_column($cates,'goods_id');
                $cates=array_merge(array($aCate),$cates);
                $map['category_id']=array('in',$cates);
            }else{
                $map['category_id']=$aCate;
            }
        }

        //拼接搜索商家
        $busNameSearch = I("busNameSearch");
        if($busNameSearch != 0){
            $map['business_id'] = $busNameSearch;
        }

        $map['check_status']=2;

        list($list,$totalCount)=$this->goodsModel->getListByPage($map,$page,'update_time desc','*',$r);

        //商品分类数据
        $category=$this->goodsCategoryModel->getCategoryList(['status'=>1]);
        $category=array_combine(array_column($category,'category_id'),$category);

        //商家数据
        $businessData = (new UsedMarketBusinessModel())->select();
        $businessData=array_combine(array_column($businessData,'id'),$businessData);

        foreach($list as &$val){

            //拼接分类名称
            $val['category']=$category[$val['category_id']]['title'];
            //拼接商家名称
            $val['businessName']=$businessData[$val['business_id']]['name'];
        }
        unset($val);

        //拼接搜索商品分类
        $optCategory=$category;
        foreach($optCategory as &$val){
            $val['value']=$val['title'];
            $val['id']=$val['category_id'];
        }

        //拼接搜索商家
        $busDataSearch=[];
        foreach($businessData as $val){
            if($val['status'] == 1){
                $busDataSearch[] = [
                    'id' => $val['id'],
                    'value' => $val['name'],
                ];
            }
        }

        //查询是否购买了服务
        $goodsIdArr = array_column($list,"goods_id");
        $payServerModel = new PayServerModel();
        $servers = $payServerModel->getPayServerListOfRunning("Goods",$goodsIdArr);
        $servers = useFieldAsArrayKey($servers,"row_id");

        $tempList = '';
        foreach($list as $key => &$val){

            if(isset($servers[$val["goods_id"]])){

                $val["serverTime"] = date("Y.m.d",$servers[$val['goods_id']]['start_time'])."--".date("Y.m.d",$servers[$val['goods_id']]['end_time']);
            }else{

                $buyServerUrl = U("buyServer",["id" => $val["goods_id"]]);
                $val["serverTime"] = "<a href='{$buyServerUrl}'>购买服务</a>";
            }

            //如果该条商品没有禁用或删除的话,就查看是否已过期
            if($val['status'] == '1'){
                //判断商品是否过期了
                if(time()>$val['end_time']){
                    $val['status'] = '4';
                }
            }

            //修改更新时间的时间显示格式
            if($val['update_time']){
                $val['update_time'] = strtotime($val['update_time']);
            }

            $val['id'] = $val['goods_id'];
        }

        //导出excel数据列表
        $attr['href'] = U('exportGoodsExcel');
        $attr['class']='btn btn-ajax';

        unset($val);

        $builder=new AdminListBuilder();
        $builder->title(L('商品列表'))
            ->data($list)
            ->setSelectPostUrl(U('Admin/Goods/index'))
            ->select('数据状态：','goodsStatus','select','','','',array_merge(array(array('id'=>0,'value'=>'全部')),[["id" =>1,"value" => "启用"],["id" =>2,"value" => "下架"]]))
            ->select('所属分类：','cate','select','','','',array_merge(array(array('id'=>0,'value'=>L('_EVERYTHING_'))),$optCategory))
            ->select('所属商家：','busNameSearch','select','','','',array_merge([['id'=>0,'value'=>L('_EVERYTHING_')]],$busDataSearch))
            ->buttonNew(U('Goods/editGoods'))
            ->keyId('goods_id')
            ->keyUid()
            ->keyText('title','商品名称')
            ->keyText('category',L('_CATEGORY_'))
            ->keyText('businessName','所属商家')
            ->keyText("stock","库存")
            ->keyText("serverTime","服务有效期")
            ->keyGoodsStatus('status')
            ->keyCreateTime()
            ->keyUpdateTime()
            ->ajaxButton(U('Goods/updateGoodsStatus?status=1&id=###'),"","上架")
            ->ajaxButton(U('Goods/updateGoodsStatus?status=2&id=###'),"","下架")
            ->ajaxButton(U('Goods/updateGoodsStatus?status=3&id=###'),"","删除")
            ->button('导出全部excel列表',$attr)
            ->keyDoActionEdit('Goods/editGoods?id=@@@')
            ->keyDoActionEdit('Goods/disableGoods?id=@@@','上架/下架')
            ->keyDoActionEdit('Goods/delGoods?id=@@@','删除')
            ->search("商品名称",'goodsName','text','');

        $builder->pagination($totalCount,$r)
            ->display();
    }

    /**
     * 导出商品excel数据
     */
    public function exportGoodsExcel(){

//        $id = I('get.goodsId');

        $data = $this->goodsModel->where(['status'=>1])->select();
        $categoryData = $this->goodsCategoryModel->field('category_id,title')->select();

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
        foreach($categoryData as $key => $val){
            foreach ( $data as $k => $v ) {
                $excelData [$k] ['title'] = $v ['title'];
                if($val['category_id'] == $v['category_id']){
                    $excelData [$k] ['cate_name'] = $val ['title'];
                }else{
                    $excelData [$k] ['cate_name'] = '暂无分类';
                }

                $excelData [$k] ['sale_price'] = $v ['sale_price'];
                $excelData [$k] ['link_phone'] = $v ['link_phone'];
                $excelData [$k] ['link_name'] = $v ['link_name'];
                $excelData [$k] ['create_time'] = Date('Y-m-d H:i:s',$v ['create_time']);
                $excelData [$k] ['statusText'] = $v ['status'] == 1 ? '上架': ($v ['status'] == 2 ? '下架': '删除');
                $num ++;
            }
        }
        $len = count ( $excelData );
        $info = array (
            "table_name_one" =>  "商品信息数据表",
            "table_name_two" => "商品信息报表",
            "lister" => "",
            "tabletime" => date ( "Y-m-d", time () )
        );

        $optional = array (
            'colwidth' => array (
                'A' => '30',
                'B' => '30',
                'C' => '20',
                'D' => '20',
                'E' => '20',
                'F' => '20',
                'G' => '15',
//                'I' => '25',
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
            '标题',
            '分类',
            '价格',
            '联系电话',
            '联系人',
            '创建时间',
            '商品状态',
        ];

        return $return;
    }


    //ajax状态上下架设置
    public function updateGoodsStatus($status){

        $ids = $_POST["ids"];

        //上架数据时，判断该数据商家是否为可用的商家
        if($status == 1){
            $busGoodsData = (new UsedMarketGoodsModel())
                ->alias("UMGoods")
                ->field('UMBus.status')
                ->join('jpgk_used_market_business as UMBus on UMBus.id = UMGoods.business_id')
                ->where(['UMGoods.goods_id'=>['IN',$ids]])
                ->select();
            foreach($busGoodsData as $val){
                if($val['status'] != '1'){
                    $this->error("状态设置".L('_FAIL_')."包含商家信息无效的数据！");
                }
            }
        }

        if(!empty($ids)){

            $this->goodsModel->where(["goods_id" => ['in',$ids]])->save(["status" => $status]);
            $this->success("状态设置".L('_SUCCESS_'),U('Goods/index'));
        }else{

            $this->error("状态设置".L('_FAIL_')."请选择操作的对象");
        }
    }

    //上架/下架商品
    public function disableGoods(){
        $goodsId = I('get.id');

        if($goodsId){
            $goodsData = $this->goodsModel->where(['goods_id'=>$goodsId])->find();

            $status = '';
            if($goodsData['status'] == '3' || $goodsData['status'] == '2'){

                //上架数据时，判断该数据商家是否为可用的商家
                $busGoodsData = (new UsedMarketGoodsModel())
                    ->alias("UMGoods")
                    ->field('UMBus.status')
                    ->join('jpgk_used_market_business as UMBus on UMBus.id = UMGoods.business_id')
                    ->where(['UMGoods.goods_id'=>$goodsId])
                    ->select();
                foreach($busGoodsData as $val){
                    if($val['status'] != '1'){
                        $this->error("状态设置".L('_FAIL_')."商家信息无效！");
                        return;
                    }
                }

                $data['status'] = '1';
                $data['start_time']=strtotime('now');
                $data['end_time']=strtotime('+1 month');
                $data['update_time']= Date('Y-m-d H:i:s');
                $this->goodsModel->where(['goods_id'=>$goodsId])->save($data);

            }elseif($goodsData['status'] == '1'){
                $status = '2';
                $this->goodsModel->where(['goods_id'=>$goodsId])->save(['status'=>$status,'update_time'=>Date('Y-m-d H:i:s')]);
            }
        }

        $this->redirect('Goods/index');
    }

    //删除商品
    public function delGoods(){
        $goodsId = I('get.id');

        if($goodsId){
            $this->goodsModel->where(['goods_id'=>$goodsId])->save(['status'=>'3']);
        }

        $this->redirect('Goods/index');
    }

    //待审核列表
    public function audit($page=1,$r=20)
    {
        $aCate=I('cate',0,'intval');
        if($aCate){
            $cates=$this->goodsCategoryModel->getCategoryList(array('pid'=>$aCate));
            if(count($cates)){
                $cates=array_column($cates,'goods_id');
                $cates=array_merge(array($aCate),$cates);
                $map['category_id']=array('in',$cates);
            }else{
                $map['category_id']=$aCate;
            }
        }

        $map['check_status']=1;

        //HQ修改 过滤咨询显示列表数据
//        if(session('user_auth')['uid']){
//            $map['uid'] = session('user_auth')['uid'];
//        }

        list($list,$totalCount)=$this->goodsModel->getListByPage($map,$page,'update_time desc','*',$r);
        $category=$this->goodsCategoryModel->getCategoryList(array('status'=>array('egt',0)),1);
        $category=array_combine(array_column($category,'category_id'),$category);
        foreach($list as &$val){
            $val['id'] = $val['goods_id'];
//            $val['category']='['.$val['category_id'].'] '.$category[$val['category_id']]['title'];
            $val['category']=$category[$val['category_id']]['title'];
            $val['update_time'] = strtotime($val['update_time']);
            //如果该条商品没有禁用或删除的话,就查看是否已过期
            if($val['status'] == '3'){
                //判断商品是否过期了
                if(time()>$val['end_time']){
                    $val['status'] = '4';
                }
            }

        }
        unset($val);

        $optCategory=$category;
        foreach($optCategory as &$val){
            $val['value']=$val['title'];
            $val['id']=$val['category_id'];
        }
        unset($val);

        $builder=new AdminListBuilder();
        $builder->title(L('待审核商品列表'))
            ->data($list)
            ->setSelectPostUrl(U('Admin/Goods/audit'))

            ->setStatusUrl(U('Goods/setGoodsStatus'))
            ->buttonEnable(U('Goods/reviewedGoods'),L('_AUDIT_SUCCESS_'))
//            ->buttonModalPopup(U('Goods/doAudit'),null,L('_AUDIT_UNSUCCESS_'),array('data-title'=>L('_AUDIT_FAIL_REASON_'),'target-form'=>'ids'))

            ->select('','cate','select','','','',array_merge(array(array('id'=>0,'value'=>L('_EVERYTHING_'))),$optCategory))

            ->keyId('goods_id')->keyUid()->keyText('title',L('_TITLE_'))->keyText('category',L('_CATEGORY_'))

            ->keyGoodsStatus()->keyCreateTime()->keyUpdateTime()
            ->keyDoActionEdit('Goods/editGoods?id=@@@')
            ->keyDoActionEdit('Goods/delGoods?id=@@@','删除');

        $builder->pagination($totalCount,$r)
            ->display();
    }

    /**
     * 商品购买置顶服务页面
     * @param int $id
     * @param int $pid
     */
    public function buyServer($id = 0)
    {

        $title="购买商品置顶服务";
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
            switch($payType){
                case 'alipay':
                    $returnUrl = "http://".$_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"];
                    writeFileLog("opensns_returnUrl",["returnUrl" => $returnUrl]);
                    $payUrl = $payService->buyService(get_uid(),'SHM',$id,$startTime,$endTime,Conf::PAY_TYPE_ALIPAY,$money,"购买商品置顶服务",$returnUrl);
                    break;
                case 'wxpay':
                    $payUrl = $payService->buyService(get_uid(),"SHM",$id,$startTime,$endTime,Conf::PAY_TYPE_WEIXIN,$money,"购买商品置顶服务");
                    $payUrl = "http://".$_SERVER["HTTP_HOST"].U("Pay/WxPay/payHtml")."&payUrl=".urlencode($payUrl);
                    break;
            }

            $this->success("请前往支付界面！",$payUrl,false,$payUrl);
        } else {

            //查询特定的资讯是否有置顶的服务
            $payServerModel = new PayServerModel();
            $newServer = $payServerModel->getDataPayServerOfRunning("SHM",$id);

            if(empty($newServer)){
                $builder=new AdminConfigBuilder();
                $builder->title($title.L('商品'))
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
                $builder->title($title.L('商品'))
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
     * 审核通过数据信息
     */
    public function reviewedGoods(){

        $ids = I('post.ids');
        !is_array($ids)&&$ids=explode(',',$ids);

        $rs = $this->goodsModel->where(['goods_id' => array('in', $ids)])->save(['check_status' => 2,'start_time'=>strtotime('now'),'end_time'=>strtotime('+30 day')]);
        if ($rs === false) {
            $this->error(L('_ERROR_SETTING_') . L('_PERIOD_'));
        }

        $this->success(L('_SUCCESS_SETTING_'), U('Goods/index'));
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

    public function setGoodsStatus($ids,$status=1)
    {
        !is_array($ids)&&$ids=explode(',',$ids);
        $builder = new AdminListBuilder();
//        S('news_home_data',null);
        //发送消息
        $messageModel=D('Common/Message');
        foreach($ids as $val){
            $goods=$this->goodsModel->getData($val);
            $tip = L('_YOUR_NEWS_').'【'.$goods['title'].'】'.L('_AUDIT_SUCCESS_').'。';
            $messageModel->sendMessage($goods['uid'],L('_NEWS_AUDIT_SUCCESS_'), $tip,  'News/Index/detail',array('id'=>$val), is_login(), 2);
        }
        //发送消息 end
        $builder->doSetStatus('News', $ids, $status);
    }

    public function editGoods()
    {
        $aId=I('get.id');
        $title=$aId?L('_EDIT_'):L('_ADD_');
        $picData = '';
        if(IS_POST){

            //参数验证
            $error = "";
            if(empty($_POST["title"])){

                $error .= "商品名称必填！";
            }

            if($_POST['sale_price'] < 0){

                $error .= "价格不可为负数！";
            }

            if (!preg_match('/^[0-9]+(.[0-9]{1})?$/', $_POST['sale_price'])) {
                $error .= "价格只可保留小数点后一位！";
            }

            if ($_POST['sale_price'] < 100 && I('post.maintenance_support') == 1) {
                $error .= "价格小于100，不可选择维养服务！";
            }

            //拼接邮费字段
            if(I('post.delivery_money') != 0){
                if (!preg_match('/^[0-9]+(.[0-9]{1})?$/', I('post.classPrice'))) {
                    $error .= "邮费价格只可保留小数点后一位！";
                }
            }

//            if(!I('post.business_id')){
//                $error .= "请选择正确的商家！";
//            }else{
//                $busTemp = (new UsedMarketBusinessModel())->where(['id' => I('post.business_id')])->find();
//                if($busTemp['status'] != 1){
//                    $error .= "商家已失效，请选择正确的商家！";
//                }
//            }

            if($_POST['stock'] < 0){

                $error .= "库存不可为负数！";
            }

            if(!empty($error)){

                $this->error($error);
            }

            $aId&&$data['goods_id']=$aId;
            $data['uid']=I('post.uid',get_uid(),'intval');
            $data['title']=I('post.title','','op_t');
            $data['category_id']=I('post.category',0,'intval');
            $data['business_id']=I('post.business_id');
            $data['sale_price']=I('post.sale_price');
            $data['stock']=I('post.stock');
            $data['bought_price']=I('post.bought_price');
            $data['start_time']=strtotime('now');
            $data['end_time']=I('post.end_time');
            $data['link_phone']=I('post.link_phone');
            $data['link_name']=I('post.link_name');
            //拼接邮费字段
            if(I('post.delivery_money') == 0){
                $data['delivery_money'] = 0;
            }else{
                $data['delivery_money'] = I('post.classPrice');
            }
            $data['maintenance_support'] = I('post.maintenance_support');

            $province = I('post.province');
            $data['province']=$province;
            $data['city']=I('post.city');
            $data['district']=I('post.district');

            $provinceData = $this->districtModel->getData($province);
            $cityData = $this->districtModel->getData(I('post.city'));

            if($provinceData || $cityData){
                $data['district_text'] = $provinceData['name'].' '.$cityData['name'];
            }

            $data['description']=I('post.description');
            $data['status']=I('post.status');
            $data['sort']=I('post.sort');

            $data['check_status']=1;
            $data['is_pay']=2;
            $this->_checkOk($data);
            $result=$this->goodsModel->editData($data);

            //添加goods图片
            $picData['pic1']=I('post.pic1');
            $picData['pic2']=I('post.pic2');
            $picData['pic3']=I('post.pic3');
            $picData['pic4']=I('post.pic4');
            $picData['pic5']=I('post.pic5');
            $picData['pic6']=I('post.pic6');
            $picData['pic7']=I('post.pic7');
            $picData['pic8']=I('post.pic8');

            $rowId = $aId ? $aId : $result;
            $app = 'SHM';

            //将关联的图片数据删除
            $this->somethingPicModel->delData(['row_id'=>$rowId,'app'=>$app]);

            //重新添加图片数据
            foreach($picData as $key => $val){
                if($val && $key == 'pic1'){
                    $this->somethingPicModel->editData([
                        'pic_id'=>$val,
                        'app'=>$app,
                        'row_id'=>$rowId,
                        'type'=>1,
                    ]);

                }elseif($val){
                    $this->somethingPicModel->editData([
                        'pic_id'=>$val,
                        'app'=>$app,
                        'row_id'=>$rowId,
                        'type'=>0,
                    ]);
                }
            }

            if($result){
                S('goods_home_data',null);
                $aId=$aId?$aId:$result;
                $this->success($title.L('_SUCCESS_'),U('Goods/index',array('goods_id'=>$aId)));
            }else{
                $this->error($title.L('_SUCCESS_'),$this->goodsModel->getError());
            }
        }else{

            $cate = '';
            $busId = '';
            if($aId){
                $data=$this->goodsModel->getData($aId);
                unset($data['district_id']);
                $data['district_id'][] = $data['province'];
                $data['district_id'][] = $data['city'];
                $data['district_id'][] = $data['district'];
                $cate = $data['category_id'];
                $busId = $data['business_id'];
                //拼接图片数据
                $goodsPic = $this->somethingPicModel->getList(['app'=>'SHM','row_id'=>$data['goods_id']]);
                if($goodsPic){
                    $goodsPicCount = count($goodsPic);

                    $count = 1;
                    for($i=0; $i<$goodsPicCount; $i++){
                        $data['pic'.$count] = $goodsPic[$i]['pic_id'];
                        $count ++;
                    }
                }
            }

            //查找下拉框 分类数据
            $category=$this->goodsCategoryModel->getCategoryList(array('status'=>1));
            $options=array();
            foreach($category as $val){
                $options[$val['category_id']]=$val['title'];
            }

            //拼接商家数据
            $busOptions=array();
            if($busId){
                $busOneData = (new UsedMarketBusinessModel())->where(['id'=>$busId])->find();
                $busOptions[$busOneData['id']] = $busOneData['name'];
            }else{
                $busOptions[0] = '请选择';
            }

            //查找商家数据 增加下拉框
            $businessData=(new UsedMarketBusinessModel())->where(['status'=>1])->select();
            foreach($businessData as $val){
                $busOptions[$val['id']]=$val['name'];
            }

            $builder=new AdminConfigBuilder();
            $builder->title($title.L('商品'))
                ->data($data)
                ->keyId('goods_id')
                ->keyReadOnly('uid',L('_PUBLISHER_'))->keyDefault('uid',get_uid())
                ->keyText('title','商品名称')
                ->keySelect('category',L('_CATEGORY_'),'',$options)->keyDefault('category',$cate)
                ->keySelect('business_id',L('所属商家'),'',$busOptions)->keyDefault('business_id',$busId)
                ->keyText('sale_price',L('想卖多少'))
                ->keyText('bought_price',L('购入价格'))
                ->keyText('stock',L('库存'))
                ->keyClassRadio('delivery_money','单件商品运费','',[0=>'免费',1=>'付费'])->keyDefault('delivery_money',0)
//                ->keyTime('start_time',L('开始时间'))->keyDefault('start_time',time())
                ->keyTime('end_time',L('结束时间'))->keyDefault('end_time',strtotime("+3 months"))
                ->keyText('link_phone',L('联系电话'))
                ->keyText('link_name',L('联系人'))
                ->keySort('sort',L('_SORT_'),'')->keyDefault('sort',999)
                ->keyCity('district_id',L('所在位置'))
//                ->keyText('district_text',L('所在位置详细描述'))
                ->keyRadio('maintenance_support','维养服务','',['1' => '支持','2' => '不支持'])->keyDefault('maintenance_support','1')
                ->keyRadio('status','状态','',['1' => '可用','2' => '下架'])->keyDefault('status','1')

                ->keyTextArea('description',L('商品描述'))

                ->keySingleImage('pic1',L('主图'))
                ->keySingleImage('pic2',L('图片2'))
                ->keySingleImage('pic3',L('图片3'))
                ->keySingleImage('pic4',L('图片4'))
                ->keySingleImage('pic5',L('图片5'))
                ->keySingleImage('pic6',L('图片6'))
                ->keySingleImage('pic7',L('图片7'))
                ->keySingleImage('pic8',L('图片8'))
//                ->keyTime('newsStartTime','置顶开始时间','','date')->keyDefault('newsStartTime','')
//                ->keyTime('newsEndTime','置顶结束时间','','date')->keyDefault('newsEndTime','')
                ->group(L('_BASIS_'),'goods_id,uid,end_time,title,delivery_money,category,maintenance_support,business_id,sale_price,stock,bought_price,link_phone,link_name,sort,district_id,status,pic1,pic2,pic3,pic4,pic5,pic6,pic7,pic8,description')
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


    private function _checkOk($data=array()){
        if(!mb_strlen($data['title'],'utf-8')){
            $this->error(L('_TIP_TITLE_EMPTY_'));
        }
//        if(mb_strlen($data['content'],'utf-8')<20){
//            $this->error(L('_TIP_CONTENT_LENGTH_'));
//        }
        return true;
    }

    //*************************************************商家管理开始**************************************************

    //商家列表start
    public function businessList($page=1,$r=20)
    {
        $busName = I("busName");
        $busType = I("busType");
        $busStatus = I("busStatus");
        $map = [];

        //拼接商家名称
        if($busName){
            $map['name'] = ['like','%'.$busName.'%'];
        }

        //拼接商家类别
        if($busType != 0){
            $map['type'] = $busType;
        }

        //拼接商家状态
        if($busStatus != 0){
            if($busStatus == 2){
                $map['status'] = 0;
            }else{
                $map['status'] = $busStatus;
            }
        }else{
            $map['status']=['neq',-1];
        }

        list($list,$totalCount)=(new UsedMarketBusinessModel())->getListByPage($map,$page,'update_time desc','*',$r);
        foreach($list as &$val){
            $val['statusText'] = $val['status'] == 1 ? '可用' : ($val['status'] == 0 ? '禁用' : '');
            $val['typeText'] = $val['type'] == 1 ? '企业' : ($val['type'] == 2 ? '个人' : '');
        }

        $builder=new AdminListBuilder();
        $builder->title('商家列表')
            ->select('商家分类：','busType','select','','','',array_merge([['id'=>0,'value'=>'全部']],[["id" =>1,"value" => "企业"],["id" =>2,"value" => "个人"]]))
            ->select('数据状态：','busStatus','select','','','',array_merge([['id'=>0,'value'=>'全部']],[["id" =>1,"value" => "可用"],["id" =>2,"value" => "禁用"]]))
            ->data($list)
            ->buttonNew(U('Goods/editBusiness'))
            ->button('删除', ['class' => 'btn ajax-post tox-confirm', 'data-confirm' => '您确定要删除数据吗?', 'url' => U('Goods/delBusiness'), 'target-form' => 'ids'])
            ->button('启用', ['class' => 'btn ajax-post tox-confirm', 'data-confirm' => '您确定要启用吗?', 'url' => U('Goods/startBusiness'), 'target-form' => 'ids'])
            ->button('禁用', ['class' => 'btn ajax-post tox-confirm', 'data-confirm' => '您确定要禁用吗?', 'url' => U('Goods/endBusiness'), 'target-form' => 'ids'])
            ->keyId()
            ->keyText('name','商家名称')
            ->keyText('phone','联系电话')
            ->keyText('statusText','状态')
            ->keyText('typeText','商家类别')
            ->keyText('create_time','创建时间')
            ->keyText('update_time','更新时间')
            ->keyDoActionEdit('Goods/editBusiness?id=###')
            ->search("商家名称",'busName','text','');

        $builder->pagination($totalCount,$r)
            ->display();
    }

    /**商家添加
     * @param int $id
     */
    public function editBusiness($id = 0)
    {
        $title=$id?L('_EDIT_'):L('_ADD_');
        if (IS_POST) {

            //参数验证
            $error = "";
            if(empty($_POST["name"])){

                $error .= "商家名称不可为空！";
            }elseif(mb_strlen($_POST["name"],'UTF8')>30){
                $error .= "商家名称长度不可超过30字！";
            }

            if(empty($_POST["busType"])){

                $error .= "商家类别不可为空！";
            }

            if(empty($_POST["address"])){

                $error .= "商家地址不可为空！";
            }

            if(empty($_POST["phone"])){

                $error .= "联系电话不可为空！";
            }

            if(!empty($error)){

                $this->error($error);
            }
            $data = I('post.');
            $data['type'] = $data['busType'];

            if ($cate = (new UsedMarketBusinessModel())->editData($data)) {

                $this->success($title.L('_SUCCESS_'), U('Goods/businessList'));
            } else {
                $this->error($title.L('_FAIL_').(new UsedMarketBusinessModel())->getError());
            }
        } else {
            $builder = new AdminConfigBuilder();

            $data = [];
            if ($id != 0) {
                $data = (new UsedMarketBusinessModel())->find($id);
            }

            $builder->title($title.L('商家'))
                ->data($data)
                ->keyId()
                ->keyText('name','商家名称')
                ->keyText('address','商家地址')
                ->keyText('phone','联系电话')
                ->keySelect('busType','商家类别','',[1 => '企业',2 => '个人'])->keyDefault('busType',1)
                ->keySelect('status','状态',null,[1 => '启用', 0 => '禁用'])->keyDefault('status',1)
                ->buttonSubmit(U('Goods/editBusiness'))->buttonBack()
                ->display();
        }
    }

    //启用商家数据
    public function startBusiness(){
        $Ids = I('post.ids');
        if($Ids){

            (new UsedMarketBusinessModel())->where(['id'=>['IN',$Ids]])->save(['status'=>1,'update_time'=>date('Y-m-d H:i:s')]);
        }

        $this->success('启用成功');
    }

    //禁用商家数据
    public function endBusiness(){
        $Ids = I('post.ids');
        if($Ids){

            //禁用商家数据
            (new UsedMarketBusinessModel())->where(['id'=>['IN',$Ids]])->save(['status'=>0,'update_time'=>date('Y-m-d H:i:s')]);

            //禁用商家下的商品数据
            (new UsedMarketGoodsModel())->where(['business_id'=>['IN',$Ids]])->save(['status'=>2]);
        }

        $this->success('禁用成功');
    }

    //删除商家数据
    public function delBusiness(){

        $Ids = I('post.ids');
        if($Ids){
            $UsedMarketBusinessModel = (new UsedMarketBusinessModel());
            $UsedMarketBusinessModel->where(['id'=>['IN',$Ids]])->save(['status'=>-1]);

            //禁用商家下的商品数据
            (new UsedMarketGoodsModel())->where(['business_id'=>['IN',$Ids]])->save(['status'=>2]);
        }

        $this->success('删除成功');
    }

    //*************************************************商家管理失败**************************************************

    //*************************************************订单管理开始**************************************************

    //订单列表start
    public function orderList($page=1,$r=20)
    {
        $orderCode = I("orderCode");
        $newsDate = I("newsDate");
        $payType = I("payType");
        $orderStateVal = I("orderStateVal");
        $map = [];

        //拼接搜索订单号
        if($orderCode){
            $map['usedMarketOrder.order_code'] = ['like','%'.$orderCode.'%'];
        }

        //拼接搜索下单时间
        if($newsDate != 0){
            $map['newsDate'] = $newsDate;
        }

        //拼接搜索下单时间
        if($payType != 0){
            $map['pay.type'] = $payType;
        }

        //拼接搜索订单状态值
        if($orderStateVal != 0){
            $map['usedMarketOrder.order_state'] = $orderStateVal-1;
        }

        list($list,$totalCount)=(new UsedMarketOrderModel())->getOrderListByPage($map,$page,'usedMarketOrder.update_time desc','usedMarketOrder.id,usedMarketOrder.order_code,usedMarketOrder.create_time,usedMarketOrder.money_total,usedMarketOrder.order_state,pay.type,pay.third_trade_no,sum(CASE WHEN goods.order_id = usedMarketOrder.id THEN 1 ELSE 0 END ) as goodsNum',$r);

        foreach($list as &$val){

            //订单状态拼接
            if($val['order_state'] == 0){
                $val['orderStateText'] = '新订单,待支付';
            }elseif($val['order_state'] == 1){
                $val['orderStateText'] = '支付成功,待收货';
            }elseif($val['order_state'] == 2){
                $val['orderStateText'] = '用户未付款取消';
            }elseif($val['order_state'] == 3){
                $val['orderStateText'] = '用户超时未支付取消';
            }elseif($val['order_state'] == 4){
                $val['orderStateText'] = '用户取消,已退款';
            }elseif($val['order_state'] == 5){
                $val['orderStateText'] = '已收货,订单完成';
            }

            //支付流水号拼接
            if(empty($val['third_trade_no'])){
                $val['thirdTradeNoText'] = '空';
            }else{
                $val['thirdTradeNoText'] = $val['third_trade_no'];
            }

            //支付方式拼接
            if($val['type'] == 1){
                $val['typeText'] = '微信';
            }elseif($val['type'] == 2){
                $val['typeText'] = '支付宝';
            }else{
                $val['typeText'] = '空';
            }
        }

        $builder=new AdminListBuilder();
        $builder->title('订单列表')
            ->select('下单时间：','newsDate','select','','','',array_merge([['id'=>0,'value'=>"全部"]],[["id" =>1,"value" => "今天"],["id" => 2,"value" => "昨天"],["id" => 3,"value" => "一周内"],["id" => 4,"value" => "一个月内"]]))
            ->select('支付方式：','payType','select','','','',array_merge([['id'=>0,'value'=>"全部"]],[["id" =>1,"value" => "微信"],["id" =>2,"value" => "支付宝"]]))
            ->select('订单状态：','orderStateVal','select','','','',array_merge([['id'=>0,'value'=>'全部']],[["id" =>1,"value" => "新订单,待支付"],["id" =>2,"value" => "支付成功,待收货"],["id" =>3,"value" => "用户未付款取消"],["id" =>4,"value" => "用户超时未支付取消"],["id" =>5,"value" => "用户取消,已退款"],["id" =>6,"value" => "已收货,订单完成"]]))
            ->data($list)
            ->keyId()
            ->keyText('order_code','订单号')
            ->keyText('goodsNum','商品数量')
            ->keyText('create_time','下单时间')
            ->keyText('money_total','订单金额')
            ->keyText('typeText','支付方式')
            ->keyText('thirdTradeNoText','支付流水号')
            ->keyText('orderStateText','订单状态')
            ->keyLink('操作', '订单明细', 'Admin/Goods/showOrderDetail?id=###')
            ->search("订单号",'orderCode','text','');
        $builder->pagination($totalCount,$r)
            ->display();
    }

    //显示订单明细
    public function showOrderDetail($page=1,$r=20){

        $orderId = I('id');
        if(empty($orderId)){
            $this->error('参数不完整！');
        }

        //查询订单商品数据
        $UsedMarketOrderGoodsModel = M('UsedMarketOrderGoods');
        $totalCount = $UsedMarketOrderGoodsModel->alias("orderGoods")
            ->join("jpgk_used_market_goods as goods on goods.goods_id = orderGoods.goods_id")
            ->where(['orderGoods.order_id'=>$orderId])
            ->count();

        $orderGoodsData = $UsedMarketOrderGoodsModel->alias("orderGoods")
            ->join("jpgk_used_market_goods as goods on goods.goods_id = orderGoods.goods_id")
            ->where(['orderGoods.order_id'=>$orderId])
            ->page($page, $r)
            ->select();

        //查询订单明细数据
        $UsedMarketOrderModel = M('UsedMarketOrder');
        $dataTop = $UsedMarketOrderModel->alias("usedMarketOrder")
            ->join("jpgk_pay as pay on pay.id = usedMarketOrder.pay_id")
            ->join("LEFT JOIN jpgk_used_market_order_ext as ext on usedMarketOrder.delivery_id = ext.id")
            ->where(['usedMarketOrder.id'=>$orderId])
            ->field('ext.detail_address,ext.user_name,ext.phone,usedMarketOrder.order_code,usedMarketOrder.goods_money_total,usedMarketOrder.remark,usedMarketOrder.create_time,usedMarketOrder.money_total,pay.type')
            ->find();

        //拼接商品数量
        $dataTop['goodsNum'] = $totalCount;

        //支付方式拼接
        if($dataTop['type'] == 1){
            $dataTop['typeText'] = '微信';
        }elseif($dataTop['type'] == 2){
            $dataTop['typeText'] = '支付宝';
        }else{
            $dataTop['typeText'] = '空';
        }
//        print_r($UsedMarketOrderModel->getLastSql());die;

        $builder=new AdminListBuilder();
        $builder->title('订单明细')
            ->data($orderGoodsData)
            ->dataTop($dataTop)
            ->keyId()
            ->keyText('title','商品名称')
            ->keyText('goods_id','商品 ID')
            ->keyText('goods_num','购买数量')
            ->keyText('goods_money','单价')
            ->keyText('delivery_money','运费');
        $builder->pagination($totalCount,$r)
            ->display();
    }

    //*************************************************维养管理开始**************************************************

    //维养列表start
    public function maintainList($page=1,$r=20)
    {
        $map = [];

        list($list,$totalCount)=(new MaintenanceModel())->getListByPage($map,$page,'update_time desc','*',$r);
        foreach($list as &$val){
            $val['timeLimitText'] = $val['time_limit'].'年';
            $val['pricePercentText'] = $val['price_percent'].'元';
        }

        $builder=new AdminListBuilder();
        $builder->title('维养列表')
            ->data($list)
            ->buttonNew(U('Goods/editMaintain'))
            ->button('删除', ['class' => 'btn ajax-post tox-confirm', 'data-confirm' => '您确定要删除数据吗?', 'url' => U('Goods/delMaintain'), 'target-form' => 'ids'])
            ->keyId()
            ->keyText('title','服务名称')
            ->keyText('timeLimitText','服务期限')
            ->keyText('pricePercentText','费用')
            ->keyText('description','服务规则')
            ->keyText('create_time','创建时间')
            ->keyText('update_time','更新时间')
            ->keyDoActionEdit('Goods/editMaintain?id=###');

        $builder->pagination($totalCount,$r)
            ->display();
    }

    /**维养添加
     * @param int $id
     */
    public function editMaintain($id = 0)
    {
        $title=$id?L('_EDIT_'):L('_ADD_');
        if(!$id){
            //验证数据条数是否只有一条
            $MaintenanceData = (new MaintenanceModel())->where(['status'=>'1'])->find();
            if(!empty($MaintenanceData['id'])){
                $this->error("维养数据只能有一条！");
            }
        }
        if (IS_POST) {

            //参数验证
            $error = "";
            if(empty($_POST["title"])){

                $error .= "服务名称不可为空！";
            }

            if(empty($_POST["time_limit"])){

                $error .= "服务期限不可为空！";
            }elseif(ceil($_POST["time_limit"]) != $_POST["time_limit"]){
                $error .= "服务期限不可为小数！";
            }

            if(empty($_POST["price_percent"])){

                $error .= "费用不可为空！";
            }

            if(empty($_POST["description"])){

                $error .= "服务规则不可为空！";
            }

            if(!empty($error)){

                $this->error($error);
            }
            $data = I('post.');
            //todo 目前状态 模块app(4:二手市场;5:供需平台) 写死
            $data['status'] = 1;
            $data['app'] = 4;

            if ($cate = (new MaintenanceModel())->editData($data)) {

                $this->success($title.L('_SUCCESS_'), U('Goods/maintainList'));
            } else {
                $this->error($title.L('_FAIL_').(new UsedMarketBusinessModel())->getError());
            }
        } else {
            $builder = new AdminConfigBuilder();

            $data = [];
            if ($id != 0) {
                $data = (new MaintenanceModel())->find($id);
            }

            $builder->title($title.L('维养服务'))
                ->data($data)
                ->keyId()
                ->keyText('title','服务名称')
                ->keyText('time_limit','服务期限(年)')
                ->keyText('price_percent','费用')
                ->keyTextArea('description','服务规则')
                ->buttonSubmit(U('Goods/editMaintain'))->buttonBack()
                ->display();
        }
    }

    //删除维养数据
    public function delMaintain(){

        $Ids = I('post.ids');
        if($Ids){
            $UsedMarketBusinessModel = (new MaintenanceModel());
            $UsedMarketBusinessModel->where(['id'=>['IN',$Ids]])->save(['status'=>-1]);
        }

        $this->success('删除成功');
    }

    //*************************************************商家管理失败**************************************************

}