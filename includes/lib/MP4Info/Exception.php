<?php
/**
 * MP4Info
 * 
 * @author 		Tommy Lacroix <lacroix.tommy@gmail.com>
 * @copyright   Copyright (c) 2006-2009 Tommy Lacroix
 * @license		LGPL version 3, http://www.gnu.org/licenses/lgpl.html
 * @package 	php-mp4info
 * @link 		$HeadURL: https://php-mp4info.googlecode.com/svn/trunk/MP4Info/Exception.php $
 */

// ---

/**
 * MP4Info Exception Class
 * 
 * @author 		Tommy Lacroix <lacroix.tommy@gmail.com>
 * @version 	1.1.20090611	$Id: Exception.php 2 2009-06-11 14:12:31Z lacroix.tommy@gmail.com $
 */

class MP4Info_Exception extends Exception {
	// {{{ Constants
	const CODE_UNKNOWN = 0x00;
	const CODE_INCOMPATIBLE = 0x01;
	// }}} Constants
	
	/**
	 * Box type
	 *
	 * @var int
	 */
	protected $boxType;
	
	
	/**
	 * Constructor
	 *
	 * @author	Tommy Lacroix <lacroix.tommy@gmail.com>
	 * @param 	string		$message
	 * @param	int			$code
	 * @param	int			$boxType
	 * @return 	MP4Info_Exception
	 * @access 	public
	 */
	public function __construct($message, $code = 0, $boxType=false) {
		parent::__construct($message, $code);
		$this->boxType = $boxType;
	} // Constructor
	
	
	/**
	 * Box type getter
	 *
	 * @author	Tommy Lacroix <lacroix.tommy@gmail.com>
	 * @return 	int
	 * @access 	public
	 */
	public function getBoxType() {
		return $this->boxType;
	} // getBoxType method
} // MP4Info_Exception class