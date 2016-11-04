# phpMathPublisher
phpMathPublisher fork, hosted on github to allow seamless integration in composer-based php apps.
Packagist package is auto-updated via [service hook](https://packagist.org/about#how-to-update-packages).

## Authors
- **Original version:** Pascal Brachet http://www.xm1math.net/phpmathpublisher/
- **php5.3 rewrite** Peter Vasilevsky a.k.a. Tux-oid, http://rulinux.net

## How to use
Call the `mathfilter($text,$size,$pathtoimg)` function in your php page.

- `$text` is the text with standard html tags and mathematical expressions (defined by the `<m>...</m>` tag).
- `$size` is the size of the police used for the formulas.
- `$pathtoimg` is the relative path between the html pages and the images directory.

With a simple `echo mathfilter($text,$size,$pathtoimg);`, you can display text with mathematical formulas.
The mathfilter function will replace all the math tags (`<m>formula</m>`) in `$text` by `<img src=the formula image >`.

### Example
`mathfilter("A math formula : <m>f(x)=sqrt{x}</m>,12,"img/")` will return:

```
"A math formula : <img src=\"img/math_988.5_903b2b36fc716cfb87ff76a65911a6f0.png\" style=\"vertical-align:-11.5px; display: inline-block ;\" alt=\"f(x)=sqrt{x}\" title=\"f(x)=sqrt{x}\">"
```

The image corresponding to a formula is created only once. Then the image is stocked into the image directories.
The first time that mathfilter is called, the images corresponding to the formulas are created, but the next times mathfilter will only return the html code.

NOTE: if the free latex fonts furnished with this script don't work well (very tiny formulas - that's could happened with some GD configurations), you should try to use the bakoma versions of these fonts ([downloadable here])http://www.ctan.org/tex-archive/fonts/cm/ps-type1/bakoma/ttf/))
