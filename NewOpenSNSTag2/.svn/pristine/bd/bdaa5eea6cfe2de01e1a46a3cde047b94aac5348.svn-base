<?php

namespace Common\Lib\Ice;

// require_once 'Ice.php';
// require_once APP_PATH."Common/Ice/client/order.php";
// require_once APP_PATH."Common/Ice/client/business.php";
// require_once APP_PATH."Common/Ice/client/goods.php";
// require_once APP_PATH."Common/Ice/client/pay.php";
// require_once APP_PATH."Common/Ice/client/customer.php";

use \Ice_InitializationData;

/**
 * 提供操作ICE远程调用客户端获取代理方法.
 */
class IceHelper {
	
	/**
	 * 连接、查找并返回一个ICE的RPC远程服务接口。
	 * 使用实例：
	 * 1、引入需要的程序文件：
	 *    require_once 'Ice.php';
	 *    require_once APP_PATH . "Common/Ice/OnlineBook.php";//不同的接口不同的文件
	 *    
	 * 2、引入使用的类对象：
	 *    use \rpc_OnlineBookPrxHelper;//不同的接口不同的帮助类
	 *    use \Common\Ice\IceHelper;
	 * 
	 * 3、调用：
	 *    $onlineBook = IceHelper::getProxy(rpc_OnlineBookPrxHelper,"version0.1");//可以不带版本号
	 *    $outmessage = $onlineBook->bookTick($rpcMessage);
	 * 
	 * @param $iceProxyPrxHelper ICE的RPC远程代理帮助类
	 * @param string $version ICE接口的版本号，可不传使用默认版本。
	 * @return Ice_Object 一个ICE的RPC远程服务接口。
	 */
	public static function getProxy($iceProxyPrxHelper = null, $version = null) {
		$result = null;
		if ($iceProxyPrxHelper == null) {
			return null;
		}
		$iceStaticId = $iceProxyPrxHelper::ice_staticId ();
// 		$iceStaticId = "::com::jpgk::book::rpc::OnlineBook";
		
		// 转换$iceStaticId
		$iceStaticId = ltrim ( $iceStaticId, "::" );
		$iceStaticId = str_replace ( "::", ".", $iceStaticId );
		
		$rpc_ice_config = C ( 'RPC_ICE_CONFIG' );
		$ICE = Ice_find ( $rpc_ice_config ['Ice.Communicator.Key'] );
		$isInitialize = true;
		if($ICE != null){
			$isInitialize = false;
			if($ICE->getProperties()->getProperty("Ice.Default.Locator") != $rpc_ice_config ['Ice.Default.Locator']){
// 				echo "<br>Ice_unregister ".$iceStaticId." Communicator<br>";
				Ice_unregister($rpc_ice_config ['Ice.Communicator.Key']);
				$isInitialize = true;
			}
		}
		if ($isInitialize) {
// 			echo "<br>Initialize ".$iceStaticId." Communicator<br>";
			$initData = new Ice_InitializationData ();
			
			// try {
			// $initData->properties = Ice_createProperties ();
			// $properFile = realpath ( APP_PATH ) . '/Common/Ice/onlinebookconfig.cfg';
			// $initData->properties->load ( $properFile );
			$initData->properties = Ice_getProperties ();
			$initData->properties->setProperty ( "Ice.Default.Locator", $rpc_ice_config ['Ice.Default.Locator'] );
// 			$initData->properties->setProperty ( $iceStaticId . "Proxy", $iceStaticId );
			$initData->properties->setProperty ( "Ice.Warn.Connections", $rpc_ice_config ['Ice.Warn.Connections'] );
			$ICE = Ice_initialize ( $initData );
			Ice_register ( $ICE, $rpc_ice_config ['Ice.Communicator.Key'], $rpc_ice_config ['Ice.Communicator.Expires'] );
			// } catch ( Ice_LocalException $ex ) {
			// return null;
			// // Deal with exception...
			// }
		}

		if(!empty($version)){

			$iceStaticId .= ".".$version;
			$version = null;
		}

// 		$p = $ICE->propertyToProxy ( $iceStaticId . "Proxy" );
		$p = $ICE->stringToProxy ( $iceStaticId);

		$result = $iceProxyPrxHelper::checkedCast ( $p, $version );
		return $result;
	}
}