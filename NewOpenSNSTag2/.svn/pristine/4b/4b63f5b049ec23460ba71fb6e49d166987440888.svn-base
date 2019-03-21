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
use Common\Lib\Lz517JPush;
use Common\Widget\UploadFileWidget;
use Gxpt\Model\AppointmentModel;
use Gxpt\Model\CategoryModel;
use Gxpt\Model\CommentModel;
use Gxpt\Model\ProductModel;
use Gxpt\Model\supplyMarketingCompanyCaseModel;
use Gxpt\Model\supplyMarketingCompanyModel;
use Gxpt\Model\supplyMarketingCompanyQualificationModel;
use Gxpt\Model\supplyMarketingHotSearchModel;
use Pay\Conf\Conf;
use Pay\Model\PayServerModel;
use Pay\Service\PayForService;
use Admin\Model\PictureModel;
use Home\Model\DistrictModel;
use Think\Upload;

class GxptController extends AdminController{

    protected $categoryModel;
    protected $productModel;
    protected $somethingPicModel;
    protected $pictureModel;
    protected $supplyMarketingCompanyCaseModel;
    protected $supplyMarketingCompanyModel;
    protected $supplyMarketingCompanyQualificationModel;
    protected $supplyMarketingHotSearchModel;
    protected $DistrictModel;

    protected $company_id = 1;

    /*极光推送
    dazhaoyu@jpgk.com.cn
    123456*/

    function _initialize()
    {
        parent::_initialize();
        $this->categoryModel = new CategoryModel();
        $this->productModel = new ProductModel();
        $this->somethingPicModel = D('Goods/SomethingPic');
        $this->pictureModel = new PictureModel();
        $this->supplyMarketingCompanyCaseModel = new supplyMarketingCompanyCaseModel();
        $this->supplyMarketingCompanyModel = new supplyMarketingCompanyModel();
        $this->supplyMarketingCompanyQualificationModel = new supplyMarketingCompanyQualificationModel();
        $this->supplyMarketingHotSearchModel = new supplyMarketingHotSearchModel();
        $this->DistrictModel = new DistrictModel();

        //todo 获取用户管理的公司的id
        if(is_administrator() == 1){

        }

        //todo 如果用户没有绑定公司，就跳转到公司信息发布界面
    }
    public function index(){

        //跳转产品列表
        $this->products();
    }

/////////////////////////////////////////////产品信息管理//////////////////////////////////////////////////////////
    //产品列表
    public function products($page = 1,$r = 20){

        //查询商品信息
        $map = [];
        $jump = [];
        if(I("productName")){
            $map['productName'] = I("productName");
            $jump['productName'] = I("productName");

        }if(I('publishDate')){
            $map['publishDate'] = I("publishDate");
            $jump['publishDate'] = I("publishDate");

        }if(I('publishDate')){
            $map["category"] = I("category");
            $jump["category"] = I("category");

        }if(I('publishDate')){
            $map['shenHeStatus'] = I("shenHeStatus");
            $jump['shenHeStatus'] = I("shenHeStatus");

        }if(I('productStatus')){
            $map['productStatus'] = I("productStatus");
            $jump['productStatus'] = I("productStatus");

        }if($page){
            $jump['page'] = $page;
        }
//        $map["companyId"] = $this->company_id;

        //查询公司相关数据
        if(I("companyName")){
            $companyData = $this->supplyMarketingCompanyModel->where(['name'=>['like','%'.I("companyName").'%']])->find();
            if($companyData['id']){
                $map['companyId'] = $companyData['id'];
                $jump['companyName'] = I("companyName");
            }
        }

        //拼接数据
        $url = '';
        foreach($jump as $key => $val){
            if($url){
                $url .= '&'.$key.'='.$val;
            }else{
                $url = '&'.$key.'='.$val;
            }
        }

        $attr['href'] = U('Gxpt/exportProductExcel',$map);
        $attr['class']='btn btn-ajax';

        //查询产品信息
        list($list,$count) = $this->productModel->getListByPage($map,$page,'update_time desc','*',$r);

        //查询产品分类
        $erJiC = [];
        $data = $this->categoryModel->getTree(0,true,["status" => 1]);

        foreach($data as $tmpD){

            //拼接列表一级分类
            foreach($list as &$v){
                if($v['one_level_category'] == $tmpD['id']){
                    $v['cateOneName'] = $tmpD['name'];
                }

                //拼接二级分类
                foreach($tmpD["_"] as $twoVal){

                    if($v['two_level_category'] == $twoVal['id']){
                        $v['cateTwoName'] = $twoVal['name'];
                    }

                    //拼接三级分类
                    foreach($twoVal["_"] as $threeVal){
                        if($v['three_level_category'] == $threeVal['id']){
                            $v['cateThreeName'] = $threeVal['name'];
                        }
                    }
                }
            }

            //二级分类搜索
            foreach($tmpD["_"] as $erTmpD){

                //拼接
                $erJiC[] = ["id" => $erTmpD["id"],"value" => $erTmpD["name"]];
            }
        }

        $builder = new AdminListBuilder();
        $builder->title("产品信息列表")
            ->data($list)
            ->buttonNew(U('Gxpt/productAdd'),"新增")
            ->ajaxButton(U('Gxpt/setproductstatus?status=0'),$jump,"下架")
            ->ajaxButton(U('Gxpt/setproductstatus?status=1'),$jump,"上架")
            ->setSelectPostUrl(U('Gxpt/products'))
            ->select('产品二级分类：','category','select','','','',array_merge(array(array('id'=>0,'value'=>L('_EVERYTHING_'))),$erJiC))
            ->select('发布日期：','publishDate','select','','','',array_merge([["id" => 4,"value" => "全部"]],[['id'=>-1,'value'=>"今天"],["id" =>1,"value" => "昨天"],["id" => 2,"value" => "一周内"],["id" => 3,"value" => "一个月内"]]))
            ->select('审核状态：','shenHeStatus','select','','','',array_merge(array(array('id'=>0,'value'=>L('_EVERYTHING_'))),[["id" =>1,"value" => "审核中"],["id" =>2,"value" => "审核通过"]]))
            ->select('产品状态：','productStatus','select','','','',array_merge(array(array('id'=>0,'value'=>L('_EVERYTHING_'))),[["id" =>1,"value" => "启用"],["id" =>2,"value" => "禁用"]]))
            ->search("产品名称",'productName','text','')
            ->search("公司名称",'companyName','text','')
            ->keyId()
            ->keyText('title','产品名称')
            ->keyText('cateOneName',"所属一级分类")
            ->keyText('cateTwoName',"所属二级分类")
            ->keyText('cateThreeName',"所属三级分类")
            ->keyText('create_time','发布时间')
            ->keyStatus()
            ->keyText('companyName',"所属公司")
            ->keyText('checkstatustext',"产品状态")
            ->keyText("isserver","是否置顶")
            ->keyText('yuyueshu',"预约数")
            ->keyText("zixunshu","咨询数")
            ->keyDoActionEdit('Gxpt/productadd?id=###'.$url,"查看")
            ->keyDoActionEdit('Gxpt/buyServer?id=###',"推广")
            ->button('导出产品Excel数据', $attr)
            ->pagination($count,$r)
            ->display();
    }

    //导出产品Excel数据
    public function exportProductExcel(){

        $map = I('get.');
        $data = (new ProductModel())->getListData($map,'','*');

        //查询产品分类
        $dataCate = $this->categoryModel->getTree(0,true,["status" => 1]);

        foreach($dataCate as $tmpD){

            //拼接列表一级分类
            foreach($data as &$v){
                if($v['one_level_category'] == $tmpD['id']){
                    $v['cateOneName'] = $tmpD['name'];
                }

                //拼接二级分类
                foreach($tmpD["_"] as $twoVal){

                    if($v['two_level_category'] == $twoVal['id']){
                        $v['cateTwoName'] = $twoVal['name'];
                    }

                    //拼接三级分类
                    foreach($twoVal["_"] as $threeVal){
                        if($v['three_level_category'] == $threeVal['id']){
                            $v['cateThreeName'] = $threeVal['name'];
                        }
                    }
                }
            }
        }

        for($i = 0; $i < count ( $data ); $i ++) {
            array_unshift ( $data [$i], $i );
        }

        $excelData = [];
        $num = 1;
        foreach($data as $key => $val){
            $excelData [$key] ['title'] = $val ['title'];
            $excelData [$key] ['cateOneName'] = $val ['cateOneName'];
            $excelData [$key] ['cateTwoName'] = $val ['cateTwoName'];
            $excelData [$key] ['cateThreeName'] = $val ['cateThreeName'];
            $excelData [$key] ['create_time'] = $val ['create_time'];
            $excelData [$key] ['status'] = $val['status'] == 1 ? L('_ENABLE_') : ($val['status'] == 2 ? L('_UNAUDITED_') : ($val['status'] == 2 ? L('_DISABLE_') : ($val['status'] == 0 ? L('_DISABLE_') : ($val['status'] == -1 ? L('_DELETE_') : ''))));
            $excelData [$key] ['companyName'] = $val ['companyName'];
            $excelData [$key] ['cateTwoName'] = $val ['cateTwoName'];
            $excelData [$key] ['isserver'] = $val ['isserver'];
            $excelData [$key] ['yuyueshu'] = $val ['yuyueshu'];
            $excelData [$key] ['zixunshu'] = $val ['zixunshu'];
            $num ++;
        }

        $len = count ( $excelData );

        $title = getExcelTitle(['产品名称','所属一级分类','所属二级分类','所属三级分类','发布时间','状态','所属公司','是否置顶','预约数','咨询数']);

        $info = getExcelInfo('供销产品信息报表');

        $optional = array (
            'colwidth' => array (
                'A' => '30',
                'B' => '20',
                'C' => '20',
                'D' => '20',
                'E' => '20',
                'F' => '20',
                'G' => '28',
                'H' => '10',
                'I' => '20',
                'J' => '20',
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

    public function productadd($id = 0){

        $title=$id?L('_EDIT_'):L('_ADD_');
        if (IS_POST) {

            //参数验证
            $error = "";
            if(empty($_POST["title"])){

                $error .= "产品名称必填！";
            }

            if(empty($_POST['price'])){

                $_POST['price'] = "0";
            }

            if(empty($_POST["one_level_category"])){

                $error .= "产品分类必选！";
            }

            if(empty($_POST["description"])){

                $error .= "产品简介必填！";
            }

            if(!empty($error)){

                $this->error($error);
            }

            $cateOne = I("one_level_category");
            $cateTwo = I("two_level_category");

            $cateOneData = $this->categoryModel->info($cateOne);
            $cateTwoData = $this->categoryModel->info($cateTwo);

            //判断如果选择了价格面议
            $priceNegotiable = I("price_negotiable");
            if($priceNegotiable == 'yes'){
                $priceUnit = '';
                $price = 0;
            }else{
                $priceUnit = I("price_unit");
                $price = I("price");
            }

            //获取地址的数据
            $province = '';
            $city = '';
            if(I("province")){
                $province = $this->DistrictModel->getData(I("province"));
            }

            if(I("city")){
                $city = $this->DistrictModel->getData(I("city"));
            }

            //如果用户选择直辖市的话 只填写市级地址
            $districtText = '';
            if($province['name'] == '北京市' || $province['name'] == '天津市' || $province['name'] == '上海市' || $province['name'] == '重庆市'){
                $districtText = $province['name'];
            }else{
                $districtText = $city['name'] ? $city['name'] : $province['name'];
            }

            //整理商品信息
            $product = [

                "id" => $id,
                "title" => I("title"),
                "price" => $price,
                "price_unit" => $priceUnit,
                "company_id" => I("company_id"), //TODO 正式上线后需要修改
                "description" => I("description"),
                "one_level_category" => I("one_level_category"),
                "two_level_category" => I("two_level_category"),
                "three_level_category" => I("three_level_category"),
                "category_text" => $cateOneData['name']."-".$cateTwoData['name'],
                "status" => 1,
                "check_status" => 1,
                "sort" => I("sort"),
                "province" => I("province"),
                "city" => I("city"),
                "district_text" => $districtText,
                "create_time" => time()
            ];

            $cate = $this->productModel->editData($product);

            //取出添加的产品图片信息
            $productImg = explode(',',I("productImg"));
            if(I("productImg")!='0'){
                if($productImg[0] == 0){
                    unset($productImg[0]);
                }
//                    $map['id'] = ['IN',array_values($productImg)];
//                    $picData = $this->pictureModel->where($map)->select();

                //todo 添加或者维护产品图片关系表 jpgk_something_pic
                $rowId = $id ? $id : $cate;
                $app = 'SupplyMarket';

                //将关联的图片数据删除
                $this->somethingPicModel->delData(['row_id'=>$rowId,'app'=>$app]);

                //重新添加图片数据
                foreach($productImg as $val){

                    if($val){
                        $this->somethingPicModel->editData([
                            'pic_id'=>$val,
                            'app'=>$app,
                            'row_id'=>$rowId,
                        ]);
                    }
                }
            }

            //todo 添加或者维护产品成功案例图片表 jpgk_supply_marketing_company_case

            //取出添加的成功案例图片信息
            $this->company_id;
            $successImg = explode(',',I("successImg"));

            if(I("successImg")!='0'){
                if($successImg[0] == 0){
                    unset($successImg[0]);
                }
//                    $map['id'] = ['IN',array_values($productImg)];
//                    $picData = $this->pictureModel->where($map)->select();

                //todo 添加或者维护产品图片关系表 jpgk_supply_marketing_company_case
                $rowId = $id ? $id : $cate;

                //将关联的图片数据删除
                $this->supplyMarketingCompanyCaseModel->delData(['product_id'=>$rowId]);

                //重新添加图片数据
                foreach($successImg as $val){

                    if($val){

                        $picData = $this->pictureModel->where(['id'=>$val])->find();
                        $this->supplyMarketingCompanyCaseModel->editData([
                            'company_id'=>0,
                            'product_id'=>$rowId,
                            'status'=>1,
                            'pic_id'=>$val,
                            'image_url'=>$picData['path'],
                            'create_time'=>time(),
                            'update_time'=>time(),
                        ]);
                    }
                }
            }

            if ($cate) {

                $urlData = I('post.urlData');

                //id有值时(编辑) 跳转到指定位置
                $url = $id ? U('Gxpt/products?'.$urlData) : U('Gxpt/products');
                S('SHOW_EDIT_BUTTON',null);
                $this->success($title.L('_SUCCESS_'));
            } else {
                $this->error($title.L('_FAIL_').$this->productModel->getError());
            }
        } else {

            $builder = new AdminConfigBuilder();

            //id不等于0代表编辑
            $data = [];
            $data["productImg"] = [];
            $data["successImg"] = [];
            $negotiable = '';
            if ($id != 0) {

                $data = $this->productModel->find($id);//查询分类信息
                $this->assign("dsparam",[
                    "one_level_category" => $data["one_level_category"],
                    "two_level_category" => $data["two_level_category"],
                    "three_level_category" => $data["three_level_category"]
                ]);
                $data["district_id"] = [

                    $data["province"],
                    $data["city"],
                    0
                ];

                if($data['price']<=0){
                    $negotiable = 'yes';
                }

                //拼接图片数据
                $app = 'SupplyMarket';

                //将关联的产品图片数据查出
                $picIdData = $this->somethingPicModel->field('pic_id')->where(['row_id'=>$id,'app' => $app])->select();
//                implode(',',$picIdData);

                foreach($picIdData as $val){
                    if(!$data["productImg"]){
                        $data["productImg"] = $val['pic_id'];
                    }else{
                        $data["productImg"] .= ','.$val['pic_id'];
                    }
                }

                //将关联的成功案例 数据查出
                $sImgData = $this->supplyMarketingCompanyCaseModel->field('pic_id')->where(['product_id'=>$id])->select();
//                print_r($sImgData);die;
//                implode(',',$picIdData);

                foreach($sImgData as $val){
                    if(!$data["successImg"]){
                        $data["successImg"] = $val['pic_id'];
                    }else{
                        $data["successImg"] .= ','.$val['pic_id'];
                    }
                }

                //拼接数据 跳转功能
                $urlData = $_GET;
                $url = '';
                foreach($urlData as $key => $val){
                    if($url){
                        $url .= '&'.$key.'='.$val;
                    }else{
                        $url = $key.'='.$val;
                    }
                }

                $this->assign('urlData',$url);
            }

            //拼接公司下拉数据
            $options = [];
            $companyData = $this->supplyMarketingCompanyModel->where(['status'=>2])->select();
            if($companyData){
                foreach($companyData as $val){
                    $options[$val['id']]=$val['name'];
                }
            }else{
                $options[0]='无可选公司';
            }

            $builder->title($title."产品")
                ->data($data)
                ->keyId()
                ->keyText('title', '产品名称')
                ->keyCheckBox("price_negotiable","价格面议",'选择后产品价格,单位填写无效',['yes'=>''])->keyDefault('price_negotiable',$negotiable)
                ->keyText("price",'产品价格')
                ->keyText("price_unit","产品单位")
                ->keySort('sort',L('_SORT_'),'')->keyDefault('sort',999)
                ->keyOthers("category","选择分类",null,"Gxpt/Test/test",["module" => "Gxpt","html" => "selectcategory"])
                ->keyCity("district_id","选择区域")
                ->keySelect('company_id','选择公司','',$options)->keyDefault('company_id',$data['company_id'])
                ->keyTextArea("description","产品简介")
                ->keyMultiImage("productImg","产品图片")
                ->keyMultiImage("successImg","成功案例")
                ->group("基础信息","id,title,price,price_unit,sort,price_negotiable,description,category,district_id,company_id,productImg,successImg")
                ->buttonSubmit(U('Gxpt/productadd'))->buttonBack()
                ->GXPTdisplay();
        }
    }

    //产品审核列表
    public function productsAudit($page = 1,$r = 20){

        //查询商品信息
        $map["shenHeStatus"] = 1;

        //查询商品信息
        list($list,$count) = $this->productModel->getListByPage($map,$page,'update_time desc','*',$r);

        $builder = new AdminListBuilder();
        $builder->title("产品信息审核列表")
            ->data($list)
            ->ajaxButton(U('Gxpt/reviewedProduct'),'',"审核通过")

            ->buttonModalPopup(U('Gxpt/uploadExcel'),null,'导入excel数据',array('data-title'=>'选择excel数据'))
            ->keyId()->keyText('title','产品名称')->keyText('create_time','发布时间')->keyStatus()->keyText('checkstatustext',"产品状态")->keyText("isserver","是否置顶")->keyText('yuyueshu',"预约数")->keyText("zixunshu","咨询数")
            ->keyDoActionEdit('Gxpt/productadd?id=###',"查看")
            ->pagination($count,$r)
            ->display();
    }

    //Excel导入弹出框
    public function uploadExcel()
    {
        if(IS_POST){

            $tmp_file = $_FILES ['file'] ['tmp_name'];
            $file_types = explode ( ".", $_FILES ['file'] ['name'] );
            $file_type = $file_types [count ( $file_types ) - 1];

            /*判别是不是.xls文件，判别是不是excel文件*/
            if (strtolower ( $file_type ) != "xlsx")
            {
                $this->error ( '不是Excel文件，重新上传' );
            }

            $upload = new \Think\Upload();
            $upload->maxSize  = 93145728 ;
            $upload->saveRule = 'time';
            $upload->allowExts  = array('xls','xlsx','csv');
            $upload->savePath =  './Uploads/Excel/';
            $info = $upload->upload();
//print_r($info);die;

            $excelTemp = [];
            if(!$info)
            {
                $this->error($upload->getError());
            }else{
                $PicAndPath = [
                    'picpath'=>str_ireplace(DIRECTORY_SEPARATOR,'/',$info['file']['savepath']) ,
                    'picname'=>$info['file']['savename']
                ];

                //获取excel数据
                $filePath = "./Uploads/".$PicAndPath['picpath'].$PicAndPath['picname'];
                $excelTemp = getExcelData($filePath);
//                print_r($data);die;
            }

            $excelData = [];
            if($excelTemp['status'] == 1){
                $excelData = $excelTemp['data'];
            }else{
                $this->error('读取excel数据失败');
            }

            //获取模板标准表头数据
            $headData = array_values($excelData [1]);
            $validateArr = $this -> getExcelHeaderData();

            //查看提交的excel表头数据 是否被篡改
            $validateHeadResult = $this->excelHeadValidate($headData, $validateArr);

            if ($validateHeadResult !== true)
            {
                $this->error($validateHeadResult);
            }

            $total = count($excelData);

            for ($i = 2; $i <= $total; $i++)
            {
                if(empty($excelData[$i]['A']) || empty($excelData[$i]['B']) || empty($excelData[$i]['C']) || is_null($excelData[$i]['D'])){
//                    $this->ajaxReturn(['status'=>0,'info'=>"第{$i}行必填字段未填！"]);
//                    break;
                }
            }

            //添加数据
            try{

                $data = [];
                for ($i = 2; $i <= $total; $i++)
                {
                    if($excelData[$i]['A']){

                            $data['title'] = $excelData[$i]['A'];
                            $data['price'] = $excelData[$i]['B'];
        //                    $data['image'] = $excelData[$i]['C'];
                            $data['district_text'] = $excelData[$i]['D'];
        //                    $data['description'] = $excelData[$i]['E'];
        //                    $data['company_id'] = $excelData[$i]['F'];

                            $oneCategory = $this->categoryModel->info($excelData[$i]['G']);
                            $twoCategory = $this->categoryModel->info($excelData[$i]['H']);
                            $threeCategory = $this->categoryModel->info($excelData[$i]['I']);

                            $data['one_level_category'] = $oneCategory['id'];
                            $data['two_level_category'] = $twoCategory['id'];
                            $data['three_level_category'] = $threeCategory['id'];
                            $data['category_text'] = $oneCategory['name'].' '.$twoCategory['name'];
                            $data['status'] = 1;
                            $data['check_status'] = 1;
                            $data['sort'] = 999;
                            $data['create_time'] = time();

                            $gxptId = $this->productModel->editData($data);

                            //添加图片数据
                            $picData = [
                                'type' => 'remote',
                                'path' => '',
                                'url' => $excelData[$i]['C'],
                                'md5' => '',
                                'sha1' => '',
                                'status' => 1,
                                'create_time' => time(),
                            ];
                            $picId = $this->pictureModel->add($picData);

                            $STPData = [
                                'app' => 'SupplyMarket',
                                'row_id' => $gxptId,
                                'pic_id' => $picId,
                                'create_time' => time(),
                            ];
                            $this->somethingPicModel->add($STPData);
        //                    //拼接图片数据
        //                    $app = 'SupplyMarket';
        //
        //                    //将关联的产品图片数据查出
        //                    $picIdData = $this->somethingPicModel->field('pic_id')->where(['row_id'=>$gxptId,'app' => $app])->select();
        //
        //                    foreach($picIdData as $val){
        //                        if(!$data["productImg"]){
        //                            $data["productImg"] = $val['pic_id'];
        //                        }else{
        //                            $data["productImg"] .= ','.$val['pic_id'];
        //                        }
        //                    }

                    //判断有值走修改 反之走添加
                    }
                }

            }catch(\Exception $e){
            $this->error('导入失败');
        }
        $this->success('导入成功',U('Gxpt/productsaudit'));

        }else{
            $this->display(T('Gxpt@Admin/excelUpload'));
        }
    }

    //获取表头数据
    private function getExcelHeaderData(){

        $return = [
            '标题',
            '价格',
            '产品图片',
            '发货地',
            '产品描述',
            '商家',
            '分类1',
            '分类2',
            '分类3',
        ];

        return $return;
    }

    private function excelHeadValidate($head, $validateArr)
    {
        //验证表头数据是否被篡改
        foreach ($validateArr as $k => $v) {
            if ($head [$k] != $v) {
                return '表头数据中"' . $v . '"不能篡改！';
            }
        }

        return true;
    }

    /**
     * 产品审核通过数据信息
     */
    public function reviewedProduct(){
        $ids = I('post.ids');
        !is_array($ids)&&$ids=explode(',',$ids);
        $rs = $this->productModel->where(['id' => array('in', $ids)])->save(['check_status' => 2]);
        if ($rs === false) {
            $this->error(L('_ERROR_SETTING_') . L('_PERIOD_'));
        }

        $this->success(L('_SUCCESS_SETTING_'), U('Gxpt/products'));
    }

    public function setproductstatus(){

        $ids = $_POST["ids"];
        if(!empty($ids)){

            $this->productModel->where(["id" => ['in',$_POST['ids']]])->save(["status" => I('get.status')]);
            $this->success("状态设置".L('_SUCCESS_'), U('Gxpt/products',$_GET));
        }else{

            $this->error("状态设置".L('_FAIL_')."请选择操作的对象");
        }
    }


    public function buyServer($id = 0)
    {
        $title="购买置顶服务";
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
                    $payUrl = $payService->buyService(get_uid(),'SupplyMarket',$id,$startTime,$endTime,Conf::PAY_TYPE_ALIPAY,$money,"购买产品置顶服务",$returnUrl);
                    break;
                case 'wxpay':
                    $tmpUrl = $payService->buyService(get_uid(),"SupplyMarket",$id,$startTime,$endTime,Conf::PAY_TYPE_WEIXIN,$money,"购买产品置顶服务");
                    $openUrl = "http://".$_SERVER["HTTP_HOST"].U("Pay/WxPay/payHtml")."&payUrl=".urlencode($tmpUrl);
                    break;
            }

            $this->success("请前往支付界面！",$payUrl,false,$payUrl,$openUrl);
        } else {

            //查询特定的资讯是否有置顶的服务
            $payServerModel = new PayServerModel();
            $newServer = $payServerModel->getDataPayServerOfRunning("SupplyMarket",$id);

            if(empty($newServer)){
                $builder=new AdminConfigBuilder();
                $builder->title($title)
                        ->keyHidden("newsId",$id)
                        ->keyTime('newsStartTime','置顶开始时间','','date')->keyDefault('newsStartTime',time())
                        ->keyTime('newsEndTime','置顶结束时间','','date')->keyDefault('newsEndTime',strtotime(date("Y-m-d",strtotime("+10 day"))))
                        ->keyRadio("payType","支付方式",'',['wxpay' => '微信支付','alipay' => '支付宝支付'])->keyDefault('payType','alipay')
                        ->keyLabel("description","请注意：如果您已支付成功，请刷新！！！")
                        ->group($title,'newsStartTime,newsEndTime,clickZD,payType,description')
                        ->buttonSubmit()->buttonBack()
                        ->newsDisplay();
            }else{

                $builder=new AdminConfigBuilder();
                $builder->title($title)
                        ->keyHidden("newsId",$id)
                        ->keyTime('newsStartTime','置顶开始时间','','date')->keyDefault('newsStartTime',$newServer["start_time"])
                        ->keyTime('newsEndTime','置顶结束时间','','date')->keyDefault('newsEndTime',$newServer["end_time"])
                    /*->keyRadio("payType","支付方式",'',['wxpay' => '微信支付','alipay' => '支付宝支付'])->keyDefault('payType','alipay')*/
                        ->keyLabel("description","请注意：该资讯还在服务中，请过期后重新购买！！！")
                        ->group($title,'newsStartTime,newsEndTime,clickZD,description')
                        ->buttonBack()
                        ->newsDisplay();
            }
        }
    }
/////////////////////////////////////////////产品信息管理//////////////////////////////////////////////////////////

/////////////////////////////////////////////产品咨询管理//////////////////////////////////////////////////////////
    //咨询回复列表
    public function zixuns($page = 1,$r = 20){

        $map["productName"] = I("productName");
        $map["isReply"] = I("status");

        // TODO
        if(!is_administrator(is_login())){
//            $map["company_id"] = $this->company_id;
        }

        $commentModel = new CommentModel();
        list($list,$count) = $commentModel->getPageList($map,$page,'coment.create_time desc','*',$r);


        foreach($list as &$l){

            if(!$l["isReply"]){

                $url = U("Gxpt/zixunreply",["id" => $l["id"]]);
                $l["operate"] = "<a href='{$url}'>回复</a>";
            }else{
                $url = U("Gxpt/setreplaystatus",["id" => $l["id"]]);
                $l["operate"] = "<a href='{$url}'>删除回复</a>";
            }
        }
//test_output_json($list);
        $builder = new AdminListBuilder();
        $builder->title("咨询列表")
                ->data($list)
                ->ajaxButton(U("Admin/Gxpt/setreplaystatus"),"","删除回复",[])
                ->setSelectPostUrl(U('Admin/Gxpt/zixuns'))
                ->select('回复情况：','status','select','','','',array_merge(array(array('id'=>0,'value'=>L('_EVERYTHING_'))),[["id" =>1,"value" => "已回复"],["id" =>2,"value" => "未回复"]]))
                ->search("产品名称",'productName','text','')
                ->keyId()->keyText('title','产品名称')->keyText('content','咨询内容')->keyText('username',"咨询用户")->keyText("create_time","咨询时间")->keyText('replaycontent',"咨询回复")->keyText("replaytime","回复时间")->keyText("operate","操作")
//                ->keyDoActionEdit('Gxpt/zixunreply?id=###',"回复")
                ->pagination($count,$r)
                ->display();
    }

    public function setreplaystatus(){

        $ids = I("ids");

        if(empty($ids) && !empty($_REQUEST["id"])){

            $ids = [$_REQUEST["id"]];
        }

        if(!empty($ids)){

            $commentModel = new CommentModel();
            $commentModel->where(["pid" => ["in",$ids]])->save(["status" => -1]);
            S('SHOW_EDIT_BUTTON',null);
            $this->success(L('_SUCCESS_'), U('Gxpt/zixuns'));
        }else{

            $this->error(L('_FAIL_').$this->productModel->getError());
        }
    }

    public function zixunreply(){

        $id = $_REQUEST["id"];
        if(IS_POST){

            $commentModel = new CommentModel();
            $coment = $commentModel -> find($id);

            $reply = [
                "uid" => get_uid(),
                "app" => "SupplyMarket",
                "row_id" => $coment["row_id"],
                "parse" => 0,
                "content" => I("content"),
                "create_time" => time(),
                "pid" => $id,
                "status" => 1
            ];

            if($commentModel->add($reply)){

                //给用户推送信息
                $jpush = new Lz517JPush();
                $jpush->pushMessageForApp("您的商品咨询有了新的回复，点击查看！",["canxun".$coment["uid"]]);

                $this->success(L('_SUCCESS_'), U('Gxpt/zixuns'));
            }else{

                $this->error(L('_FAIL_').$commentModel->getError());
            }
        }else{

            $id = I("id");

            $builder = new AdminConfigBuilder();

            $builder->title("咨询回复")
                ->keyTextArea("content","回复内容")
                ->buttonSubmit(U("Gxpt/zixunreply",["id" => $id]))->buttonBack()
                ->display();
        }
    }
/////////////////////////////////////////////产品咨询管理//////////////////////////////////////////////////////////


/////////////////////////////////////////////产品预约管理//////////////////////////////////////////////////////////
    //预约列表
    public function yuyues($page = 1,$r = 20){

        //查询数据
        $map = [];
        $map["productName"] = I("productName");
        $map["status"] = I("status");

        //判断是否为管理员
        if(!is_administrator(is_login())){
//            $map["company_id"] = is_login();
        }

        $appointmentModel = new AppointmentModel();
        list($list,$count) = $appointmentModel->getPageList($map,$page,"appointment.create_time desc","*",$r);

        foreach($list as &$l){

            if($l["status"] == 1 || $l["status"] == 2){

                $url = U("Gxpt/cancelappointment",["id" => $l["id"]]);
                $l["operate"] = "<a href='{$url}'>取消预约</a>";
            }else{

                $l["operate"] = "暂无操作";
            }
        }

        $builder = new AdminListBuilder();
        $builder->title("预约列表")
            ->data($list)
                ->ajaxButton(U("Gxpt/setappointmentstatus",["status" => 2]),"","确认预约")
                ->ajaxButton(U("Gxpt/setappointmentstatus",["status" => 4]),"","确认交易")
                ->setSelectPostUrl(U('Admin/Gxpt/yuyues'))
                ->select('预约状态：','status','select','','','',array_merge(array(array('id'=>0,'value'=>L('_EVERYTHING_'))),[["id" =>1,"value" => "预约中"],["id" =>2,"value" => "确认预约"],["id" =>3,"value" => "取消预约"],["id" =>4,"value" => "确认交易"]]))
                ->search("产品名称",'productName','text','')
                ->keyId()->keyText('title','产品名称')->keyText('phone','预约用户')->keyText('username',"用户手机号")->keyText("appointmentTimeText","预约服务时间")->keyText('statusText',"预约状态")->keyText("remark","备注")->keyText("operate","操作")
            ->pagination($count,$r)
                ->display();
    }

    public function setappointmentstatus(){

        $status = $_REQUEST["status"];
        $ids = I("ids");

        if(!empty($ids)){

            $appointmentModel = new AppointmentModel();
            $appointments = $appointmentModel->where(["id" => ["in",$ids]])->select();

            //取出数据中的状态
            $statusArr = array_column($appointments,'status');

            //先确认预约后才能确认交易 确认交易后不能在确认预约
            //$status = 2 : 确认预约 | $status = 4 : 确认交易
            //预约状态(1:预约中;2:预约成功;3:(预约方)已取消;4:已完成;5:(发布方)已取消)

            if($status == 2){

                foreach($statusArr as $val){
                    if($val == '4'){
                        $this->error(L('数据状态已完成的,不可重新完成预约！'));
                    }elseif($val == '3'){
                        $this->error(L('数据状态为用户取消的,不可重新完成预约！'));
                    }
                }
            }elseif($status == 4){

                foreach($statusArr as $val){
                    if($val != 2){
                        $this->error(L('数据状态为预约成功的,才可确认交易！'));
                    }
                }
            }

            $appointmentModel->where(["id" => ["in",$ids]])->save(["status" => $status]);

            //给用户推送信息
            $tags = [];
            foreach($appointments as $appointment){

                array_push($tags,"canxun".$appointment["uid"]);
            }
            $tags = array_unique($tags);

            $jpush = new Lz517JPush();
            $jpush->pushMessageForApp("您的预约有了新状态，点击查看！",$tags);

            S('SHOW_EDIT_BUTTON',null);
            $this->success(L('_SUCCESS_'), U('Gxpt/yuyues'));
        }else{

            $this->error(L('_FAIL_'));
        }
    }

    public function cancelappointment($id = 0){

        $id = $_REQUEST["id"];
        if(IS_POST){

            $appointmentModel = new AppointmentModel();
            $appointment = $appointmentModel->find($id);
            if($appointmentModel->where(["id" => $id])->save(["remark" => I("remark"),"status" => 5])){

                //给用户推送信息
                $jpush = new Lz517JPush();
                $jpush->pushMessageForApp("您的预约有了新状态，点击查看！",["canxun".$appointment["uid"]]);

                $this->success(L('_SUCCESS_'), U('Gxpt/yuyues'));
            }else{

                $this->error(L('_FAIL_'));
            }
        }else{

            $id = I("id");

            $builder = new AdminConfigBuilder();

            $builder->title("取消预约")
                    ->keyTextArea("remark","取消原因")
                    ->buttonSubmit(U("Gxpt/cancelappointment",["id" => $id]))->buttonBack()
                    ->display();
        }
    }
/////////////////////////////////////////////产品预约管理//////////////////////////////////////////////////////////

/////////////////////////////////////////////产品分类管理//////////////////////////////////////////////////////////
    //产品分类管理
    public function categories(){

        //显示页面
        $builder = new AdminTreeListBuilder();

        $categoryModel = new CategoryModel();
        $tree = $categoryModel->getTree();

        $builder->title("产品分类管理（注意：产品分类禁用、删除后，分类下对应的产品将会默认不在显示！）")
                ->suggest("")
                ->buttonNew(U('addcategory'))
                ->data($tree)
                ->setLevel(3)
                ->setModel("category")
                ->display();
    }

    //添加产品分类
    public function addcategory($id = 0, $pid = 0){

        $title=$id?L('_EDIT_'):L('_ADD_');
        if (IS_POST) {
            //参数验证
            $error = "";
            if(empty($_POST["name"]) && !$id){

                $error .= "分类标题必填！";
            }

            if(empty($_POST['sort']) && !$id){

                $error .= "分类排序必填！";
            }

            if(empty($_POST['icon_url_id'])  && !$id){

                $error .= "分类的图片必选！";
            }

            if($_POST["is_hot"] == 1){

                if(empty($_POST["hot_icon_url_id"])){

                    $error = "设置为热门分类后，热门的图片必选！";
                }
            }

            if(!empty($error)){

                $this->error($error);
            }

            if($id && $_POST["title"]){

                $_POST["name"] = $_POST["title"];
                unset($_POST["title"]);
            }

            if($_POST['icon_url_id']) {
                if ($cate = $this->categoryModel->editData($_POST)) {

                    //添加图片
                    $icon_url = I('icon_url_id');
                    $hot_icon_url = I('hot_icon_url_id');

                    $rowId = $id ? $id : $cate;

                    //重新添加图片数据
                    $newIconUrl = '';
                    $newHotIconUrl = '';
                    if ($icon_url) {

                        $icData = $this->pictureModel->where(['id' => $icon_url])->find();
                        $newIconUrl = $icData['path'];
                    }

                    if ($hot_icon_url) {

                        $hiuData = $this->pictureModel->where(['id' => $hot_icon_url])->find();
                        $newHotIconUrl = $hiuData['path'];
                    }

                    //将图片添加到
                    $this->categoryModel->editData(['id' => $rowId, 'icon_url' => $newIconUrl, 'hot_icon_url' => $newHotIconUrl]);

                    S('SHOW_EDIT_BUTTON', null);
                    $this->success($title . L('_SUCCESS_'), U('Gxpt/categories'));
                } else {
                    $this->error($title . L('_FAIL_') . $this->categoryModel->getError());
                }
            }else{
                $this->categoryModel->editData($_POST);
                $this->success($title.L('_SUCCESS_'), U('Gxpt/categories'));

            }
        } else {

            $builder = new AdminConfigBuilder();

            //id不等于0代表编辑
            if ($id != 0) {

                $data = $this->categoryModel->find($id);//查询分类信息
            }

            //如果存在pid，就查询父级分类
            $opt = [];
            if($pid != 0){

                $fatherCategory = $this->categoryModel->find($pid);
                $opt[$fatherCategory["id"]] = $fatherCategory["name"];
            }else{

                $pid = 0;
            }

            $builder->title($title.L('_CATEGORY_'))
                ->data($data)
                ->keyId()
                ->keyText('name', L('_TITLE_'))
                ->keySelect('pid',L('_FATHER_CLASS_'), L('_FATHER_CLASS_SELECT_'), array('0' =>L('_TOP_CLASS_')) + $opt)->keyDefault('pid',$pid)
                ->keyInteger('sort',L('_SORT_'))->keyDefault('sort',1)
                ->keySingleImage('icon_url_id',"图片")
                ->keyStatus()->keyDefault('status',1)


                ->keySelect("is_hot","是否热门", null, array('0' =>"否","1" => "是"))
                ->keySingleImage("hot_icon_url_id","热门图片")
                ->group("基础信息","id,name,pid,sort,icon_url_id,status,hot_icon_url_id,is_hot")

                //                ->keyText("hot_degree","热度")
//                ->group("是否是热门分类","hot_icon_url,is_hot")

                ->buttonSubmit(U('Gxpt/addcategory'))->buttonBack()
                ->display();
        }
    }

    //产品分类状态管理
    public function setcategorystatus($ids, $status){

        !is_array($ids)&&$ids=explode(',',$ids);

        if(in_array(1,$ids)){

            $this->error(L('_ERROR_CANNOT_'));
        }

        $builder = new AdminListBuilder();
        $builder->doSetStatus('supply_marketing_product_category', $ids, $status);
    }

    public function selectCategoryLevelOne(){

        if (IS_AJAX){
            $pid = I('pid');  //默认的省份id

            if( !empty($pid) ){
                //$map['id'] = $pid;
            }
            $map["pid"] = 0;
            $map["status"] = 1;
            $list = $this->categoryModel->where($map)->select();

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

                $list = $this->categoryModel->where($map)->select();
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

                $list = $this->categoryModel->where($map)->select();
            }

            $data = "<option value =''>-全部-</option>";
            foreach ($list as $k => $vo) {
                $data .= "<option ";
                if( $did == $vo['id'] ){
                    $data .= " selected ";
                }
                $data .= " value ='" . $vo['id'] . "'>" . $vo['name'] . "</option>";
            }
            $this->ajaxReturn($data);
        }
    }
/////////////////////////////////////////////产品分类管理//////////////////////////////////////////////////////////

/////////////////////////////////////////////公司信息管理//////////////////////////////////////////////////////////

    //公司信息列表
    public function company($page = 1,$r = 20){
        //查询公司信息
        $companyName = I("companyName");

        $map['status'] = 2;
        //搜索了公司名称/联系电话
        if($companyName){
            $where['name'] = ['like','%'.$companyName.'%'];
            $where['phone'] = ['like','%'.$companyName.'%'];
            $where['_logic'] = 'or';
            $map['_complex'] = $where;
        }

        $attr['href'] = U('Gxpt/exportCompanyExcel');
        $attr['class']='btn btn-ajax';

        list($list,$count) = $this->supplyMarketingCompanyModel->getListByPage($map,$page,'id desc','*',$r);

        $builder = new AdminListBuilder();
        $builder->title("公司信息列表")
            ->data($list)
            ->buttonNew(U('Gxpt/companyAdd'),"新增")
            ->setSelectPostUrl(U('Gxpt/company'))
            ->search("公司名称/联系电话",'companyName','text','')
            ->keyId()->keyText('name','公司名称')->keyText('phone','联系电话')->keyText('address',"公司地址")
            ->keyCreateTime()->keyUpdateTime()
            ->keyDoActionEdit('Gxpt/companyAdd?id=###',"编辑")
            ->button('删除', ['class' => 'btn ajax-post tox-confirm', 'data-confirm' => '您确定要删除数据吗?', 'url' => U('Gxpt/delCompany',['status'=> -1,'page'=>$page]), 'target-form' => 'ids'])
            ->button('导出全部公司Excel数据', $attr)
            ->pagination($count,$r)
            ->display();
    }

    //////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    /**
     * 导出报名excel数据
     */
    public function exportCompanyExcel(){

        $data = $this->supplyMarketingCompanyModel->getListData(['status'=>2],'','*');


        for($i = 0; $i < count ( $data ); $i ++) {
            array_unshift ( $data [$i], $i );
        }

        $excelData = array ();
        $title = array (
            "row" => 1,
            "count" => 15,
            "title" => $this->getExcelHeaderDataCompany()
        );

        $num = 1;
        foreach ( $data as $k => $v ) {
            $excelData [$k] ['name'] = $v ['name'];
            $excelData [$k] ['phone'] = $v ['phone'];
            $excelData [$k] ['address'] = $v ['address'];
            $excelData [$k] ['create_time'] = Date('Y-m-d H:i:s',$v ['create_time']);
            $excelData [$k] ['update_time'] = Date('Y-m-d H:i:s',$v ['update_time']);
            $num ++;
        }
        $len = count ( $excelData );
        $info = array (
            "table_name_one" =>  "公司信息报表",
            "table_name_two" => "公司信息报表",
            "lister" => "",
            "tabletime" => date ( "Y-m-d", time () )
        );

        $optional = array (
            'colwidth' => array (
                'A' => '30',
                'B' => '20',
                'C' => '50',
                'D' => '20',
                'E' => '20',
//                'F' => '20',
//                'G' => '15',
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

    private function getExcelHeaderDataCompany()
    {
        $return = [
            '公司名称',
            '联系电话',
            '公司地址',
            '创建时间',
            '更新时间',
        ];

        return $return;
    }

    /**
     * 设置公司状态：删除 -1
     * @param $ids
     * @param $status
     * @author HQ
     */
    public function delCompany(){

        $ids = I('post.ids');
        $status = I('get.status');
        $page = I('get.page');

        !is_array($ids)&&$ids=explode(',',$ids);

        $rs = $this->supplyMarketingCompanyModel->where(['id' => array('in', $ids)])->save(['status' => $status]);
        if ($rs === false) {
            $this->error(L('_ERROR_SETTING_') . L('_PERIOD_'));
        }

        $this->success(L('_SUCCESS_SETTING_'), U('Gxpt/company',['page'=>$page]));
    }

    //公司信息审核列表
    public function companyaudit($page = 1,$r = 20){

        //查询公司信息

        list($list,$count) = $this->supplyMarketingCompanyModel->getListByPage(['status'=>1],$page,'update_time desc','*',$r);

        $builder = new AdminListBuilder();
        $builder->title("公司信息审核列表")
            ->data($list)
            ->keyId()
            ->ajaxButton(U('Gxpt/reviewedCompany'),'','审核通过')
            ->setSelectPostUrl(U('Gxpt/products'))
            ->keyText('name','公司名称')->keyText('phone','联系电话')->keyText('address',"公司地址")
            ->keyDoActionEdit('Gxpt/companyAdd?id=###',"查看")
            ->pagination($count,$r)
            ->display();
    }

    /**
     * 公司审核通过数据信息
     */
    public function reviewedCompany($ids){

        $ids = I('post.ids');
        !is_array($ids)&&$ids=explode(',',$ids);

        $rs = $this->supplyMarketingCompanyModel->where(['id' => array('in', $ids)])->save(['status' => 2,'lisense_status'=>2]);
        if ($rs === false) {
            $this->error(L('_ERROR_SETTING_') . L('_PERIOD_'));
        }

        $this->success(L('_SUCCESS_SETTING_'), U('Gxpt/company'));
    }

    //公司信息添加
    public function companyAdd($id = 0){

        $title=$id?L('_EDIT_'):L('_ADD_');
        if (IS_POST) {

            //参数验证
            $error = "";
            if(empty($_POST["name"])){

                $error .= "公司名称必填！";
            }

            if(!empty($error)){

                $this->error($error);
            }

            if ($cate = $this->supplyMarketingCompanyModel->editData($_POST)) {

                $rowId = $id ? $id : $cate;

                //取出添加的资质图片信息
                $enterpriseImgs = I("enterpriseImgs");
//                $enterpriseImgs = explode(',',I("enterpriseImgs"));

                //将关联的公司图片数据删除
                $this->supplyMarketingCompanyQualificationModel->delData(['company_id'=>$rowId]);
                if($enterpriseImgs){

//                    $map['id'] = ['IN',array_values($productImg)];
//                    $picData = $this->pictureModel->where($map)->select();

                    //todo 添加或者维护公司图片关系表 jpgk_supplyMarketingCompanyQualificationModel
                    $rowId = $id ? $id : $cate;

                    //重新添加图片数据
                    $picData = $this->pictureModel->where(['id'=>$enterpriseImgs])->find();
                    $this->supplyMarketingCompanyQualificationModel->editData([
                        'company_id'=>$rowId,
                        'pic_id'=>$picData['id'],
                        'status'=>1,
                        'pic_id'=>$enterpriseImgs,
                        'image_url'=>$picData['path'],
                        'create_time'=>time(),
                        'update_time'=>time(),
                    ]);
                }

                //todo 添加或者维护产品成功案例图片表


                //取出添加的成功案例图片信息
//                $successImg = explode(',',I("successImgs"));
                $successImg = I("successImgs");

                //将关联的产品成功案例图片数据删除
                $this->supplyMarketingCompanyCaseModel->delData(['product_id'=>0,'company_id'=>$rowId]);

                if($successImg){
//                    unset($successImg[0]);
//                    $map['id'] = ['IN',array_values($productImg)];
//                    $picData = $this->pictureModel->where($map)->select();

                    $rowId = $id ? $id : $cate;

                    //重新添加图片数据
//                    foreach($successImg as $val){
//                        if($val){
                            $picData = $this->pictureModel->where(['id'=>$successImg])->find();
                            $this->supplyMarketingCompanyCaseModel->editData([
                                'company_id'=>$rowId,
                                'product_id'=>'',
                                'status'=>1,
                                'pic_id'=>$successImg,
                                'image_url'=>$picData['path'],
                                'create_time'=>time(),
                                'update_time'=>time(),
                            ]);
//                        }
//                    }
                }

                S('SHOW_EDIT_BUTTON',null);
                $this->success($title.L('_SUCCESS_'), U('Gxpt/company'));
            } else {
                $this->error($title.L('_FAIL_').$this->supplyMarketingCompanyModel->getError());
            }
        } else {

            $builder = new AdminConfigBuilder();

            //id不等于0代表编辑
            $data = [];
            if ($id != 0) {

                $data = $this->supplyMarketingCompanyModel->find($id);

                //资质图片
                $enterpriseImgsNew = $this->supplyMarketingCompanyQualificationModel->where(['company_id'=>$id])->find();
                $data['enterpriseImgs'] = $enterpriseImgsNew['pic_id'];

                //成功案例
                $successImgsNew = $this->supplyMarketingCompanyCaseModel->where(['company_id'=>$id,'product_id'=>0])->find();
                $data['successImgs'] = $successImgsNew['pic_id'];
            }


            $builder->title($title."公司信息")
                ->data($data)
                ->keyId()
                ->keyText('name', '公司名称')
                ->keyText("phone","联系电话")
                ->keyText("address","公司地址")
                ->keyText("longitude","经度")
                ->keyText("latitude","纬度")
                ->keyTime('reg_time','营业执照注册日期','','date')
                ->keyTextArea("description","公司介绍")
                ->keySingleImage("lisense_pic_id","营业执照","")
                ->keySingleImage("enterpriseImgs","资质图片","")
                ->keySingleImage("successImgs","成功案例","")

                ->group("基础信息","id,name,phone,lisense_pic_id,address,longitude,latitude,reg_time,description,enterpriseImgs,successImgs")

                ->buttonSubmit(U('Gxpt/companyAdd'))->buttonBack()
                ->GXPTdisplay();
        }

    }
/////////////////////////////////////////////公司信息管理//////////////////////////////////////////////////////////
/////////////////////////////////////////////热门搜索管理//////////////////////////////////////////////////////////

    //热门搜索列表
    public function hotSearch($page = 1,$r = 20){

        $map['status'] = ['neq',-1];
        //查询商品信息
        list($list,$count) = $this->supplyMarketingHotSearchModel->getListByPage($map,$page,'update_time desc','*',$r);

        foreach($list as $key => $val){
            $list[$key]['create_time'] = Date('Y-m-d H:i:s',time($val['create_time']));
        }

        $builder = new AdminListBuilder();
        $builder->title("热门搜索列表")
            ->data($list)
            ->buttonNew(U('Gxpt/hotSearchAdd'),"新增")
            ->keyId()->keyText('name','关键字名称')->keyText('sort','排序')->keyText('create_time',"创建时间")->keyStatus()
            ->keyDoActionEdit('Gxpt/hotSearchAdd?id=###',"编辑")
            ->pagination($count,$r)
            ->display();
    }


    public function hotSearchAdd($id=0){
        $title=$id?L('_EDIT_'):L('_ADD_');
        if (IS_POST) {

            //参数验证
            $error = "";
            if(empty($_POST["name"])){

                $error .= "关键字名称必填！";
            }

            if(empty($_POST["sort"])){

                $_POST["sort"] = '999';
            }

            if(!empty($error)){

                $this->error($error);
            }

            if ($cate = $this->supplyMarketingHotSearchModel->editData($_POST)) {

                S('SHOW_EDIT_BUTTON',null);
                $this->success($title.L('_SUCCESS_'), U('Gxpt/hotSearch'));
            } else {
                $this->error($title.L('_FAIL_').$this->supplyMarketingHotSearchModel->getError());
            }
        } else {

            $builder = new AdminConfigBuilder();

            //id不等于0代表编辑
            $data = [];
            if ($id != 0) {
                $data = $this->supplyMarketingHotSearchModel->find($id);
            }

            $builder->title($title."搜索关键字信息")
                ->data($data)
                ->keyId()
                ->keyText('name', '关键字名称')
                ->keyText("sort","排序(不填的话,默认999)")
                ->keyStatusNew("status","状态",'',array(0 => '禁用', 1 => '可用'))->keyDefault('status',1)
                ->buttonSubmit(U('Gxpt/hotSearchAdd'))->buttonBack()
                ->display();
        }
    }
}