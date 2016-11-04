# phpMathPublisher
phpMathPublisher fork, hosted on github to allow seamless integration in composer-based php apps.
Packagist package is auto-updated via [service hook](https://packagist.org/about#how-to-update-packages).

## Authors
- **Original version:** Pascal Brachet http://www.xm1math.net/phpmathpublisher/
- **php5.3 rewrite** Peter Vasilevsky a.k.a. Tux-oid, http://rulinux.net

## How to use
1. Configure settings via the constructor `new PhpMathPublisher($path, $size)`
  - where `$path` is the path where the png files should be stored
  - and `size` is the text size to be used when generating these images
2. Call one of the interface methods on your `PhpMathPublisher` object
  - `mathImage($text)`: Creates the formula image (if the image is not in the cache) and returns the `<img src=...></img>` html code
  - `mathImagePath($text)`: Creates the formula image (if the image is not in the cache) and returns the path to the image
  - `mathImageBinary($text)`: Creates the formula image and returns the binary PNG contents (warning: does not use the file cache, thus inefficient)
  - `mathFilter($text)`: Replaces all `<m>` tags in `$text` with `<img>` tags by using `mathImage()`

### Example
`(new PhpMathPublisher('img/', 12))->mathFilter('A math formula : <m>f(x)=sqrt{x}</m>')` will return:

```
'A math formula : <img src=\"img/math_988.5_903b2b36fc716cfb87ff76a65911a6f0.png\" style=\"vertical-align:-11.5px; display: inline-block ;\" alt=\"f(x)=sqrt{x}\" title=\"f(x)=sqrt{x}\">'
```

The image corresponding to a formula is created only once. Then the image is stocked into the image directories.
The first time that mathfilter is called, the images corresponding to the formulas are created, but the next times mathfilter will only return the html code.

NOTE: if the free latex fonts furnished with this script don't work well (very tiny formulas - that's could happened with some GD configurations), you should try to use the bakoma versions of these fonts ([downloadable here](http://www.ctan.org/tex-archive/fonts/cm/ps-type1/bakoma/ttf/))
