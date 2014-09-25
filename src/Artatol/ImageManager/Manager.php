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
class Manager extends Nette\Object {

	/** @var \Aws\S3\S3Client */
	private $s3Client;

	/** @var type string */
	private $bucket;

	/** @var type integer */
	private $maxWidth;

	/** @var type integer */
	private $maxHeight;

	/** @var type string */
	private $directory;

	public function __construct(Aws\S3\S3Client $client, array $args) {
		$this->s3Client = $client;
		$this->bucket = $args["awsBucket"];
		$this->maxWidth = $args["photoMaxWidth"];
		$this->maxHeight = $args["photoMaxHeight"];
		$this->directory = $args["awsDirectory"];
	}

	public function get($key) {
		$temp = $this->s3Client->getObject(array(
			"Bucket" => $this->bucket,
			"Key" => $this->directory."/".$key
		));
		dump($temp);
	}

	public function test() {
		return $this->s3Client->doesBucketExist($this->bucket);
	}

}
