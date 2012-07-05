<?php
/**
 * MP4Info
 * 
 * @author 		Tommy Lacroix <lacroix.tommy@gmail.com>
 * @copyright   Copyright (c) 2006-2009 Tommy Lacroix
 * @license		LGPL version 3, http://www.gnu.org/licenses/lgpl.html
 * @package 	php-mp4info
 * @link 		$HeadURL: https://php-mp4info.googlecode.com/svn/trunk/MP4Info/Box/tkhd.php $
 */

// ---

/**
 * x.x Track Header (TKHD)
 * 
 * @author 		Tommy Lacroix <lacroix.tommy@gmail.com>
 * @version 	1.1.20090611	$Id: tkhd.php 2 2009-06-11 14:12:31Z lacroix.tommy@gmail.com $
 * @todo 		Factor this into a fullbox
 */
class MP4Info_Box_tkhd extends MP4Info_Box {
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
	 * Layer
	 * 
	 * @var int
	 */
	protected $layer;
	
	/**
	 * Volume
	 * 
	 * @var float
	 */
	protected $volume;

	/**
	 * Width
	 *
	 * @var float
	 */
	protected $width;

	/**
	 * Height
	 *
	 * @var float
	 */
	protected $height;
	
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
	 * @return 	MP4Info_Box_tkhd
	 * @access 	public
	 * @throws 	MP4Info_Exception
	 */		
	public function __construct($totalSize, $boxType, $data, $parent=false) {
		if (!self::isCompatible($boxType,$parent)) {
			throw new Exception('This box isn\'t "tkhd"');
		}
		
		// Get timezone
		if (self::$timezone === false) {
			self::$timezone = date('Z');
		}
		
		// Call ancestor's constructor
		parent::__construct($totalSize,$boxType,'',$parent);
		
		// Get data
		$data = self::getDataFrom3rd($data,$totalSize);
		
		// Unpack
		$ar = unpack('Cversion/C3flags',$data);
		if ($ar['version'] == 0) {
			// 32 bit
			$ar2 = unpack('Nctime/Nmtime/NtrackId/Ndummy/Nduration/N2dummy1/nlayer/naltGroup/nvolume/ndummy2/N9matrix/Nwidth/Nheight',substr($data,4));
		} else if ($ar['version'] == 1) {
			// 64 bit
			$ar2 = unpack('N2ctime/N2mtime/NtrackId/Ndummy/N2duration/N2dummy1/nlayer/naltGroup/nvolume/ndummy2/N9matrix/Nwidth/Nheight',substr($data,4));
		} else {
			throw new Exception('Unhandled version: '.$ar['version']);
		}
		
		// Save		
		$this->version = $ar['version'];
		$this->flags = $ar['flags1']*65536+$ar['flags1']*256+$ar['flags1']*1;
		$this->ctime = date('r',(isset($ar2['ctime']) ? $ar2['ctime'] : $ar2['ctime1'])-2082826800-self::$timezone);
		$this->mtime = date('r',(isset($ar2['mtime']) ? $ar2['mtime'] : $ar2['mtime1'])-2082826800-self::$timezone);
		$this->trackId = $ar2['trackId'];
		$this->duration = (isset($ar2['duration']) ? $ar2['duration'] : $ar2['duration1']);
		$this->layer = ($ar2['layer']>32767 ? $ar2['layer']-65536 : $ar2['layer']);
		$this->volume = MP4Info_Helper::fromFixed8($ar2['volume']);
		$this->width = MP4Info_Helper::fromFixed16($ar2['width']);
		$this->height = MP4Info_Helper::fromFixed16($ar2['height']);
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
		return $boxType == 0x746b6864;
	} // isCompatible method
	
	
	/**
	 * Width getter
	 *
	 * @author 	Tommy Lacroix <lacroix.tommy@gmail.com>
	 * @return 	float
	 * @access 	public
	 */
	public function getWidth() {
		return $this->width;
	} // getWidth method

	
	/**
	 * Height getter
	 *
	 * @author 	Tommy Lacroix <lacroix.tommy@gmail.com>
	 * @return 	float
	 * @access 	public
	 */
	public function getHeight() {
		return $this->height;
	} // getHeight method

	
	/**
	 * Duration getter
	 *
	 * @author 	Tommy Lacroix <lacroix.tommy@gmail.com>
	 * @return 	int
	 * @access 	public
	 */
	public function getDuration() {
		return $this->duration;
	} // getDuration method

	
	/**
	 * String converter
	 *
	 * @author 	Tommy Lacroix <lacroix.tommy@gmail.com>
	 * @return	string
	 * @access 	public
	 */		
	public function toString() {
		return '[MP4Info_Box_tkhd]';
	} // toString method
} // MP4Info_Box_tkhd class