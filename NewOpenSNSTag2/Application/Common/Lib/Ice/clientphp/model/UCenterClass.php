<?php
// **********************************************************************
//
// Copyright (c) 2003-2015 ZeroC, Inc. All rights reserved.
//
// This copy of Ice is licensed to you under the terms described in the
// ICE_LICENSE file included in this distribution.
//
// **********************************************************************
//
// Ice version 3.6.1
//
// <auto-generated>
//
// Generated from file `UCenterClass.ice'
//
// Warning: do not edit this file.
//
// </auto-generated>
//

require_once APP_PATH.'Common/Lib/Ice/BaseException.php';
require_once APP_PATH.'Common/Lib/Ice/BaseService.php';
require_once APP_PATH.'Common/Lib/Ice/FileSystem.php';
require_once APP_PATH.'Common/Lib/Ice/ResponseModel.php';

global $com_jpgk_catering_rpc_ucenter__t_UCenterModel;
global $com_jpgk_catering_rpc_ucenter__t_UCenterModelPrx;

if(!class_exists('com_jpgk_catering_rpc_ucenter_UCenterModel'))
{
    class com_jpgk_catering_rpc_ucenter_UCenterModel extends Ice_ObjectImpl
    {
        public function __construct($uid=0, $username='', $token='', $lastLoginTime=0)
        {
            $this->uid = $uid;
            $this->username = $username;
            $this->token = $token;
            $this->lastLoginTime = $lastLoginTime;
        }

        public static function ice_staticId()
        {
            return '::com::jpgk::catering::rpc::ucenter::UCenterModel';
        }

        public function __toString()
        {
            global $com_jpgk_catering_rpc_ucenter__t_UCenterModel;
            return IcePHP_stringify($this, $com_jpgk_catering_rpc_ucenter__t_UCenterModel);
        }

        public $uid;
        public $username;
        public $token;
        public $lastLoginTime;
    }

    class com_jpgk_catering_rpc_ucenter_UCenterModelPrxHelper
    {
        public static function checkedCast($proxy, $facetOrCtx=null, $ctx=null)
        {
            return $proxy->ice_checkedCast('::com::jpgk::catering::rpc::ucenter::UCenterModel', $facetOrCtx, $ctx);
        }

        public static function uncheckedCast($proxy, $facet=null)
        {
            return $proxy->ice_uncheckedCast('::com::jpgk::catering::rpc::ucenter::UCenterModel', $facet);
        }

        public static function ice_staticId()
        {
            return '::com::jpgk::catering::rpc::ucenter::UCenterModel';
        }
    }

    $com_jpgk_catering_rpc_ucenter__t_UCenterModel = IcePHP_defineClass('::com::jpgk::catering::rpc::ucenter::UCenterModel', 'com_jpgk_catering_rpc_ucenter_UCenterModel', -1, false, false, $Ice__t_Object, null, array(
        array('uid', $IcePHP__t_int, false, 0),
        array('username', $IcePHP__t_string, false, 0),
        array('token', $IcePHP__t_string, false, 0),
        array('lastLoginTime', $IcePHP__t_int, false, 0)));

    $com_jpgk_catering_rpc_ucenter__t_UCenterModelPrx = IcePHP_defineProxy($com_jpgk_catering_rpc_ucenter__t_UCenterModel);
}

global $com_jpgk_catering_rpc_ucenter__t_UserinfoModel;
global $com_jpgk_catering_rpc_ucenter__t_UserinfoModelPrx;

if(!class_exists('com_jpgk_catering_rpc_ucenter_UserinfoModel'))
{
    class com_jpgk_catering_rpc_ucenter_UserinfoModel extends Ice_ObjectImpl
    {
        public function __construct($uid=0, $username='', $nickname='', $score1=0, $avatar='', $level='', $signature='', $ans=0, $following=0, $sex=0, $posProvince=0, $posCity=0, $posDistrict=0, $birthday='', $brand='', $department='', $position='', $job='', $phone='', $company='')
        {
            $this->uid = $uid;
            $this->username = $username;
            $this->nickname = $nickname;
            $this->score1 = $score1;
            $this->avatar = $avatar;
            $this->level = $level;
            $this->signature = $signature;
            $this->ans = $ans;
            $this->following = $following;
            $this->sex = $sex;
            $this->posProvince = $posProvince;
            $this->posCity = $posCity;
            $this->posDistrict = $posDistrict;
            $this->birthday = $birthday;
            $this->brand = $brand;
            $this->department = $department;
            $this->position = $position;
            $this->job = $job;
            $this->phone = $phone;
            $this->company = $company;
        }

        public static function ice_staticId()
        {
            return '::com::jpgk::catering::rpc::ucenter::UserinfoModel';
        }

        public function __toString()
        {
            global $com_jpgk_catering_rpc_ucenter__t_UserinfoModel;
            return IcePHP_stringify($this, $com_jpgk_catering_rpc_ucenter__t_UserinfoModel);
        }

        public $uid;
        public $username;
        public $nickname;
        public $score1;
        public $avatar;
        public $level;
        public $signature;
        public $ans;
        public $following;
        public $sex;
        public $posProvince;
        public $posCity;
        public $posDistrict;
        public $birthday;
        public $brand;
        public $department;
        public $position;
        public $job;
        public $phone;
        public $company;
    }

    class com_jpgk_catering_rpc_ucenter_UserinfoModelPrxHelper
    {
        public static function checkedCast($proxy, $facetOrCtx=null, $ctx=null)
        {
            return $proxy->ice_checkedCast('::com::jpgk::catering::rpc::ucenter::UserinfoModel', $facetOrCtx, $ctx);
        }

        public static function uncheckedCast($proxy, $facet=null)
        {
            return $proxy->ice_uncheckedCast('::com::jpgk::catering::rpc::ucenter::UserinfoModel', $facet);
        }

        public static function ice_staticId()
        {
            return '::com::jpgk::catering::rpc::ucenter::UserinfoModel';
        }
    }

    $com_jpgk_catering_rpc_ucenter__t_UserinfoModel = IcePHP_defineClass('::com::jpgk::catering::rpc::ucenter::UserinfoModel', 'com_jpgk_catering_rpc_ucenter_UserinfoModel', -1, false, false, $Ice__t_Object, null, array(
        array('uid', $IcePHP__t_int, false, 0),
        array('username', $IcePHP__t_string, false, 0),
        array('nickname', $IcePHP__t_string, false, 0),
        array('score1', $IcePHP__t_int, false, 0),
        array('avatar', $IcePHP__t_string, false, 0),
        array('level', $IcePHP__t_string, false, 0),
        array('signature', $IcePHP__t_string, false, 0),
        array('ans', $IcePHP__t_int, false, 0),
        array('following', $IcePHP__t_int, false, 0),
        array('sex', $IcePHP__t_int, false, 0),
        array('posProvince', $IcePHP__t_int, false, 0),
        array('posCity', $IcePHP__t_int, false, 0),
        array('posDistrict', $IcePHP__t_int, false, 0),
        array('birthday', $IcePHP__t_string, false, 0),
        array('brand', $IcePHP__t_string, false, 0),
        array('department', $IcePHP__t_string, false, 0),
        array('position', $IcePHP__t_string, false, 0),
        array('job', $IcePHP__t_string, false, 0),
        array('phone', $IcePHP__t_string, false, 0),
        array('company', $IcePHP__t_string, false, 0)));

    $com_jpgk_catering_rpc_ucenter__t_UserinfoModelPrx = IcePHP_defineProxy($com_jpgk_catering_rpc_ucenter__t_UserinfoModel);
}

global $com_jpgk_catering_rpc_ucenter__t_UserRegistModel;
global $com_jpgk_catering_rpc_ucenter__t_UserRegistModelPrx;

if(!class_exists('com_jpgk_catering_rpc_ucenter_UserRegistModel'))
{
    class com_jpgk_catering_rpc_ucenter_UserRegistModel extends Ice_ObjectImpl
    {
        public function __construct($username='', $position='', $pwd='', $verify='', $brand='', $department='', $job='', $company='', $nickName='')
        {
            $this->username = $username;
            $this->position = $position;
            $this->pwd = $pwd;
            $this->verify = $verify;
            $this->brand = $brand;
            $this->department = $department;
            $this->job = $job;
            $this->company = $company;
            $this->nickName = $nickName;
        }

        public static function ice_staticId()
        {
            return '::com::jpgk::catering::rpc::ucenter::UserRegistModel';
        }

        public function __toString()
        {
            global $com_jpgk_catering_rpc_ucenter__t_UserRegistModel;
            return IcePHP_stringify($this, $com_jpgk_catering_rpc_ucenter__t_UserRegistModel);
        }

        public $username;
        public $position;
        public $pwd;
        public $verify;
        public $brand;
        public $department;
        public $job;
        public $company;
        public $nickName;
    }

    class com_jpgk_catering_rpc_ucenter_UserRegistModelPrxHelper
    {
        public static function checkedCast($proxy, $facetOrCtx=null, $ctx=null)
        {
            return $proxy->ice_checkedCast('::com::jpgk::catering::rpc::ucenter::UserRegistModel', $facetOrCtx, $ctx);
        }

        public static function uncheckedCast($proxy, $facet=null)
        {
            return $proxy->ice_uncheckedCast('::com::jpgk::catering::rpc::ucenter::UserRegistModel', $facet);
        }

        public static function ice_staticId()
        {
            return '::com::jpgk::catering::rpc::ucenter::UserRegistModel';
        }
    }

    $com_jpgk_catering_rpc_ucenter__t_UserRegistModel = IcePHP_defineClass('::com::jpgk::catering::rpc::ucenter::UserRegistModel', 'com_jpgk_catering_rpc_ucenter_UserRegistModel', -1, false, false, $Ice__t_Object, null, array(
        array('username', $IcePHP__t_string, false, 0),
        array('position', $IcePHP__t_string, false, 0),
        array('pwd', $IcePHP__t_string, false, 0),
        array('verify', $IcePHP__t_string, false, 0),
        array('brand', $IcePHP__t_string, false, 0),
        array('department', $IcePHP__t_string, false, 0),
        array('job', $IcePHP__t_string, false, 0),
        array('company', $IcePHP__t_string, false, 0),
        array('nickName', $IcePHP__t_string, false, 0)));

    $com_jpgk_catering_rpc_ucenter__t_UserRegistModelPrx = IcePHP_defineProxy($com_jpgk_catering_rpc_ucenter__t_UserRegistModel);
}
?>