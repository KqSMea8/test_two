<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Admin\Controller;

use Admin\Builder\AdminConfigBuilder;
use Admin\Builder\AdminListBuilder;
use Admin\Builder\AdminSortBuilder;
use Common\Model\MemberModel;
use User\Model\UcenterMemberModel;
use User\Model\JobModel;
use User\Model\GloryMemberModel;
use User\Api\UserApi;
use User\Model\AppDownloadModel;
use User\Model\UserAppOpenModel;
use User\Model\AppStatisticsModel;
use Live\Model\LiveRoomModel;
use Common\Lib\Lz517JPush;

/**
 * 后台用户控制器
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
class UserController extends AdminController
{


    protected $newsModel;
    protected $newsCategoryModel;
    protected $eventModel;
    protected $gloryMemberModel;
    protected $AppStatisticsModel;
    protected $userAppOpenModel;

    function _initialize()
    {
        parent::_initialize();
        $this->newsModel = D('News/News');
        $this->newsCategoryModel = D('News/NewsCategory');
        $this->eventModel = D('Event/Event');
        $this->userAppOpenModel = new UserAppOpenModel();
        $this->gloryMemberModel = new GloryMemberModel();
        $this->AppStatisticsModel = new AppStatisticsModel();
    }

    /**
     * 用户管理首页
     * @author 麦当苗儿 <zuojiazi@vip.qq.com>
     */
    public function index()
    {
        $nickname = I('nickname', '', 'text');
        $startTime = I('startTime');
        $endTime = I('endTime');

        $createStartTime = I('createStartTime');
        $createEndTime = I('createEndTime');

        //Sql拼接
        $map['status'] = array('egt', 0);

        //查询注册时间
        if($createStartTime && $createEndTime){
            if($createEndTime < $createStartTime){
                $this->error('查询用户注册时间数据时,注册时间结束不可小于注册时间开始！');
            }
            $map['reg_time'] = ['between',[strtotime($createStartTime.' 00:00:00'),strtotime($createEndTime.' 23:59:59')]];
        }elseif(!$createStartTime && $createEndTime){
            $this->error('查询用户注册时间数据时,注册时间开始不可为空！');
        }

        //查询启动App数据
        if($startTime && $endTime){
            if($endTime < $startTime){
                $this->error('查询用户启动App数据时,App启动结束时间不可小于App启动开始时间！');
            }
        }elseif(!$startTime && $endTime){
            $this->error('查询用户启动App数据时,App启动开始时间不可为空！');
        }

        if (is_numeric($nickname)) {
            $map['uid|nickname'] = array(intval($nickname), array('like', '%' . $nickname . '%'), '_multi' => true);
        } else {
            if ($nickname !== '') {
                $map['nickname'] = array('like', '%' . (string)$nickname . '%');
            }
        }
        $list = $this->lists('Member', $map);
        int_to_string($list);

        //查询用户进入App的次数
        $userAppOpenData = $this->userAppOpenModel->select();

        foreach($list as $key=>$v){
            $list[$key]['ext']=query_user(array('username','mobile','email','source'),$v['uid']);
            $list[$key]['reg_time'] = Date('Y-m-d H:i:s',$v['reg_time']);

            //拼接时间 筛选数据
            if($startTime && $endTime){
                foreach($userAppOpenData as $k2=>$v2){
                    if($v['uid'] == $v2['uid'] && ($startTime." 00:00:00" < $v2['create_time'] && $endTime." 23:59:59" > $v2['create_time']) ){
                        if($list[$key]['appOpen']){
                            $list[$key]['appOpen'] = $list[$key]['appOpen'] + 1;
                            $list[$key]['loginTime'] = $v2['create_time'];
                        }else{
                            $list[$key]['appOpen'] = 1;
                            $list[$key]['loginTime'] = $v2['create_time'];
                        }
                    }
                }
            }else{
                foreach($userAppOpenData as $k2=>$v2){
                    if($v['uid'] == $v2['uid']){
                        if($list[$key]['appOpen']){
                            $list[$key]['appOpen'] = $list[$key]['appOpen'] + 1;
                            $list[$key]['loginTime'] = $v2['create_time'];
                        }else{
                            $list[$key]['appOpen'] = 1;
                            $list[$key]['loginTime'] = $v2['create_time'];
                        }
                    }
                }
            }
        }

        if($createStartTime && $createEndTime){
            $createTime['createStartTime'] = $createStartTime;
            $createTime['createEndTime'] = $createEndTime;
            $this->assign('createTime',$createTime);
        }

        if($startTime && $endTime){

            $AppTime['startTime'] = $startTime;
            $AppTime['endTime'] = $endTime;
            $this->assign('AppTime',$AppTime);
        }


        $this->assign('_list',$list);

        $this->meta_title = L('_USER_INFO_');
        $this->display();
    }

    /**
     * 导出报名excel数据
     */
    public function exportExcel(){

        $map['a.status'] = ['eq', 1];
//        $data = $this->lists('Member', $map);

        $model = M('');
        $sql = "SELECT
			  a.`mobile`,
			  a.`username`,
			  a.`email`,
			  b.`brand`,
			  b.`nickname`,
			  b.`department`,
			  b.`position`,
			  b.`job`,
			  b.`login`,
			  b.`reg_time`,
			  b.`last_login_time`,
			  b.`company`
			FROM
			  `jpgk_ucenter_member` a
			  JOIN `jpgk_member` b
				ON a.`id` = b.uid
			WHERE a.`status` = 1 AND a.`id`>1";

        $data = $model->query($sql);
//        print_r($data);die;
        int_to_string($data);

        for($i = 0; $i < count ( $data ); $i ++) {
            array_unshift ( $data [$i], $i );
        }

        $excelData = [];
        $title = array (
            "row" => 1,
            "count" => 15,
            "title" => $this->getExcelHeaderData()
        );

        $num = 1;
        foreach ( $data as $k => $v ) {

            $excelData [$k] ['nickname'] = $v ['nickname'];
            $excelData [$k] ['username'] = $v ['username'];
            $excelData [$k] ['mobile'] = $v ['mobile'];
            $excelData [$k] ['email'] = $v['email'];
            $excelData [$k] ['company'] = $v ['company'];
            $excelData [$k] ['brand'] = $v ['brand'];
            $excelData [$k] ['position'] = $v ['position'];
            $excelData [$k] ['department'] = $v ['department'];
            $excelData [$k] ['job'] = $v ['job'];
            $excelData [$k] ['reg_time'] = $v ['reg_time'] == 0 ? '空' : Date('Y-m-d H:i:s',$v ['reg_time']);
            $excelData [$k] ['login'] = $v ['login'];
            $excelData [$k] ['last_login_time'] = $v ['last_login_time'] == 0 ? '空' : Date('Y-m-d H:i:s',$v ['last_login_time']);
            $num ++;
        }
        $len = count ( $excelData );
        $info = array (
            "table_name_one" =>  "用户信息数据表",
            "table_name_two" => "用户信息报表",
            "lister" => "",
            "tabletime" => date ( "Y-m-d", time () )
        );

        $optional = array (
            'colwidth' => array (
                'A' => '20',
                'B' => '30',
                'C' => '20',
                'D' => '25',
                'E' => '25',
                'F' => '30',
                'G' => '30',
                'H' => '30',
                'I' => '30',
                'J' => '30',
                'K' => '30',
                'L' => '30',
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
            '用户昵称',
            '用户名',
            '手机号码',
            '邮箱',
            '公司',
            '品牌',
            '地区',
            '部门',
            '职位',
            '注册时间',
            '登录次数',
            '最后登录时间',
        ];

        return $return;
    }

    /**
     * 重置用户密码
     * @author 黄庆
     */
    public function initPass()
    {
        $uids = I('id');
        !is_array($uids) && $uids = explode(',', $uids);
        foreach ($uids as $key => $val) {
            if (!query_user('uid', $val)) {
                unset($uids[$key]);
            }
        }
        if (!count($uids)) {
            $this->error(L('_ERROR_USER_RESET_SELECT_').L('_EXCLAMATION_'));
        }
        $ucModel = UCenterMember();
        $data = $ucModel->create(array('password' => '123456'));
        $res = $ucModel->where(array('id' => array('in', $uids)))->save(array('password' => $data['password']));
        if ($res) {
            $this->success(L('_SUCCESS_PW_RESET_').L('_EXCLAMATION_'));
        } else {
            $this->error(L('_ERROR_PW_RESET_'));
        }
    }

    /**
     * 修改用户密码
     * @author 黄庆
     */
    public function updatePass()
    {
        if(IS_POST){
            $uids = I('id');

            if(!$uids){
                $this->error('请选择要修改密码的用户！');
            }
            if(!I('post.newPwd')){
                $this->error('密码不可为空！');
            }
            if(strlen(I('post.newPwd')) < 6){
                $this->error('密码长度不可小于6位！');
            }
            $uidsArr = explode(',',$uids);

            if(!is_administrator()){
                if(in_array(1,$uidsArr)){
                    $this->error('您无权限修改管理员的密码！');
                }
            }
            !is_array($uids) && $uids = explode(',', $uids);
            foreach ($uids as $key => $val) {
                if (!query_user('uid', $val)) {
                    unset($uids[$key]);
                }
            }

            $ucModel = UCenterMember();
            $data = $ucModel->create(array('password' => I('post.newPwd')));
            $res = $ucModel->where(array('id' => array('in', $uids)))->save(array('password' => $data['password']));
            if ($res) {
                $this->success(L('_SUCCESS_PW_RESET_').L('_EXCLAMATION_'));
            } else {
                $this->error(L('_ERROR_PW_RESET_'));
            }
        }else{
            $this->assign('id',implode(",",I('get.id')));
            $this->display(T('User@Admin/updatePass'));
        }
    }

    public function changeGroup()
    {

        if ($_POST['do'] == 1) {
            //清空group
            $aAll = I('post.all', 0, 'intval');
            $aUids = I('post.uid', array(), 'intval');
            $aGids = I('post.gid', array(), 'intval');

            if ($aAll) {//设置全部用户
                $prefix = C('DB_PREFIX');
                D('')->execute("TRUNCATE TABLE {$prefix}auth_group_access");
                $aUids = UCenterMember()->getField('id', true);

            } else {
                M('AuthGroupAccess')->where(array('uid' => array('in', implode(',', $aUids))))->delete();;
            }
            foreach ($aUids as $uid) {
                foreach ($aGids as $gid) {
                    M('AuthGroupAccess')->add(array('uid' => $uid, 'group_id' => $gid));
                }
            }


            $this->success(L('_SUCCESS_'));
        } else {
            $aId = I('post.id', array(), 'intval');

            foreach ($aId as $uid) {
                $user[] = query_user(array('space_link', 'uid'), $uid);
            }


            $groups = M('AuthGroup')->where(array('status' => 1))->select();
            $this->assign('groups', $groups);
            $this->assign('users', $user);
            $this->display();
        }

    }

    /**用户扩展资料信息页
     * @param null $uid
     * @author 郑钟良<zzl@ourstu.com>
     */
    public function expandinfo_select($page = 1, $r = 20)
    {
        $nickname = I('nickname');
        $map['status'] = array('egt', 0);
        if (is_numeric($nickname)) {
            $map['uid|nickname'] = array(intval($nickname), array('like', '%' . $nickname . '%'), '_multi' => true);
        } else {
            $map['nickname'] = array('like', '%' . (string)$nickname . '%');
        }
        $list = M('Member')->where($map)->order('last_login_time desc')->page($page, $r)->select();
        $totalCount = M('Member')->where($map)->count();
        int_to_string($list);
        //扩展信息查询
        $map_profile['status'] = 1;
        $field_group = D('field_group')->where($map_profile)->select();
        $field_group_ids = array_column($field_group, 'id');
        $map_profile['profile_group_id'] = array('in', $field_group_ids);
        $fields_list = D('field_setting')->where($map_profile)->getField('id,field_name,form_type');
        $fields_list = array_combine(array_column($fields_list, 'field_name'), $fields_list);
        $fields_list = array_slice($fields_list, 0, 8);//取出前8条，用户扩展资料默认显示8条
        foreach ($list as &$tkl) {
            $tkl['id'] = $tkl['uid'];
            $map_field['uid'] = $tkl['uid'];
            foreach ($fields_list as $key => $val) {
                $map_field['field_id'] = $val['id'];
                $field_data = D('field')->where($map_field)->getField('field_data');
                if ($field_data == null || $field_data == '') {
                    $tkl[$key] = '';
                } else {
                    $tkl[$key] = $field_data;
                }
            }
        }
        $builder = new AdminListBuilder();
        $builder->title(L('_USER_EXPAND_INFO_LIST_'));
        $builder->meta_title = L('_USER_EXPAND_INFO_LIST_');
        $builder->setSearchPostUrl(U('Admin/User/expandinfo_select'))->search(L('_SEARCH_'), 'nickname', 'text', L('_PLACEHOLDER_NICKNAME_ID_'));
        $builder->keyId()->keyLink('nickname', L('_NICKNAME_'), 'User/expandinfo_details?uid=###');
        foreach ($fields_list as $vt) {
            $builder->keyText($vt['field_name'], $vt['field_name']);
        }
        $builder->data($list);
        $builder->pagination($totalCount, $r);
        $builder->display();
    }


    /**用户扩展资料详情
     * @param string $uid
     * @author 郑钟良<zzl@ourstu.com>
     */
    public function expandinfo_details($uid = 0)
    {
        if (IS_POST) {
            /* 修改积分 xjw129xjt(肖骏涛)*/
            $data = I('post.');

            //1.通过传过来的供应商名称 将供应商的标识取出  HQ
            if($data['facilitator_id']){

                $facilitatorId = implode(",",$data['facilitator_id']);

                //2.将供应商标识添加到表中
                if($facilitatorId){
                    M('Member')->where(['uid' => $data['id']])->save(['facilitator_id'=>$facilitatorId]);
                }
            }

            foreach ($data as $key => $val) {
                if (substr($key, 0, 5) == 'score') {
                    $data_score[$key] = $val;
                }
            }
            unset($key, $val);
            $res = D('Member')->where(array('uid' => $data['id']))->save($data_score);
            foreach ($data_score as $key => $val) {
                $value = query_user(array($key), $data['id']);
                if ($val == $value[$key]) {
                    continue;
                }
                D('Ucenter/Score')->addScoreLog($data['id'], cut_str('score', $key, 'l'), 'to', $val, '', 0, get_nickname(is_login()) . L('_BACKGROUND_ADJUSTMENT_'));
                D('Ucenter/Score')->cleanUserCache($data['id'], cut_str('score', $key, 'l'));
            }
            unset($key, $val);
            /* 修改积分 end*/
            /*身份设置 zzl(郑钟良)*/
            $data_role = array();
            foreach ($data as $key => $val) {
                if ($key == 'role') {
                    $data_role = explode(',', $val);
                } else if (substr($key, 0, 4) == 'role') {
                    $data_role[] = $val;
                }
            }
            unset($key, $val);
            $this->_resetUserRole($uid, $data_role);
            $this->success(L('_SUCCESS_OPERATE_').L('_EXCLAMATION_'));
            /*身份设置 end*/
        } else {
            $map['uid'] = $uid;
            $map['status'] = array('egt', 0);
            $member = M('Member')->where($map)->find();
            $member['id'] = $member['uid'];
            $member['username'] = query_user('username', $uid);
            $member['facilitator_id']=explode(',',$member['facilitator_id']);
            //扩展信息查询
            $map_profile['status'] = 1;
            $field_group = D('field_group')->where($map_profile)->select();
            $field_group_ids = array_column($field_group, 'id');
            $map_profile['profile_group_id'] = array('in', $field_group_ids);
            $fields_list = D('field_setting')->where($map_profile)->getField('id,field_name,form_type');
            $fields_list = array_combine(array_column($fields_list, 'field_name'), $fields_list);
            $map_field['uid'] = $member['uid'];
            foreach ($fields_list as $key => $val) {
                $map_field['field_id'] = $val['id'];
                $field_data = D('field')->where($map_field)->getField('field_data');
                if ($field_data == null || $field_data == '') {
                    $member[$key] = '';
                } else {
                    $member[$key] = $field_data;
                }
                $member[$key] = $field_data;
            }
            $builder = new AdminConfigBuilder();
            $builder->title(L('_USER_EXPAND_INFO_DETAIL_'));
            $builder->meta_title = L('_USER_EXPAND_INFO_DETAIL_');
            $builder->keyId()->keyReadOnly('username', L('_USER_NAME_'))->keyReadOnly('nickname', L('_NICKNAME_'));
            $field_key = array('id', 'username', 'nickname');
            foreach ($fields_list as $vt) {
                $field_key[] = $vt['field_name'];
                $builder->keyReadOnly($vt['field_name'], $vt['field_name']);
            }

            //查询微信服务商数据
            $newData = [];
            /*$facilitatorData = M('WechatFacilitator')->where(['status'=>1])->select();
            foreach($facilitatorData as &$val){
                $newData[] = [
                'id' => $val['id'],
                'name' => $val['name']
                ];
            }*/

            $facilitatorData = M('WechatKaEnterprise')->where(['status'=>1])->select();
            foreach($facilitatorData as &$val){
                $newData[] = [
                    'id' => $val['id'],
                    'name' => $val['name']
                ];
            }



            $field_key[] = 'facilitator_id';
            $builder->keyChosen('facilitator_id','所属的微信服务商',null,$newData);

            /* 积分设置 xjw129xjt(肖骏涛)*/
            $field = D('Ucenter/Score')->getTypeList(array('status' => 1));
            $score_key = array();
            foreach ($field as $vf) {
                $score_key[] = 'score' . $vf['id'];
                $builder->keyText('score' . $vf['id'], $vf['title']);
            }
            $score_data = D('Member')->where(array('uid' => $uid))->field(implode(',', $score_key))->find();
            $member = array_merge($member, $score_data);
            /*积分设置end*/
            $builder->data($member);

            /*身份设置 zzl(郑钟良)*/
            $already_role = D('UserRole')->where(array('uid' => $uid, 'status' => 1))->field('role_id')->select();
            if (count($already_role)) {
                $already_role = array_column($already_role, 'role_id');
            }
            $roleModel = D('Role');
            $role_key = array();
            $no_group_role = $roleModel->where(array('group_id' => 0, 'status' => 1))->select();
            if (count($no_group_role)) {
                $role_key[] = 'role';
                $no_group_role_options = $already_no_group_role = array();
                foreach ($no_group_role as $val) {
                    if (in_array($val['id'], $already_role)) {
                        $already_no_group_role[] = $val['id'];
                    }
                    $no_group_role_options[$val['id']] = $val['title'];
                }
                $builder->keyCheckBox('role', L('_ROLE_GROUP_NONE_'), L('_MULTI_OPTIONS_'), $no_group_role_options)->keyDefault('role', implode(',', $already_no_group_role));
            }

            $role_group = D('RoleGroup')->select();
            foreach ($role_group as $group) {
                $group_role = $roleModel->where(array('group_id' => $group['id'], 'status' => 1))->select();
                if (count($group_role)) {
                    $role_key[] = 'role' . $group['id'];
                    $group_role_options = $already_group_role = array();
                    foreach ($group_role as $val) {
                        if (in_array($val['id'], $already_role)) {
                            $already_group_role = $val['id'];
                        }
                        $group_role_options[$val['id']] = $val['title'];
                    }
                    $myJs = "$('.group_list').last().children().last().append('<a class=\"btn btn-default\" id=\"checkFalse\">".L('_SELECTION_CANCEL_')."</a>');";
                    $myJs = $myJs."$('#checkFalse').click(";
                    $myJs = $myJs."function(){ $('input[type=\"radio\"]').attr(\"checked\",false)}";
                    $myJs = $myJs.");";

                    $builder->keyRadio('role' . $group['id'], L('_ROLE_GROUP_',array('title'=>$group['title'])), L('_ROLE_GROUP_VICE_'), $group_role_options)->keyDefault('role' . $group['id'], $already_group_role)->addCustomJs($myJs);
                }
            }
            /*身份设置 end*/

            $builder->group(L('_BASIC_SETTINGS_'), implode(',', $field_key));
            $builder->group(L('_SETTINGS_SCORE_'), implode(',', $score_key));
            $builder->group(L('_SETTINGS_ROLE_'), implode(',', $role_key));
            $builder->buttonSubmit('', L('_SAVE_'));
            $builder->buttonBack();
            $builder->display();
        }

    }

    /**
     * 重新设置某一用户拥有身份
     * @param int $uid
     * @param array $haveRole
     * @return bool
     * @author 郑钟良<zzl@ourstu.com>
     */
    private function _resetUserRole($uid = 0, $haveRole = array())
    {
        $userRoleModel = D('UserRole');
        $memberModel = D('Common/Member');
        $map['uid'] = $uid;
        foreach ($haveRole as $val) {
            $map['role_id'] = $val;
            $userRole = $userRoleModel->where($map)->find();
            if ($userRole) {
                if (!$userRole['init']) {
                    $memberModel->initUserRoleInfo($val, $uid);
                }
                if ($userRole['status'] != 1) {
                    $userRoleModel->where($map)->setField('status', 1);
                }
            } else {
                $data = $map;
                $data['status'] = 1;
                $data['step'] = 'start';
                $data['init'] = 1;
                $res = $userRoleModel->add($data);
                if ($res) {
                    $memberModel->initUserRoleInfo($val, $uid);
                }
            }
        }
        $map_remove['uid'] = $uid;
        $map_remove['role_id'] = array('not in', $haveRole);
        $userRoleModel->where($map_remove)->setField('status', -1);
        $user_info = $memberModel->where(array('uid' => $uid))->find();
        if (!in_array($user_info['show_role'], $haveRole)) {
            $user_data['show_role'] = $haveRole[count($haveRole) - 1];
        }
        if (!in_array($user_info['last_login_role'], $haveRole)) {
            $user_data['last_login_role'] = $haveRole[count($haveRole) - 1];
        }
        $memberModel->where(array('uid' => $uid))->save($user_data);
        return true;
    }

    /**扩展用户信息分组列表
     * @author 郑钟良<zzl@ourstu.com>
     */
    public function profile($page = 1, $r = 20)
    {
        $map['status'] = array('egt', 0);
        $profileList = D('field_group')->where($map)->order("sort asc")->page($page, $r)->select();
        $totalCount = D('field_group')->where($map)->count();
        $builder = new AdminListBuilder();
        $builder->title(L('_GROUP_EXPAND_INFO_LIST_'));
        $builder->meta_title = L('_GROUP_EXPAND_INFO_');
        $builder->buttonNew(U('editProfile', array('id' => '0')))->buttonDelete(U('changeProfileStatus', array('status' => '-1')))->setStatusUrl(U('changeProfileStatus'))->buttonSort(U('sortProfile'));
        $builder->keyId()->keyText('profile_name', L('_GROUP_NAME_'))->keyText('sort', L('_SORT_'))->keyTime("createTime", L('_CREATE_TIME_'))->keyBool('visiable', L('_PUBLIC_IF_'));
        $builder->keyStatus()->keyDoAction('User/field?id=###', L('_FIELD_MANAGER_'))->keyDoAction('User/editProfile?id=###', L('_EDIT_'));
        $builder->data($profileList);
        $builder->pagination($totalCount, $r);
        $builder->display();
    }

    /**扩展分组排序
     * @author 郑钟良<zzl@ourstu.com>
     */
    public function sortProfile($ids = null)
    {
        if (IS_POST) {
            $builder = new AdminSortBuilder();
            $builder->doSort('Field_group', $ids);
        } else {
            $map['status'] = array('egt', 0);
            $list = D('field_group')->where($map)->order("sort asc")->select();
            foreach ($list as $key => $val) {
                $list[$key]['title'] = $val['profile_name'];
            }
            $builder = new AdminSortBuilder();
            $builder->meta_title = L('_GROUPS_SORT_');
            $builder->data($list);
            $builder->buttonSubmit(U('sortProfile'))->buttonBack();
            $builder->display();
        }
    }

    /**扩展字段列表
     * @param $id
     * @author 郑钟良<zzl@ourstu.com>
     */
    public function field($id, $page = 1, $r = 20)
    {
        $profile = D('field_group')->where('id=' . $id)->find();
        $map['status'] = array('egt', 0);
        $map['profile_group_id'] = $id;
        $field_list = D('field_setting')->where($map)->order("sort asc")->page($page, $r)->select();
        $totalCount = D('field_setting')->where($map)->count();
        $type_default = array(
            'input' => L('_ONE-WAY_TEXT_BOX_'),
            'radio' => L('_RADIO_BUTTON_'),
            'checkbox' => L('_CHECKBOX_'),
            'select' => L('_DROP-DOWN_BOX_'),
            'time' => L('_DATE_'),
            'textarea' => L('_MULTI_LINE_TEXT_BOX_')
        );
        $child_type = array(
            'string' => L('_STRING_'),
            'phone' => L('_PHONE_NUMBER_'),
            'email' => L('_MAILBOX_'),
            'number' => L('_NUMBER_'),
            'join' => L('_RELATED_FIELD_')
        );
        foreach ($field_list as &$val) {
            $val['form_type'] = $type_default[$val['form_type']];
            $val['child_form_type'] = $child_type[$val['child_form_type']];
        }
        $builder = new AdminListBuilder();
        $builder->title('【' . $profile['profile_name'] . '】 字段管理');
        $builder->meta_title = $profile['profile_name'] . L('_FIELD_MANAGEMENT_');
        $builder->buttonNew(U('editFieldSetting', array('id' => '0', 'profile_group_id' => $id)))->buttonDelete(U('setFieldSettingStatus', array('status' => '-1')))->setStatusUrl(U('setFieldSettingStatus'))->buttonSort(U('sortField', array('id' => $id)))->button(L('_RETURN_'), array('href' => U('profile')));
        $builder->keyId()->keyText('field_name', L('_FIELD_NAME_'))->keyBool('visiable', L('_OPEN_YE_OR_NO_'))->keyBool('required', L('_WHETHER_THE_REQUIRED_'))->keyText('sort', L('_SORT_'))->keyText('form_type', L('_FORM_TYPE_'))->keyText('child_form_type', L('_TWO_FORM_TYPE_'))->keyText('form_default_value', L('_DEFAULT_'))->keyText('validation', L('_FORM_VERIFICATION_MODE_'))->keyText('input_tips', L('_USER_INPUT_PROMPT_'));
        $builder->keyTime("createTime", L('_CREATE_TIME_'))->keyStatus()->keyDoAction('User/editFieldSetting?profile_group_id=' . $id . '&id=###', L('_EDIT_'));
        $builder->data($field_list);
        $builder->pagination($totalCount, $r);
        $builder->display();
    }

    /**分组排序
     * @param $id
     * @author 郑钟良<zzl@ourstu.com>
     */
    public function sortField($id = '', $ids = null)
    {
        if (IS_POST) {
            $builder = new AdminSortBuilder();
            $builder->doSort('FieldSetting', $ids);
        } else {
            $profile = D('field_group')->where('id=' . $id)->find();
            $map['status'] = array('egt', 0);
            $map['profile_group_id'] = $id;
            $list = D('field_setting')->where($map)->order("sort asc")->select();
            foreach ($list as $key => $val) {
                $list[$key]['title'] = $val['field_name'];
            }
            $builder = new AdminSortBuilder();
            $builder->meta_title = $profile['profile_name'] . L('_FIELD_SORT_');
            $builder->data($list);
            $builder->buttonSubmit(U('sortField'))->buttonBack();
            $builder->display();
        }
    }

    /**添加、编辑字段信息
     * @param $id
     * @param $profile_group_id
     * @param $field_name
     * @param $child_form_type
     * @param $visiable
     * @param $required
     * @param $form_type
     * @param $form_default_value
     * @param $validation
     * @param $input_tips
     * @author 郑钟良<zzl@ourstu.com>
     */
    public function editFieldSetting($id = 0, $profile_group_id = 0, $field_name = '', $child_form_type = 0, $visiable = 0, $required = 0, $form_type = 0, $form_default_value = '', $validation = 0, $input_tips = '')
    {
        if (IS_POST) {
            $data['field_name'] = $field_name;
            if ($data['field_name'] == '') {
                $this->error(L('_FIELD_NAME_CANNOT_BE_EMPTY_'));
            }
            $data['profile_group_id'] = $profile_group_id;
            $data['visiable'] = $visiable;
            $data['required'] = $required;
            $data['form_type'] = $form_type;
            $data['form_default_value'] = $form_default_value;
            //当表单类型为以下三种是默认值不能为空判断@MingYang
            $form_types = array('radio', 'checkbox', 'select');
            if (in_array($data['form_type'], $form_types)) {
                if ($data['form_default_value'] == '') {
                    $this->error($data['form_type'] . L('_THE_DEFAULT_VALUE_OF_THE_FORM_TYPE_CAN_NOT_BE_EMPTY_'));
                }
            }
            $data['input_tips'] = $input_tips;
            //增加当二级字段类型为join时也提交$child_form_type @MingYang
            if ($form_type == 'input') {
                $data['child_form_type'] = $child_form_type;
            } else {
                $data['child_form_type'] = '';
            }
            $data['validation'] = $validation;
            if ($id != '') {
                $res = D('field_setting')->where('id=' . $id)->save($data);
            } else {
                $map['field_name'] = $field_name;
                $map['status'] = array('egt', 0);
                $map['profile_group_id'] = $profile_group_id;
                if (D('field_setting')->where($map)->count() > 0) {
                    $this->error(L('_THIS_GROUP_ALREADY_HAS_THE_SAME_NAME_FIELD_PLEASE_USE_ANOTHER_NAME_'));
                }
                $data['status'] = 1;
                $data['createTime'] = time();
                $data['sort'] = 0;
                $res = D('field_setting')->add($data);
            }
            $role_ids = I('post.role_ids', array());
            $this->_setFieldRole($role_ids, $res, $id);
            $this->success($id == '' ? L('_ADD_FIELD_SUCCESS_') : L('_EDIT_FIELD_SUCCESS_'), U('field', array('id' => $profile_group_id)));
        } else {
            $roleOptions = D('Role')->selectByMap(array('status' => array('gt', -1)), 'id asc', 'id,title');

            $builder = new AdminConfigBuilder();
            if ($id != 0) {
                $field_setting = D('field_setting')->where('id=' . $id)->find();

                //所属身份
                $roleConfigModel = D('RoleConfig');
                $map = getRoleConfigMap('expend_field', 0);
                unset($map['role_id']);
                $map['value'] = array('like', array('%,' . $id . ',%', $id . ',%', '%,' . $id, $id), 'or');
                $already_role_id = $roleConfigModel->where($map)->field('role_id')->select();
                $already_role_id = array_column($already_role_id, 'role_id');
                $field_setting['role_ids'] = $already_role_id;
                //所属身份 end

                $builder->title(L('_MODIFY_FIELD_INFORMATION_'));
                $builder->meta_title = L('_MODIFY_FIELD_INFORMATION_');
            } else {
                $builder->title(L('_ADD_FIELD_'));
                $builder->meta_title = L('_NEW_FIELD_');
                $field_setting['profile_group_id'] = $profile_group_id;
                $field_setting['visiable'] = 1;
                $field_setting['required'] = 1;
            }
            $type_default = array(
                'input' => L('_ONE-WAY_TEXT_BOX_'),
                'radio' => L('_RADIO_BUTTON_'),
                'checkbox' => L('_CHECKBOX_'),
                'select' => L('_DROP-DOWN_BOX_'),
                'time' => L('_DATE_'),
                'textarea' => L('_MULTI_LINE_TEXT_BOX_')
            );
            $child_type = array(
                'string' => L('_STRING_'),
                'phone' => L('_PHONE_NUMBER_'),
                'email' => L('_MAILBOX_'),
                //增加可选择关联字段类型 @MingYang
                'join' => L('_RELATED_FIELD_'),
                'number' => L('_NUMBER_')
            );
            $builder->keyReadOnly("id", L('_LOGO_'))->keyReadOnly('profile_group_id', L('_GROUP_ID_'))->keyText('field_name', L('_FIELD_NAME_'))->keyChosen('role_ids', L('_POSSESSION_OF_THE_FIELD_'), L('_DETAIL_COME_TO_'), $roleOptions)->keySelect('form_type', L('_FORM_TYPE_'), '', $type_default)->keySelect('child_form_type', L('_TWO_FORM_TYPE_'), '', $child_type)->keyTextArea('form_default_value', "多个值用'|'分割开,格式【字符串：男|女，数组：1:男|2:女，关联数据表：字段名|表名】开")
                ->keyText('validation', L('_FORM_VALIDATION_RULES_'), '例：min=5&max=10')->keyText('input_tips', L('_USER_INPUT_PROMPT_'), L('_PROMPTS_THE_USER_TO_ENTER_THE_FIELD_INFORMATION_'))->keyBool('visiable', L('_OPEN_YE_OR_NO_'))->keyBool('required', L('_WHETHER_THE_REQUIRED_'));
            $builder->data($field_setting);
            $builder->buttonSubmit(U('editFieldSetting'), $id == 0 ? L('_ADD_') : L('_MODIFY_'))->buttonBack();
            $builder->display();
        }

    }

    /**设置字段状态：删除=-1，禁用=0，启用=1
     * @param $ids
     * @param $status
     * @author 郑钟良<zzl@ourstu.com>
     */
    public function setFieldSettingStatus($ids, $status)
    {
        $builder = new AdminListBuilder();
        $builder->doSetStatus('field_setting', $ids, $status);
    }

    /**设置分组状态：删除=-1，禁用=0，启用=1
     * @param $status
     * @author 郑钟良<zzl@ourstu.com>
     */
    public function changeProfileStatus($status)
    {
        $id = array_unique((array)I('ids', 0));
        if ($id[0] == 0) {
            $this->error(L('_PLEASE_CHOOSE_TO_OPERATE_THE_DATA_'));
        }
        $id = is_array($id) ? $id : explode(',', $id);
        D('field_group')->where(array('id' => array('in', $id)))->setField('status', $status);
        if ($status == -1) {
            $this->success(L('_DELETE_SUCCESS_'));
        } else if ($status == 0) {
            $this->success(L('_DISABLE_SUCCESS_'));
        } else {
            $this->success(L('_ENABLE_SUCCESS_'));
        }

    }

    /**添加、编辑分组信息
     * @param $id
     * * @param $profile_name
     * @author 郑钟良<zzl@ourstu.com>
     */
    public function editProfile($id = 0, $profile_name = '', $visiable = 1)
    {
        if (IS_POST) {
            $data['profile_name'] = $profile_name;
            $data['visiable'] = $visiable;
            if ($data['profile_name'] == '') {
                $this->error(L('_GROUP_NAME_CANNOT_BE_EMPTY_'));
            }
            if ($id != '') {
                $res = D('field_group')->where('id=' . $id)->save($data);
            } else {
                $map['profile_name'] = $profile_name;
                $map['status'] = array('egt', 0);
                if (D('field_group')->where($map)->count() > 0) {
                    $this->error(L('_ALREADY_HAS_THE_SAME_NAME_GROUP_PLEASE_USE_THE_OTHER_GROUP_NAME_'));
                }
                $data['status'] = 1;
                $data['createTime'] = time();
                $res = D('field_group')->add($data);
            }
            if ($res) {
                $this->success($id == '' ? L('_ADD_GROUP_SUCCESS_') : L('_EDIT_GROUP_SUCCESS_'), U('profile'));
            } else {
                $this->error($id == '' ? L('_ADD_GROUP_FAILURE_') : L('_EDIT_GROUP_FAILED_'));
            }
        } else {
            $builder = new AdminConfigBuilder();
            if ($id != 0) {
                $profile = D('field_group')->where('id=' . $id)->find();
                $builder->title(L('_MODIFIED_GROUP_INFORMATION_'));
                $builder->meta_title = L('_MODIFIED_GROUP_INFORMATION_');
            } else {
                $builder->title(L('_ADD_EXTENDED_INFORMATION_PACKET_'));
                $builder->meta_title = L('_NEW_GROUP_');
            }
            $builder->keyReadOnly("id", L('_LOGO_'))->keyText('profile_name', L('_GROUP_NAME_'))->keyBool('visiable', L('_OPEN_YE_OR_NO_'));
            $builder->data($profile);
            $builder->buttonSubmit(U('editProfile'), $id == 0 ? L('_ADD_') : L('_MODIFY_'))->buttonBack();
            $builder->display();
        }

    }

    /**
     * 修改昵称初始化
     * @author huajie <banhuajie@163.com>
     */
    public function updateNickname()
    {
        $nickname = M('Member')->getFieldByUid(UID, 'nickname');
        $this->assign('nickname', $nickname);
        $this->meta_title = L('_MODIFY_NICKNAME_');
        $this->display();
    }

    /**
     * 修改昵称提交
     * @author huajie <banhuajie@163.com>
     */
    public function submitNickname()
    {
        //获取参数
        $nickname = I('post.nickname');
        $password = I('post.password');
        empty($nickname) && $this->error(L('_PLEASE_ENTER_A_NICKNAME_'));
        empty($password) && $this->error(L('_PLEASE_ENTER_THE_PASSWORD_'));

        //密码验证
        $User = new UserApi();
        $uid = $User->login(UID, $password, 4);
        ($uid == -2) && $this->error(L('_INCORRECT_PASSWORD_'));

        $Member = D('Member');
        $data = $Member->create(array('nickname' => $nickname));
        if (!$data) {
            $this->error($Member->getError());
        }

        $res = $Member->where(array('uid' => $uid))->save($data);

        if ($res) {
            $user = session('user_auth');
            $user['username'] = $data['nickname'];
            session('user_auth', $user);
            session('user_auth_sign', data_auth_sign($user));
            $this->success(L('_MODIFY_NICKNAME_SUCCESS_'));
        } else {
            $this->error(L('_MODIFY_NICKNAME_FAILURE_'));
        }
    }

    /**
     * 修改密码初始化
     * @author huajie <banhuajie@163.com>
     */
    public function updatePassword()
    {
        $this->meta_title = L('_CHANGE_PASSWORD_');
        $this->display();
    }

    /**
     * 修改密码提交
     * @author huajie <banhuajie@163.com>
     */
    public function submitPassword()
    {
        //获取参数
        $password = I('post.old');
        empty($password) && $this->error(L('_PLEASE_ENTER_THE_ORIGINAL_PASSWORD_'));
        $data['password'] = I('post.password');
        empty($data['password']) && $this->error(L('_PLEASE_ENTER_A_NEW_PASSWORD_'));
        $repassword = I('post.repassword');
        empty($repassword) && $this->error(L('_PLEASE_ENTER_THE_CONFIRMATION_PASSWORD_'));

        if ($data['password'] !== $repassword) {
            $this->error(L('_YOUR_NEW_PASSWORD_IS_NOT_CONSISTENT_WITH_THE_CONFIRMATION_PASSWORD_'));
        }

        $Api = new UserApi();
        $res = $Api->updateInfo(UID, $password, $data);
        if ($res['status']) {
            $this->success(L('_CHANGE_PASSWORD_SUCCESS_'));
        } else {
            $this->error(UCenterMember()->getErrorMessage($res['info']));
        }
    }

    /**
     * 用户行为列表
     * @author huajie <banhuajie@163.com>
     */
    public function action()
    {
        // $aModule = I('post.module', '-1', 'text');
        $aModule = $this->parseSearchKey('module');

        is_null($aModule) && $aModule = -1;
        if ($aModule != -1) {
            $map['module'] = $aModule;
        }
        unset($_REQUEST['module']);
        $this->assign('current_module', $aModule);
        $map['status'] = array('gt', -1);
        //获取列表数据
        $Action = M('Action')->where(array('status' => array('gt', -1)));

        $list = $this->lists($Action, $map);
        lists_plus($list);
        int_to_string($list);
        // 记录当前列表页的cookie
        Cookie('__forward__', $_SERVER['REQUEST_URI']);
        $this->assign('_list', $list);
        $module = D('Common/Module')->getAll();
        foreach ($module as $key => $v) {
            if ($v['is_setup'] == false) {
                unset($module[$key]);
            }
        }
        $module = array_merge(array(array('name' => '', 'alias' => L('_SYSTEM_'))), $module);
        $this->assign('module', $module);

        $this->meta_title = L('_USER_BEHAVIOR_');
        $this->display();
    }

    protected function parseSearchKey($key = null)
    {
        $action = MODULE_NAME . '_' . CONTROLLER_NAME . '_' . ACTION_NAME;
        $post = I('post.');
        if (empty($post)) {
            $keywords = cookie($action);
        } else {
            $keywords = $post;
            cookie($action, $post);
            $_GET['page'] = 1;
        }

        if (!$_GET['page']) {
            cookie($action, null);
            $keywords = null;
        }
        return $key ? $keywords[$key] : $keywords;
    }

    /**
     * 新增行为
     * @author huajie <banhuajie@163.com>
     */
    public function addAction()
    {
        $this->meta_title = L('_NEW_BEHAVIOR_');


        $module = D('Module')->getAll();
        $this->assign('module', $module);
        $this->assign('data', null);
        $this->display('editaction');
    }

    /**
     * 编辑行为
     * @author huajie <banhuajie@163.com>
     */
    public function editAction()
    {
        $id = I('get.id');
        empty($id) && $this->error(L('_PARAMETERS_CANT_BE_EMPTY_'));
        $data = M('Action')->field(true)->find($id);

        $module = D('Module')->getAll();
        $this->assign('module', $module);
        $this->assign('data', $data);
        $this->meta_title = L('_EDITING_BEHAVIOR_');
        $this->display();
    }

    /**
     * 更新行为
     * @author huajie <banhuajie@163.com>
     */
    public function saveAction()
    {
        $res = D('Action')->update();
        if (!$res) {
            $this->error(D('Action')->getError());
        } else {
            $this->success($res['id'] ? L('_UPDATE_SUCCESS_') : L('_NEW_SUCCESS_'), Cookie('__forward__'));
        }
    }

    /**
     * 会员状态修改
     * @author 朱亚杰 <zhuyajie@topthink.net>
     */
    public function changeStatus($method = null)
    {
        $id = array_unique((array)I('id', 0));
        if (count(array_intersect(explode(',', C('USER_ADMINISTRATOR')), $id)) > 0) {
            $this->error(L('_DO_NOT_ALLOW_THE_SUPER_ADMINISTRATOR_TO_PERFORM_THE_OPERATION_'));
        }
        $id = is_array($id) ? implode(',', $id) : $id;
        if (empty($id)) {
            $this->error(L('_PLEASE_CHOOSE_TO_OPERATE_THE_DATA_'));
        }
        $map['uid'] = array('in', $id);
        $map1['id'] = array('in', $id);
        switch (strtolower($method)) {
            case 'forbiduser':
                $data = array('status' => 0);
                M('ucenter_member')->where($map1)->save($data);
                $this->forbid('Member', $map);
                break;
            case 'resumeuser':
                $data = array('status' => 1);
                M('ucenter_member')->where($map1)->save($data);
                $this->resume('Member', $map);
                break;
            case 'deleteuser':
                $data = array('status' => -1);
                M('ucenter_member')->where($map1)->save($data);
                $this->delete('Member', $map);
                break;
            default:
                $this->error(L('_ILLEGAL_'));

        }
    }


    /**
     * 获取用户注册错误信息
     * @param  integer $code 错误编码
     * @return string        错误信息
     */
    private function showRegError($code = 0)
    {
        switch ($code) {
            case -1:
                $error = L('_USER_NAME_MUST_BE_IN_LENGTH_') . modC('USERNAME_MIN_LENGTH', 2, 'USERCONFIG') . '-' . modC('USERNAME_MAX_LENGTH', 32, 'USERCONFIG') . L('_BETWEEN_CHARACTERS_');
                break;
            case -2:
                $error = L('_USER_NAME_IS_FORBIDDEN_TO_REGISTER_');
                break;
            case -3:
                $error = L('_USER_NAME_IS_OCCUPIED_');
                break;
            case -4:
                $error = L('_PASSWORD_LENGTH_MUST_BE_BETWEEN_6-30_CHARACTERS_');
                break;
            case -5:
                $error = L('_MAILBOX_FORMAT_IS_NOT_CORRECT_');
                break;
            case -6:
                $error = L('_MAILBOX_LENGTH_MUST_BE_BETWEEN_1-32_CHARACTERS_');
                break;
            case -7:
                $error = L('_MAILBOX_IS_PROHIBITED_TO_REGISTER_');
                break;
            case -8:
                $error = L('_MAILBOX_IS_OCCUPIED_');
                break;
            case -9:
                $error = L('_MOBILE_PHONE_FORMAT_IS_NOT_CORRECT_');
                break;
            case -10:
                $error = L('_MOBILE_PHONES_ARE_PROHIBITED_FROM_REGISTERING_');
                break;
            case -11:
                $error = L('_PHONE_NUMBER_IS_OCCUPIED_');
                break;
            case -12:
                $error = L('_USER_NAME_MY_RULE_').L('_EXCLAMATION_');
                break;
            default:
                $error = L('_UNKNOWN_ERROR_');
        }
        return $error;
    }


    public function scoreList()
    {
        //读取数据
        $map = array('status' => array('GT', -1));
        $model = D('Ucenter/Score');
        $list = $model->getTypeList($map);

        //显示页面
        $builder = new AdminListBuilder();
        $builder
            ->title(L('_INTEGRAL_TYPE_'))
            ->suggest(L('_CANNOT_DELETE_ID_4_'))
            ->buttonNew(U('editScoreType'))
            ->setStatusUrl(U('setTypeStatus'))->buttonEnable()->buttonDisable()->button(L('_DELETE_'), array('class' => 'btn ajax-post tox-confirm', 'data-confirm' => '您确实要删除积分分类吗？（删除后对应的积分将会清空，不可恢复，请谨慎删除！）', 'url' => U('delType'), 'target-form' => 'ids'))
            ->button(L('_RECHARGE_'), array('href' => U('recharge')))
            ->keyId()->keyText('title', L('_NAME_'))
            ->keyText('unit', L('_UNIT_'))->keyStatus()->keyDoActionEdit('editScoreType?id=###')
            ->data($list)
            ->display();
    }

    public function recharge()
    {
        $scoreTypes = D('Ucenter/Score')->getTypeList(array('status' => 1));
        if (IS_POST) {
            $aUids = I('post.uid');
            foreach ($scoreTypes as $v) {
                $aAction = I('post.action_score' . $v['id'], '', 'op_t');
                $aValue = I('post.value_score' . $v['id'], 0, 'intval');
                D('Ucenter/Score')->setUserScore($aUids, $aValue, $v['id'], $aAction, '', 0, L('_BACKGROUND_ADMINISTRATOR_RECHARGE_PAGE_RECHARGE_'));
                D('Ucenter/Score')->cleanUserCache($aUids, $aValue);

            }
            $this->success(L('_SET_UP_'), 'refresh');
        } else {

            $this->assign('scoreTypes', $scoreTypes);
            $this->display();
        }
    }

    public function getNickname()
    {
        $uid = I('get.uid', 0, 'intval');
        if ($uid) {
            $user = query_user(null, $uid);
            $this->ajaxReturn($user);
        } else {
            $this->ajaxReturn(null);
        }

    }

    public function setTypeStatus($ids, $status)
    {
        $builder = new AdminListBuilder();
        $builder->doSetStatus('ucenter_score_type', $ids, $status);

    }

    public function delType($ids)
    {
        $model = D('Ucenter/Score');
        $res = $model->delType($ids);
        if ($res) {
            $this->success(L('_DELETE_SUCCESS_'));
        } else {
            $this->error(L('_DELETE_FAILED_'));
        }
    }

    public function editScoreType()
    {
        $aId = I('id', 0, 'intval');
        $model = D('Ucenter/Score');
        if (IS_POST) {
            $data['title'] = I('post.title', '', 'op_t');
            $data['status'] = I('post.status', 1, 'intval');
            $data['unit'] = I('post.unit', '', 'op_t');

            if ($aId != 0) {
                $data['id'] = $aId;
                $res = $model->editType($data);
            } else {
                $res = $model->addType($data);
            }
            if ($res) {
                $this->success(($aId == 0 ? L('_ADD_') : L('_EDIT_')) . L('_SUCCESS_'));
            } else {
                $this->error(($aId == 0 ? L('_ADD_') : L('_EDIT_')) . L('_FAILURE_'));
            }
        } else {
            $builder = new AdminConfigBuilder();
            if ($aId != 0) {
                $type = $model->getType(array('id' => $aId));
            } else {
                $type = array('status' => 1, 'sort' => 0);
            }
            $builder->title(($aId == 0 ? L('_NEW_') : L('_EDIT_')) . L('_INTEGRAL_CLASSIFICATION_'))->keyId()->keyText('title', L('_NAME_'))
                ->keyText('unit', L('_UNIT_'))
                ->keySelect('status', L('_STATUS_'), null, array(-1 => L('_DELETE_'), 0 => L('_DISABLE_'), 1 => L('_ENABLE_')))
                ->data($type)
                ->buttonSubmit(U('editScoreType'))->buttonBack()->display();
        }
    }

    /**
     * 重新设置拥有字段的身份
     * @param $role_ids 身份ids
     * @param $add_id 新增字段时字段id
     * @param $edit_id 编辑字段时字段id
     * @return bool
     * @author 郑钟良<zzl@ourstu.com>
     */
    private function _setFieldRole($role_ids, $add_id, $edit_id)
    {
        $type = 'expend_field';
        $roleConfigModel = D('RoleConfig');
        $map = getRoleConfigMap($type, 0);
        if ($edit_id) {//编辑字段
            unset($map['role_id']);
            $map['value'] = array('like', array('%,' . $edit_id . ',%', $edit_id . ',%', '%,' . $edit_id, $edit_id), 'or');
            $already_role_id = $roleConfigModel->where($map)->select();
            $already_role_id = array_column($already_role_id, 'role_id');

            unset($map['value']);
            if (count($role_ids) && count($already_role_id)) {
                $need_add_role_ids = array_diff($role_ids, $already_role_id);
                $need_del_role_ids = array_diff($already_role_id, $role_ids);
            } else if (count($role_ids)) {
                $need_add_role_ids = $role_ids;
            } else {
                $need_del_role_ids = $already_role_id;
            }

            foreach ($need_add_role_ids as $val) {
                $map['role_id'] = $val;
                $oldConfig = $roleConfigModel->where($map)->find();
                if (count($oldConfig)) {
                    $oldConfig['value'] = implode(',', array_merge(explode(',', $oldConfig['value']), array($edit_id)));
                    $roleConfigModel->saveData($map, $oldConfig);
                } else {
                    $data = $map;
                    $data['value'] = $edit_id;
                    $roleConfigModel->addData($data);
                }
            }

            foreach ($need_del_role_ids as $val) {
                $map['role_id'] = $val;
                $oldConfig = $roleConfigModel->where($map)->find();
                $oldConfig['value'] = array_diff(explode(',', $oldConfig['value']), array($edit_id));
                if (count($oldConfig['value'])) {
                    $oldConfig['value'] = implode(',', $oldConfig['value']);
                    $roleConfigModel->saveData($map, $oldConfig);
                } else {
                    $roleConfigModel->deleteData($map);
                }
            }

        } else {//新增字段
            foreach ($role_ids as $val) {
                $map['role_id'] = $val;
                $oldConfig = $roleConfigModel->where($map)->find();
                if (count($oldConfig)) {
                    $oldConfig['value'] = implode(',', array_unique(array_merge(explode(',', $oldConfig['value']), array($add_id))));
                    $roleConfigModel->saveData($map, $oldConfig);
                } else {
                    $data = $map;
                    $data['value'] = $add_id;
                    $roleConfigModel->addData($data);
                }
            }
        }
        return true;
    }

    //课程列表start
    public function QTList($page=1,$r=20)
    {
//        $aCate=I('cate',0,'intval');
//        if($aCate){
//            $cates=$this->classCategoryModel->getCategoryList(array('pid'=>$aCate));
//            if(count($cates)){
//                $cates=array_column($cates,'id');
//                $cates=array_merge(array($aCate),$cates);
//                $map['category']=array('in',$cates);
//            }else{
//                $map['category']=$aCate;
//            }
//        }

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
        list($list,$totalCount)=(new UcenterMemberModel())->getListByPage($map,$page,'update_time desc','*',$r);

        $builder=new AdminListBuilder();
        $builder->title('课程列表')
            ->data($list)
//            ->setSelectPostUrl(U('Admin/News/index'))
//            ->buttonNew(U('classroom/editClass'))

            ->keyId()->keyText('username','用户名')
            ->keyText('classSource','手机号')
            ->keyText('classSource','邮箱')
            ->keyText('classSource','手机号')
            ->keyText('classSource','手机号')
            ->keyText('click_rate','地区')
            ->keyText('price','品牌名称')
            ->keyCreateTime();
//            ->keyDoActionEdit('Classroom/editClass?id=###')
//            ->keyDoActionEdit('Classroom/disableClass?id=###','启用/禁用')
//            ->keyDoActionEdit('Classroom/delClass?id=###','删除');

        $builder->pagination($totalCount,$r)
            ->display();
    }

    //职位列表start
    public function joblist($page=1,$r=20)
    {

        $map = [];
//        $map['status']=['eq',1];

//        $positions=$this->_getPositions(1);
        list($list,$totalCount)=(new JobModel())->getListByPage($map,$page,'update_time desc','*',$r);

        foreach($list as &$val){
            $val['statusText'] = $val['status'] == 1 ? '启用' : '禁用';
        }

        $builder=new AdminListBuilder();
        $builder->title('职位列表')
            ->data($list)
//            ->setSelectPostUrl(U('Admin/News/index'))
            ->buttonNew(U('user/editJob'))
//            ->ajaxButton(U('User/delJob?id=###'),"","删除",['data-confirm' => '您确定要删除数据吗?'])
            ->button(L('_DELETE_'), ['class' => 'btn ajax-post tox-confirm', 'data-confirm' => '您确定要删除数据吗?', 'url' => U('User/delJob?id=###'), 'target-form' => 'ids'])
            ->keyId()
            ->keyText('name','名称')
            ->keyText('statusText','状态')
            ->keyText('sort','排序')
            ->keyCreateTime()
            ->keyUpdateTime()
            ->keyDoActionEdit('User/editJob?id=###')
            ->keyDoActionEdit('User/disableJob?id=###','启用/禁用');
//            ->keyDoActionEdit('User/delJob?id=###','删除');

        $builder->pagination($totalCount,$r)
            ->display();
    }

    /**职位添加
     * @param int $id
     */
    public function editJob($id = 0)
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

            if ($cate = (new JobModel())->editData(I('post.'))) {
                S('SHOW_EDIT_BUTTON',null);
                $this->success($title.L('_SUCCESS_'), U('User/jobList'));
            } else {
                $this->error($title.L('_FAIL_').(new JobModel())->getError());
            }
        } else {
            $builder = new AdminConfigBuilder();

            if ($id != 0) {
                $data = (new JobModel())->find($id);
            }

            $builder->title($title.L('职位'))
                ->data($data)
                ->keyId()->keyText('name', L('_TITLE_'))
                ->keyInteger('sort',L('_SORT_'))->keyDefault('sort', 999)
                ->keySelect('status','状态',null,[1 => '启用', 0 => '禁用'])->keyDefault('status',1)
                ->buttonSubmit(U('User/editJob'))->buttonBack()
                ->display();
        }
    }

    //删除职位数据
    public function delJob(){

        $Ids = I('post.ids');
        if($Ids){
           (new JobModel())->where(['id'=>['in',$Ids]])->delete();
        }

        $this->success('删除成功');
    }

    //启用禁用职位数据
    public function disableJob(){

        $Id = I('get.id');

        if($Id){
            $jobData = (new JobModel())->where(['id'=>$Id])->find();

            $status = '';
            $jobData['status'] == '1' ? $status = '0' : ($jobData['status'] == '0' ? $status = '1':'');

            (new JobModel())->where(['id'=>$Id])->save(['status'=>$status,'update_time'=>time()]);
        }

        $this->redirect('User/joblist');
    }


    //极光推送列表
    public function JPushList($page=1,$r=20){
        $title = I("title");
        if($title){
            $map['title'] = ['like','%'.$title.'%'];
        }

        $aCate=I('cate');
        $cateVal = $aCate == 0 ? 'news' : ($aCate == 1 ? 'event' : ($aCate == 2 ? 'live' : ''));
        $map['status'] = 1;

        //cate等于0时显示 资讯的数据，为1时显示活动的数据
        if($aCate == 0){

            list($list,$totalCount)=$this->newsModel->getListByPage($map,$page,'update_time desc','*',$r);
        }elseif($aCate == 1){

            $list = $this->eventModel->where($map)->page($page, $r)->order('update_time desc')->select();
            $totalCount = $this->eventModel->where($map)->count();
        }elseif($aCate == 2){

            list($list,$totalCount)=(new LiveRoomModel())->getListByPage($map,$page,'update_time desc','*',$r);
        }

        unset($val);

        $builder=new AdminListBuilder();
        $builder->title(L('推送列表'))
            ->data($list)
            ->setSelectPostUrl(U('Admin/User/JPushList'))
            ->select('数据来源 ：','cate','select','','','',array_merge(array(array('id'=>0,'value'=>'资讯')),[["id" =>1,"value" => "活动"]],[["id" =>2,"value" => "直播"]]))

            ->keyId('id')->keyText('title',L('_TITLE_'))
            ->keyGoodsStatus('status')->keyCreateTime()

            ->buttonModalPopup(U("User/JpushForm?cate=$cateVal"),null,"发送推送信息",['data-title'=>'编辑推送信息','target-form'=>'ids'])
            ->buttonJpushModalPopup(U("User/JpushForm?cate=$cateVal"),null,"发送普通推送信息",['data-title'=>'编辑推送信息'])
//            ->buttonModalPopup(U('User/JpushForm'),"","发送推送信息")
            ->search("标题",'title','text','');

        $builder->pagination($totalCount,$r)
            ->display();
    }

    //推送信息表单
    public function JpushForm(){
        if(IS_POST){
            $ids=I('post.ids');
            $ids = explode(',',$ids);

            //参数验证
            $error = "";
            if($ids[1]){

                $error .= "不可一次操作多条数据！";
            }
            if(!$_POST['reason']){

                $error .= "推送信息不可为空！";
            }
            if(!$_POST['startTime']){

                $error .= "推送时间不可为空！";
            }

            if(!empty($error)){

                $this->error($error);
            }

            $cateVal=I('post.cateVal');
            $reason = I('post.reason');
            $startTime =I('post.startTime').':00';

            if($ids[0]){
                //1.判断如果类型为资讯时 查询 id 标题 描述
                //2.如果类型为活动时 查询 id 活动状态是否结束1为正在进行 2为过期 活动创建时间
                $needData = '';
                if($cateVal == 'news'){
                    $newsData = $this->newsModel->where(['id'=>$ids[0]])->find();
                    $needData = [
                        'id'=> $ids[0],
                        'type'=> $cateVal,
                        'title'=> $newsData['title'],
                        'description'=> $newsData['description'],
                    ];
                }elseif($cateVal == 'event'){
                    $eventData = $this->eventModel->where(['id'=>$ids[0]])->find();
                    $needData = [
                        'id'=> $ids[0],
                        'type'=> $cateVal,
                        'status'=> $eventData['eTime']>time() ? 1 : 2,
                        'create_time'=> $eventData['create_time'],
                    ];
                }elseif($cateVal == 'live'){
                    $liveData = (new LiveRoomModel())->where(['id'=>$ids[0]])->find();
                    $needData = [
                        'id'=> $ids[0],
                        'type'=> $cateVal,
                        'title'=> $liveData['title'],
                        'description'=> $liveData['description'],
                    ];
                }
            }else{
                $needData = [
                    'type'=> 'general',
                ];
            }

            $return = (new Lz517JPush()) -> pushTimingMessageForApp(['message'=>$reason,'time'=>$startTime,'needData'=>$needData]);
            if($return['http_code'] == 200){
                $this->success('推送成功');
            }else{
                $this->success('推送失败');
            }
        }else{
            $ids=I('ids');
            $cate=I('cate');
            $ids=implode(',',$ids);
            $this->assign('ids',$ids);
            $this->assign('cateVal',$cate);
            $this->display(T('User@Admin/jpushForm'));
        }
    }

    //荣耀会员列表
    public function gloryMemberList($page=1,$r=20)
    {
        $map['status']=['eq',1];

        list($list,$totalCount)=$this->gloryMemberModel->getListByPage($map,$page,'update_time desc','*',$r);

        foreach($list as &$val){
            $val['startTime'] = Date('Y-m-d H:i:s',$val['start_time']);
            $val['endTime'] = Date('Y-m-d H:i:s',$val['end_time']);
        }
        unset($val);

        $builder=new AdminListBuilder();
        $builder->title('荣耀会员时间管理列表')
            ->data($list)
            ->setSelectPostUrl(U('Admin/User/gloryMemberList'))
            ->buttonNew(U('User/editGloryMember'))
            ->keyId()
            ->keyText('startTime','开始时间')
            ->keyText('endTime','结束时间')
            ->keyStatus()->keyCreateTime()->keyUpdateTime()
            ->button(L('_DELETE_'), array('class' => 'btn ajax-post tox-confirm', 'data-confirm' => '您确实要删除吗？', 'url' => U('delGloryMember'), 'target-form' => 'ids'));
        $builder->pagination($totalCount,$r)
            ->display();
    }

    //新增
    public function editGloryMember($id = 0){

        $title=$id?L('_EDIT_'):L('_ADD_');
        if (IS_POST) {

            //参数验证
            $error = "";
            if(empty($_POST["start_time"])){

                $error .= "开始时间必填！";
            }

            if(empty($_POST["end_time"])){

                $error .= "结束时间必填！";
            }

            if($_POST["end_time"]<=$_POST["start_time"]){

                $error .= "开始时间不可大于或等于结束时间！";
            }

            if(!empty($error)){

                $this->error($error);
            }

            $_POST['status'] = 1;

            if ($cate = $this->gloryMemberModel->editData(I('post.'))) {
                S('SHOW_EDIT_BUTTON',null);
                $this->success($title.L('_SUCCESS_'), U('User/gloryMemberList'));
            } else {
                $this->error($title.L('_FAIL_').$this->gloryMemberModel->getError());
            }
        } else {
            $builder = new AdminConfigBuilder();

            if ($id != 0) {
                $data = $this->gloryMemberModel->find($id);
            }

            $builder->title($title.L('荣耀会员管理时间'))
                ->data($data)
                ->keyId()
                ->keyTime('start_time','开始时间')
                ->keyTime('end_time','结束时间')
                ->buttonSubmit(U('User/editGloryMember'))->buttonBack()
                ->display();
        }
    }

    //删除荣耀会员时间数据
    public function delGloryMember(){
        $ids = $_POST['ids'];
        $res = $this->gloryMemberModel->where(['id'=>['IN',$ids]])->save(['status'=>'-1']);
        if ($res) {
            $this->success(L('_DELETE_SUCCESS_'));
        } else {
            $this->error(L('_DELETE_FAILED_'));
        }
    }

    //后台微信扫码数统计列表
    public function AppDownloadList($page=1,$r=20)
    {
        list($list,$totalCount)=(new AppDownloadModel())->getListByPage([],$page,'create_time desc','*',$r);

        $builder=new AdminListBuilder();
        $builder->title('App扫码下载列表')
            ->data($list)
//            ->setSelectPostUrl(U('Admin/User/gloryMemberList'))
            ->keyId()
            ->keyText('sweep_num','用户扫码数')
            ->keyText('click_num','点击下载按钮的次数(微信扫码"立即下载"按钮)')
            ->keyText('download_num','下载应用次数')
            ->keyText('create_time','创建时间');
        $builder->pagination($totalCount,$r)
            ->display();
    }

    //统计列表
    public function statisticsList($page=1,$r=20)
    {
        $map['status']=['neq',-1];

        list($list,$totalCount)=$this->AppStatisticsModel->getListByPage($map,$page,'update_time desc','*',$r);

        foreach($list as &$val){
            $val['startTime'] = Date('Y-m-d H:i:s',$val['start_time']);
            $val['endTime'] = Date('Y-m-d H:i:s',$val['end_time']);
        }
        unset($val);

        $builder=new AdminListBuilder();
        $builder->title('运营推广数据管理列表')
            ->data($list)
            ->setSelectPostUrl(U('Admin/User/statisticsList'))
            ->buttonNew(U('User/editStatistics'))
            ->keyId()
            ->keyText('title','名称')
            ->keyText('app_jpk_link','jpk下载包地址')
            ->keyStatus()
            ->keyCreateTime()
            ->keyUpdateTime()
            ->button(L('_DELETE_'), array('class' => 'btn ajax-post tox-confirm', 'data-confirm' => '您确实要删除吗？', 'url' => U('delStatistics'), 'target-form' => 'ids'))
            ->ajaxButton(U('updateStatisticsStatus?status=1&id=###'),"","启用")
            ->ajaxButton(U('updateStatisticsStatus?status=2&id=###'),"","禁用")
            ->keyDoActionEdit('User/editStatistics?id=###')
            ->keyLink('查看统计列表', '查看统计列表', 'Admin/User/showUserStatisticsList?id=###');
        $builder->pagination($totalCount,$r)
            ->display();
    }

    //新增
    public function editStatistics($id = 0){

        $title=$id?L('_EDIT_'):L('_ADD_');
        if (IS_POST) {

            //参数验证
            $error = "";
            if(empty($_POST["title"])){

                $error .= "名称必填！";
            }
            if(empty($_POST["member_id"])){

                $error .= "所属人员不可为空！";
            }elseif(!is_numeric($_POST["member_id"])){

                $error .= "所属人员只可为数字！";
            }
            if(empty($_POST["app_jpk_link"])){

                $error .= "安卓jpk下载包地址不可为空！";
            }elseif(strpos(I('url')," ")){
                $error .= "安卓jpk下载包地址中不可包含空格！";
            }

            if(!empty($error)){

                $this->error($error);
            }

            $_POST['status'] = 1;

            if ($cate = $this->AppStatisticsModel->editData(I('post.'))) {
                S('SHOW_EDIT_BUTTON',null);
                $this->success($title.L('_SUCCESS_'), U('User/statisticsList'));
            } else {
                $this->error($title.L('_FAIL_').$this->AppStatisticsModel->getError());
            }
        } else {
            $builder = new AdminConfigBuilder();

            if ($id != 0) {
                $data = $this->AppStatisticsModel->find($id);
            }

            $builder->title($title.L('推广人员信息'))
                ->data($data)
                ->keyId()
                ->keyText('title','名称')
                ->keyText('member_id','所属人员(对应的用户ID)')
                ->keyText('app_jpk_link','安卓jpk下载包地址')
                ->buttonSubmit(U('User/editStatistics'))->buttonBack()
                ->display();
        }
    }

    //启用禁用推广数据
    public function updateStatisticsStatus(){
        $ids = $_POST['ids'];
        $res = $this->AppStatisticsModel->where(['id'=>['IN',$ids]])->save(['status'=>$_GET['status']]);
        if ($res) {
            $this->success('操作成功！');
        } else {
            $this->error('操作失败！');
        }
    }

    //删除推广数据
    public function delStatistics(){
        $ids = $_POST['ids'];
        $res = $this->AppStatisticsModel->where(['id'=>['IN',$ids]])->save(['status'=>'-1']);
        if ($res) {
            $this->success(L('_DELETE_SUCCESS_'));
        } else {
            $this->error(L('_DELETE_FAILED_'));
        }
    }

    //显示运营推广数据列表
    public function showUserStatisticsList($id,$page=1,$r=20){
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
}
