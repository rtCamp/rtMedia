<?php
/**
 * MP4Info
 * 
 * @author 		Tommy Lacroix <lacroix.tommy@gmail.com>
 * @copyright   Copyright (c) 2006-2009 Tommy Lacroix
 * @license		LGPL version 3, http://www.gnu.org/licenses/lgpl.html
 * @package 	php-mp4info
 * @link 		$HeadURL: https://php-mp4info.googlecode.com/svn/trunk/MP4Info/Box/ilst_sub.php $
 */

// ---

/**
 * x.x ILST sub blocks (numerous)
 * 
 * @author 		Tommy Lacroix <lacroix.tommy@gmail.com>
 * @version 	1.1.20090611	$Id: ilst_sub.php 2 2009-06-11 14:12:31Z lacroix.tommy@gmail.com $
 */
class MP4Info_Box_ilst_sub extends MP4Info_Box {
	/**
	 * Constructor
	 *
	 * @author 	Tommy Lacroix <lacroix.tommy@gmail.com>
	 * @param	int					$totalSize
	 * @param	int					$boxType
	 * @param	file|string			$f
	 * @param	MP4Info_Box			$parent
	 * @return 	MP4Info_Box_ilst_sub
	 * @access 	public
	 * @throws 	MP4Info_Exception
	 */		
	public function __construct($totalSize, $boxType, $data, $parent) {
		if (!$parent instanceof MP4Info_Box_ilst) {
			throw new MP4Info_Exception('This box isn\'t "islt" child', MP4Info_Exception::CODE_INCOMPATIBLE, $boxType);
		}

		// Call ancestor
		parent::__construct($totalSize,$boxType,$data,$parent);
		
		// Get data
		$data = $this->data;
		
		// Unpack
		$type = self::getType($this->boxType);
		$ar = unpack('Nlen',$data);
		if (substr($data,4,4) == 'data') {
			$info = substr($data,8,$ar['len']-8);
			switch ($type) {
				case 'uint8':
					$info = reset(unpack('C',$info));
					break;
				case 'uint16':
					$info = reset(unpack('n',$info));
					break;
				case 'uint32':
					$info = reset(unpack('N',$info));
					break;
				case 'text':
					break;
			}
			$this->data = $info;
			$parent->setKeyValue($this->boxTypeStr, $info);
		} else {
			throw new MP4Info_Exception('Didn\'t get the "data" code');
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
		return ($parent instanceof MP4Info_Box_ilst);
	} // isCompatible method

	
	/**
	 * Get sub type
	 * http://atomicparsley.sourceforge.net/mpeg-4files.html
	 *
	 * @author 	Tommy Lacroix <lacroix.tommy@gmail.com>
	 * @param	int			$boxType
	 * @return	string
	 * @access 	protected
	 * @static
	 * @todo 	The © codes should be chr(...), as utf8 encoding messes things up
	 */
	static protected function getType($boxType) {
		switch (pack('N',$boxType)) {
			case '©alb':
			case '©art':
			case 'aART':
			case '©cmt':
			case '©day':
			case '©nam':
			case '©gen':
			case '©wrt':
			case '©too':
			case 'cprt':
			case '©grp':
			case 'catg':
			case 'desc':
			case '©lyr':
			case 'tvnn':
			case 'tvsh':
			case 'tven':
			case 'purd':
				return 'text';

			case 'gnre':
			case 'trkn':
			case 'disk':
			case 'tmpo':
			case 'cpil':
			case 'rtng':
			case 'stik':
			case 'pcst':
			case 'purl':
			case 'egid':
			case 'tvsn':
			case 'tves':
			case 'pgap':
				return 'uint8';
			default:
				return '';
		}
	} // getType method
	

	/**
	 * String converter
	 *
	 * @author 	Tommy Lacroix <lacroix.tommy@gmail.com>
	 * @return	string
	 * @access 	public
	 */		
	public function toString() {
		return '[MP4Info_Box_ilst_sub['.$this->boxTypeStr.']:'.$this->getData().']';
	} // toString converter
} // MP4Info_Box_ilst_sub class