<?php
	require 'php-sdk/facebook.php';
	$facebook = new Facebook(array(
		'appId' => '124593811064320',
		'secret' => '21edb9fabde67d1f0718e13ddcc2ac4f'
	));

	setcookie('fbs_'.$facebook->getAppId(),' ',time()-100,'/','atetnowski.com/facebook');
	$facebook->destroySession();
	header('Location: index.php');
?>