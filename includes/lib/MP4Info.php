<?php
/**
 * MP4Info
 * 
 * @author 		Tommy Lacroix <lacroix.tommy@gmail.com>
 * @copyright   Copyright (c) 2006-2009 Tommy Lacroix
 * @license		LGPL version 3, http://www.gnu.org/licenses/lgpl.html
 * @package 	php-mp4info
 * @link 		$HeadURL: https://php-mp4info.googlecode.com/svn/trunk/MP4Info.php $
 */

// ---

/**
 * MP4Info main class
 * 
 * @author 		Tommy Lacroix <lacroix.tommy@gmail.com>
 * @version 	1.1.20090611	$Id: MP4Info.php 2 2009-06-11 14:12:31Z lacroix.tommy@gmail.com $
 */
class MP4Info {
	// {{{ Audio codec types
	const MP4_AUDIO_CODEC_UNCOMPRESSED = 0x00;
	const MP4_AUDIO_CODEC_MP3 = 0x02;
	const MP4_AUDIO_CODEC_AAC = 0xe0;
	// }}}
	
	// {{{ Video codec types
	const MP4_VIDEO_CODEC_H264 = 0xe0;
	// }}}	
	

	/**
	 * Get information from MP4 file
	 *
	 * @author 	Tommy Lacroix <lacroix.tommy@gmail.com>
	 * @param	string		$file
	 * @return	array
	 * @access 	public
	 * @static
	 */
	public static function getInfo($file) {
		// Open file
		$f = fopen($file,'rb');
		if (!$f) {
			throw new Exception('Cannot open file: '.$file);
		}
		
		// Get all boxes
		try {
			while (($box = MP4Info_Box::fromStream($f))) {
				$boxes[] = $box;
			}
		} catch (Exception $e) { }
		
		// Close
		fclose($f);
		
		// Return info
		return self::getInfoFromBoxes($boxes);
	} // getInfo method
	
	
	/**
	 * Get information from MP4 boxes
	 *
	 * @author 	Tommy Lacroix <lacroix.tommy@gmail.com>
	 * @param	string		$file
	 * @return	array
	 * @access 	public
	 * @static
	 */	
	public static function getInfoFromBoxes($boxes, &$context=null) {
		if ($context === null) {
			$context = new stdClass();
			$context->hasVideo = false;
			$context->hasAudio = false;
			$context->video = new stdClass();
			$context->audio = new stdClass();
		}
		
		foreach ($boxes as &$box) {
			// Interpret box
			switch ($box->getBoxTypeStr()) {
				case 'hdlr':
					switch ($box->getHandlerType()) {
						case MP4Info_Box_hdlr::HANDLER_VIDEO:
							$context->hasVideo = true;
							break;
						case MP4Info_Box_hdlr::HANDLER_SOUND:
							$context->hasAudio = true;
							break;
					}
					break;
				case 'mvhd':
					$context->duration = $box->getRealDuration();
					break;
				case 'ilst':
					if ($box->hasValue('©too')) {
						$context->encoder = $box->getValue('©too');
					}
					break;
				case 'uuid':
					$meta = $box->getXMPMetaData();
					if ($meta !== false) {
						// Try to get duration
						if (!isset($context->duration)) {
							if (preg_match('/<(|[a-z]+:)duration[\s\n\r]([^>]*)>/im',$meta,$m)) {
								if (preg_match_all('/xmpDM:([a-z]+)="([^"]+)"/',$m[2],$mm)) {
									$value = $scale = false;
									foreach ($mm[1] as $k=>$v) {
										if (($v == 'value') || ($v == 'scale')) {
											if (preg_match('/^1\/([0-9]+)$/',$mm[2][$k],$mmm)) {
												$mm[2][$k] = 1/$mmm[1];
											}
											$$v = $mm[2][$k];
										}
									}
									if (($value !== false) && ($scale !== false)) {
										$context->duration = $value*$scale;
									}
								}
							}
						}
						
						// Try to get size
						if ((!isset($context->width)) || (!isset($context->height))) {
							if (preg_match('/<(|[a-z]+:)videoFrameSize[\s\n\r]([^>]*)>/im',$meta,$m)) {
								if (preg_match_all('/[a-z]:([a-z]+)="([^"]+)"/',$m[2],$mm)) {
									$w = $h = false;
									foreach ($mm[1] as $k=>$v) {
										if (($v == 'w') || ($v == 'h')) {
											$$v = $mm[2][$k];
										}
									}
									if ($w != false) {
										$context->video->width = $w;
										$context->hasVideo = true;
									}
									if ($h != false) {
										$context->video->height = $h;
										$context->hasVideo = true;
									}
								}
							}
						}			
						
						// Try to get encoder
						if (preg_match('/softwareAgent="([^"]+)"/i',$meta,$m)) {
							$context->encoder = $m[1];
						}
						
						// Try to get audio channels
						if (preg_match('/audioChannelType="([^"]+)"/i',$meta,$m)) {
							switch (strtolower($m[1])) {
								case 'stereo':
								case '2':
									$context->audio->channels = 2;
									$context->hasAudio = true;
									break;
								case 'mono':
								case '1':
									$context->audio->channels = 1;
									$context->hasAudio = true;
									break;
								case '5.1':
								case '5':
									$context->audio->channels = 5;
									$context->hasAudio = true;
									break;
							}
						}						
						
						// Try to get audio frequency
						if (preg_match('/audioSampleRate="([^"]+)"/i',$meta,$m)) {
							$context->audio->frequency = $m[1]/1000;
							$context->hasAudio = true;
						}
						
						// Try to get video frame rate
						if (preg_match('/videoFrameRate="([^"]+)"/i',$meta,$m)) {
							$context->video->fps = $m[1];
							$context->hasVideo = true;
						}
						
						//print htmlentities($meta);
					}
					break;
				case 'stsd':
					$values = $box->getValues();
					foreach (array_keys($values) as $codec) {
						switch ($codec) {
							case '.mp3':
								$context->audio->codec = self::MP4_AUDIO_CODEC_MP3;
								$context->audio->codecStr = 'MP3';
								$context->hasAudio = true;
								break;
							case 'mp4a':
							case 'mp4s':
								$context->audio->codec = self::MP4_AUDIO_CODEC_AAC;
								$context->audio->codecStr = 'AAC';
								$context->hasAudio = true;
								break;
							case 'avc1':
							case 'h264':
							case 'H264':
								$context->video->codec = self::MP4_VIDEO_CODEC_H264;
								$context->video->codecStr = 'H.264';
								$context->hasVideo = true;
								break;
						}
					}
					break;
				case 'tkhd':
					if ($box->getWidth() > 0) {
						$context->hasVideo = true;
						$context->video->width = $box->getWidth();
						$context->video->height = $box->getHeight();
						$context->hasVideo = true;
					}
					break;
			}
			
			// Process children
			if ($box->hasChildren()) {
				self::getInfoFromBoxes($box->children(), $context);
			}
		}
		
		return $context;
	} // getInfoFromBoxes method
	
	
	/**
	 * Display boxes for debugging
	 *
	 * @param	MP4Info_Box[]	$boxes
	 * @param 	int				$level
	 * @access 	public
	 * @static
	 */
	public static function displayBoxes($boxes,$level=0) {
		foreach ($boxes as $box) {
			print str_repeat('&nbsp;',$level*4) . $box->toString() . '<br>';
			if ($box->hasChildren()) {
				$this->displayBoxes($box->children(), $level+1);
			}
		}
	} // displayBoxes method
} // MP4Info class

// ---

// {{{ Dependencies
include "MP4Info/Helper.php";
include "MP4Info/Exception.php";
include "MP4Info/Box.php";
// }}} Dependencies
