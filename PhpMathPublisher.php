require_once __DIR__.'/src/mathpublisher.php';

class PMP_PhpMathPublisher 
{
	public function mathfilter($text, $size, $pathtoimg)
	{
		\mathfilter($text, $size, $pathtoimg);
	}
}
