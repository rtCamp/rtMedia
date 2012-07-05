<?php
/**
 * MP4Info
 * 
 * @author 		Tommy Lacroix <lacroix.tommy@gmail.com>
 * @copyright   Copyright (c) 2006-2009 Tommy Lacroix
 * @license		LGPL version 3, http://www.gnu.org/licenses/lgpl.html
 * @package 	php-mp4info
 * @link 		$HeadURL: https://php-mp4info.googlecode.com/svn/trunk/MP4Info/Box/ftyp.php $
 */

// ---

/**
 * 4.3 File Type Box (FTYP)
 * 
 * @author 		Tommy Lacroix <lacroix.tommy@gmail.com>
 * @version 	1.1.20090611	$Id: ftyp.php 2 2009-06-11 14:12:31Z lacroix.tommy@gmail.com $
 */
class MP4Info_Box_ftyp extends MP4Info_Box {
	/**
	 * Major brand
	 *
	 * @var int
	 */
	protected $majorBrand;

	/**
	 * Minor brand
	 *
	 * @var int
	 */
	protected $minorBrand;

	/**
	 * Compatible brands
	 *
	 * @var int[]
	 */
	protected $compatibleBrands;
	
	/**
	 * Major Brands' names
	 * 
	 * @var {n:Str,n:Str,...}
	 * @static
	 */
	protected static $brandNames = array(
		'3g2a' => '3GPP2 Media (.3G2)',
		'3ge6' => '3GPP (.3GP) Release 6 MBMS Extended Presentations',
		'3ge7' => '3GPP (.3GP) Release 7 MBMS Extended Presentations',
		'3gg6' => '3GPP Release 6 General Profile',
		'3gp1' => '3GPP Media (.3GP) Release 1 ? (non-existent)',
		'3gp2' => '3GPP Media (.3GP) Release 2 ? (non-existent)',
		'3gp3' => '3GPP Media (.3GP) Release 3 ? (non-existent)',
		'3gp4' => '3GPP Media (.3GP) Release 4',
		'3gp5' => '3GPP Media (.3GP) Release 5',
		'3gp6' => '3GPP Media (.3GP) Release 6 Basic Profile',
		'3gr6' => '3GPP Media (.3GP) Release 6 Progressive Download',
		'3gs6' => '3GPP Media (.3GP) Release 6 Streaming Servers',
		'3gs7' => '3GPP Media (.3GP) Release 7 Streaming Servers',
		'avc1' => 'MP4 Base w/ AVC ext [ISO 14496-12:2005]',
		'caep' => 'Canon Digital Camera',
		'caqv' => 'Casio Digital Camera',
		'cdes' => 'Convergent Design',
		'f4v' => 'Video for Adobe Flash Player 9+ (.F4V)',
		'f4p' => 'Protected Video for Adobe Flash Player 9+ (.F4P)',
		'f4a' => 'Audio for Adobe Flash Player 9+ (.F4A)',
		'f4b' => 'Audio Book for Adobe Flash Player 9+ (.F4B)',
		'isc2' => 'ISMACryp 2.0 Encrypted File',
		'iso2' => 'MP4 Base Media v2 [ISO 14496-12:2005]',
		'isom' => 'MP4  Base Media v1 [IS0 14496-12:2003]',
		'jp2' => 'JPEG 2000 Image (.JP2) [ISO 15444-1 ?]',
		'jp20' => 'Unknown, from GPAC samples (prob non-existent)',
		'jpm' => 'JPEG 2000 Compound Image (.JPM) [ISO 15444-6]',
		'jpx' => 'JPEG 2000 w/ extensions (.JPX) [ISO 15444-2]',
		'kddi' => '3GPP2 EZmovie for KDDI 3G Cellphones',
		'm4a ' => 'Apple iTunes AAC-LC (.M4A) Audio',
		'm4b ' => 'Apple iTunes AAC-LC (.M4B) Audio Book',
		'm4p ' => 'Apple iTunes AAC-LC (.M4P) AES Protected Audio',
		'm4v ' => 'Apple iTunes Video (.M4V) Video',
		'm4vh' => 'Apple TV (.M4V)',
		'm4vp' => 'Apple iPhone (.M4V)',
		'mj2s' => 'Motion JPEG 2000 [ISO 15444-3] Simple Profile',
		'mjp2' => 'Motion JPEG 2000 [ISO 15444-3] General Profile',
		'mmp4' => 'MPEG-4/3GPP Mobile Profile (.MP4 / .3GP) (for NTT)',
		'mp21' => 'MPEG-21 [ISO/IEC 21000-9]',
		'mp41' => 'MP4 v1 [ISO 14496-1:ch13]',
		'mp42' => 'MP4 v2 [ISO 14496-14]',
		'mp71' => 'MP4 w/ MPEG-7 Metadata [per ISO 14496-12]',
		'mppi' => 'Photo Player, MAF [ISO/IEC 23000-3]',
		'mqt' => 'Sony / Mobile QuickTime (.MQV)',
		'msnv' => 'MPEG-4 (.MP4) for SonyPSP',
		'ndas' => 'MP4 v2 [ISO 14496-14] Nero Digital AAC Audio',
		'ndsc' => 'MPEG-4 (.MP4) Nero Cinema Profile',
		'ndsh' => 'MPEG-4 (.MP4) Nero HDTV Profile',
		'ndsm' => 'MPEG-4 (.MP4) Nero Mobile Profile',
		'ndsp' => 'MPEG-4 (.MP4) Nero Portable Profile',
		'ndss' => 'MPEG-4 (.MP4) Nero Standard Profile',
		'ndxc' => 'H.264/MPEG-4 AVC (.MP4) Nero Cinema Profile',
		'ndxh' => 'H.264/MPEG-4 AVC (.MP4) Nero HDTV Profile',
		'ndxm' => 'H.264/MPEG-4 AVC (.MP4) Nero Mobile Profile',
		'ndxp' => 'H.264/MPEG-4 AVC (.MP4) Nero Portable Profile',
		'ndxs' => 'H.264/MPEG-4 AVC (.MP4) Nero Standard Profile',
		'odcf  ' => 'OMA DCF DRM Format 2.0 (OMA-TS-DRM-DCF-V2_0-20060303-A)',
		'opf2 ' => 'OMA PDCF DRM Format 2.1 (OMA-TS-DRM-DCF-V2_1-20070724-C)',
		'opx2  ' => 'OMA PDCF DRM + XBS extensions (OMA-TS-DRM_XBS-V1_0-20070529-C)',
		'qt  ' => 'Apple QuickTime (.MOV/QT)',
		'sdv' => 'SD Memory Card Video',
	);
	
	
	/**
	 * Constructor
	 *
	 * @author 	Tommy Lacroix <lacroix.tommy@gmail.com>
	 * @param	int					$totalSize
	 * @param	int					$boxType
	 * @param	file|string			$data
	 * @param	MP4Info_Box			$parent
	 * @return 	MP4Info_Box_ftyp
	 * @access 	public
	 * @throws 	MP4Info_Exception
	 */	
	public function __construct($totalSize, $boxType, $data, $parent) {
		if (!self::isCompatible($boxType, $parent)) {
			throw new MP4Info_Exception('This box isn\'t "ftyp"',MP4Info_Exception::CODE_INCOMPATIBLE,$boxType);
		}

		// Call ancestor
		parent::__construct($totalSize, $boxType, false, $parent);
		
		// Get data
		$data = self::getDataFrom3rd($data, $totalSize);

		// Unpack
		$ar = unpack('NmajorBrand/NminorVersion/N*compatibleBrands',$data);
		$compatibleBrands = array();
		foreach ($ar as $k=>$v) {
			if (substr($k,0,16) == 'compatibleBrands') {
				$compatibleBrands[] = $v;
			}
		}
		
		// Save properties
		$this->majorBrand = $ar['majorBrand'];
		$this->minorVersion = $ar['minorVersion'];
		$this->compatibleBrands = $compatibleBrands;
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
		return $boxType == 0x66747970;
	} // isCompatible method
	
	
	/**
	 * Major brand getter
	 *
	 * @author 	Tommy Lacroix <lacroix.tommy@gmail.com>
	 * @return	int
	 * @access 	public
	 */
	public function getMajorBrand() {
		return $this->majorBrand;
	} // getMajorBrand method

	
	/**
	 * Minor version getter
	 *
	 * @author 	Tommy Lacroix <lacroix.tommy@gmail.com>
	 * @return	int
	 * @access 	public
	 */
	public function getMinorVersion() {
		return $this->minorVersion;
	} // getMinorVersion method
	

	/**
	 * Compatible brands getter
	 *
	 * @author 	Tommy Lacroix <lacroix.tommy@gmail.com>
	 * @return	int[]
	 * @access 	public
	 */
	public function getCompatibleBrands() {
		return $this->compatibleBrands;
	} // getCompatibleBrands method

	
	/**
	 * Convert a brand 32bit code to a string
	 *
	 * @author 	Tommy Lacroix <lacroix.tommy@gmail.com>
	 * @param	int			$brand
	 * @return	string
	 * @access 	public
	 * @static
	 */
	public static function brandToString($brand) {
		if (isset(self::$$brandNames[$brand])) {
			return self::$$brandNames[$brand];
		} else {
			return $brand;
		}
	} // brandToString method
	
	
	/**
	 * String converter
	 *
	 * @author 	Tommy Lacroix <lacroix.tommy@gmail.com>
	 * @return	string
	 * @access 	public
	 */
	public function toString() {
		return '[MP4Info_Box_ftyp]';
	} // toString method
} // MP4Info_Box_ftyp method