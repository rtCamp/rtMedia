<?php
/**
 * Displays message.
 *
 * @package rtMedia
 */

?>
<# if( data.strong && '' !== data.strong ) { #>
	<p><strong>{{data.msg}}</strong></p>
<# } else { #>
	<p>{{data.msg}}</p>
<# } #>
