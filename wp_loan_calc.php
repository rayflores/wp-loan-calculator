<?php 
/*
* Plugin Name: WP Loan Calculator
* Plugin URI:  https://www.rayflores.com/plugins/wp-lc/
* Description: Custom Loan Calculator
* Author:      Ray Flores
* Author URI:  https://www.rayflores.com
* Version:     0.1
*/

class WP_Loan_Calc {  
	/**
 	 * Option key, and option page slug
 	 * @var string
 	 */
	private $key = 'wplc_options';
	/**
	 * Options Page title
	 * @var string
	 */
	protected $title = 'Loan Calculator';
	/**
	 * Holds an instance of the object
	 *
	 * @var WP_Loan_Calc
	 **/
	private static $instance = null;
	/**
	 * Constructor
	 * @since 0.1
	 */
	private function __construct() {
		// Set our title
		$this->title = __( 'Loan Calculator', 'wplc' );
		// enqueue our scripts
		add_action( 'wp_enqueue_scripts', array($this, 'wplc_enqueue_scripts') );
		// shortcode
		add_shortcode( 'wplc', array($this, 'wplc_loan_calc_shortcode') );
	}
	public function wplc_enqueue_scripts(){
		// styles
		wp_register_style( 'boots-css', '//maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.3/css/bootstrap.min.css' );
		wp_register_style( 'wplc-css', plugins_url( '/css/wplc_style.css', __FILE__ ) );
		// scripts
		wp_register_script( 'boots-js', '//maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.3/js/bootstrap.bundle.min.js', array('jquery') );
		wp_register_script( 'numeral-js', plugins_url( '/js/numeral.min.js', __FILE__ ), array( 'jquery' ) );
		wp_register_script( 'jcalx', plugins_url( '/js/jquery-calx-2.2.7.js', __FILE__ ), array( 'jquery' ) );
		wp_register_script( 'wplc-js', plugins_url( '/js/wplc_script.js', __FILE__ ), array( 'jquery' ) );
	}
	public function wplc_loan_calc_shortcode( ){
		$options = get_option($this->key);
		wp_enqueue_style( 'boots-css' );
		wp_enqueue_style( 'wplc-css' );
		wp_enqueue_script( 'boots-js', array( 'jquery' ) );
		wp_enqueue_script( 'numeral-js', array( 'jquery' ) );
		wp_enqueue_script( 'jcalx', array( 'jquery' ) );
		wp_enqueue_script( 'wplc-js', array( 'jquery' ) );
		ob_start();
		// loan calculator
		?>
		<form class="wplc-form">
			<table class="table-responsive table-dark">
				<thead>
					<tr>
						<th scope="col">Inputs</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<th scope="row">Loan Amount</th>
						<td><input type="text" data-cell="A1" data-format="$0,0[.]00" value="5000"/></td>
					</tr>
					<tr>
						<th scope="row">Interest Rate</th>
						<td><input type="text" data-cell="A2" data-format="0.00%" value="4.5"/></td>
					</tr>
					<tr>
						<th scope="row">Periods ( term in months )</th>
						<td><input type="text" data-cell="A3" value="60"/></td>
					</tr>
					<tr>
						<th scope="row">Monthly Payment</th>
						<td><input type="text" name="wplc_apr" data-cell="A4"/></td>
					</tr>
				</tbody>
			</table>
		</form>
		<?php 
		return ob_get_clean();
	}
	/**
	 * Returns the running object
	 *
	 * @return WP_Loan_Calc
	 **/
	public static function get_instance() {
		if( is_null( self::$instance ) ) {
			self::$instance = new self();
			self::$instance->hooks();
		}
		return self::$instance;
	}
		/**
	 * Initiate our hooks
	 * @since 0.1
	 */
	public function hooks() {
		global $my_admin_page;
		add_action( 'admin_init', array( $this, 'wplc_init' ) );
		add_action( 'admin_menu', array( $this, 'wplc_add_options_page' ) );
		// enqueue admin scripts/styles
		
		add_action( 'admin_enqueue_scripts', array($this, 'wplc_admin_enqueue_scripts') );
	}
	/**
	 * Register our setting to WP
	 * @since  0.1.0
	 */
	public function wplc_init() {
		register_setting( $this->key, $this->key );
		//main
		add_settings_section(
			'wplc_options_section', 
			__( 'Loan Calculator Settings', 'wplc' ), 
			array( $this, 'wplc_options_section_callback'), 
			$this->key
		);
	}
	/**
	 * Register our settings description to WP
	 * @since  0.1
	 */
	public function wplc_options_section_callback(  ) { 
		echo '<p>Adjust the formulas for the calculator.  This Microsoft Excel ;)</p>';
		
	}
	/**
	 * Add menu options page
	 * @since 0.1
	 */
	public function wplc_add_options_page() {
		global $my_admin_page;
		$my_admin_page = add_menu_page( $this->title, $this->title, 'manage_options', $this->key, array( $this, 'wplc_admin_page_display' ) );
	}
	
	/**
	 * Admin page markup.
	 * @since  0.1
	 */
	public function wplc_admin_page_display() {
		$options = get_option( $this->key );
		if(isset($_POST['example_plugin_reset'])) {
				check_admin_referer('wplc_reset', 'wplc_reset-nonce');
				$this->wplc_reset_options();
		?>
				<div class="updated alert alert-info" role="alert">
					<p><?php _e('All options have been restored to their default values.', 'wplc'); ?></p>
				</div>
		<?php } ?>
		<div class="wrap">
			<h1><?php echo $this->title; ?></h1>
			<form method="POST" action="options.php">
			<?php 
				do_settings_sections( $this->key );
				// option fields go here
				submit_button();
			?>
			</form>
			<div id="wplc-reset" style="clear: both;">
				<form method="post" action="">
					<?php wp_nonce_field('wplc_reset', 'wplc_reset-nonce'); ?>
					<label style="font-weight:normal;">
						<?php printf(__('Do you wish to <strong>restore</strong> the default options for', 'wplc')); ?> <?php echo $this->title ?>? </label>
					<input class="button-primary" type="submit" name="wplc_reset" value="Restore Defaults" />
				</form>
			</div>
		</div>
		<?php 
	}
	/**
	* Admin enqueue scripts
	* @since 0.1
	*/
	public function wplc_admin_enqueue_scripts(){
		
	}
	/** 
	* Restore to default options
	*/
	public function wplc_reset_options(){
		delete_option( $this-> key );
	}
}
/**
 * Helper function to get/return the WP_Loan_Calc object
 * @since  0.1
 * @return WP_Loan_Calc object
 */
function wp_loan_calc() {
	return WP_Loan_Calc::get_instance();
}
// fire!
wp_loan_calc();