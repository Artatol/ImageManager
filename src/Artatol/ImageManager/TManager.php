<?php

namespace Artatol\ImageManager;

use Nette;

/**
 * @author Martin Charouzek <martin@charouzkovi.cz>
 */
trait TManager
{

	/** @var manager */
	public $manager;


	/**
	 * @param Manager $manager
	 */
	public function injectManager(Manager $manager)
	{
		$this->manager = $manager;
	}


	/**
	 * @param string $class
	 * @return Nette\Templating\FileTemplate
	 */
	protected function createTemplate($class = NULL)
	{
		$template = parent::createTemplate($class);
		$template->_manager = $this->manager;
		return $template;
	}

}