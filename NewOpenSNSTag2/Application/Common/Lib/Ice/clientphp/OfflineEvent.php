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
// Generated from file `OfflineEvent.ice'
//
// Warning: do not edit this file.
//
// </auto-generated>
//

require_once "Ice.php";
require_once APP_PATH.'Common/Lib/Ice/BaseException.php';
require_once APP_PATH.'Common/Lib/Ice/BaseService.php';
require_once APP_PATH.'Common/Lib/Ice/Page.php';
require_once APP_PATH.'Common/Lib/Ice/clientphp/model/UCenter.php';
require_once APP_PATH.'Common/Lib/Ice/clientphp/model/OfflineEventClass.php';

global $com_jpgk_catering_rpc_events__t_OfflineEventDetailV0316;
global $com_jpgk_catering_rpc_events__t_OfflineEventDetailV0316Prx;

if(!class_exists('com_jpgk_catering_rpc_events_OfflineEventDetailV0316'))
{
    class com_jpgk_catering_rpc_events_OfflineEventDetailV0316 extends com_jpgk_catering_rpc_events_OfflineEventDetail
    {
        public function __construct($title='', $viewCount=0, $startEndTime='', $deadTime='', $address='', $explain='', $picUrl='', $price=0.0, $limitCount=0, $signCount=0, $signStatus=false, $user=null, $startTime='')
        {
            parent::__construct($title, $viewCount, $startEndTime, $deadTime, $address, $explain, $picUrl, $price, $limitCount, $signCount, $signStatus, $user);
            $this->startTime = $startTime;
        }

        public static function ice_staticId()
        {
            return '::com::jpgk::catering::rpc::events::OfflineEventDetailV0316';
        }

        public function __toString()
        {
            global $com_jpgk_catering_rpc_events__t_OfflineEventDetailV0316;
            return IcePHP_stringify($this, $com_jpgk_catering_rpc_events__t_OfflineEventDetailV0316);
        }

        public $startTime;
    }

    class com_jpgk_catering_rpc_events_OfflineEventDetailV0316PrxHelper
    {
        public static function checkedCast($proxy, $facetOrCtx=null, $ctx=null)
        {
            return $proxy->ice_checkedCast('::com::jpgk::catering::rpc::events::OfflineEventDetailV0316', $facetOrCtx, $ctx);
        }

        public static function uncheckedCast($proxy, $facet=null)
        {
            return $proxy->ice_uncheckedCast('::com::jpgk::catering::rpc::events::OfflineEventDetailV0316', $facet);
        }

        public static function ice_staticId()
        {
            return '::com::jpgk::catering::rpc::events::OfflineEventDetailV0316';
        }
    }

    $com_jpgk_catering_rpc_events__t_OfflineEventDetailV0316 = IcePHP_declareClass('::com::jpgk::catering::rpc::events::OfflineEventDetailV0316');
    $com_jpgk_catering_rpc_events__t_OfflineEventDetailV0316Prx = IcePHP_declareProxy('::com::jpgk::catering::rpc::events::OfflineEventDetailV0316');

    $com_jpgk_catering_rpc_events__t_OfflineEventDetailV0316 = IcePHP_defineClass('::com::jpgk::catering::rpc::events::OfflineEventDetailV0316', 'com_jpgk_catering_rpc_events_OfflineEventDetailV0316', -1, false, false, $com_jpgk_catering_rpc_events__t_OfflineEventDetail, null, array(
        array('startTime', $IcePHP__t_string, false, 0)));

    $com_jpgk_catering_rpc_events__t_OfflineEventDetailV0316Prx = IcePHP_defineProxy($com_jpgk_catering_rpc_events__t_OfflineEventDetailV0316);
}

global $com_jpgk_catering_rpc_events__t_OfflineEventDetailV0425;
global $com_jpgk_catering_rpc_events__t_OfflineEventDetailV0425Prx;

if(!class_exists('com_jpgk_catering_rpc_events_OfflineEventDetailV0425'))
{
    class com_jpgk_catering_rpc_events_OfflineEventDetailV0425 extends com_jpgk_catering_rpc_events_OfflineEventDetailV0316
    {
        public function __construct($title='', $viewCount=0, $startEndTime='', $deadTime='', $address='', $explain='', $picUrl='', $price=0.0, $limitCount=0, $signCount=0, $signStatus=false, $user=null, $startTime='', $eventId=0, $status='', $createTime=0)
        {
            parent::__construct($title, $viewCount, $startEndTime, $deadTime, $address, $explain, $picUrl, $price, $limitCount, $signCount, $signStatus, $user, $startTime);
            $this->eventId = $eventId;
            $this->status = $status;
            $this->createTime = $createTime;
        }

        public static function ice_staticId()
        {
            return '::com::jpgk::catering::rpc::events::OfflineEventDetailV0425';
        }

        public function __toString()
        {
            global $com_jpgk_catering_rpc_events__t_OfflineEventDetailV0425;
            return IcePHP_stringify($this, $com_jpgk_catering_rpc_events__t_OfflineEventDetailV0425);
        }

        public $eventId;
        public $status;
        public $createTime;
    }

    class com_jpgk_catering_rpc_events_OfflineEventDetailV0425PrxHelper
    {
        public static function checkedCast($proxy, $facetOrCtx=null, $ctx=null)
        {
            return $proxy->ice_checkedCast('::com::jpgk::catering::rpc::events::OfflineEventDetailV0425', $facetOrCtx, $ctx);
        }

        public static function uncheckedCast($proxy, $facet=null)
        {
            return $proxy->ice_uncheckedCast('::com::jpgk::catering::rpc::events::OfflineEventDetailV0425', $facet);
        }

        public static function ice_staticId()
        {
            return '::com::jpgk::catering::rpc::events::OfflineEventDetailV0425';
        }
    }

    $com_jpgk_catering_rpc_events__t_OfflineEventDetailV0425 = IcePHP_declareClass('::com::jpgk::catering::rpc::events::OfflineEventDetailV0425');
    $com_jpgk_catering_rpc_events__t_OfflineEventDetailV0425Prx = IcePHP_declareProxy('::com::jpgk::catering::rpc::events::OfflineEventDetailV0425');

    $com_jpgk_catering_rpc_events__t_OfflineEventDetailV0425 = IcePHP_defineClass('::com::jpgk::catering::rpc::events::OfflineEventDetailV0425', 'com_jpgk_catering_rpc_events_OfflineEventDetailV0425', -1, false, false, $com_jpgk_catering_rpc_events__t_OfflineEventDetailV0316, null, array(
        array('eventId', $IcePHP__t_int, false, 0),
        array('status', $IcePHP__t_string, false, 0),
        array('createTime', $IcePHP__t_int, false, 0)));

    $com_jpgk_catering_rpc_events__t_OfflineEventDetailV0425Prx = IcePHP_defineProxy($com_jpgk_catering_rpc_events__t_OfflineEventDetailV0425);
}

global $com_jpgk_catering_rpc_events__t_OfflineEventDetailV0525;
global $com_jpgk_catering_rpc_events__t_OfflineEventDetailV0525Prx;

if(!class_exists('com_jpgk_catering_rpc_events_OfflineEventDetailV0525'))
{
    class com_jpgk_catering_rpc_events_OfflineEventDetailV0525 extends com_jpgk_catering_rpc_events_OfflineEventDetailV0425
    {
        public function __construct($title='', $viewCount=0, $startEndTime='', $deadTime='', $address='', $explain='', $picUrl='', $price=0.0, $limitCount=0, $signCount=0, $signStatus=false, $user=null, $startTime='', $eventId=0, $status='', $createTime=0, $signUpStatus=0)
        {
            parent::__construct($title, $viewCount, $startEndTime, $deadTime, $address, $explain, $picUrl, $price, $limitCount, $signCount, $signStatus, $user, $startTime, $eventId, $status, $createTime);
            $this->signUpStatus = $signUpStatus;
        }

        public static function ice_staticId()
        {
            return '::com::jpgk::catering::rpc::events::OfflineEventDetailV0525';
        }

        public function __toString()
        {
            global $com_jpgk_catering_rpc_events__t_OfflineEventDetailV0525;
            return IcePHP_stringify($this, $com_jpgk_catering_rpc_events__t_OfflineEventDetailV0525);
        }

        public $signUpStatus;
    }

    class com_jpgk_catering_rpc_events_OfflineEventDetailV0525PrxHelper
    {
        public static function checkedCast($proxy, $facetOrCtx=null, $ctx=null)
        {
            return $proxy->ice_checkedCast('::com::jpgk::catering::rpc::events::OfflineEventDetailV0525', $facetOrCtx, $ctx);
        }

        public static function uncheckedCast($proxy, $facet=null)
        {
            return $proxy->ice_uncheckedCast('::com::jpgk::catering::rpc::events::OfflineEventDetailV0525', $facet);
        }

        public static function ice_staticId()
        {
            return '::com::jpgk::catering::rpc::events::OfflineEventDetailV0525';
        }
    }

    $com_jpgk_catering_rpc_events__t_OfflineEventDetailV0525 = IcePHP_declareClass('::com::jpgk::catering::rpc::events::OfflineEventDetailV0525');
    $com_jpgk_catering_rpc_events__t_OfflineEventDetailV0525Prx = IcePHP_declareProxy('::com::jpgk::catering::rpc::events::OfflineEventDetailV0525');

    $com_jpgk_catering_rpc_events__t_OfflineEventDetailV0525 = IcePHP_defineClass('::com::jpgk::catering::rpc::events::OfflineEventDetailV0525', 'com_jpgk_catering_rpc_events_OfflineEventDetailV0525', -1, false, false, $com_jpgk_catering_rpc_events__t_OfflineEventDetailV0425, null, array(
        array('signUpStatus', $IcePHP__t_int, false, 0)));

    $com_jpgk_catering_rpc_events__t_OfflineEventDetailV0525Prx = IcePHP_defineProxy($com_jpgk_catering_rpc_events__t_OfflineEventDetailV0525);
}

global $com_jpgk_catering_rpc_events__t_OfflineEventDetailWithVote;
global $com_jpgk_catering_rpc_events__t_OfflineEventDetailWithVotePrx;

if(!class_exists('com_jpgk_catering_rpc_events_OfflineEventDetailWithVote'))
{
    class com_jpgk_catering_rpc_events_OfflineEventDetailWithVote extends com_jpgk_catering_rpc_events_OfflineEventDetailV0525
    {
        public function __construct($title='', $viewCount=0, $startEndTime='', $deadTime='', $address='', $explain='', $picUrl='', $price=0.0, $limitCount=0, $signCount=0, $signStatus=false, $user=null, $startTime='', $eventId=0, $status='', $createTime=0, $signUpStatus=0, $deadLine=0, $eventVoteDetail=null)
        {
            parent::__construct($title, $viewCount, $startEndTime, $deadTime, $address, $explain, $picUrl, $price, $limitCount, $signCount, $signStatus, $user, $startTime, $eventId, $status, $createTime, $signUpStatus);
            $this->deadLine = $deadLine;
            $this->eventVoteDetail = $eventVoteDetail;
        }

        public static function ice_staticId()
        {
            return '::com::jpgk::catering::rpc::events::OfflineEventDetailWithVote';
        }

        public function __toString()
        {
            global $com_jpgk_catering_rpc_events__t_OfflineEventDetailWithVote;
            return IcePHP_stringify($this, $com_jpgk_catering_rpc_events__t_OfflineEventDetailWithVote);
        }

        public $deadLine;
        public $eventVoteDetail;
    }

    class com_jpgk_catering_rpc_events_OfflineEventDetailWithVotePrxHelper
    {
        public static function checkedCast($proxy, $facetOrCtx=null, $ctx=null)
        {
            return $proxy->ice_checkedCast('::com::jpgk::catering::rpc::events::OfflineEventDetailWithVote', $facetOrCtx, $ctx);
        }

        public static function uncheckedCast($proxy, $facet=null)
        {
            return $proxy->ice_uncheckedCast('::com::jpgk::catering::rpc::events::OfflineEventDetailWithVote', $facet);
        }

        public static function ice_staticId()
        {
            return '::com::jpgk::catering::rpc::events::OfflineEventDetailWithVote';
        }
    }

    $com_jpgk_catering_rpc_events__t_OfflineEventDetailWithVote = IcePHP_declareClass('::com::jpgk::catering::rpc::events::OfflineEventDetailWithVote');
    $com_jpgk_catering_rpc_events__t_OfflineEventDetailWithVotePrx = IcePHP_declareProxy('::com::jpgk::catering::rpc::events::OfflineEventDetailWithVote');

    $com_jpgk_catering_rpc_events__t_OfflineEventDetailWithVote = IcePHP_defineClass('::com::jpgk::catering::rpc::events::OfflineEventDetailWithVote', 'com_jpgk_catering_rpc_events_OfflineEventDetailWithVote', -1, false, false, $com_jpgk_catering_rpc_events__t_OfflineEventDetailV0525, null, array(
        array('deadLine', $IcePHP__t_int, false, 0),
        array('eventVoteDetail', $com_jpgk_catering_rpc_events__t_EventVoteDetail, false, 0)));

    $com_jpgk_catering_rpc_events__t_OfflineEventDetailWithVotePrx = IcePHP_defineProxy($com_jpgk_catering_rpc_events__t_OfflineEventDetailWithVote);
}

global $com_jpgk_catering_rpc_events__t_OfflineEventDetailV0801;
global $com_jpgk_catering_rpc_events__t_OfflineEventDetailV0801Prx;

if(!class_exists('com_jpgk_catering_rpc_events_OfflineEventDetailV0801'))
{
    class com_jpgk_catering_rpc_events_OfflineEventDetailV0801 extends com_jpgk_catering_rpc_events_OfflineEventDetailV0525
    {
        public function __construct($title='', $viewCount=0, $startEndTime='', $deadTime='', $address='', $explain='', $picUrl='', $price=0.0, $limitCount=0, $signCount=0, $signStatus=false, $user=null, $startTime='', $eventId=0, $status='', $createTime=0, $signUpStatus=0, $description='')
        {
            parent::__construct($title, $viewCount, $startEndTime, $deadTime, $address, $explain, $picUrl, $price, $limitCount, $signCount, $signStatus, $user, $startTime, $eventId, $status, $createTime, $signUpStatus);
            $this->description = $description;
        }

        public static function ice_staticId()
        {
            return '::com::jpgk::catering::rpc::events::OfflineEventDetailV0801';
        }

        public function __toString()
        {
            global $com_jpgk_catering_rpc_events__t_OfflineEventDetailV0801;
            return IcePHP_stringify($this, $com_jpgk_catering_rpc_events__t_OfflineEventDetailV0801);
        }

        public $description;
    }

    class com_jpgk_catering_rpc_events_OfflineEventDetailV0801PrxHelper
    {
        public static function checkedCast($proxy, $facetOrCtx=null, $ctx=null)
        {
            return $proxy->ice_checkedCast('::com::jpgk::catering::rpc::events::OfflineEventDetailV0801', $facetOrCtx, $ctx);
        }

        public static function uncheckedCast($proxy, $facet=null)
        {
            return $proxy->ice_uncheckedCast('::com::jpgk::catering::rpc::events::OfflineEventDetailV0801', $facet);
        }

        public static function ice_staticId()
        {
            return '::com::jpgk::catering::rpc::events::OfflineEventDetailV0801';
        }
    }

    $com_jpgk_catering_rpc_events__t_OfflineEventDetailV0801 = IcePHP_declareClass('::com::jpgk::catering::rpc::events::OfflineEventDetailV0801');
    $com_jpgk_catering_rpc_events__t_OfflineEventDetailV0801Prx = IcePHP_declareProxy('::com::jpgk::catering::rpc::events::OfflineEventDetailV0801');

    $com_jpgk_catering_rpc_events__t_OfflineEventDetailV0801 = IcePHP_defineClass('::com::jpgk::catering::rpc::events::OfflineEventDetailV0801', 'com_jpgk_catering_rpc_events_OfflineEventDetailV0801', -1, false, false, $com_jpgk_catering_rpc_events__t_OfflineEventDetailV0525, null, array(
        array('description', $IcePHP__t_string, false, 0)));

    $com_jpgk_catering_rpc_events__t_OfflineEventDetailV0801Prx = IcePHP_defineProxy($com_jpgk_catering_rpc_events__t_OfflineEventDetailV0801);
}

global $com_jpgk_catering_rpc_events__t_OfflineEventService;
global $com_jpgk_catering_rpc_events__t_OfflineEventServicePrx;

if(!interface_exists('com_jpgk_catering_rpc_events_OfflineEventService'))
{
    interface com_jpgk_catering_rpc_events_OfflineEventService extends com_jpgk_common_rpc_BaseService
    {
        public function getOfflineTypeList();
        public function saveOfflineEvent($e, $model);
        public function getOfflineEventList($type, $province, $city, $page, $outPage);
        public function getOfflineEventDetailH5($eventId, $uid);
        public function getOfflineEventDetailWithVote($eventVoteId, $uid, $sort);
        public function getOfflineEventDetailWithVoteByUidOrCode($eventVoteId, $uid, $code, $sort);
        public function getOfflineEventDetailV0425($eventId, $uid);
        public function getOfflineEventDetailV0525($eventId, $uid);
        public function getOfflineEventDetailV0801($eventId, $uid);
        public function getOfflineEventDetailV0316($eventId, $uid);
        public function getOfflineEventDetail($eventId, $user);
        public function joinInOfflineEvent($eventId, $user);
        public function attendEvent($eventAttend);
        public function cancelEvent($uid, $eventId, $status);
        public function getOfflineEventListByUser($user, $page, $outPage);
        public function vote($eventVoteId, $eventVoteItemId, $uid);
        public function voteByCode($eventVoteId, $eventVoteItemId, $code);
        public function weChatPay($eventId, $uid, $price);
        public function getOfflineEventlWithVotes($province, $city, $page, $outPage);
        public function getOfflineEventVoteDetailH5($eventVoteId, $uid);
    }

    class com_jpgk_catering_rpc_events_OfflineEventServicePrxHelper
    {
        public static function checkedCast($proxy, $facetOrCtx=null, $ctx=null)
        {
            return $proxy->ice_checkedCast('::com::jpgk::catering::rpc::events::OfflineEventService', $facetOrCtx, $ctx);
        }

        public static function uncheckedCast($proxy, $facet=null)
        {
            return $proxy->ice_uncheckedCast('::com::jpgk::catering::rpc::events::OfflineEventService', $facet);
        }

        public static function ice_staticId()
        {
            return '::com::jpgk::catering::rpc::events::OfflineEventService';
        }
    }

    $com_jpgk_catering_rpc_events__t_OfflineEventService = IcePHP_defineClass('::com::jpgk::catering::rpc::events::OfflineEventService', 'com_jpgk_catering_rpc_events_OfflineEventService', -1, true, false, $Ice__t_Object, array($com_jpgk_common_rpc__t_BaseService), null);

    $com_jpgk_catering_rpc_events__t_OfflineEventServicePrx = IcePHP_defineProxy($com_jpgk_catering_rpc_events__t_OfflineEventService);

    IcePHP_defineOperation($com_jpgk_catering_rpc_events__t_OfflineEventService, 'getOfflineTypeList', 0, 0, 0, null, null, array($com_jpgk_catering_rpc_events__t_OfflineTypeSeq, false, 0), null);
    IcePHP_defineOperation($com_jpgk_catering_rpc_events__t_OfflineEventService, 'saveOfflineEvent', 0, 0, 0, array(array($com_jpgk_catering_rpc_events__t_OfflineEvent, false, 0), array($com_jpgk_FileSystem_rpc__t_UploadModel, false, 0)), null, array($IcePHP__t_bool, false, 0), array($com_jpgk_common_rpc__t_NullValueException));
    IcePHP_defineOperation($com_jpgk_catering_rpc_events__t_OfflineEventService, 'getOfflineEventList', 0, 0, 0, array(array($IcePHP__t_int, false, 0), array($IcePHP__t_int, false, 0), array($IcePHP__t_int, false, 0), array($com_jpgk_common_rpc__t_Page, false, 0)), array(array($com_jpgk_common_rpc__t_Page, false, 0)), array($com_jpgk_catering_rpc_events__t_offlineEventSeq, false, 0), array($com_jpgk_common_rpc__t_NullValueException));
    IcePHP_defineOperation($com_jpgk_catering_rpc_events__t_OfflineEventService, 'getOfflineEventDetailH5', 0, 0, 0, array(array($IcePHP__t_int, false, 0), array($IcePHP__t_int, false, 0)), null, array($com_jpgk_catering_rpc_events__t_OfflineEventDetailH5, false, 0), null);
    IcePHP_defineOperation($com_jpgk_catering_rpc_events__t_OfflineEventService, 'getOfflineEventDetailWithVote', 0, 0, 0, array(array($IcePHP__t_int, false, 0), array($IcePHP__t_int, false, 0), array($IcePHP__t_int, false, 0)), null, array($com_jpgk_catering_rpc_events__t_OfflineEventDetailWithVote, false, 0), null);
    IcePHP_defineOperation($com_jpgk_catering_rpc_events__t_OfflineEventService, 'getOfflineEventDetailWithVoteByUidOrCode', 0, 0, 0, array(array($IcePHP__t_int, false, 0), array($IcePHP__t_int, false, 0), array($IcePHP__t_string, false, 0), array($IcePHP__t_int, false, 0)), null, array($com_jpgk_catering_rpc_events__t_OfflineEventDetailWithVote, false, 0), null);
    IcePHP_defineOperation($com_jpgk_catering_rpc_events__t_OfflineEventService, 'getOfflineEventDetailV0425', 0, 0, 0, array(array($IcePHP__t_int, false, 0), array($IcePHP__t_int, false, 0)), null, array($com_jpgk_catering_rpc_events__t_OfflineEventDetailV0425, false, 0), null);
    IcePHP_defineOperation($com_jpgk_catering_rpc_events__t_OfflineEventService, 'getOfflineEventDetailV0525', 0, 0, 0, array(array($IcePHP__t_int, false, 0), array($IcePHP__t_int, false, 0)), null, array($com_jpgk_catering_rpc_events__t_OfflineEventDetailV0525, false, 0), null);
    IcePHP_defineOperation($com_jpgk_catering_rpc_events__t_OfflineEventService, 'getOfflineEventDetailV0801', 0, 0, 0, array(array($IcePHP__t_int, false, 0), array($IcePHP__t_int, false, 0)), null, array($com_jpgk_catering_rpc_events__t_OfflineEventDetailV0801, false, 0), null);
    IcePHP_defineOperation($com_jpgk_catering_rpc_events__t_OfflineEventService, 'getOfflineEventDetailV0316', 0, 0, 0, array(array($IcePHP__t_int, false, 0), array($IcePHP__t_int, false, 0)), null, array($com_jpgk_catering_rpc_events__t_OfflineEventDetailV0316, false, 0), null);
    IcePHP_defineOperation($com_jpgk_catering_rpc_events__t_OfflineEventService, 'getOfflineEventDetail', 0, 0, 0, array(array($IcePHP__t_int, false, 0), array($com_jpgk_catering_rpc_ucenter__t_UCenterModel, false, 0)), null, array($com_jpgk_catering_rpc_events__t_OfflineEventDetail, false, 0), array($com_jpgk_common_rpc__t_NullValueException));
    IcePHP_defineOperation($com_jpgk_catering_rpc_events__t_OfflineEventService, 'joinInOfflineEvent', 0, 0, 0, array(array($IcePHP__t_int, false, 0), array($com_jpgk_catering_rpc_ucenter__t_UCenterModel, false, 0)), null, array($IcePHP__t_bool, false, 0), array($com_jpgk_common_rpc__t_NullValueException));
    IcePHP_defineOperation($com_jpgk_catering_rpc_events__t_OfflineEventService, 'attendEvent', 0, 0, 0, array(array($com_jpgk_catering_rpc_events__t_OfflineEventAttend, false, 0)), null, array($com_jpgk_common_rpc__t_ResponseModel, false, 0), null);
    IcePHP_defineOperation($com_jpgk_catering_rpc_events__t_OfflineEventService, 'cancelEvent', 0, 0, 0, array(array($IcePHP__t_int, false, 0), array($IcePHP__t_int, false, 0), array($com_jpgk_catering_rpc_events__t_EventAttendStatus, false, 0)), null, array($com_jpgk_common_rpc__t_ResponseModel, false, 0), null);
    IcePHP_defineOperation($com_jpgk_catering_rpc_events__t_OfflineEventService, 'getOfflineEventListByUser', 0, 0, 0, array(array($com_jpgk_catering_rpc_ucenter__t_UCenterModel, false, 0), array($com_jpgk_common_rpc__t_Page, false, 0)), array(array($com_jpgk_common_rpc__t_Page, false, 0)), array($com_jpgk_catering_rpc_events__t_offlineEventSeq, false, 0), array($com_jpgk_common_rpc__t_NullValueException));
    IcePHP_defineOperation($com_jpgk_catering_rpc_events__t_OfflineEventService, 'vote', 0, 0, 0, array(array($IcePHP__t_int, false, 0), array($IcePHP__t_int, false, 0), array($IcePHP__t_int, false, 0)), null, array($com_jpgk_common_rpc__t_ResponseModel, false, 0), null);
    IcePHP_defineOperation($com_jpgk_catering_rpc_events__t_OfflineEventService, 'voteByCode', 0, 0, 0, array(array($IcePHP__t_int, false, 0), array($IcePHP__t_int, false, 0), array($IcePHP__t_string, false, 0)), null, array($com_jpgk_common_rpc__t_ResponseModel, false, 0), null);
    IcePHP_defineOperation($com_jpgk_catering_rpc_events__t_OfflineEventService, 'weChatPay', 0, 0, 0, array(array($IcePHP__t_int, false, 0), array($IcePHP__t_int, false, 0), array($IcePHP__t_double, false, 0)), null, array($com_jpgk_common_rpc__t_ResponseModel, false, 0), null);
    IcePHP_defineOperation($com_jpgk_catering_rpc_events__t_OfflineEventService, 'getOfflineEventlWithVotes', 0, 0, 0, array(array($IcePHP__t_int, false, 0), array($IcePHP__t_int, false, 0), array($com_jpgk_common_rpc__t_Page, false, 0)), array(array($com_jpgk_common_rpc__t_Page, false, 0)), array($com_jpgk_catering_rpc_events__t_OfflineEventlWithVoteSeq, false, 0), null);
    IcePHP_defineOperation($com_jpgk_catering_rpc_events__t_OfflineEventService, 'getOfflineEventVoteDetailH5', 0, 0, 0, array(array($IcePHP__t_int, false, 0), array($IcePHP__t_int, false, 0)), null, array($com_jpgk_catering_rpc_events__t_OfflineEventDetailH5, false, 0), null);
}
?>