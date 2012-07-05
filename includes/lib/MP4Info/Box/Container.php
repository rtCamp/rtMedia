<?php
/**
 * MP4Info
 * 
 * @author 		Tommy Lacroix <lacroix.tommy@gmail.com>
 * @copyright   Copyright (c) 2006-2009 Tommy Lacroix
 * @license		LGPL version 3, http://www.gnu.org/licenses/lgpl.html
 * @package 	php-mp4info
 * @link 		$HeadURL: https://php-mp4info.googlecode.com/svn/trunk/MP4Info/Box/Container.php $
 */

// ---

/**
 * Generic container box
 * 
 * @author 		Tommy Lacroix <lacroix.tommy@gmail.com>
 * @version 	1.1.20090611	$Id: Container.php 2 2009-06-11 14:12:31Z lacroix.tommy@gmail.com $
 */
class MP4Info_Box_Container extends MP4Info_Box {
	/**
	 * Constructor
	 *
	 * @author 	Tommy Lacroix <lacroix.tommy@gmail.com>
	 * @param	int						$totalSize
	 * @param	int						$boxType
	 * @param	file|string				$data
	 * @param	MP4Info_Box				$parent
	 * @return 	MP4Info_Box_Container
	 * @access 	public
	 * @throws 	MP4Info_Exception
	 */
	public function __construct($totalSize, $boxType, $data, $parent) {
		if (!self::isCompatible($boxType, $parent)) {
			throw new MP4Info_Exception('This box isn\'t a container',MP4Info_Exception::CODE_INCOMPATIBLE,$boxType);
		}

		// Call ancestor
		parent::__construct($totalSize, $boxType, false, $parent);
		
		// Unpack
		if (is_string($data)) {
			while ($data != '') {
				try {
					$box = MP4Info_Box::fromString($data, $this);
					if (!$box instanceof MP4Info_Box) {
						break;
					}
				} catch (Exception $e) {
					break;
				}
			}
		} else {
			do {
				try {
					$box = MP4Info_Box::fromStream($data, $this);
					if (!$box instanceof MP4Info_Box) {
						break;
					}
				} catch (Exception $e) {
					break;
				}
			} while ($box !== false);
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
	public static function isCompatible($boxType, $parent) {
		return 	($boxType == 0x6D6F6F76) ||	// moov
				($boxType == 0x7472616B) ||	// trak
				($boxType == 0x6d646961) ||	// mdia
				($boxType == 0x6D696E66) ||	// minf
				($boxType == 0x7374626c) ||	// stbl
				($boxType == 0x75647461) ||	// udta
				false;
	} // isCompatible method
	
	
	/**
	 * String converter
	 *
	 * @author 	Tommy Lacroix <lacroix.tommy@gmail.com>
	 * @return	string
	 * @access 	public
	 */	
	public function toString() {
		return '[MP4Info_Box_Container['.$this->boxTypeStr.']:'.count($this->children).']';
	} // toString method	
} // MP4Info_Box_Container method