<?php
// Zakoncz, jeżeli plik jest załadowany bezpośrednio
if ( !defined( 'ABSPATH' ) )
	exit;
?>

<div class="wrap tplis-cl-settings">

	<div class="tplis-cl-admin-head">
	<h1><?php _e( 'EU Cookie Law Compliance', TPLIS_CL_DOMAIN ); ?></h1>
	<a target="_blank" class="button button-primary tplis-cl-button-preview" href="<?php echo add_query_arg( array( 'tplis-cl-preview' => 'yes' ), home_url() ); ?>" ><span class="dashicons dashicons-visibility"></span> <?php _e( 'Live preview', TPLIS_CL_DOMAIN ); ?></a>
	</div>
	
	<?php $settings->show_navigation(); ?>
	<?php $settings->show_forms(); ?>

</div>