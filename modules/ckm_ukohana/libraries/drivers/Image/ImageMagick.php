<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * ImageMagick Image Driver.
 *
 * $Id: ImageMagick.php 329 2011-06-21 03:08:23Z zhangjyr $
 *
 * @package    Image
 * @author     Kohana Team
 * @copyright  (c) 2007-2008 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class Image_ImageMagick_Driver extends Image_Driver {

	// Directory that IM is installed in
	protected $dir = '';

	// Command extension (exe for windows)
	protected $ext = '';
	private $_sequence = 1;
	// Temporary image filename
	protected $tmp_image;
	const PROP_WIDTH = 0;
	const PROP_HEIGHT = 1;
	/**
	 * Attempts to detect the ImageMagick installation directory.
	 *
	 * @throws  Kohana_Exception
	 * @param   array   configuration
	 * @return  void
	 */
	public function __construct($config)
	{
		if (empty($config['directory']))
		{
			// Attempt to locate IM by using "which" (only works for *nix!)
			if ( ! is_file($path = exec('which convert')))
				throw new Kohana_Exception('image.imagemagick.not_found');

			$config['directory'] = dirname($path);
		}

		// Set the command extension
		$this->ext = (PHP_SHLIB_SUFFIX === 'dll') ? '.exe' : '';

		// Check to make sure the provided path is correct
		if ( ! is_file(realpath($config['directory']).'/convert'.$this->ext))
			throw new Kohana_Exception('image.imagemagick.not_found', 'convert'.$this->ext);

		// Set the installation directory
		$this->dir = str_replace('\\', '/', realpath($config['directory'])).'/';
	}

	/**
	 * Creates a temporary image and executes the given actions. By creating a
	 * temporary copy of the image before manipulating it, this process is atomic.
	 */
	public function process($image, $actions, $dir, $file, $render = FALSE)
	{
		// We only need the filename
		$image = $image['file'];
		// Unique temporary filename
		$this->tmp_image = $dir.'k2img--'.sha1(time().$dir.$file).substr($file, strrpos($file, '.'));
		// Copy the image to the temporary file
		copy($image, $this->tmp_image);
		$this->_properties = array_slice(getimagesize($this->tmp_image), 0, 2, FALSE);
		// Quality change is done last
		//$quality = (int) arr::remove('quality', $actions);
		// Use 95 for the default quality
		empty($quality) and $quality = 95;
		// All calls to these will need to be escaped, so do it now
		$this->cmd_image = escapeshellarg($this->tmp_image);
		$this->new_image = ($render)? $this->cmd_image : escapeshellarg($dir.$file);



		$outfile= $render !== FALSE?$this->tmp_image:$this->new_image;
		//log::debug('outfile:'.$outfile);

		if ($status = $this->execute($actions,$outfile))
		{
			// Use convert to change the image into its final version. This is
			// done to allow the file type to change correctly, and to handle
			// the quality conversion in the most effective way possible.
//			if ($error = exec(escapeshellcmd($this->dir.'convert'.$this->ext).' -quality '.$quality.'% '.$this->cmd_image.' '.$this->new_image))
//			{
//				log::debug('copy to new img error');
//				log::debug(escapeshellcmd($this->dir.'convert'.$this->ext).' -quality '.$quality.'% '.$this->cmd_image.' '.$this->new_image);
//				$this->errors[] = $error;
//			}

				// Output the image directly to the browser
				if ($render !== FALSE)
				{
					$contents = file_get_contents($outfile);
					switch (substr($file, strrpos($file, '.') + 1))
					{
						case 'jpg':
						case 'jpeg':
							header('Content-Type: image/jpeg');
						break;
						case 'gif':
							header('Content-Type: image/gif');
						break;
						case 'png':
							header('Content-Type: image/png');
						break;
 					}
					echo $contents;
				}

		}

		// Remove the temporary image
		@unlink($this->tmp_image);
		$this->tmp_image = '';

		return $status;
	}

	public function crop($prop)
	{
		// Sanitize and normalize the properties into geometry
		$this->sanitize_geometry($prop);

		// Set the IM geometry based on the properties
		$geometry = escapeshellarg($prop['width'].'x'.$prop['height'].'+'.$prop['left'].'+'.$prop['top']);


//		if ($error = exec(escapeshellcmd($this->dir.'convert'.$this->ext).' -crop '.$geometry.' '.$this->cmd_image.' '.$this->cmd_image))
//		{
//			$this->errors[] = $error;
//			return FALSE;
//		}

		$this->cmd .= ' -crop '.$geometry ;
		$this->_properties[self::PROP_WIDTH] =
			($prop['width']+$prop['left'] > $this->_properties[self::PROP_WIDTH])? $this->_properties[self::PROP_WIDTH]-$prop['left']:$prop['width'];
		$this->_properties[self::PROP_HEIGHT] =
			($prop['height']+$prop['top'] > $this->_properties[self::PROP_HEIGHT])? $this->_properties[self::PROP_HEIGHT]-$prop['top']:$prop['height'];
		return TRUE;
	}

	public function flip($dir)
	{
		// Convert the direction into a IM command
		$dir = ($dir === Image::HORIZONTAL) ? '-flop' : '-flip';

//		if ($error = exec(escapeshellcmd($this->dir.'convert'.$this->ext).' '.$dir.' '.$this->cmd_image.' '.$this->cmd_image))
//		{
//			$this->errors[] = $error;
//			return FALSE;
//		}

		$this->cmd .=   ' '.$dir ;

		return TRUE;
	}

	public function resize($prop)
	{
		switch ($prop['master'])
		{
			case Image::WIDTH:  // Wx
				$dim = escapeshellarg($prop['width'].'x');
			break;
			case Image::HEIGHT: // xH
				$dim = escapeshellarg('x'.$prop['height']);
			break;
			case Image::FIT:
			case Image::AUTO:   // WxH
				$dim = escapeshellarg($prop['width'].'x'.$prop['height']);
			break;
			case Image::NONE:   // WxH!
				$dim = escapeshellarg($prop['width'].'x'.$prop['height'].'!');
			break;
			case Image::FILL:   // WxH^
				$dim = escapeshellarg($prop['width'].'x'.$prop['height'].'^');
			break;
		}

		// Use "convert" to change the width and height
//		if ($error = exec(escapeshellcmd($this->dir.'convert'.$this->ext).' -colorspace RGB -filter Lanczos -define filter:blur=.9891028367558475 -resize '.$dim.' '.$this->cmd_image.' '.$this->cmd_image))
//		{
//			$this->errors[] = $error;
//			return FALSE;
//		}
		$this->cmd .= ' -colorspace RGB -filter Lanczos -define filter:blur=.9891028367558475 -resize '.$dim;

		if($prop['master']== Image::FILL )
		{
			$cropprop = $prop;
			$widthpct = $this->_properties[self::PROP_WIDTH] / $prop['width'];
			$heightpct = $this->_properties[self::PROP_HEIGHT] / $prop['height'];
			if($widthpct < $heightpct)
			{
				$this->_properties[self::PROP_WIDTH] = $prop['width'];
				$this->_properties[self::PROP_HEIGHT] = $this->_properties[self::PROP_HEIGHT]/$widthpct;
			}
			else
			{
				$this->_properties[self::PROP_WIDTH] = $this->_properties[self::PROP_WIDTH] /$heightpct;
				$this->_properties[self::PROP_HEIGHT] = $prop['height'] ;

			}
			$cropprop['top'] = 'center';
			$cropprop['left'] = 'center';
			return $this->crop($cropprop);
		}
		else
		{
			$this->_properties[self::PROP_WIDTH] = $prop['width'];
	    	$this->_properties[self::PROP_HEIGHT] = $prop['height'];
		}

		return TRUE;
	}

	public function rotate($amt)
	{
//		if ($error = exec(escapeshellcmd($this->dir.'convert'.$this->ext).' -rotate '.escapeshellarg($amt).' -background transparent '.$this->cmd_image.' '.$this->cmd_image))
//		{
//			$this->errors[] = $error;
//			return FALSE;
//		}

        if ($amt == Image_Driver::ROTATE_AMOUNT_AUTO) {
            $this->cmd .=  ' -auto-orient ';
        }
        else {
            $this->cmd .= '  -rotate '.escapeshellarg($amt).' -background transparent ';
        }

		return TRUE;
	}

	public function sharpen($amount)
	{
		// Set the sigma, radius, and amount. The amount formula allows a nice
		// spread between 1 and 100 without pixelizing the image badly.
		$sigma  = 0.5;
		$radius = $sigma * 2;
		$amount = round(($amount / 80) * 3.14, 2);

		// Convert the amount to an IM command
		$sharpen = escapeshellarg($radius.'x'.$sigma.'+'.$amount.'+0');

//		if ($error = exec(escapeshellcmd($this->dir.'convert'.$this->ext).' -unsharp '.$sharpen.' '.$this->cmd_image.' '.$this->cmd_image))
//		{
//			$this->errors[] = $error;
//			return FALSE;
//		}

		$this->cmd .=  ' -unsharp '.$sharpen ;

		return TRUE;
	}

	// Tianium implementation
	public function execute($actions,$outfile="")
	{
		$this->cmd = " ".$this->cmd_image;

		$time = time()+microtime();

		$quality = (int) arr::remove('quality', $actions);
		empty($quality) and $quality = 95;

		foreach ($actions as $func => $args)
		{
			if (isset($args[0])) {
				foreach($args as $arg)
				{
					if ( ! $this->$func($arg)){
					return FALSE;}
				}
			}
			else if ( ! $this->$func($args)){
				return FALSE;
			}
		}
		if (!isset($this->cmd) || empty($this->cmd)) {
			log::warn( "no imagemagick cmd built.");
			return FALSE;
		}
        //$this->cmd.=' -auto-orient ';
		log::debug( "time to execute imagemagick cmd:convert{$this->cmd} $this->cmd_image");
		$cmd = escapeshellcmd($this->dir.'convert'.$this->ext).$this->cmd.' -quality '.$quality.'% '.$outfile;
        //echo $cmd,'<br/>';
		if ($error = exec($cmd))
		{
			$this->errors[] = $error;
			return FALSE;
		}

		$span = round(time()+microtime()-$time, 4);
		log::debug( "success execute imagemagick process, use time $span sec ");
		return TRUE;
	}


	public function addtext($setting)
	{
		if (!isset($setting['text'])) {
			return FALSE;
		}
		$setting['text'] = str_replace(array("'",'"','!'),array("\'",'\"',"\!"),$setting['text'] );

		if (!isset($setting['x'])) {
			$setting['x'] = 0;
		}
		if (!isset($setting['y'])) {
			$setting['y'] = 0;
		}

		$props = array();
		if (isset($setting['font']) && (!isset($this->last_font) || $this->last_font != $setting['font'])) {
			$this->last_font = $props['font'] = $setting['font'];
		}
		else if (!isset($this->last_font) || empty($this->last_font)) {
			$this->last_font = $props['font'] = "Arial.tff";
		}

		if (isset($setting['size']) && (!isset($this->last_pointsize) || $this->last_pointsize != $setting['size'])) {
			$this->last_pointsize = $props['pointsize'] = $setting['size'];
		}
		else if (!isset($this->last_pointsize) || empty($this->last_pointsize)) {
			$this->last_pointsize = $props['pointsize'] = 12;
		}

		if (isset($setting['color']) && (!isset($this->last_color) || $this->last_color != $setting['color'])) {
			$this->last_color = $props['fill'] = '"'.$this->normalizeColor($setting['color']).'"';
		}
		else if (!isset($this->last_color) || empty($this->last_color)) {
			$this->last_color = $props['fill'] = '"#000000"';
		}

		// set command;
		foreach ($props as $key => $value) {
			$this->cmd .= " -$key $value";
		}
		$this->cmd .= " -draw \"text {$setting['x']},{$setting['y']} '{$setting['text']}'\"";
		return TRUE;
	}


	public function copyimg($setting)
	{
		// resource $dst_im , resource $src_im , int $dst_x , int $dst_y , int $src_x , int $src_y , int $src_w , int $src_h
		$src = $setting['src'];
        $resize = isset($setting['src_w']) && isset($setting['src_h'])?"[{$setting['src_w']}x{$setting['src_h']}]":"";
        $this->cmd .=   ' '.$src.$resize.' -geometry  +'.$setting['dst_x'].'+'.$setting['dst_y'].' -composite ';
        //$cmd = " -composite -compose atop -geometry -13-17 white-highlight.png red-circle.png red-ball.png"


        //$error = exec($cmd);
        //echo $cmd."<br>";

		return true;
	}

	public function addRectangle($setting)
	{

//		foreach($settings as $setting)
//		{
//
// 			$image = new Imagick($this->tmp_image);
//			$draw = new ImagickDraw();    //Create a new drawing class (?)
//			$draw->setFillColor($this->getColor($setting['color']));    // Set up some colors to use for fill and outline
//			$draw->setStrokeColor( $this->getColor($setting['color']) );
//			$draw->rectangle( $setting['x1'], $setting['y1'], $setting['x2'], $setting['y2'] );    // Draw the rectangle
//			$image->drawImage( $draw );
//			$image->writeImage($this->tmp_image);
//			//$image->setImageFormat($this->ext);
//		}
//
		$props = array();
		if (isset($setting['color']) && (!isset($this->last_color) || $this->last_color != $setting['color'])) {
			$this->last_color = $props['stroke'] = '"'.$this->normalizeColor($setting['color']).'"';
		}
		else if (!isset($this->last_color) || empty($this->last_color)) {
			$this->last_color = $props['stroke'] = '"#000000"';
		}

		foreach ($props as $key => $value) {
			$this->cmd .= " -$key $value";
		}
		$this->cmd .= " -fill \"{$this->last_color}\" -draw 'rectangle {$setting['x1']},{$setting['y1']},{$setting['x2']},{$setting['y2']}'";
		return true;
	}

	protected function normalizeColor($strcolor)
	{
		if (preg_match('/^[0-9A-Za-z]{6}$/', $strcolor)) {
			return '#'.strtoupper($strcolor);
		}
		return $strcolor;
	}


	protected function properties()
	{
		return $this->_properties;  //array_slice(getimagesize($this->tmp_image), 0, 2, FALSE);
	}

} // End Image ImageMagick Driver