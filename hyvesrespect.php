<?php
/*
Plugin Name: Hyves Respect
Plugin URI: http://wordpress.org/extend/plugins/hyves-respect/ 
Description: Adds a Hyves respect button to your posts and lets you customize it's display methods (Obsolete, Hyves has been shut down)
Author: Martijn Heesters - d-Media
Version: 1.4
Author URI: http://d-media.nl
*/

// in admin area add pointer with new feature, only show this once on the plugin page
if ( is_admin() ) {
	// only show pointer once
	if ( (get_option( 'hvyesrespect_pointershowed') != true) ) {
		add_action( 'admin_enqueue_scripts','enqueue');
		function enqueue() {
			// following scripts are needed to show pointers
			wp_enqueue_style( 'wp-pointer' ); 
			wp_enqueue_script( 'jquery-ui' ); 
			wp_enqueue_script( 'wp-pointer' ); 
			wp_enqueue_script( 'utils' );
			add_action( 'admin_print_footer_scripts','hs_print_footer_scripts', 99 );
		}
	}
}

function hs_print_footer_scripts() {
	// show pointer on hyves respect plugin page
	$page = '';
	if ( isset($_GET['page']) ){
		$page = $_GET['page'];
		if ( $page == 'hyvesrespect.php' ) {
			$id 			= 'hyvesrespect_stylelayout';
			$content 		= '<h3>'.__( 'New feature: Style layout', 'hyvesrespect' ).'</h3>';
			$content 		.= '<p>'.__( 'We\'ve implemented a new styling feature. You can now choose between 3 layouts of the button: horizontal, horizontal without a counter and vertical.', 'hyvesrespect' ).'</p>';
			$position_at 	= 'top left';
			}
		hs_print_button( $id, $content, __( "Close", 'hyvesrespect' ), $position_at);
		add_option( 'hvyesrespect_pointershowed', true ); // set option so pointer will only be shown once
	}
}

function hs_print_button( $id, $content, $button, $position_at) {
?>
<script type="text/javascript"> 
//<![CDATA[ 
jQuery(document).ready( function() { 
	jQuery('#<?php echo $id; ?>').pointer({ 
		content: '<?php echo addslashes( $content ); ?>', 
		buttons: function( event, t ) {
			button = jQuery('<a id="pointer-close" class="button-secondary">' + '<?php echo $button; ?>' + '</a>');
			button.bind( 'click.pointer', function() {
				t.element.pointer('close');
			});
			return button;
		},
		position: {
			my: 'top', 
			at: '<?php echo $position_at; ?>', 
			offset: '40 85'
		},
		arrow: {
			edge: 'left',
			align: 'top',
			offset: 10
		},
		close: function() { },
	}).pointer('open'); 
}); 
//]]> 
</script>
<?php
}
	
// Only of use in the admin interface
if ( is_admin() ) {				
    add_action( 'admin_init' , 'hyvesrespect_register_plugin_settings' ); // Setup plugin component registration
    add_action( 'admin_menu' , 'hyvesrespect_options' ); // if you're in the admin menu, show the options panel
} else {
    // if you're not in the admin menu but on the frontend, include the meta tags (if they are enabled in the settings)
    if (get_option( 'hyvesrespect_showmeta' ) != 1){ add_action( 'wp_head','meta_add_hyves' ); }
}

// front end

// this function adds opengraph tags to the page header
function meta_add_hyves(){
    global $post;
    setup_postdata($post);
    $des = $post->post_excerpt;
    if ( $post->post_excerpt == "" ){
            $des = $post->post_content;
            $des = strip_tags( $des );
            $des = substr( $des, 0, 200 );
    }
    $des = htmlentities( $des, ENT_COMPAT, "UTF-8" );
    if ( is_single() ){
        ?>
        <script> jQuery(document).ready(function(){ jQuery("html").attr("xmlns:og","http://opengraphprotocol.org/schema/") }); </script>
        <meta property="og:type" content="article" />
        <meta property="og:title" content="<?php echo $post->post_title ?>" />
        <meta property="og:site_name" content="<?php bloginfo( 'name' ) ?>" />
        <meta property="og:url" content="<?php echo get_permalink( $post->ID ) ?>" />
        <meta property="og:description" content="<?php echo $des; ?>" />
        <?php
    }
}

// this function receives the post content,adds the button, and returns the result
function add_post_footer_hyvesrespect( $text ){
    global $posts;
    // only show if it's a single page or if it's not a single page and showonlysingle is not enabled
    if ( 
        is_single() ||	
        ( get_option( 'hyvesrespect_showonlyinsingle' ) != 1 )
    ){
			// 3 different display styles
			switch(get_option( 'hyvesrespect_stylelayout' )){
				case 'vertical':
					$styleDisplay='vertical';
					$styleWidth='70';
					$styleHeight='80';				
					break;
				case 'nocount':
					$styleDisplay='no-count';
					$styleWidth='70';
					$styleHeight='80';				
					break;
				default:
					$styleDisplay='horizontal';
					$styleWidth='150';
					$styleHeight='21';
			} // switch

        // add break before iframe if option is selected
        if ( get_option( 'hyvesrespect_breakbefore' ) == 1 ){ $breakBefore = '<br />'; } else { $breakBefore = ''; }
        // add break after iframe if option is selected
        if ( get_option( 'hyvesrespect_breakafter' ) == 1 ){ $breakAfter = '<br />'; } else { $breakAfter = ''; }
        // show as block or inline style depending on setting
        if ( get_option( 'hyvesrespect_displaystyle' ) == 'inline' ){ $displayType = 'inline'; } else { $displayType = 'block'; }
        // code for the button
        $iframe='<iframe src="http://www.hyves.nl/respect/button?counterStyle='.$styleDisplay.'&url='.urlencode( get_permalink( $post->ID ) ).'" style="display: '.$displayType.',border: medium none; overflow:hidden; width:'.$styleWidth.'px; height:'.$styleHeight.'px;" scrolling="no" frameborder="0" allowTransparency="true" ></iframe>';
        // if selected add a containing div with a classname
        if (get_option( 'hyvesrespect_divstyling' ) == 1){ $iframe='<div class="hyvesRespectButton">'.$iframe.'</div>'; }                
        // show button before or after the post depening on setting
        if ( get_option( 'hyvesrespect_location' ) == 'top' ){
                $text=$breakBefore.$iframe.$breakAfter.$text;
        } else {
                $text=$text.$breakBefore.$iframe.$breakAfter;
        }
    }
    // return the post
    return $text;
}

// add filter to the content
add_filter( 'the_content', 'add_post_footer_hyvesrespect' );

// admin area

// register plugin options
function hyvesrespect_register_plugin_settings() {
    // only for users who can manage options
    if ( current_user_can( 'manage_options' ) ){
			// load localisation
			if ( ! load_plugin_textdomain( 'hyvesrespect', '/wp-content/languages/' ) ){
				load_plugin_textdomain( 'hyvesrespect', false, basename( dirname( __FILE__ ) ) . '/i18n' );
			}
			// register css
         wp_register_style( 'hyvesrespectStylesheet', WP_PLUGIN_URL .'/'.basename( dirname( __FILE__ ) ).'/hyvesrespect.css' );
         // register js
         wp_register_script( 'hyvesrespectScript', WP_PLUGIN_URL .'/'.basename( dirname( __FILE__ ) ).'/hyvesrespect.js' );		  
        // add options with default values (only adds them if they don't exist yet)
        add_option( 'hyvesrespect_location', 'bottom' );
        add_option( 'hyvesrespect_displaystyle', 'inline' );
        add_option( 'hyvesrespect_showmeta' );
        add_option( 'hyvesrespect_breakbefore' );
        add_option( 'hyvesrespect_breakafter' );
        add_option( 'hyvesrespect_showonlyinsingle' );
        add_option( 'hyvesrespect_stylelayout','horizontal' );
    }
}

// adds page to the admin menu
function hyvesrespect_options(){
	$page=add_options_page( 'Hyves respect button settings', 'Hyves respect', 'administrator', basename(__FILE__), 'hyvesrespect_options_page' );
	// Using registered $page handle to hook stylesheet loading
	add_action( 'admin_print_styles-' . $page, 'hyvesrespect_admin_stylesandscripts' );	
}

// add js and stylesheet for options page, It will be called only on your plugin admin page, enqueue our stylesheet here
function hyvesrespect_admin_stylesandscripts() {
	wp_enqueue_style('hyvesrespectStylesheet');
	wp_enqueue_script('hyvesrespectScript');
}

// plugin options page
function hyvesrespect_options_page(){
    if ( isset( $_POST ) ){
            if ( isset( $_POST['Submit'] ) ){
                update_option( 'hyvesrespect_location', $_POST['location'] );
                update_option( 'hyvesrespect_displaystyle', $_POST['displaystyle'] );
                update_option( 'hyvesrespect_showmeta', $_POST['showmeta'] );
                update_option( 'hyvesrespect_breakbefore', $_POST['breakbefore'] );
                update_option( 'hyvesrespect_breakafter', $_POST['breakafter'] );
                update_option( 'hyvesrespect_showonlyinsingle', $_POST['showonlyinsingle'] );
                update_option( 'hyvesrespect_divstyling', $_POST['divstyling'] );
					 update_option( 'hyvesrespect_stylelayout', $_POST['stylelayout'] );
            }
    }
    ?>
     <div class="wrap">
        <div class="icon32" id="icon-options-general"><br/></div><h2><?php _e( 'Settings for Hyves respect button', 'hyvesrespect' );?></h2>
        <form method="post" action="options-general.php?page=hyvesrespect.php">
            <table class="form-table">
                <tr>
                    <td valign="top"><strong><?php _e( 'Display options', 'hyvesrespect' );?></strong></td>
                    <td valign="top">
                        <input type="checkbox" value="1" <?php if ( get_option( 'hyvesrespect_showonlyinsingle') == '1' ) echo 'checked="checked"'; ?> name="showonlyinsingle" />
                        <label for="hyvesrespect_breakafter"><?php _e( 'Only show respect button on single post pages (ea. button doesn\'t show up in loops)', 'hyvesrespect' );?></label>
                    </td>
                </tr>		
                <tr>
                    <td valign="top"><strong><?php _e( 'Display style', 'hyvesrespect' );?></strong></td>
                    <td>
                        <select id="hyvesrespect_location" name="location">
                            <option value="bottom"><?php _e( 'Bottom', 'hyvesrespect' );?></option>
                            <option value="top" <?php if ( get_option( 'hyvesrespect_location' ) == 'top' ) echo ' selected="selected"'; ?>><?php _e( 'Top', 'hyvesrespect' );?></option>
                        </select>
                        <label for="hyvesrespect_location"><?php _e( 'Show button on top or bottom of post', 'hyvesrespect' );?></label>
                        <br />
                        <select id="hyvesrespect_displaystyle" name="displaystyle">
                            <option value="block">Block</option>
                            <option value="inline" <?php if (get_option( 'hyvesrespect_displaystyle' ) == 'inline' ) echo ' selected="selected"'; ?>>Inline</option>
                        </select>
                        <label for="hyvesrespect_displaystyle"><?php _e( 'Choose button display style', 'hyvesrespect' );?></label>
                        <br />
                        <select id="hyvesrespect_stylelayout" name="stylelayout" onchange="hyvesrespect_changePreview()">
                            <option value="horizontal" <?php if (get_option( 'hyvesrespect_stylelayout' ) == 'horizontal' ) echo ' selected="selected"'; ?>><?php _e( 'Horizontal (default)', 'hyvesrespect' );?></option>
                            <option value="nocount" <?php if (get_option( 'hyvesrespect_stylelayout' ) == 'nocount' ) echo ' selected="selected"'; ?>><?php _e( 'Horizontal - No counter', 'hyvesrespect' );?></option>
									 <option value="vertical" <?php if (get_option( 'hyvesrespect_stylelayout' ) == 'vertical' ) echo ' selected="selected"'; ?>><?php _e( 'Vertical', 'hyvesrespect' );?></option>
                        </select>
                        <label for="hyvesrespect_stylelayout"><?php _e( 'Choose button layout', 'hyvesrespect' );?></label>
                        <br />
								<div id="hyvesrespect_preview"></div>
                        <input type="checkbox" value="1" <?php if ( get_option( 'hyvesrespect_breakbefore' ) == '1' ) echo 'checked="checked"'; ?> id="hyvesrespect_breakbefore" name="breakbefore" />
                        <label for="hyvesrespect_breakbefore"><?php _e( 'Add break before the button', 'hyvesrespect' );?></label>
                        <br />
                        <input type="checkbox" value="1" <?php if (get_option( 'hyvesrespect_breakafter' ) == '1' ) echo 'checked="checked"'; ?> id="hyvesrespect_breakafter" name="breakafter" />
                        <label for="hyvesrespect_breakafter"><?php _e( 'Add break after the button', 'hyvesrespect' );?></label>
                        <br />
                        <input type="checkbox" id="hyvesrespect_divstyling" value="1" <?php if (get_option( 'hyvesrespect_divstyling' ) == '1' ) echo 'checked="checked"'; ?> name="divstyling" />
                        <label for="hyvesrespect_divstyling"><?php _e( 'Add a containing div for each button with the classname <i>hyvesRespectButton</i>, use this to style and position the button', 'hyvesrespect' );?></label>                                
                    </td>
                </tr>
                <tr>
                    <td valign="top"><strong><?php _e( 'Expert options', 'hyvesrespect' );?></strong></td>
                    <td valign="top">
                        <input type="checkbox" value="1" <?php if ( get_option( 'hyvesrespect_showmeta' ) == '1' ) echo 'checked="checked"'; ?> id="hyvesrespect_showmeta" name="showmeta" />
                        <label for="hyvesrespect_showmeta"><?php _e( 'Disable including OpenGraph properties. (You can disable this option if another plugin already includes the og meta tags)', 'hyvesrespect' );?></label>
                    </td>
                </tr>
                

            </table>
            <p class="submit"><input type="submit" name="Submit" value="<?php _e( 'Save Changes', 'hyvesrespect' );?>" /></p>
        </form>

        <div id="poststuff">
            <div class="stuffbox" style="background-color:#FFFFFF;width:600px;">
                <h3><label for="link_name">Support</label></h3>
                <div class="inside">
                    <ul>
                        <li>Please don't hesitate to send us your <a href="http://wordpress.org/tags/hyves-respect" target="_blank">support questions &raquo;</a> or <a href="http://wordpress.org/support/view/plugin-committer/martijnh" target="_blank">feature requests &raquo;</a></li>
                        <li>Support us back by mentioning or rating the <a href="http://wordpress.org/extend/plugins/hyves-respect/" target="_blank">Hyves respect button plugin &raquo;</a></li>
                        <li>For an overview of our services, visit our company website <a href="http://d-media.nl?ref=wp-hyves-respect" target="_blank">d-Media</a></li>
                    </ul>                    
                </div>
            </div>        
        </div>
        
    </div>
    <?php
}

// add plugin settings link on the plugin overview page
add_action( 'plugin_action_links_' . plugin_basename(__FILE__), 'hyvesrespect_filter_plugin_actions' );
function hyvesrespect_filter_plugin_actions( $links ){
    return array_merge( array( '<a href="options-general.php?page=hyvesrespect.php">Settings</a>' ), $links );
}
?>
