<?php

/**
 * Test: Nette\Configurator and services inheritance and overwriting.
 */

use Nette\Configurator,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


class MyApp extends Nette\Application\Application
{
}


$configurator = new Configurator;
$configurator->setDebugMode(FALSE);
$configurator->setTempDirectory(TEMP_DIR);
$configurator->addConfig(Tester\FileMock::create('
services:
	app2 < application: # inherits from extended application
		autowired: no

	application: # extends original application
		class: MyApp
		setup: # extends original setup
			- $errorPresenter(Error)
', 'neon'));
$container = $configurator->createContainer();


Assert::type( 'MyApp', $container->getService('application') );
Assert::true( $container->getService('application')->catchExceptions );
Assert::same( 'Error', $container->getService('application')->errorPresenter );

Assert::type( 'MyApp', $container->getService('app2') );
Assert::true( $container->getService('app2')->catchExceptions );
Assert::same( 'Error', $container->getService('app2')->errorPresenter );
