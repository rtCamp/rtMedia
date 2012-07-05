<?php
/**
 * MP4Info
 * 
 * @author 		Tommy Lacroix <lacroix.tommy@gmail.com>
 * @copyright   Copyright (c) 2006-2009 Tommy Lacroix
 * @license		LGPL version 3, http://www.gnu.org/licenses/lgpl.html
 * @package 	php-mp4info
 * @link 		$HeadURL: https://php-mp4info.googlecode.com/svn/trunk/MP4Info/Box/mvhd.php $
 */

// ---

/**
 * x.x Movie Header (MVHD)
 * 
 * @author 		Tommy Lacroix <lacroix.tommy@gmail.com>
 * @version 	1.0.20090601	$Id: mvhd.php 2 2009-06-11 14:12:31Z lacroix.tommy@gmail.com $
 * @todo 		Factor this into a fullbox
 */
class MP4Info_Box_mvhd extends MP4Info_Box {
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
	 * Rate
	 *
	 * @var int
	 */
	protected $rate;
	
	/**
	 * Volume
	 *
	 * @var int
	 */
	protected $volume;
	
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
	 * @return 	MP4Info_Box_mvhd
	 * @access 	public
	 * @throws 	MP4Info_Exception
	 */		
	public function __construct($totalSize, $boxType, $data, $parent) {
		if (!self::isCompatible($boxType)) {
			throw new Exception('This box isn\'t "mvhd"');
		}
		
		// Get timezone
		if (self::$timezone === false) {
			self::$timezone = date('Z');
		}
		
		// Call ancestor's constructor
		parent::__construct($totalSize,$boxType,'',$parent);

		// Unpack
		$ar = unpack('Cversion/C3flags',$data);
		if ($ar['version'] == 0) {
			// 32 bit
			$ar2 = unpack('Nctime/Nmtime/NtimeScale/Nduration/Nrate/nvolume/ndummy/N2dummy2/N9matrix/N3dummy3/NnextTrack',substr($data,4));
		} else if ($ar['version'] == 1) {
			// 64 bit
			$ar2 = unpack('N2ctime/N2mtime/NtimeScale/N2duration/Nrate/nvolume/ndummy/N2dummy2/N9matrix/N3dummy3/NnextTrack',substr($data,4));
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
		$this->rate = MP4Info_Helper::fromFixed16($ar2['rate']);
		$this->volume = MP4Info_Helper::fromFixed8($ar2['volume']);
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
	static public function isCompatible($boxType) {
		return $boxType == 0x6D766864;
	} // isCompatible method

	
	/**
	 * Creation time getter
	 *
	 * @author 	Tommy Lacroix <lacroix.tommy@gmail.com>
	 * @return 	int
	 * @access 	public
	 */
	public function getCreationTime() {
		return $this->ctime;
	} // getCreationTime method

	
	/**
	 * Time scale getter
	 *
	 * @author 	Tommy Lacroix <lacroix.tommy@gmail.com>
	 * @return 	int
	 * @access 	public
	 */
	public function getTimeScale() {
		return $this->timeScale;
	} // getTimeScale method
	
	
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
	 * Real duration getter
	 *
	 * @author 	Tommy Lacroix <lacroix.tommy@gmail.com>
	 * @return 	float
	 * @access 	public
	 */
	public function getRealDuration() {
		return $this->duration/$this->timeScale;
	} // getRealDuration method
	
	
	/**
	 * Rate getter
	 *
	 * @author 	Tommy Lacroix <lacroix.tommy@gmail.com>
	 * @return 	int
	 * @access 	public
	 */
	public function getRate() {
		return $this->rate();
	} // getRate method

	
	/**
	 * Volume getter
	 *
	 * @author 	Tommy Lacroix <lacroix.tommy@gmail.com>
	 * @return 	int
	 * @access 	public
	 */
	public function getVolume() {
		return $this->volume();
	} // getVolume method

	
	/**
	 * String converter
	 *
	 * @author 	Tommy Lacroix <lacroix.tommy@gmail.com>
	 * @return	string
	 * @access 	public
	 */		
	public function toString() {
		return '[MP4Info_Box_mvhd]';
	} // toString method
}