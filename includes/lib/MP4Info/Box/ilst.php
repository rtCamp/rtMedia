<?php
/**
 * MP4Info
 * 
 * @author 		Tommy Lacroix <lacroix.tommy@gmail.com>
 * @copyright   Copyright (c) 2006-2009 Tommy Lacroix
 * @license		LGPL version 3, http://www.gnu.org/licenses/lgpl.html
 * @package 	php-mp4info
 * @link 		$HeadURL: https://php-mp4info.googlecode.com/svn/trunk/MP4Info/Box/ilst.php $
 */

// ---

/**
 * x.x ??? (ILST)
 * 
 * @author 		Tommy Lacroix <lacroix.tommy@gmail.com>
 * @version 	1.0.20090601	$Id: ilst.php 2 2009-06-11 14:12:31Z lacroix.tommy@gmail.com $
 */
class MP4Info_Box_ilst extends MP4Info_Box_Container {
	/**
	 * Values
	 *
	 * @var {}
	 * @access protected
	 */
	protected $values = array();
	
	
	/**
	 * Constructor
	 *
	 * @author 	Tommy Lacroix <lacroix.tommy@gmail.com>
	 * @param	int					$totalSize
	 * @param	int					$boxType
	 * @param	file|string			$data
	 * @param	MP4Info_Box			$parent
	 * @return 	MP4Info_Box_ilst
	 * @access 	public
	 * @throws 	MP4Info_Exception
	 */	
	public function __construct($totalSize, $boxType, $data, $parent) {
		if (!self::isCompatible($boxType, $parent)) {
			throw new MP4Info_Exception('This box isn\'t "ilst"', MP4Info_Exception::CODE_INCOMPATIBLE, $boxType);
		}
		
		// Call ancestor
		parent::__construct($totalSize, $boxType, $data, $parent);
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
		return $boxType == 0x696C7374;
	} // isCompatible method
	
	
	/**
	 * Check if a given key has a value
	 *
	 * @author 	Tommy Lacroix <lacroix.tommy@gmail.com>
	 * @param	string		$k
	 * @return	bool
	 * @access 	public
	 */
	public function hasValue($k) {
		return (isset($this->values[$k])) || (isset($this->values[utf8_decode($k)]));
	} // hasValue method
	
	
	/**
	 * Get the value of a given key
	 *
	 * @author 	Tommy Lacroix <lacroix.tommy@gmail.com>
	 * @param	string		$k
	 * @return	mixed
	 * @access 	public
	 */	
	public function getValue($k) {
		if (isset($this->values[$k])) {
			return $this->values[$k];
		} else if (isset($this->values[utf8_decode($k)])) {
			return $this->values[utf8_decode($k)];
		} else {
			return false;
		}
	} // getValue method
	
	
	/**
	 * Set a value for a given key
	 *
	 * @author 	Tommy Lacroix <lacroix.tommy@gmail.com>
	 * @param	string		$k
	 * @param	mixed		$v
	 * @access 	public
	 */
	public function setKeyValue($k,$v) {
		$this->values[$k] = $v;
	} // setKeyValue method

	
	/**
	 * String converter
	 *
	 * @author 	Tommy Lacroix <lacroix.tommy@gmail.com>
	 * @return	string
	 * @access 	public
	 */	
	public function toString() {
		return '[MP4Info_Box_ilst:'.count($this->boxes).']';
	} // toString method
} // MP4Info_Box_ilst class