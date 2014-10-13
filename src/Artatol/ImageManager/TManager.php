<?php

namespace Artatol\ImageManager;

use Nette;

/**
 * @author Martin Charouzek <martin@charouzkovi.cz>
 */
trait TManager
{

	/** @var \Artatol\ImageManager\Manager @inject */
	public $manager;

	/**
	 * @param string $class
	 * @return Nette\Templating\FileTemplate
	 */
	protected function createTemplate($class = NULL)
	{
		$template = parent::createTemplate($class);
		$template->_img = $this->manager;
		return $template;
	}

}