<?php
/***************************************************************************
 *   copyright            : (C) 2005 by Pascal Brachet - France            *
 *   pbrachet_NOSPAM_xm1math.net (replace _NOSPAM_ by @)                   *
 *   http://www.xm1math.net/phpmathpublisher/                              *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/

namespace RL\PhpMathPublisher;

use RL\PhpMathPublisher\Helper;
use RL\PhpMathPublisher\MathExpression;

/**
 * \RL\PhpMathPublisher\PhpMathPublisher
 *
 * @author Pascal Brachet <pbrachet@xm1math.net>
 * @author Peter Vasilevsky <tuxoiduser@gmail.com> a.k.a. Tux-oid
 * @license GPLv2
 */
class PhpMathPublisher
{
    /**
     * @var \RL\PhpMathPublisher\Helper
     */
    protected $helper;

    /**
     * @var int
     */
    protected $size;

    /**
     * @var string
     */
    protected $path;

    /**
     * Constructor
     */
    public function __construct($path, $size = 10)
    {
        $this->helper = new Helper();
        $this->path = $path;
        $this->size = $size;
    }

    /**
     * @param $n
     * @return int
     */
    public function detectImg($n)
    {
        /*
         Detects if the formula image already exists in the $dirImg cache directory.
         In that case, the function returns a parameter (recorded in the name of the image file) which allows to align correctly the image with the text.
         */
        $dirImg = $this->helper->getDirImg();
        $ret = 0;
        $handle = opendir($dirImg);
        while ($fi = readdir($handle)) {
            $info = pathinfo($fi);
            if ($fi != "." && $fi != ".." && $info["extension"] == "png" && preg_match("#^math#", $fi)) {
                list($math, $v, $name) = explode("_", $fi);
                if ($name == $n) {
                    $ret = $v;
                    break;
                }
            }
        }
        closedir($handle);

        return $ret;
    }
    
    private function createFormula($text)
    {
        $formula = new MathExpression($this->helper->tableExpression(trim($text)), $this->helper);
        $formula->draw($this->size);
        return $formula;
    }

    private function mathImageInternal($text)
    {
        $dirImg = $this->helper->getDirImg();
        $nameImg = md5(trim($text) . $this->size) . '.png';
        $v = $this->detectImg($nameImg);
        if ($v == 0) {
            //the image doesn't exist in the cache directory. we create it.
            $formula = $this->createFormula($text);
            $v = 1000 - imagesy($formula->image) + $formula->verticalBased + 3;
            //1000+baseline ($v) is recorded in the name of the image
            ImagePNG($formula->image, $dirImg . "/math_" . $v . "_" . $nameImg);
        }
        
        return [$v, $nameImg];
    }
    
    private function getImagePath($v, $nameImg)
    {
        return $this->path . "math_" . $v . "_" . $nameImg;
    }

    /**
     * @param $text
     * @return string
     */
    public function mathImage($text)
    {
        /*
         Creates the formula image (if the image is not in the cache) and returns the <img src=...></img> html code.
         */
        list($v, $nameImg) = $this->mathImageInternal($text);
        $vAlign = $v - 1000;
        return '<img src="' . $this->getImagePath($v, $nameImg) . '" style="vertical-align:' . $vAlign . 'px;' . ' display: inline-block ;" alt="' . $text . '" title="' . $text . '"/>';
    }

    /**
     * @param $text
     * @return string
     */
    public function mathImagePath($text)
    {
        /*
        Creates the formula image (if the image is not in the cache) and returns the path to the image.
        */
        list($v, $nameImg) = $this->mathImageInternal($text);
        return realpath($this->getImagePath($v, $nameImg));
    }

    /**
     * @param $text
     * @return image
     */
    public function mathImageBinary($text)
    {
        /*
        Creates the formula image (if the image is not in the cache) and returns the binary PNG contents.
        WARNING: does not use the file caching mechanism the mather mathImage*() functions use, and thus is inefficient.
        */
        $formula = $this->createFormula($text);
        return $formula->image;
    }

    /**
     * @param $text
     * @return mixed|string
     */
    public function mathFilter($text)
    {
        /*
         Replaces all <m> tags in $text with <img> tags by using mathImage().
         */
        $text = stripslashes($text);
        $this->size = max($this->size, 10);
        $this->size = min($this->size, 24);
        preg_match_all("|<m>(.*?)</m>|", $text, $regs, PREG_SET_ORDER);
        foreach ($regs as $math) {
            $t = str_replace('<m>', '', $math[0]);
            $t = str_replace('</m>', '', $t);
            $code = $this->mathImage(trim($t), $this->size, $this->path);
            $text = str_replace($math[0], $code, $text);
        }

        return $text;
    }

    /**
     * @param \RL\PhpMathPublisher\Helper $helper
     */
    public function setHelper($helper)
    {
        $this->helper = $helper;
    }

    /**
     * @return \RL\PhpMathPublisher\Helper
     */
    public function getHelper()
    {
        return $this->helper;
    }

    /**
     * @param string $path
     */
    public function setPath($path)
    {
        $this->path = $path;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param int $size
     */
    public function setSize($size)
    {
        $this->size = $size;
    }

    /**
     * @return int
     */
    public function getSize()
    {
        return $this->size;
    }

}

