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

namespace ftes\PhpMathPublisher;

/**
 * \ftes\PhpMathPublisher\PhpMathPublisher
 *
 * @author Pascal Brachet <pbrachet@xm1math.net>
 * @author Peter Vasilevsky <tuxoiduser@gmail.com> a.k.a. Tux-oid
 * @license GPLv2
 */
class PhpMathPublisher
{
    /**
     * @var \ftes\PhpMathPublisher\Helper
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
     * @param string $imgpath where to store images
     * @param string $webpath web path under which the stored images are available
     * @param int $size font-size for the formula
     */
    public function __construct($imgpath, $webpath, $size = 10)
    {
        $this->helper = new Helper();
        $this->helper->setDirImg($imgpath);
        $this->path = $webpath;
        $this->size = $size;
    }

    /**
     * Check if the wanted image already exists in the cache
     *
     * @param string $n the base name of the image
     * @return int
     */
    protected function detectImage($n)
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
                list(, $v, $name) = explode("_", $fi);
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
        $nameImg = md5(trim($text) . $this->size) . '.png';
        $v = $this->detectImage($nameImg);
        if ($v == 0) {
            //the image doesn't exist in the cache directory. we create it.
            $v = $this->renderImage($text, $this->getImagePath("%s", $nameImg));
        }
        
        return [$v, $nameImg];
    }
    
    private function getImagePath($v, $nameImg, $forWeb=false)
    {
		$basePath = $forWeb ? $this->path : $this->helper->getDirImg();
        return $basePath . "math_" . $v . "_" . $nameImg;
    }

    /**
     * Creates the formula image (if the image is not in the cache) and returns the <img src=...></img> html code.
     *
     * @param string $text the formula
     * @return string html code
     */
    public function mathImage($text)
    {
        list($v, $nameImg) = $this->mathImageInternal($text);
        $vAlign = $v - 1000;
        return '<img src="' . $this->getImagePath($v, $nameImg, true) . '" style="vertical-align:' . $vAlign . 'px;' . ' display: inline-block ;" alt="' . $text . '" title="' . $text . '"/>';
    }

    /**
     * Creates the formula image (if the image is not in the cache) and returns the path to the image.
     * 
     * @param $text the formula
     * @return string path to the image on the file system (imgpath), not the web (webpath)
     */
    public function mathImagePath($text)
    {
        list($v, $nameImg) = $this->mathImageInternal($text);
        return realpath($this->getImagePath($v, $nameImg));
    }

    /**
     * Creates the formula image (if the image is not in the cache) and returns the binary PNG contents.
     * WARNING: does not use the file caching mechanism the mather mathImage*() functions use, and thus is inefficient.
     * 
     * @param $text the formula
     * @return image
     */
    public function mathImageBinary($text)
    {
        $formula = $this->createFormula($text);
        return $formula->image;
    }

    /**
     * Creates an image for the given formula at the given place
     *
     * @param string $text the formula
     * @param string $file where to write the file to (full path). Use %s to have the vertical alignment included
     * @return int the alignment + 1000
     */
    public function renderImage($text, $file)
    {
        $formula = $this->createFormula($text);

        //1000+baseline ($v) is recorded in the name of the image
        $v = 1000 - imagesy($formula->image) + $formula->verticalBased + 3;
        $file = sprintf($file, $v);
        imagepng($formula->image, $file);
        return $v;
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
            $code = $this->mathImage(trim($t));
            $text = str_replace($math[0], $code, $text);
        }

        return $text;
    }

    /**
     * @param \ftes\PhpMathPublisher\Helper $helper
     */
    public function setHelper($helper)
    {
        $this->helper = $helper;
    }

    /**
     * @return \ftes\PhpMathPublisher\Helper
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

