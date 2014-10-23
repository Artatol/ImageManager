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
class Manager extends Nette\Object
{

    /** @var \Aws\S3\S3Client */
    protected $s3Client;

    /** @var type string */
    protected $bucket;

    /** @var type integer */
    protected $maxWidth;

    /** @var type integer */
    protected $maxHeight;

    /** @var type string */
    protected $directory;

    /** @var type string */
    protected $tempDir;

    /** @var type string */
    protected $ACL;

    public function __construct(S3Client $client, array $args)
    {
        $this->s3Client = $client;
        $this->bucket = $args["awsBucket"];
        $this->maxWidth = $args["photoMaxWidth"];
        $this->maxHeight = $args["photoMaxHeight"];
        $this->directory = $args["awsDirectory"];
        $this->tempDir = $args["tempDir"];
    }

    /**
     * @param $key
     * @param int $width
     * @param int $height
     * @return array
     */
    public function get($key, $width = 0, $height = 0)
    {
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
                    return ["image" => $image->toString(Image::PNG, 100), "contentType" => $temp["ContentType"]];
                    break;
                case "image/gif":
                    return ["image" => $image->toString(Image::GIF, 100), "contentType" => $temp["ContentType"]];
                    break;
                default:
                    return ["image" => $image->toString(Image::JPEG, 100), "contentType" => $temp["ContentType"]];
                    break;
            }
        } else {
            throw new NotValidImageException("Not valid image.");
        }
    }

    /**
     * @param $file
     * @param null | int $id_picture
     * @return string $key
     * @throws NotValidImageException
     */
    public function put($file, $id_picture = null)
    {
        try {
            $img = Image::fromFile($file);
        } catch (\Exception $e) {
            throw new NotValidImageException("Image file is invalid.");
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

        if ($img instanceof Image) {
            if ($img->getWidth() > $this->maxWidth) {
                $img->resize($this->maxWidth, null);
            }
            if ($img->getHeight() > $this->maxHeight) {
                $img->resize(null, $this->maxHeight);
            }
            $type = (getimagesizefromstring($img)) ? getimagesizefromstring($img)["mime"] : "image/jpeg";
            if ($id_picture === null OR $id_picture == '') {
                $date = new Utils\DateTime();
                $id_picture = (int)$date->format("YmdHis") . Utils\Strings::random(5, '0-9');
            }

            switch ($type) {
                case "image/png":
                    $extension = ".png";
                    break;
                case "image/gif":
                    $extension = ".gif";
                    break;
                default:
                    $extension = ".jpg";
                    break;
            }

            $key = $id_picture . $extension;

            $tmpFile = $this->tempDir . "/" . $key;
            file_put_contents($file, $img->toString());
            $this->s3Client->putObject(array(
                'Bucket' => $this->bucket,
                'Key' => $this->directory . '/' . $key,
                'SourceFile' => $file,
                'ACL' => $this->ACL
            ));
            unlink($tmpFile);
            return $key;
        } else {
            throw NotValidImageException("Not valid file");
        }
    }

    /**
     * @return bool
     */
    public function doesBucketExist()
    {
        return $this->s3Client->doesBucketExist($this->bucket);
    }

    /**
     * @param Image $img
     * @param $width
     * @param $height
     * @return Image
     */
    protected function resize(Image $img, $width, $height)
    {
        if ($width != 0 && $height != 0) {
            $img->resize($width, $height, Image::FILL);
            $img->crop('50%', '50%', $width, $height);
        }
        if ($width == 0 && $height != 0) {
            $img->resize(null, $height, Image::SHRINK_ONLY);
        }
        if ($width != 0 && $height == 0) {
            $img->resize($width, null, Image::SHRINK_ONLY);
        }
        return $img;
    }

}
