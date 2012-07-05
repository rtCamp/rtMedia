<?php
/**
 * MP4Info
 * 
 * @author 		Tommy Lacroix <lacroix.tommy@gmail.com>
 * @copyright   Copyright (c) 2006-2009 Tommy Lacroix
 * @license		LGPL version 3, http://www.gnu.org/licenses/lgpl.html
 * @package 	php-mp4info
 * @link 		$HeadURL: https://php-mp4info.googlecode.com/svn/trunk/MP4Info/Helper.php $
 */

// ---

/**
 * MP4Info helper functions
 * 
 * @author 		Tommy Lacroix <lacroix.tommy@gmail.com>
 * @version 	1.0.20090601	$Id: Helper.php 2 2009-06-11 14:12:31Z lacroix.tommy@gmail.com $
 */
class MP4Info_Helper {
	/**
	 * Convert a short to a float
	 *
	 * @author 	Tommy Lacroix <lacroix.tommy@gmail.com>
	 * @param	int		$n
	 * @return	float
	 * @access 	public
	 * @static
	 */
	public static function fromFixed8($n) {
		return $n / 256;
	} // fromFixed8 method

	
	/**
	 * Convert a long to a float
	 *
	 * @author 	Tommy Lacroix <lacroix.tommy@gmail.com>
	 * @param	int		$n
	 * @return	float
	 * @access 	public
	 * @static
	 */
	public static function fromFixed16($n) {
		return $n / 65536;
	} // fromFixed16 method
	
	
	/**
	 * Convert binary packed (5bit) letters to string
	 *
	 * @author 	Tommy Lacroix <lacroix.tommy@gmail.com>
	 * @param	int		$n
	 * @param	int		$pad
	 * @return	string
	 * @access 	public
	 * @static
	 */
	public static function fromPackedLetters($n, $pad=1) {
		$s = decbin($n);
		$s .= str_repeat('0',8-(strlen($s)%8));
		$s = substr($s,0,-$pad);
		$out = '';
		while (strlen($s)>=5) {
			$letter = substr($s,0,5);
			$nl = bindec($letter) + 0x60;
			$out .= chr($nl);
			$s = substr($s,5);
		}
		return $out;
	} // fromPackedLetters method
} // MP4Info_Helper class
