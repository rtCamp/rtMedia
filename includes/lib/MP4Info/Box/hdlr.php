<?php
/**
 * MP4Info
 * 
 * @author 		Tommy Lacroix <lacroix.tommy@gmail.com>
 * @copyright   Copyright (c) 2006-2009 Tommy Lacroix
 * @license		LGPL version 3, http://www.gnu.org/licenses/lgpl.html
 * @package 	php-mp4info
 * @link 		$HeadURL: https://php-mp4info.googlecode.com/svn/trunk/MP4Info/Box/hdlr.php $
 */

// ---

/**
 * 8.9 Handler Reference Box (HDLR)
 * 
 * @author 		Tommy Lacroix <lacroix.tommy@gmail.com>
 * @version 	1.0.20090601	$Id: hdlr.php 2 2009-06-11 14:12:31Z lacroix.tommy@gmail.com $
 * @todo 		Factor this into a fullbox
 */
class MP4Info_Box_hdlr extends MP4Info_Box {
	// {{{ Constants
	const HANDLER_VIDEO = 'vide';
	const HANDLER_SOUND = 'soun';
	// }}} Constants
	
	/**
	 * Handler type
	 *
	 * @var uint32
	 */
	protected $handlerType;
	
	/**
	 * Name
	 *
	 * @var	string
	 */
	protected $name;
	
	/**
	 * Timezone
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
	 * @return 	MP4Info_Box_hdlr
	 * @access 	public
	 * @throws 	MP4Info_Exception
	 */	
	public function __construct($totalSize, $boxType, $data, $parent) {
		if (!self::isCompatible($boxType, $parent)) {
			throw new Exception('This box isn\'t "ftyp"');
		}

		// Get timezone
		if (self::$timezone === false) {
			self::$timezone = date('Z');
		}
		
		// Call ancestor
		parent::__construct($totalSize,$boxType,'',$parent);
		
		// Get data
		$data = self::getDataFrom3rd($data,$totalSize);

		// Unpack
		$ar = unpack('Cversion/C3flags',$data);
		if ($ar['version'] == 0) {
			// 32 bit
			$ar2 = unpack('Nctime/Nmtime/NtimeScale/Nduration',substr($data,4));
			$len = 6*4;
		} else if ($ar['version'] == 1) {
			// 64 bit
			$ar2 = unpack('N2ctime/N2mtime/NtimeScale/N2duration',substr($data,4));
			$len = 9*4;
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
		$this->handlerType = substr($data,$len,4);
		$this->name = substr($data,$len+8,-1);
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
		return $boxType == 0x68646c72;
	} // isCompatible method
	
	
	/**
	 * Handler type getter
	 *
	 * @author 	Tommy Lacroix <lacroix.tommy@gmail.com>
	 * @return	int
	 * @access 	public
	 */
	public function getHandlerType() {
		return $this->handlerType;
	} // getHandlerType method
	
	
	/**
	 * String converter
	 *
	 * @author 	Tommy Lacroix <lacroix.tommy@gmail.com>
	 * @return	string
	 * @access 	public
	 */	
	public function toString() {
		return '[MP4Info_Box_hdlr:'.$this->handlerType.']';
	} // toString method
} // MP4Info_Box_hdlr class