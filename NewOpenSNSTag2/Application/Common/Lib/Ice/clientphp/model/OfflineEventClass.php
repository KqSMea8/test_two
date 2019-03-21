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
// Generated from file `OfflineEventClass.ice'
//
// Warning: do not edit this file.
//
// </auto-generated>
//

require_once APP_PATH.'Common/Lib/Ice/BaseException.php';
require_once APP_PATH.'Common/Lib/Ice/BaseService.php';
require_once APP_PATH.'Common/Lib/Ice/Page.php';
require_once APP_PATH.'Common/Lib/Ice/clientphp/model/UCenter.php';

global $com_jpgk_catering_rpc_events__t_EventException;

if(!class_exists('com_jpgk_catering_rpc_events_EventException'))
{
    class com_jpgk_catering_rpc_events_EventException extends com_jpgk_common_rpc_BaseException
    {
        public function __construct($reason='', $userMessage='')
        {
            parent::__construct($reason, $userMessage);
        }

        public function ice_name()
        {
            return 'com::jpgk::catering::rpc::events::EventException';
        }

        public function __toString()
        {
            global $com_jpgk_catering_rpc_events__t_EventException;
            return IcePHP_stringifyException($this, $com_jpgk_catering_rpc_events__t_EventException);
        }
    }

    $com_jpgk_catering_rpc_events__t_EventException = IcePHP_defineException('::com::jpgk::catering::rpc::events::EventException', 'com_jpgk_catering_rpc_events_EventException', false, $com_jpgk_common_rpc__t_BaseException, null);
}

global $com_jpgk_catering_rpc_events__t_OfflineType;
global $com_jpgk_catering_rpc_events__t_OfflineTypePrx;

if(!class_exists('com_jpgk_catering_rpc_events_OfflineType'))
{
    class com_jpgk_catering_rpc_events_OfflineType extends Ice_ObjectImpl
    {
        public function __construct($id=0, $title='')
        {
            $this->id = $id;
            $this->title = $title;
        }

        public static function ice_staticId()
        {
            return '::com::jpgk::catering::rpc::events::OfflineType';
        }

        public function __toString()
        {
            global $com_jpgk_catering_rpc_events__t_OfflineType;
            return IcePHP_stringify($this, $com_jpgk_catering_rpc_events__t_OfflineType);
        }

        public $id;
        public $title;
    }

    class com_jpgk_catering_rpc_events_OfflineTypePrxHelper
    {
        public static function checkedCast($proxy, $facetOrCtx=null, $ctx=null)
        {
            return $proxy->ice_checkedCast('::com::jpgk::catering::rpc::events::OfflineType', $facetOrCtx, $ctx);
        }

        public static function uncheckedCast($proxy, $facet=null)
        {
            return $proxy->ice_uncheckedCast('::com::jpgk::catering::rpc::events::OfflineType', $facet);
        }

        public static function ice_staticId()
        {
            return '::com::jpgk::catering::rpc::events::OfflineType';
        }
    }

    $com_jpgk_catering_rpc_events__t_OfflineType = IcePHP_defineClass('::com::jpgk::catering::rpc::events::OfflineType', 'com_jpgk_catering_rpc_events_OfflineType', -1, false, false, $Ice__t_Object, null, array(
        array('id', $IcePHP__t_int, false, 0),
        array('title', $IcePHP__t_string, false, 0)));

    $com_jpgk_catering_rpc_events__t_OfflineTypePrx = IcePHP_defineProxy($com_jpgk_catering_rpc_events__t_OfflineType);
}

global $com_jpgk_catering_rpc_events__t_OfflineTypeSeq;

if(!isset($com_jpgk_catering_rpc_events__t_OfflineTypeSeq))
{
    $com_jpgk_catering_rpc_events__t_OfflineTypeSeq = IcePHP_defineSequence('::com::jpgk::catering::rpc::events::OfflineTypeSeq', $com_jpgk_catering_rpc_events__t_OfflineType);
}

global $com_jpgk_catering_rpc_events__t_OfflineEvent;
global $com_jpgk_catering_rpc_events__t_OfflineEventPrx;

if(!class_exists('com_jpgk_catering_rpc_events_OfflineEvent'))
{
    class com_jpgk_catering_rpc_events_OfflineEvent extends Ice_ObjectImpl
    {
        public function __construct($title='', $startTime='', $endTime='', $address='', $explain='', $picUrl='', $limitCount=0, $isSignType=false, $deadTime='', $user=null)
        {
            $this->title = $title;
            $this->startTime = $startTime;
            $this->endTime = $endTime;
            $this->address = $address;
            $this->explain = $explain;
            $this->picUrl = $picUrl;
            $this->limitCount = $limitCount;
            $this->isSignType = $isSignType;
            $this->deadTime = $deadTime;
            $this->user = $user;
        }

        public static function ice_staticId()
        {
            return '::com::jpgk::catering::rpc::events::OfflineEvent';
        }

        public function __toString()
        {
            global $com_jpgk_catering_rpc_events__t_OfflineEvent;
            return IcePHP_stringify($this, $com_jpgk_catering_rpc_events__t_OfflineEvent);
        }

        public $title;
        public $startTime;
        public $endTime;
        public $address;
        public $explain;
        public $picUrl;
        public $limitCount;
        public $isSignType;
        public $deadTime;
        public $user;
    }

    class com_jpgk_catering_rpc_events_OfflineEventPrxHelper
    {
        public static function checkedCast($proxy, $facetOrCtx=null, $ctx=null)
        {
            return $proxy->ice_checkedCast('::com::jpgk::catering::rpc::events::OfflineEvent', $facetOrCtx, $ctx);
        }

        public static function uncheckedCast($proxy, $facet=null)
        {
            return $proxy->ice_uncheckedCast('::com::jpgk::catering::rpc::events::OfflineEvent', $facet);
        }

        public static function ice_staticId()
        {
            return '::com::jpgk::catering::rpc::events::OfflineEvent';
        }
    }

    $com_jpgk_catering_rpc_events__t_OfflineEvent = IcePHP_declareClass('::com::jpgk::catering::rpc::events::OfflineEvent');
    $com_jpgk_catering_rpc_events__t_OfflineEventPrx = IcePHP_declareProxy('::com::jpgk::catering::rpc::events::OfflineEvent');

    $com_jpgk_catering_rpc_events__t_OfflineEvent = IcePHP_defineClass('::com::jpgk::catering::rpc::events::OfflineEvent', 'com_jpgk_catering_rpc_events_OfflineEvent', -1, false, false, $Ice__t_Object, null, array(
        array('title', $IcePHP__t_string, false, 0),
        array('startTime', $IcePHP__t_string, false, 0),
        array('endTime', $IcePHP__t_string, false, 0),
        array('address', $IcePHP__t_string, false, 0),
        array('explain', $IcePHP__t_string, false, 0),
        array('picUrl', $IcePHP__t_string, false, 0),
        array('limitCount', $IcePHP__t_int, false, 0),
        array('isSignType', $IcePHP__t_bool, false, 0),
        array('deadTime', $IcePHP__t_string, false, 0),
        array('user', $com_jpgk_catering_rpc_ucenter__t_UCenterModel, false, 0)));

    $com_jpgk_catering_rpc_events__t_OfflineEventPrx = IcePHP_defineProxy($com_jpgk_catering_rpc_events__t_OfflineEvent);
}

global $com_jpgk_catering_rpc_events__t_OfflineEventItem;
global $com_jpgk_catering_rpc_events__t_OfflineEventItemPrx;

if(!class_exists('com_jpgk_catering_rpc_events_OfflineEventItem'))
{
    class com_jpgk_catering_rpc_events_OfflineEventItem extends Ice_ObjectImpl
    {
        public function __construct($user=null, $eventId=0, $status='', $picUrl='', $title='', $startTime='', $address='', $price=0.0, $createTime=0, $replyCount='', $partake=false)
        {
            $this->user = $user;
            $this->eventId = $eventId;
            $this->status = $status;
            $this->picUrl = $picUrl;
            $this->title = $title;
            $this->startTime = $startTime;
            $this->address = $address;
            $this->price = $price;
            $this->createTime = $createTime;
            $this->replyCount = $replyCount;
            $this->partake = $partake;
        }

        public static function ice_staticId()
        {
            return '::com::jpgk::catering::rpc::events::OfflineEventItem';
        }

        public function __toString()
        {
            global $com_jpgk_catering_rpc_events__t_OfflineEventItem;
            return IcePHP_stringify($this, $com_jpgk_catering_rpc_events__t_OfflineEventItem);
        }

        public $user;
        public $eventId;
        public $status;
        public $picUrl;
        public $title;
        public $startTime;
        public $address;
        public $price;
        public $createTime;
        public $replyCount;
        public $partake;
    }

    class com_jpgk_catering_rpc_events_OfflineEventItemPrxHelper
    {
        public static function checkedCast($proxy, $facetOrCtx=null, $ctx=null)
        {
            return $proxy->ice_checkedCast('::com::jpgk::catering::rpc::events::OfflineEventItem', $facetOrCtx, $ctx);
        }

        public static function uncheckedCast($proxy, $facet=null)
        {
            return $proxy->ice_uncheckedCast('::com::jpgk::catering::rpc::events::OfflineEventItem', $facet);
        }

        public static function ice_staticId()
        {
            return '::com::jpgk::catering::rpc::events::OfflineEventItem';
        }
    }

    $com_jpgk_catering_rpc_events__t_OfflineEventItem = IcePHP_declareClass('::com::jpgk::catering::rpc::events::OfflineEventItem');
    $com_jpgk_catering_rpc_events__t_OfflineEventItemPrx = IcePHP_declareProxy('::com::jpgk::catering::rpc::events::OfflineEventItem');

    $com_jpgk_catering_rpc_events__t_OfflineEventItem = IcePHP_defineClass('::com::jpgk::catering::rpc::events::OfflineEventItem', 'com_jpgk_catering_rpc_events_OfflineEventItem', -1, false, false, $Ice__t_Object, null, array(
        array('user', $com_jpgk_catering_rpc_ucenter__t_UserinfoModel, false, 0),
        array('eventId', $IcePHP__t_int, false, 0),
        array('status', $IcePHP__t_string, false, 0),
        array('picUrl', $IcePHP__t_string, false, 0),
        array('title', $IcePHP__t_string, false, 0),
        array('startTime', $IcePHP__t_string, false, 0),
        array('address', $IcePHP__t_string, false, 0),
        array('price', $IcePHP__t_double, false, 0),
        array('createTime', $IcePHP__t_int, false, 0),
        array('replyCount', $IcePHP__t_string, false, 0),
        array('partake', $IcePHP__t_bool, false, 0)));

    $com_jpgk_catering_rpc_events__t_OfflineEventItemPrx = IcePHP_defineProxy($com_jpgk_catering_rpc_events__t_OfflineEventItem);
}

global $com_jpgk_catering_rpc_events__t_offlineEventSeq;

if(!isset($com_jpgk_catering_rpc_events__t_offlineEventSeq))
{
    $com_jpgk_catering_rpc_events__t_offlineEventSeq = IcePHP_defineSequence('::com::jpgk::catering::rpc::events::offlineEventSeq', $com_jpgk_catering_rpc_events__t_OfflineEventItem);
}

global $com_jpgk_catering_rpc_events__t_OfflineEventDetail;
global $com_jpgk_catering_rpc_events__t_OfflineEventDetailPrx;

if(!class_exists('com_jpgk_catering_rpc_events_OfflineEventDetail'))
{
    class com_jpgk_catering_rpc_events_OfflineEventDetail extends Ice_ObjectImpl
    {
        public function __construct($title='', $viewCount=0, $startEndTime='', $deadTime='', $address='', $explain='', $picUrl='', $price=0.0, $limitCount=0, $signCount=0, $signStatus=false, $user=null)
        {
            $this->title = $title;
            $this->viewCount = $viewCount;
            $this->startEndTime = $startEndTime;
            $this->deadTime = $deadTime;
            $this->address = $address;
            $this->explain = $explain;
            $this->picUrl = $picUrl;
            $this->price = $price;
            $this->limitCount = $limitCount;
            $this->signCount = $signCount;
            $this->signStatus = $signStatus;
            $this->user = $user;
        }

        public static function ice_staticId()
        {
            return '::com::jpgk::catering::rpc::events::OfflineEventDetail';
        }

        public function __toString()
        {
            global $com_jpgk_catering_rpc_events__t_OfflineEventDetail;
            return IcePHP_stringify($this, $com_jpgk_catering_rpc_events__t_OfflineEventDetail);
        }

        public $title;
        public $viewCount;
        public $startEndTime;
        public $deadTime;
        public $address;
        public $explain;
        public $picUrl;
        public $price;
        public $limitCount;
        public $signCount;
        public $signStatus;
        public $user;
    }

    class com_jpgk_catering_rpc_events_OfflineEventDetailPrxHelper
    {
        public static function checkedCast($proxy, $facetOrCtx=null, $ctx=null)
        {
            return $proxy->ice_checkedCast('::com::jpgk::catering::rpc::events::OfflineEventDetail', $facetOrCtx, $ctx);
        }

        public static function uncheckedCast($proxy, $facet=null)
        {
            return $proxy->ice_uncheckedCast('::com::jpgk::catering::rpc::events::OfflineEventDetail', $facet);
        }

        public static function ice_staticId()
        {
            return '::com::jpgk::catering::rpc::events::OfflineEventDetail';
        }
    }

    $com_jpgk_catering_rpc_events__t_OfflineEventDetail = IcePHP_declareClass('::com::jpgk::catering::rpc::events::OfflineEventDetail');
    $com_jpgk_catering_rpc_events__t_OfflineEventDetailPrx = IcePHP_declareProxy('::com::jpgk::catering::rpc::events::OfflineEventDetail');

    $com_jpgk_catering_rpc_events__t_OfflineEventDetail = IcePHP_defineClass('::com::jpgk::catering::rpc::events::OfflineEventDetail', 'com_jpgk_catering_rpc_events_OfflineEventDetail', -1, false, false, $Ice__t_Object, null, array(
        array('title', $IcePHP__t_string, false, 0),
        array('viewCount', $IcePHP__t_int, false, 0),
        array('startEndTime', $IcePHP__t_string, false, 0),
        array('deadTime', $IcePHP__t_string, false, 0),
        array('address', $IcePHP__t_string, false, 0),
        array('explain', $IcePHP__t_string, false, 0),
        array('picUrl', $IcePHP__t_string, false, 0),
        array('price', $IcePHP__t_double, false, 0),
        array('limitCount', $IcePHP__t_int, false, 0),
        array('signCount', $IcePHP__t_int, false, 0),
        array('signStatus', $IcePHP__t_bool, false, 0),
        array('user', $com_jpgk_catering_rpc_ucenter__t_UserinfoModel, false, 0)));

    $com_jpgk_catering_rpc_events__t_OfflineEventDetailPrx = IcePHP_defineProxy($com_jpgk_catering_rpc_events__t_OfflineEventDetail);
}

global $com_jpgk_catering_rpc_events__t_OfflineEventDetailH5;
global $com_jpgk_catering_rpc_events__t_OfflineEventDetailH5Prx;

if(!class_exists('com_jpgk_catering_rpc_events_OfflineEventDetailH5'))
{
    class com_jpgk_catering_rpc_events_OfflineEventDetailH5 extends Ice_ObjectImpl
    {
        public function __construct($title='', $description='', $signUpStatus=0, $signStatus=0, $h5Url='', $limitCount=0, $signCount=0, $startEndTime='')
        {
            $this->title = $title;
            $this->description = $description;
            $this->signUpStatus = $signUpStatus;
            $this->signStatus = $signStatus;
            $this->h5Url = $h5Url;
            $this->limitCount = $limitCount;
            $this->signCount = $signCount;
            $this->startEndTime = $startEndTime;
        }

        public static function ice_staticId()
        {
            return '::com::jpgk::catering::rpc::events::OfflineEventDetailH5';
        }

        public function __toString()
        {
            global $com_jpgk_catering_rpc_events__t_OfflineEventDetailH5;
            return IcePHP_stringify($this, $com_jpgk_catering_rpc_events__t_OfflineEventDetailH5);
        }

        public $title;
        public $description;
        public $signUpStatus;
        public $signStatus;
        public $h5Url;
        public $limitCount;
        public $signCount;
        public $startEndTime;
    }

    class com_jpgk_catering_rpc_events_OfflineEventDetailH5PrxHelper
    {
        public static function checkedCast($proxy, $facetOrCtx=null, $ctx=null)
        {
            return $proxy->ice_checkedCast('::com::jpgk::catering::rpc::events::OfflineEventDetailH5', $facetOrCtx, $ctx);
        }

        public static function uncheckedCast($proxy, $facet=null)
        {
            return $proxy->ice_uncheckedCast('::com::jpgk::catering::rpc::events::OfflineEventDetailH5', $facet);
        }

        public static function ice_staticId()
        {
            return '::com::jpgk::catering::rpc::events::OfflineEventDetailH5';
        }
    }

    $com_jpgk_catering_rpc_events__t_OfflineEventDetailH5 = IcePHP_defineClass('::com::jpgk::catering::rpc::events::OfflineEventDetailH5', 'com_jpgk_catering_rpc_events_OfflineEventDetailH5', -1, false, false, $Ice__t_Object, null, array(
        array('title', $IcePHP__t_string, false, 0),
        array('description', $IcePHP__t_string, false, 0),
        array('signUpStatus', $IcePHP__t_int, false, 0),
        array('signStatus', $IcePHP__t_int, false, 0),
        array('h5Url', $IcePHP__t_string, false, 0),
        array('limitCount', $IcePHP__t_int, false, 0),
        array('signCount', $IcePHP__t_int, false, 0),
        array('startEndTime', $IcePHP__t_string, false, 0)));

    $com_jpgk_catering_rpc_events__t_OfflineEventDetailH5Prx = IcePHP_defineProxy($com_jpgk_catering_rpc_events__t_OfflineEventDetailH5);
}

global $com_jpgk_catering_rpc_events__t_OfflineEventAttend;
global $com_jpgk_catering_rpc_events__t_OfflineEventAttendPrx;

if(!class_exists('com_jpgk_catering_rpc_events_OfflineEventAttend'))
{
    class com_jpgk_catering_rpc_events_OfflineEventAttend extends Ice_ObjectImpl
    {
        public function __construct($name='', $phone='', $brand='', $department='', $uid=0, $eventId=0, $position='', $job='')
        {
            $this->name = $name;
            $this->phone = $phone;
            $this->brand = $brand;
            $this->department = $department;
            $this->uid = $uid;
            $this->eventId = $eventId;
            $this->position = $position;
            $this->job = $job;
        }

        public static function ice_staticId()
        {
            return '::com::jpgk::catering::rpc::events::OfflineEventAttend';
        }

        public function __toString()
        {
            global $com_jpgk_catering_rpc_events__t_OfflineEventAttend;
            return IcePHP_stringify($this, $com_jpgk_catering_rpc_events__t_OfflineEventAttend);
        }

        public $name;
        public $phone;
        public $brand;
        public $department;
        public $uid;
        public $eventId;
        public $position;
        public $job;
    }

    class com_jpgk_catering_rpc_events_OfflineEventAttendPrxHelper
    {
        public static function checkedCast($proxy, $facetOrCtx=null, $ctx=null)
        {
            return $proxy->ice_checkedCast('::com::jpgk::catering::rpc::events::OfflineEventAttend', $facetOrCtx, $ctx);
        }

        public static function uncheckedCast($proxy, $facet=null)
        {
            return $proxy->ice_uncheckedCast('::com::jpgk::catering::rpc::events::OfflineEventAttend', $facet);
        }

        public static function ice_staticId()
        {
            return '::com::jpgk::catering::rpc::events::OfflineEventAttend';
        }
    }

    $com_jpgk_catering_rpc_events__t_OfflineEventAttend = IcePHP_defineClass('::com::jpgk::catering::rpc::events::OfflineEventAttend', 'com_jpgk_catering_rpc_events_OfflineEventAttend', -1, false, false, $Ice__t_Object, null, array(
        array('name', $IcePHP__t_string, false, 0),
        array('phone', $IcePHP__t_string, false, 0),
        array('brand', $IcePHP__t_string, false, 0),
        array('department', $IcePHP__t_string, false, 0),
        array('uid', $IcePHP__t_int, false, 0),
        array('eventId', $IcePHP__t_int, false, 0),
        array('position', $IcePHP__t_string, false, 0),
        array('job', $IcePHP__t_string, false, 0)));

    $com_jpgk_catering_rpc_events__t_OfflineEventAttendPrx = IcePHP_defineProxy($com_jpgk_catering_rpc_events__t_OfflineEventAttend);
}

global $com_jpgk_catering_rpc_events__t_EventVoteItem;
global $com_jpgk_catering_rpc_events__t_EventVoteItemPrx;

if(!class_exists('com_jpgk_catering_rpc_events_EventVoteItem'))
{
    class com_jpgk_catering_rpc_events_EventVoteItem extends Ice_ObjectImpl
    {
        public function __construct($id=0, $name='', $description='', $reason='', $pictureUrl='', $voteNum=0, $ifCanVote=0, $showType=0)
        {
            $this->id = $id;
            $this->name = $name;
            $this->description = $description;
            $this->reason = $reason;
            $this->pictureUrl = $pictureUrl;
            $this->voteNum = $voteNum;
            $this->ifCanVote = $ifCanVote;
            $this->showType = $showType;
        }

        public static function ice_staticId()
        {
            return '::com::jpgk::catering::rpc::events::EventVoteItem';
        }

        public function __toString()
        {
            global $com_jpgk_catering_rpc_events__t_EventVoteItem;
            return IcePHP_stringify($this, $com_jpgk_catering_rpc_events__t_EventVoteItem);
        }

        public $id;
        public $name;
        public $description;
        public $reason;
        public $pictureUrl;
        public $voteNum;
        public $ifCanVote;
        public $showType;
    }

    class com_jpgk_catering_rpc_events_EventVoteItemPrxHelper
    {
        public static function checkedCast($proxy, $facetOrCtx=null, $ctx=null)
        {
            return $proxy->ice_checkedCast('::com::jpgk::catering::rpc::events::EventVoteItem', $facetOrCtx, $ctx);
        }

        public static function uncheckedCast($proxy, $facet=null)
        {
            return $proxy->ice_uncheckedCast('::com::jpgk::catering::rpc::events::EventVoteItem', $facet);
        }

        public static function ice_staticId()
        {
            return '::com::jpgk::catering::rpc::events::EventVoteItem';
        }
    }

    $com_jpgk_catering_rpc_events__t_EventVoteItem = IcePHP_defineClass('::com::jpgk::catering::rpc::events::EventVoteItem', 'com_jpgk_catering_rpc_events_EventVoteItem', -1, false, false, $Ice__t_Object, null, array(
        array('id', $IcePHP__t_int, false, 0),
        array('name', $IcePHP__t_string, false, 0),
        array('description', $IcePHP__t_string, false, 0),
        array('reason', $IcePHP__t_string, false, 0),
        array('pictureUrl', $IcePHP__t_string, false, 0),
        array('voteNum', $IcePHP__t_int, false, 0),
        array('ifCanVote', $IcePHP__t_int, false, 0),
        array('showType', $IcePHP__t_int, false, 0)));

    $com_jpgk_catering_rpc_events__t_EventVoteItemPrx = IcePHP_defineProxy($com_jpgk_catering_rpc_events__t_EventVoteItem);
}

global $com_jpgk_catering_rpc_events__t_EventVoteItemSeq;

if(!isset($com_jpgk_catering_rpc_events__t_EventVoteItemSeq))
{
    $com_jpgk_catering_rpc_events__t_EventVoteItemSeq = IcePHP_defineSequence('::com::jpgk::catering::rpc::events::EventVoteItemSeq', $com_jpgk_catering_rpc_events__t_EventVoteItem);
}

global $com_jpgk_catering_rpc_events__t_EventVoteDetail;
global $com_jpgk_catering_rpc_events__t_EventVoteDetailPrx;

if(!class_exists('com_jpgk_catering_rpc_events_EventVoteDetail'))
{
    class com_jpgk_catering_rpc_events_EventVoteDetail extends Ice_ObjectImpl
    {
        public function __construct($id=0, $title='', $statusShow='', $deadLine=0, $rule='', $eventVoteItems=null, $userNum=0)
        {
            $this->id = $id;
            $this->title = $title;
            $this->statusShow = $statusShow;
            $this->deadLine = $deadLine;
            $this->rule = $rule;
            $this->eventVoteItems = $eventVoteItems;
            $this->userNum = $userNum;
        }

        public static function ice_staticId()
        {
            return '::com::jpgk::catering::rpc::events::EventVoteDetail';
        }

        public function __toString()
        {
            global $com_jpgk_catering_rpc_events__t_EventVoteDetail;
            return IcePHP_stringify($this, $com_jpgk_catering_rpc_events__t_EventVoteDetail);
        }

        public $id;
        public $title;
        public $statusShow;
        public $deadLine;
        public $rule;
        public $eventVoteItems;
        public $userNum;
    }

    class com_jpgk_catering_rpc_events_EventVoteDetailPrxHelper
    {
        public static function checkedCast($proxy, $facetOrCtx=null, $ctx=null)
        {
            return $proxy->ice_checkedCast('::com::jpgk::catering::rpc::events::EventVoteDetail', $facetOrCtx, $ctx);
        }

        public static function uncheckedCast($proxy, $facet=null)
        {
            return $proxy->ice_uncheckedCast('::com::jpgk::catering::rpc::events::EventVoteDetail', $facet);
        }

        public static function ice_staticId()
        {
            return '::com::jpgk::catering::rpc::events::EventVoteDetail';
        }
    }

    $com_jpgk_catering_rpc_events__t_EventVoteDetail = IcePHP_declareClass('::com::jpgk::catering::rpc::events::EventVoteDetail');
    $com_jpgk_catering_rpc_events__t_EventVoteDetailPrx = IcePHP_declareProxy('::com::jpgk::catering::rpc::events::EventVoteDetail');

    $com_jpgk_catering_rpc_events__t_EventVoteDetail = IcePHP_defineClass('::com::jpgk::catering::rpc::events::EventVoteDetail', 'com_jpgk_catering_rpc_events_EventVoteDetail', -1, false, false, $Ice__t_Object, null, array(
        array('id', $IcePHP__t_int, false, 0),
        array('title', $IcePHP__t_string, false, 0),
        array('statusShow', $IcePHP__t_string, false, 0),
        array('deadLine', $IcePHP__t_int, false, 0),
        array('rule', $IcePHP__t_string, false, 0),
        array('eventVoteItems', $com_jpgk_catering_rpc_events__t_EventVoteItemSeq, false, 0),
        array('userNum', $IcePHP__t_int, false, 0)));

    $com_jpgk_catering_rpc_events__t_EventVoteDetailPrx = IcePHP_defineProxy($com_jpgk_catering_rpc_events__t_EventVoteDetail);
}

global $com_jpgk_catering_rpc_events__t_EventVoteDetailSeq;

if(!isset($com_jpgk_catering_rpc_events__t_EventVoteDetailSeq))
{
    $com_jpgk_catering_rpc_events__t_EventVoteDetailSeq = IcePHP_defineSequence('::com::jpgk::catering::rpc::events::EventVoteDetailSeq', $com_jpgk_catering_rpc_events__t_EventVoteDetail);
}

global $com_jpgk_catering_rpc_events__t_OfflineEventlWithVote;
global $com_jpgk_catering_rpc_events__t_OfflineEventlWithVotePrx;

if(!class_exists('com_jpgk_catering_rpc_events_OfflineEventlWithVote'))
{
    class com_jpgk_catering_rpc_events_OfflineEventlWithVote extends Ice_ObjectImpl
    {
        public function __construct($deadTime='', $title='', $statusShow='', $picUrl='', $eventVoteDetails=null)
        {
            $this->deadTime = $deadTime;
            $this->title = $title;
            $this->statusShow = $statusShow;
            $this->picUrl = $picUrl;
            $this->eventVoteDetails = $eventVoteDetails;
        }

        public static function ice_staticId()
        {
            return '::com::jpgk::catering::rpc::events::OfflineEventlWithVote';
        }

        public function __toString()
        {
            global $com_jpgk_catering_rpc_events__t_OfflineEventlWithVote;
            return IcePHP_stringify($this, $com_jpgk_catering_rpc_events__t_OfflineEventlWithVote);
        }

        public $deadTime;
        public $title;
        public $statusShow;
        public $picUrl;
        public $eventVoteDetails;
    }

    class com_jpgk_catering_rpc_events_OfflineEventlWithVotePrxHelper
    {
        public static function checkedCast($proxy, $facetOrCtx=null, $ctx=null)
        {
            return $proxy->ice_checkedCast('::com::jpgk::catering::rpc::events::OfflineEventlWithVote', $facetOrCtx, $ctx);
        }

        public static function uncheckedCast($proxy, $facet=null)
        {
            return $proxy->ice_uncheckedCast('::com::jpgk::catering::rpc::events::OfflineEventlWithVote', $facet);
        }

        public static function ice_staticId()
        {
            return '::com::jpgk::catering::rpc::events::OfflineEventlWithVote';
        }
    }

    $com_jpgk_catering_rpc_events__t_OfflineEventlWithVote = IcePHP_declareClass('::com::jpgk::catering::rpc::events::OfflineEventlWithVote');
    $com_jpgk_catering_rpc_events__t_OfflineEventlWithVotePrx = IcePHP_declareProxy('::com::jpgk::catering::rpc::events::OfflineEventlWithVote');

    $com_jpgk_catering_rpc_events__t_OfflineEventlWithVote = IcePHP_defineClass('::com::jpgk::catering::rpc::events::OfflineEventlWithVote', 'com_jpgk_catering_rpc_events_OfflineEventlWithVote', -1, false, false, $Ice__t_Object, null, array(
        array('deadTime', $IcePHP__t_string, false, 0),
        array('title', $IcePHP__t_string, false, 0),
        array('statusShow', $IcePHP__t_string, false, 0),
        array('picUrl', $IcePHP__t_string, false, 0),
        array('eventVoteDetails', $com_jpgk_catering_rpc_events__t_EventVoteDetailSeq, false, 0)));

    $com_jpgk_catering_rpc_events__t_OfflineEventlWithVotePrx = IcePHP_defineProxy($com_jpgk_catering_rpc_events__t_OfflineEventlWithVote);
}

global $com_jpgk_catering_rpc_events__t_OfflineEventlWithVoteSeq;

if(!isset($com_jpgk_catering_rpc_events__t_OfflineEventlWithVoteSeq))
{
    $com_jpgk_catering_rpc_events__t_OfflineEventlWithVoteSeq = IcePHP_defineSequence('::com::jpgk::catering::rpc::events::OfflineEventlWithVoteSeq', $com_jpgk_catering_rpc_events__t_OfflineEventlWithVote);
}

global $com_jpgk_catering_rpc_events__t_EventAttendStatus;

if(!class_exists('com_jpgk_catering_rpc_events_EventAttendStatus'))
{
    class com_jpgk_catering_rpc_events_EventAttendStatus
    {
        const JOIN = 0;
        const ATTEND = 1;
        const CANCELL = 2;
    }

    $com_jpgk_catering_rpc_events__t_EventAttendStatus = IcePHP_defineEnum('::com::jpgk::catering::rpc::events::EventAttendStatus', array('JOIN', 0, 'ATTEND', 1, 'CANCELL', 2));
}
?>