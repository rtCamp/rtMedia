<?php
/**
 * MP4Info
 * 
 * @author 		Tommy Lacroix <lacroix.tommy@gmail.com>
 * @copyright   Copyright (c) 2006-2009 Tommy Lacroix
 * @license		LGPL version 3, http://www.gnu.org/licenses/lgpl.html
 * @package 	php-mp4info
 * @link 		$HeadURL: https://php-mp4info.googlecode.com/svn/trunk/MP4Info/Box/uuid.php $
 */

// ---

/**
 * x.x ??? (UUID)
 * 
 * @author 		Tommy Lacroix <lacroix.tommy@gmail.com>
 * @version 	1.0.20090601	$Id: uuid.php 2 2009-06-11 14:12:31Z lacroix.tommy@gmail.com $
 * @todo 		Limited to XMP meta data... extend
 */
class MP4Info_Box_uuid extends MP4Info_Box {
	// {{{ Constants
	const UUID_XMP_METADATA = 'BE7ACFCB97A942';
	// }}} Constants
	
	/**
	 * UUID
	 *
	 * @var	string
	 */
	protected $uuid;
	
	/**
	 * XMP data
	 *
	 * @var string
	 */
	protected $xmp;
	
	
	/**
	 * Constructor
	 *
	 * @author 	Tommy Lacroix <lacroix.tommy@gmail.com>
	 * @param	int					$totalSize
	 * @param	int					$boxType
	 * @param	file|string			$data
	 * @param	MP4Info_Box			$parent
	 * @return 	MP4Info_Box_uuid
	 * @access 	public
	 * @throws 	MP4Info_Exception
	 */		
	public function __construct($totalSize, $boxType, $data, $parent) {
		if (!self::isCompatible($boxType, $parent)) {
			throw new Exception('This box isn\'t "uuid"');
		}

		// Call ancestor
		parent::__construct($totalSize,$boxType,'',$parent);
		
		// Unpack
		$data = self::getDataFrom3rd($data,$totalSize);
		$this->uuid = bin2hex(substr($data,0,16));
		$this->xmp = substr($data,16);
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
		return $boxType == 0x75756964;
	} // isCompatible method
	
	
	/**
	 * UUID getter
	 *
	 * @author 	Tommy Lacroix <lacroix.tommy@gmail.com>
	 * @return	string
	 * @access 	public
	 */
	public function getUUID() {
		return strtoupper($this->uuid);
	} // getUUID method

	
	/**
	 * XMP Meta data getter
	 *
	 * @author 	Tommy Lacroix <lacroix.tommy@gmail.com>
	 * @return	string
	 * @access 	public
	 */
	public function getXMPMetaData() {
		return (substr(strtoupper($this->uuid),0,14) == self::UUID_XMP_METADATA) ? $this->xmp : false;
	} // getXMPMetaData method
	
	
	/**
	 * String converter
	 *
	 * @author 	Tommy Lacroix <lacroix.tommy@gmail.com>
	 * @return	string
	 * @access 	public
	 */			
	public function toString() {
		return '[MP4Info_Box_uuid]';
	} // toString method
} // MP4Info_Box_uuid class