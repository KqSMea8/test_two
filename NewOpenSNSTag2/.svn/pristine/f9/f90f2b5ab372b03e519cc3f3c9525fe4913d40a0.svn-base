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

class AppUpdateController extends AdminController{

    protected $classCategoryModel;
    protected $classModel;
    protected $classSourceModel;
    protected $FileModel;

    function _initialize()
    {
        parent::_initialize();
        $this->AppUpdateModel = D('Other/AppUpdate');
        $this->FileModel =  D('Admin/File');
    }

    //版本更新列表start
    public function AppUpdateList($page=1,$r=20)
    {
//        $positions=$this->_getPositions(1);
        list($list,$totalCount)=$this->AppUpdateModel->getListByPage('',$page,'update_time desc','*',$r);


        //拼接数据
        foreach($list as &$val){
            $val['typeText'] = $val['type'] == 1 ? 'android' : ($val['type'] == 2 ? 'ios':'');
            $val['isForceText'] = $val['is_force'] == 1 ? '强制' : ($val['is_force'] == 0 ? '非强制':'');
        }

        unset($val);

        $builder=new AdminListBuilder();
        $builder->title('版本更新列表')
            ->data($list)
            ->setSelectPostUrl(U('AppUpdate/AppUpdateList'))
            ->buttonNew(U('AppUpdate/editApp'))
            ->keyId()
            ->keyText('version','版本号')
            ->keyText('typeText','系统')
            ->keyText('isForceText','是否强制升级版本')
//            ->keyText('updateTime','更新时间')
            ->keyText('description','更新内容')
            ->keyText('url','下载地址')
            ->keyDoActionEdit('AppUpdate/editApp?id=###')
            ->keyDoActionEdit('AppUpdate/delApp?id=###','删除');

        $builder->pagination($totalCount,$r)
            ->display();
    }


    //删除
    public function delApp(){
        $appId = I('get.id');

        if($appId){
            $this->AppUpdateModel->where(['id'=>$appId])->delete();
        }

        $this->redirect('AppUpdate/AppUpdateList');
    }

    public function editApp()
    {
        set_time_limit(0);
        $aId=I('id',0,'intval');
        $title=$aId?L('_EDIT_'):L('_ADD_');
        if(IS_POST){
        
            //参数验证
            $error = "";
            if(empty($_POST['version'])){

                $error .= "版本号不可为空  ";
            }

            if(empty($_POST['description'])){

                $error .= "更新内容不可为空  ";
            }

            if($_POST['type'] == 1 && empty($_POST['file'])){
                $error .= "选择安卓时 请上传apk  ";
            }

            if($_POST['type'] == 2 && empty($_POST['iosLink'])){
                $error .= "选择苹果时 应用链接不可为空";
            }

            if(!empty($error)){

                $this->error($error);
            }

            //拼接出下载链接
            $url = '';

            if($_POST['type'] == 1){
                if(I('post.file')){
                    $fileData = $this->FileModel->find(I('post.file'));
                    $url = 'http://image.lz517.com/catering'.$fileData['savepath'].$fileData['savename'];
                }
            }

            if($_POST['type'] == 2 ){
                $url = I('post.iosLink');
            }


            $aId&&$data['id']=$aId;
            $data['version']=I('post.version');
            $data['url']=$url;
            $data['description']=I('post.description');
            $data['type']=I('post.type');
            $data['is_force']=I('post.is_force');

            if($result=$this->AppUpdateModel->editData($data)){

                S('news_home_data',null);
                $aId=$aId?$aId:$result;


                $this->success($title.L('_SUCCESS_'),U('AppUpdate/AppUpdateList'));
            }else{
                $this->error($title.L('_SUCCESS_'),$this->AppUpdateModel->getError());
            }
        }else{

            if($aId){
                $data=$this->AppUpdateModel->find($aId);
            }

            $builder=new AdminConfigBuilder();

            $builder->title($title.'版本')
                ->data($data)
                ->keyId()
                ->keyText('version','版本号')
//                ->keyText('url','下载地址')
                ->keyTextArea('description','更新内容')
                ->keyRadio('type','系统','',[1=>'android',2=>'ios'])->keyDefault('type',1)
                ->keyRadio('is_force','是否强制升级版本','',[0=>'非强制',1=>'强制'])->keyDefault('is_force',0)
                ->keyText('iosLink','应用链接(苹果)')
                ->keySingleFile('file','上传apk(安卓)')
//                ->keyAppUpdateFile('file','上传apk(安卓)')
                ->group(L('_BASIS_'),'id,version,file,description,type,iosLink,is_force')

                ->buttonSubmit()->buttonBack()
                ->newsDisplay();

        }
    }

}