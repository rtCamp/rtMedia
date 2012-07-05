<?php
/**
 * MP4Info
 * 
 * @author 		Tommy Lacroix <lacroix.tommy@gmail.com>
 * @copyright   Copyright (c) 2006-2009 Tommy Lacroix
 * @license		LGPL version 3, http://www.gnu.org/licenses/lgpl.html
 * @package 	php-mp4info
 * @link 		$HeadURL: https://php-mp4info.googlecode.com/svn/trunk/MP4Info/Box/stsd.php $
 */

// ---

/**
 * x.x ??? (STSD)
 * 
 * @author 		Tommy Lacroix <lacroix.tommy@gmail.com>
 * @version 	1.0.20090601	$Id: stsd.php 2 2009-06-11 14:12:31Z lacroix.tommy@gmail.com $
 * @todo 		Factor this into a fullbox
 */
class MP4Info_Box_stsd extends MP4Info_Box {
	/**
	 * Version
	 *
	 * @var int
	 */
	protected $version;
	
	/**
	 * Flags
	 *
	 * @var int
	 */
	protected $flags;
	
	/**
	 * Count
	 *
	 * @var int
	 */
	protected $count;
	
	/**
	 * Values
	 *
	 * @var string{}
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
	 * @return 	MP4Info_Box_stsd
	 * @access 	public
	 * @throws 	MP4Info_Exception
	 */		
	public function __construct($totalSize, $boxType, $data, $parent) {
		if (!self::isCompatible($boxType, $parent)) {
			throw new Exception('This box isn\'t "stsd"');
		}

		// Call ancestor
		parent::__construct($totalSize,$boxType,'',$parent);
		
		// Get data
		$data = self::getDataFrom3rd($data, $totalSize);

		// Unpack
		$ar = unpack('Cversion/C3flags/Ncount',$data);
		$this->version = $ar['version'];
		$this->flags = $ar['flags1']*65536+$ar['flags1']*256+$ar['flags1']*1;
		$this->count = $ar['count'];
		
		// Unpack SAMPLEDESCRIPTION
		$desc = substr($data,8);
		for ($i=0;$i<$this->count;$i++) {
			$ar = unpack('Nlen',$desc);
			$type = substr($desc,4,4);
			$info = substr($desc,8,$ar['len']-8);
			$desc = substr($desc,$ar['len']);
			$this->values[$type] = $info;
		}
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
		return $boxType == 0x73747364;
	} // isCompatible method
	
	/**
	 * Values getter
	 *
	 * @author 	Tommy Lacroix <lacroix.tommy@gmail.com>
	 * @return string{}
	 * @access 	public
	 */
	public function getValues() {
		return $this->values;
	} // getValues method
	
	/**
	 * Value getter
	 *
	 * @author 	Tommy Lacroix <lacroix.tommy@gmail.com>
	 * @param	string $key
	 * @return	string
	 * @access 	public
	 */
	public function getValue($key) {
		return isset($this->values[$key]) ? $this->values[$key] : false;
	} // getValue method
	
	/**
	 * String converter
	 *
	 * @author 	Tommy Lacroix <lacroix.tommy@gmail.com>
	 * @return	string
	 * @access 	public
	 */		
	public function toString() {
		return '[MP4Info_Box_stsd:'.implode(',',array_keys($this->values)).']';
	} // toString method
} // MP4Info_Box_stsd class