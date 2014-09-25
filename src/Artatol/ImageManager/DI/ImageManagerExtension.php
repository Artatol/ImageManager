<?php

/**
 * This file is part of the Artatol (http://www.artatol.cz)
 * Copyright (c) 2014 Martin Charouzek (martin@charouzkovi.cz)
 */

namespace Artatol\ImageManager\DI;

use Nette;
use Nette\DI\Config;
use Nette\PhpGenerator as Code;

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
		'photo' => ["maxWidth" => 1920, "maxHeight"=> 1080],
		'awsBucket' => null,
		'awsKey' => null,
		'awsSecret' => null
	);

	public function loadConfiguration() {
		$builder = $this->getContainerBuilder();
		$config = $this->getConfig($this->defaults);
	}

	public static function register(Nette\Configurator $configurator) {
		$configurator->onCompile[] = function ($config, Nette\DI\Compiler $compiler) {
			$compiler->addExtension('imageManger', new ImageMangerExtension());
		};
	}

}
