<?php
/**
 * MP4Info
 * 
 * @author 		Tommy Lacroix <lacroix.tommy@gmail.com>
 * @copyright   Copyright (c) 2006-2009 Tommy Lacroix
 * @license		LGPL version 3, http://www.gnu.org/licenses/lgpl.html
 * @package 	php-mp4info
 * @link 		$HeadURL: https://php-mp4info.googlecode.com/svn/trunk/MP4Info/Box/meta.php $
 */

// ---

/**
 * x.x Meta (META)
 * 
 * @author 		Tommy Lacroix <lacroix.tommy@gmail.com>
 * @version 	1.0.20090601	$Id: meta.php 2 2009-06-11 14:12:31Z lacroix.tommy@gmail.com $
 */
class MP4Info_Box_meta extends MP4Info_Box_container {
	
	/**
	 * Constructor
	 *
	 * @author 	Tommy Lacroix <lacroix.tommy@gmail.com>
	 * @param	int					$totalSize
	 * @param	int					$boxType
	 * @param	file|string			$f
	 * @param	MP4Info_Box_meta	$parent
	 * @access 	public
	 * @throws 	MP4Info_Exception
	 */	
	public function __construct($totalSize, $boxType, $data, $parent=false) {
		if (!self::isCompatible($boxType, $parent)) {
			throw new MP4Info_Exception('This box isn\'t "meta"',MP4Info_Exception::CODE_INCOMPATIBLE,false,$boxType);
		}
		
		$ar = unpack('Nlen',$data);
		
		parent::__construct($totalSize, $boxType, substr($data,4,$ar['len']), $parent);
	} // Constructor
	
	
	/**
	 * Check if block is compatible with class
	 *
	 * @author 	Tommy Lacroix <lacroix.tommy@gmail.com>
	 * @param 	int					$boxType
	 * @param 	MP4Info_Box			$parent
	 * @return 	bool
	 * @access 	public
	 * @static
	 */
	static public function isCompatible($boxType, $parent) {
		return $boxType == 0x6D657461;
	} // isCompatible method

	
	/**
	 * String converter
	 *
	 * @author 	Tommy Lacroix <lacroix.tommy@gmail.com>
	 * @return 	string
	 * @access 	public
	 */
	public function toString() {
		return '[MP4Info_Box_meta:'.count($this->boxes).']';
	} // toString method
} // MP4Info_Box_meta class