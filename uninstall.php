<?php
if( !defined( 'ABSPATH') && !defined('WP_UNINSTALL_PLUGIN') ){ exit(); }
		
delete_option( 'hyvesrespect_location' );
delete_option( 'hyvesrespect_displaystyle' );
delete_option( 'hyvesrespect_showmeta' );
delete_option( 'hyvesrespect_breakbefore' );
delete_option( 'hyvesrespect_breakafter' );
delete_option( 'hyvesrespect_showonlyinsingle' );
delete_option( 'hyvesrespect_stylelayout' );
?>