<?php
/**
 * Template to display embedded playlists.
 *
 * @package   CuePro
 * @copyright Copyright (c) 2016, AudioTheme, LLC
 * @license   GPL-2.0+
 * @since     1.1.0
 */

?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<title><?php echo wp_get_document_title(); ?></title>
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<?php do_action( 'cue_embed_head' ); ?>
</head>
<body class="cue-embed">
	<?php cue_playlist( get_post(), $args ); ?>
	<?php do_action( 'cue_embed_footer' ); ?>
	<?php wp_print_scripts( array( 'cue', 'cuepro-insights' ) ); ?>
</body>
</html>
