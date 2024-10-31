<?php
/**
 * Hooks Files
 *
 * @package  Conexa\OcasaWoo\Hooks
 */

use Conexa\Woo\Helper\Helper;
$url = $args['url'];
?>
<div class="ocasa-cover">
	<img src="<?php echo esc_url( Helper::get_assets_folder_url() . '/ocasa.png' ); ?>" />
</div>
<style>
	.ocasa-cover{
		width: 100%;
		height: 100%;
		position:absolute;
		top:0;
		left:0;
		background-color: white;
		display: flex;
		justify-content: center;
		align-items: center;
	}
</style>
<script>
	jQuery(document).ready(function(){                       
		var win = window.open("<?php echo esc_url( $url ); ?>", '_blank');            
	});
</script>
