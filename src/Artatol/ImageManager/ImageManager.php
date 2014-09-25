<?php

/**
 * This file is part of the Artatol (http://www.artatol.cz)
 * Copyright (c) 2014 Martin Charouzek (martin@charouzkovi.cz)
 */

namespace Artatol\ImageManager;

use Nette;
use Aws;

/**
 * @author Martin Charouzek <martin@charouzkovi.cz>
 */
class ImageManager extends Nette\Object {

	/** @var \Aws\S3\S3Client */
	private $s3Client;

	public function __construct(Aws\S3\S3Client $client) {
		$this->s3Client = $client;
	}

}
