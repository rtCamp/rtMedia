<?php
/**
 * MP4Info
 * 
 * @author 		Tommy Lacroix <lacroix.tommy@gmail.com>
 * @copyright   Copyright (c) 2006-2009 Tommy Lacroix
 * @license		LGPL version 3, http://www.gnu.org/licenses/lgpl.html
 * @package 	php-mp4info
 * @link 		$HeadURL: https://php-mp4info.googlecode.com/svn/trunk/MP4Info/Box/mdhd.php $
 */

// ---

/**
 * 8.8 Media Header Box (MDHD)
 * 
 * @author 		Tommy Lacroix <lacroix.tommy@gmail.com>
 * @version 	1.0.20090611	$Id: mdhd.php 2 2009-06-11 14:12:31Z lacroix.tommy@gmail.com $
 * @todo 		Factor this into a fullbox
 */
class MP4Info_Box_mdhd extends MP4Info_Box {
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
	 * Creation time
	 *
	 * @var string
	 */
	protected $ctime;
	
	/**
	 * Modification time
	 *
	 * @var unknown_type
	 */
	protected $mtime;
	
	/**
	 * Time scale
	 *
	 * @var int
	 */
	protected $timeScale;
	
	/**
	 * Duration
	 *
	 * @var int
	 */
	protected $duration;
	
	/**
	 * Time zone
	 *
	 * @var int
	 * @static
	 */
	protected static $timezone = false;
	
	
	/**
	 * Constructor
	 *
	 * @author 	Tommy Lacroix <lacroix.tommy@gmail.com>
	 * @param	int					$totalSize
	 * @param	int					$boxType
	 * @param	file|string			$data
	 * @param	MP4Info_Box			$parent
	 * @return 	MP4Info_Box_mdhd
	 * @access 	public
	 * @throws 	MP4Info_Exception
	 */	
	public function __construct($totalSize, $boxType, $data, $parent) {
		if (!self::isCompatible($boxType, $parent)) {
			throw new Exception('This box isn\'t "mdhd"');
		}
		
		// Get timezone
		if (self::$timezone === false) {
			self::$timezone = date('Z');
		}
		
		// Call ancestor
		parent::__construct($totalSize, $boxType, '', $parent);
		
		// Unpack
		$ar = unpack('Cversion/C3flags',$data);
		if ($ar['version'] == 0) {
			// 32 bit
			$ar2 = unpack('Nctime/Nmtime/NtimeScale/Nduration/nlanguage/ndummy',substr($data,4));
		} else if ($ar['version'] == 1) {
			// 64 bit
			$ar2 = unpack('N2ctime/N2mtime/NtimeScale/N2duration/nlanguage/ndummy',substr($data,4));
		} else {
			throw new Exception('Unhandled version: '.$ar['version']);
		}
		
		// Save		
		$this->version = $ar['version'];
		$this->flags = $ar['flags1']*65536+$ar['flags1']*256+$ar['flags1']*1;
		$this->ctime = date('r',(isset($ar2['ctime']) ? $ar2['ctime'] : $ar2['ctime1'])-2082826800-self::$timezone);
		$this->mtime = date('r',(isset($ar2['mtime']) ? $ar2['mtime'] : $ar2['mtime1'])-2082826800-self::$timezone);
		$this->timeScale = $ar2['timeScale'];
		$this->duration = (isset($ar2['duration']) ? $ar2['duration'] : $ar2['duration1']);
		$this->language = MP4Info_Helper::fromPackedLetters($ar2['language'],1);
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
	static function isCompatible($boxType, $parent) {
		return $boxType == 0x6d646864;
	} // isCompatible method

	
	/**
	 * String converter
	 *
	 * @author 	Tommy Lacroix <lacroix.tommy@gmail.com>
	 * @return	string
	 * @access 	public
	 */	
	public function toString() {
		return '[MP4Info_Box_mdhd]';
	} // toString method
} // MP4Info_Box_mdhd class