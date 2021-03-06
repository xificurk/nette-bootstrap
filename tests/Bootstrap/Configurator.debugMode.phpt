<?php

/**
 * Test: Nette\Configurator and production mode.
 */

use Nette\Configurator,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';



test(function(){
	unset($_SERVER['HTTP_X_FORWARDED_FOR']);
	$_SERVER['REMOTE_ADDR'] = 'xx';

	$configurator = new Configurator;
	Assert::false( $configurator->isDebugMode() );

	$configurator->setDebugMode(TRUE);
	Assert::true( $configurator->isDebugMode() );

	$configurator->setDebugMode(FALSE);
	Assert::false( $configurator->isDebugMode() );

	$configurator->setDebugMode($_SERVER['REMOTE_ADDR']);
	Assert::true( $configurator->isDebugMode() );
});


test(function(){ // localhost
	unset($_SERVER['HTTP_X_FORWARDED_FOR']);

	$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
	Assert::true( Configurator::detectDebugMode() );
	Assert::true( Configurator::detectDebugMode('192.168.1.1') );

	$_SERVER['REMOTE_ADDR'] = '::1';
	Assert::true( Configurator::detectDebugMode() );

	$_SERVER['REMOTE_ADDR'] = '192.168.1.1';
	Assert::false( Configurator::detectDebugMode() );
	Assert::false( Configurator::detectDebugMode('192.168.1.1.0') );
	Assert::true( Configurator::detectDebugMode('192.168.1.1') );
	Assert::true( Configurator::detectDebugMode('a,192.168.1.1,b') );
	Assert::true( Configurator::detectDebugMode('a 192.168.1.1 b') );

	Assert::false( Configurator::detectDebugMode(array()) );
	Assert::true( Configurator::detectDebugMode(array('192.168.1.1')) );
});


test(function(){ // localhost + proxy
	$_SERVER['HTTP_X_FORWARDED_FOR'] = 'xx';

	$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
	Assert::false( Configurator::detectDebugMode() );

	$_SERVER['REMOTE_ADDR'] = '::1';
	Assert::false( Configurator::detectDebugMode() );

	$_SERVER['REMOTE_ADDR'] = '192.168.1.1';
	Assert::false( Configurator::detectDebugMode() );
	Assert::true( Configurator::detectDebugMode($_SERVER['REMOTE_ADDR']) );
});


test(function(){ // missing $_SERVER['REMOTE_ADDR']
	unset($_SERVER['HTTP_X_FORWARDED_FOR'], $_SERVER['REMOTE_ADDR']);

	Assert::false( Configurator::detectDebugMode() );
	Assert::false( Configurator::detectDebugMode('127.0.0.1') );

	Assert::true( Configurator::detectDebugMode(php_uname('n')) );
	Assert::true( Configurator::detectDebugMode(array(php_uname('n'))) );
});


test(function(){ // secret
	unset($_SERVER['HTTP_X_FORWARDED_FOR']);
	$_SERVER['REMOTE_ADDR'] = '192.168.1.1';
	$_COOKIE[Configurator::COOKIE_SECRET] = '*secret*';

	Assert::false( Configurator::detectDebugMode() );
	Assert::true( Configurator::detectDebugMode('192.168.1.1') );
	Assert::false( Configurator::detectDebugMode('abc@192.168.1.1') );
	Assert::true( Configurator::detectDebugMode('*secret*@192.168.1.1') );

	$_COOKIE[Configurator::COOKIE_SECRET] = array('*secret*');
	Assert::false( Configurator::detectDebugMode('*secret*@192.168.1.1') );
});
