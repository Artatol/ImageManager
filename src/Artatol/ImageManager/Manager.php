<?php

/**
 * This file is part of the Artatol (http://www.artatol.cz)
 * Copyright (c) 2014 Martin Charouzek (martin@charouzkovi.cz)
 */

namespace Artatol\ImageManager;

use Nette;
use Nette\Utils;
use Nette\Utils\Image;
use Aws\S3\S3Client;

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

	/** @var type string */
	private $photoCache;

	/** @var type string */
	private $wwwDir;

	public function __construct(S3Client $client, array $args) {
		$this->s3Client = $client;
		$this->bucket = $args["awsBucket"];
		$this->maxWidth = $args["photoMaxWidth"];
		$this->maxHeight = $args["photoMaxHeight"];
		$this->directory = $args["awsDirectory"];
		$this->photoCache = $args["photoCache"];
		$this->wwwDir = $args["wwwDir"];
	}

	private function get($key, $width = 0, $height = 0) {
		$path = "/" . $this->photoCache . "/" . $width . "-" . $height . "-" . $key;
		if (!is_file($this->wwwDir . "/" . $path)) {
			try {
				$temp = $this->s3Client->getObject(array(
					"Bucket" => $this->bucket,
					"Key" => $this->directory . "/" . $key
				));
			} catch (\Aws\S3\Exception\NoSuchKeyException $e) {
				throw new NotFoundException("Image key " . $key . " was not found in bucket " . $this->bucket);
			}
			$image = Image::fromString($temp["Body"]);
			if ($image) {
				if ($width != 0 OR $height != 0) {
					$this->resize($image, $width, $height);
				}
				switch ($temp["ContentType"]) {
					case "image/png":
						$image->save($this->wwwDir . "/" . $path, 100, Image::PNG);
						break;
					case "image/gif":
						$image->save($this->wwwDir . "/" . $path, 100, Image::GIF);
						break;
					default:
						$image->save($this->wwwDir . "/" . $path, 100, Image::JPEG);
						break;
				}
			} else {
				throw new NotValidImageException("Not valid image.");
			}
		}
		return $path;
	}

	public function getImgObject($key, $width = 0, $height = 0, $title = null, $classes = null) {
		try {
			$path = $this->get($key, $width, $height);
			$img = Utils\Html::el("img");
			$img->src = $path;
			if ($title) {
				$img->title = $title;
				$img->alt = $title;
			}
			if ($classes) {
				$img->class = $classes;
			}
			return $img;
		} catch (\Artatol\ImageManager\NotFoundException $e) {
			//log or whatever
			return null;
		}
	}

	public function upload($file) {
		try {
			$img = Utils\Image::fromFile($file);
		} catch (\Exception $e) {
			throw new \Artatol\ImageManager\NotValidImageException("Image file is not valid.");
		}
		$exif = \exif_read_data($file);
		if ($exif && !empty($exif['Orientation'])) {
			switch ($exif['Orientation']) {
				case 8:
					$img->rotate(90, 0);
					break;
				case 3:
					$img->rotate(180, 0);
					break;
				case 6:
					$img->rotate(-90, 0);
					break;
			}
		}
		if ($img instanceof Utils\Image) {
			if ($img->getWidth() > $this->maxWidth) {
				$img->resize($this->maxWidth, null);
			}
			if ($img->getHeight() > $this->maxHeight) {
				$img->resize(null, $this->maxHeight);
			}
			$img->send();
		} else {
			throw NotValidImageException("Not valid file");
		}
	}

	/**
	 * @return bool
	 */
	public function doesBucketExist() {
		return $this->s3Client->doesBucketExist($this->bucket);
	}

	/**
	 * @param Image $img
	 * @param $width
	 * @param $height
	 * @return Image
	 */
	private function resize(\Nette\Utils\Image $img, $width, $height) {
		if ($width != 0 && $height != 0) {
			$img->resize($width, $height, \Nette\Image::FILL);
			$img->crop('50%', '50%', $width, $height);
		}
		if ($width == 0 && $height != 0) {
			$img->resize(null, $height, \Nette\Image::SHRINK_ONLY);
		}
		if ($width != 0 && $height == 0) {
			$img->resize($width, null, \Nette\Image::SHRINK_ONLY);
		}
		return $img;
	}

}
