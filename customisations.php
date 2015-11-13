<?php
/**
 * Plugin Name: Customisations
 * Description: A very simple plugin to house custom css and functions.
 * Version: 	1.0.0
 * Author: 		shramee
 * Author URI: 	http://www.shramee.com/
 * @developer Shramee <shramee.srivastav@gmail.com>
 * @package Theme_Customisations
 */
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Pootle_Customisations {

	public function __construct () {
		add_action( 'admin_menu', array( $this, 'menu' ), 999 );
		add_action( 'admin_init', array( $this, 'fields' ) );
		add_action( 'plugins_loaded', array( $this, 'plugins_loaded' ) );
		add_action( 'wp_head', array( $this, 'head' ) );
	}

	public function plugins_loaded() {
		$php = get_option( 'pootle_custo_php' );
		$code = str_replace( array( '<?php', '<?', '?>', ), '', $php['php'] );
		if ( ! empty( $php['apply'] ) || ! empty( $_GET['pc_test'] ) ) {
			eval( $code );
		}
	}

	public function head() {
		?>
		<style id="pootle-customizations-css">
			<?php echo get_option( 'pootle_custo_css' ) ?>
			@media only screen and (max-width:768px) {
			<?php echo get_option( 'pootle_custo_mob_css' ) ?>
			}
		</style>
		<?php
	}

	public function menu() {
    add_theme_page(
        'Customizations',
        'Customizations',
        'edit_theme_options',
        'pootle_customizations',
        array( $this, 'render_page' )
    );
	}

	public function render_page() {
		?>
		<!-- Create a header in the default WordPress 'wrap' container -->
		<div class="wrap">

			<style>
				#editor{
					position: relative;
					width: 550px;
					height: 520px;
				}
			</style>

			<div id="icon-themes" class="icon32"></div>
			<h2>Customizations</h2>
			<?php settings_errors(); ?>

			<?php
			$active_tab = 'css';
			if( isset( $_GET[ 'tab' ] ) ) {
				$active_tab = $_GET[ 'tab' ];
			} // end if
			?>

			<h2 class="nav-tab-wrapper">
				<a href="?page=pootle_customizations&tab=css" class="nav-tab <?php echo $active_tab == 'css' ? 'nav-tab-active' : ''; ?>">CSS</a>
				<a href="?page=pootle_customizations&tab=mob_css" class="nav-tab <?php echo $active_tab == 'mob_css' ? 'nav-tab-active' : ''; ?>">Mobile CSS</a>
				<a href="?page=pootle_customizations&tab=php" class="nav-tab <?php echo $active_tab == 'php' ? 'nav-tab-active' : ''; ?>">Functions</a>
			</h2>

			<form method="post" action="options.php">
				<?php
				settings_fields( 'pootle_custo_' . $active_tab );
				do_settings_sections( 'pootle_customizations_' . $active_tab );
				submit_button();
				?>
			</form>
			<script src="//cdnjs.cloudflare.com/ajax/libs/ace/1.2.2/ace.js"></script>
			<script src="//cdnjs.cloudflare.com/ajax/libs/ace/1.2.2/mode-php.js"></script>
			<script src="//cdnjs.cloudflare.com/ajax/libs/ace/1.2.2/mode-css.js"></script>
			<script>
				(
					function ( $ ) {
						var editor = ace.edit( "editor" ),
							EditorMode = ace.require("ace/mode/<?php echo 'php' == $active_tab ? 'php' : 'css' ?>").Mode,
							$apply = $('[name="pootle_custo_php[apply]"]');
						editor.session.setMode( new EditorMode() );
						editor.on( 'change', function ( e ) {
							$('textarea.hidden').val( editor.getValue() );
						} );
					}
				)( jQuery );
			</script>
		</div><!-- /.wrap -->
		<?php
	}

	public function fields() {

		// First, we register a section. This is necessary since all future options must belong to a
		add_settings_section(
			'pootle_custo_css',
			__( 'Custom CSS Styles', 'sandbox' ),
			array( $this, 'render_section_css' ),
			'pootle_customizations_css'
		);

		add_settings_section(
			'pootle_custo_mob_css',
			__( 'Custom Mobile CSS Styles', 'sandbox' ),
			array( $this, 'render_section_mob_css' ),
			'pootle_customizations_mob_css'
		);

		add_settings_section(
			'pootle_custo_php',
			__( 'Custom PHP code', 'sandbox' ),
			array( $this, 'render_section_php' ),
			'pootle_customizations_php'
		);

		// Finally, we register the fields with WordPress
		register_setting(
			'pootle_custo_css',
			'pootle_custo_css'
		);

		// Finally, we register the fields with WordPress
		register_setting(
			'pootle_custo_mob_css',
			'pootle_custo_mob_css'
		);

		// Finally, we register the fields with WordPress
		register_setting(
			'pootle_custo_php',
			'pootle_custo_php'
		);

		// Next, we'll introduce the fields for toggling the visibility of content elements.
		add_settings_field(
			'show_header',
			__( 'Header', 'sandbox' ),
			'sandbox_toggle_header_callback',
			'sandbox_theme_display_options',
			'general_settings_section',
			array(
				__( 'Activate this setting to display the header.', 'sandbox' ),
			)
		);
	}

	public function render_section_css() {
		$value = get_option( 'pootle_custo_css' );
		?>
		<textarea class="hidden" name="pootle_custo_css"><?php echo $value; ?></textarea>
		<div id="editor"><?php echo $value ?></div>
		<?php
	}

	public function render_section_mob_css() {
		$value = get_option( 'pootle_custo_mob_css' );
		?>
		<textarea class="hidden" name="pootle_custo_mob_css"><?php echo $value; ?></textarea>
		<div id="editor"><?php echo $value ?></div>
		<?php
	}

	public function render_section_php() {
		$value = get_option( 'pootle_custo_php', array(
			'php' => "<?php\n\n\n?>",
			'save' => false,
			) );
		?>
		<textarea class="hidden" name="pootle_custo_php[php]"><?php echo $value['php']; ?></textarea>
		<div id="editor"><?php esc_html_e( $value['php'] ) ?></div>
		<h4>Please <a href="<?php echo site_url() ?>?pc_test=1">click here</a> to test your code on home page before applying, If you get a white page error or fatal error don't apply it.</h4>
		<label><input type="checkbox" value="1" name="pootle_custo_php[apply]"> Apply php code</label>
		<?php
	}

	public function theme_customisations_template( $template ) {
		if ( file_exists( untrailingslashit( plugin_dir_path( __FILE__ ) ) . '/custom/templates/' . basename( $template ) ) ) {
			$template = untrailingslashit( plugin_dir_path( __FILE__ ) ) . '/custom/templates/' . basename( $template );
		}

		return $template;
	}
} // End Class

new Pootle_Customisations();