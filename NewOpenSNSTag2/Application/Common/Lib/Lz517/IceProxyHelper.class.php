<?php

namespace Common\Lib\Lz517;
use Common\Lib\Ice\IceHelper;

/**
 * Class IceProxyHelper
 *
 *
 * @package Common\Lib\Lz517
 */
class IceProxyHelper
{
    private static $version = 'V1.0.3';
    public static function getGxptDetail(){

        try{
            require_once APP_PATH."Common/Lib/Ice/clientphp/SupplyMarketing.php";

            return IceHelper::getProxy(com_jpgk_catering_rpc_supplymarketing_SupplyMarketingServicePrxHelper,self::$version);
        }catch (\Exception $e){

            print_r($e);die;
//            var_dump($e);die();
        }
    }

    public static function getGoodsDetail(){

        try{
            require_once APP_PATH."Common/Lib/Ice/clientphp/SecondhandMarket.php";

            return IceHelper::getProxy(com_jpgk_catering_rpc_secondhandmarket_SecondHandServicePrxHelper,self::$version);
        }catch (\Exception $e){

            print_r($e);die;
//            var_dump($e);die();
        }
    }

    public static function getComment(){

        try{
            require_once APP_PATH."Common/Lib/Ice/clientphp/Comment.php";

            return IceHelper::getProxy(com_jpgk_catering_rpc_comment_CommentServicePrxHelper,self::$version);
        }catch (\Exception $e){

//            print_r($e);die;
            var_dump($e);die();
        }
    }

    public static function getEventDetail(){

        try{
            require_once APP_PATH."Common/Lib/Ice/clientphp/OfflineEvent.php";

            return IceHelper::getProxy(com_jpgk_catering_rpc_events_OfflineEventServicePrxHelper,self::$version);
        }catch (\Exception $e){

            print_r($e);die;
//            var_dump($e);die();
        }
    }

    public static function getCommon(){

        try{
            require_once APP_PATH."Common/Lib/Ice/Common.php";

            return IceHelper::getProxy(com_jpgk_catering_rpc_common_CommonServicePrxHelper,self::$version);
        }catch (\Exception $e){

            print_r($e);die;
//            var_dump($e);die();
        }
    }
}