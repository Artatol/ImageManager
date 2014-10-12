<?php

/**
 * This file is part of the Artatol (http://www.artatol.cz)
 * Copyright (c) 2014 Martin Charouzek (martin@charouzkovi.cz)
 */

namespace Artatol\ImageManager\DI;

use Nette;
use Artatol;
use Nette\DI\Config;
use Nette\PhpGenerator as Code;
use Aws;
use Guzzle;

if (!class_exists('Nette\DI\CompilerExtension')) {
	class_alias('Nette\Config\CompilerExtension', 'Nette\DI\CompilerExtension');
	class_alias('Nette\Config\Compiler', 'Nette\DI\Compiler');
	class_alias('Nette\Config\Helpers', 'Nette\DI\Config\Helpers');
}

if (isset(Nette\Loaders\NetteLoader::getInstance()->renamed['Nette\Configurator']) || !class_exists('Nette\Configurator')) {
	unset(Nette\Loaders\NetteLoader::getInstance()->renamed['Nette\Configurator']);
	class_alias('Nette\Config\Configurator', 'Nette\Configurator');
}

/**
 * @author Martin Charouzek <martin@charouzkovi.cz>
 */
class ImageManagerExtension extends Nette\DI\CompilerExtension {

	/**
	 * @var array
	 */
	public $defaults = array(
		'awsBucket' => null,
		'awsDirectory' => '/',
		'photoMaxWidth' => 1920,
		'photoMaxHeight' => 1080
	);

	/**
	 * @var array
	 */
	public $credentials = array(
		'key' => null,
		'secret' => null,
	);

	public function loadConfiguration() {
		$builder = $this->getContainerBuilder();
		$config = $this->getConfig($this->defaults);
		unset($config["credentials"]);

		$credentials = (isset($this->getConfig()["credentials"]) ? $this->getConfig()["credentials"] : $this->credentials);

		$builder->addDefinition($this->prefix("aws.credentials"))
				->setClass("Aws\Common\Credentials\Credentials", [$credentials["key"], $credentials["secret"]])
				->setAutowired(FALSE);		
		$builder->addDefinition($this->prefix('aws.client'))
				->setClass("Aws\S3\S3Client")
				->setFactory("Aws\S3\S3Client::factory")
				->addSetup("setCredentials", [$this->prefix("@aws.credentials")])
				->setAutowired(FALSE);
		$builder->addDefinition($this->prefix('manager'))
				->setClass('Artatol\ImageManager\Manager',[$this->prefix("@aws.client"), $config]);
				
	}

	public static function register(Nette\Configurator $configurator) {
		$configurator->onCompile[] = function ($config, Nette\DI\Compiler $compiler) {
			$compiler->addExtension('imageManger', new ImageMangerExtension());
		};
	}

}
