<?php
require_once('arc2/ARC2.php');

/**
* Abstract class to provide ARC features in local DB for all subclasses
* only one place to withhold db configurations
*/
abstract class DBHelper
{
	
	function __construct()
	{
		//nothing
	}

	//Local Store for Content Negotiation
	protected function _getLocalStore($name)
	{
		$config = array(
			/* db */
			'db_host' => 'localhost',
			'db_name' => 'mi8',
			'db_user' => 'root',
			'db_pwd' => '',
			/* store */
			'store_name' => $name,
			/* stop after 100 errors */
			'max_errors' => 100
		);
		$store = ARC2::getStore($config);
		if (!$store->isSetUp()) $store->setUp();
		return $store;
	}

	/**
	* Setup remote SPARQL Endpoint
	* !Could be obsolete, because oneliner
	*/
	protected function _getRemoteStore($url)
	{
		$store = ARC2::getRemoteStore(array('remote_store_endpoint' => $url));
		return $store;
	}
}