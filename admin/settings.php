<?php

class DSCPF_Settings {
	public $dscpf_options;
	public CONST ACTION = "dscpf_option_group-options";
	public CONST DSCPF_OPTION_NAME = "dscpf_options";

	public function __construct() {
		add_action( 'admin_init', array( $this, 'dscpf_admin_init' ) );
		add_action( 'admin_menu', array( $this, 'dscpf_admin_menu' ) );
        add_action( 'admin_footer', array($this, 'dscpf_action_client_ajax') ); // Write the ajax to flush rewrite rule
        add_action( 'wp_ajax_dscpf_flush_rewrite_rule', array($this, 'dscpf_action_server_ajax' )); //ajax admin handler

	}

	public function dscpf_admin_init() {
		register_setting(
			'dscpf_option_group', // option_group
			DSCPF_Settings::DSCPF_OPTION_NAME, // option_name
			array( $this, 'dscpf_sanitize_fields' ) // sanitize_callback
		);
		
		add_settings_section(
			'dscpf_setting_section', // id
			'Settings Section', // title
			array( $this, 'dscpf_section_info' ), // callback 
			'dscpf-admin' // page
		);

        add_settings_field(
			'permalink_prefix', // id
			'Current category prefix (default category)', // title
			array( $this, 'permalink_prefix' ), // callback output field
			'dscpf-admin', // page
			'dscpf_setting_section' // section
		);
		$this->dscpf_options = get_option( DSCPF_Settings::DSCPF_OPTION_NAME );
	}

	public function dscpf_admin_menu() {
		add_management_page(
			'DSCPF', // page_title
			'DSCPF', // menu_title
			'manage_options', // capability
			'dscpf-settings', // menu_slug
			array( $this, 'dscpf_output_page' ), // function
			1000000 // position
		);
	}

	public function dscpf_output_page() {
		?>
		<div class="wrap">
			<h2>DSCPF</h2>
			<p></p>
			<?php settings_errors(); ?>

			<form method="post" action="options.php">
				<?php
					settings_fields( 'dscpf_option_group' );
					do_settings_sections( 'dscpf-admin' );
					//submit_button();
				?>
				<label for="flush_rewrite_rule_btn">You must hit this button to activate the fix =>&nbsp;</label>
				<input id="flush_rewrite_rule_btn" type="button" name="btn_flush_rewrite_rule" value="Flush rewrite rule">
				<span id="dscpf_response"></span>
			</form>
		</div>
		<br>
		<div class="wrap">
			<pre>
#########################################################################
# Synopsis
#########################################################################

(A) Not duplicated categories structure slugs example:
		One hierarchical:		maincat1/
		Two hierarchical:		maincat1/subcat
		Three hierarchical:		maincat1/subcat/subsubcat

(B) Duplicated categories structures slugs must follow this structure:
		One hierarchical:		maincat2/
		Two hierarchical:		maincat2/subcat-maincat2
		Three hierarchical:		maincat2/subcat-maincat2/subsubcat-subcat-maincat2

(C) New permalinks on duplicated categories will generate this:
		One hierarchical:		maincat2/
		Two hierarchical:		maincat2/subcat
		Three hierarchical:		maincat2/subcat/subsubcat

<b style="color:red">
IMPORTANT: Duplicated categories structures slugs must follow the structure demonstrated at (B)
to make this plugin work properly. This is the default way that Wordpress name the duplicated slugs on category taxonomy.
</b>
			</pre>
		</div>
	<?php }

	public function dscpf_sanitize_fields($input) {
		$sanitary_values = array();
		if ( isset( $input['permalink_prefix'] ) ) {
			$sanitary_values['permalink_prefix'] = sanitize_text_field( $input['permalink_prefix'] );
		}
		return $sanitary_values;
	}

	public function dscpf_section_info() {
		
	}

	public function permalink_prefix() {
		printf(
			'<input readonly class="regular-text" type="text" name="%s[permalink_prefix]" id="permalink_prefix" value="%s">',
			DSCPF_Settings::DSCPF_OPTION_NAME,
			isset( $this->dscpf_options['permalink_prefix'] ) ? esc_attr( $this->dscpf_options['permalink_prefix']) : 'category'
		);
	}

    public function dscpf_action_client_ajax() { ?>
        <script id="js_flush_ajax_call" type="text/javascript" >
        jQuery(document).ready(function($) {$=jQuery;
            $("#flush_rewrite_rule_btn").click(function(){
                var data = {
                    'action': 'dscpf_flush_rewrite_rule',
                    'nonce': $("#_wpnonce").val(),
                };
                // since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
                $.post(ajaxurl, data, function(response) {
					var data = JSON.parse(response);
					if(data.status == true){
                    	$("#dscpf_response").text(data.message).css("color", "green");
					}else{
						$("#dscpf_response").text(data.message).css("color", "red");
					}
                });
            });

        });
        </script> <?php
    }
    
    public function dscpf_action_server_ajax() {

        if ( isset( $_REQUEST['nonce'] ) && wp_verify_nonce( $_REQUEST['nonce'], DSCPF_Settings::ACTION) ) {
            flush_rewrite_rules();
			$response = array(
				"status" => 	true,
				"message" => 	__("Flushed the rules successfully", "DSCPF")
			);
          } else {
			$response = array(
				"status" => 	false,
				"message" => 	__("Security problem", "DSCPF")
			);
          }
		echo json_encode($response);
        wp_die(); // this is required to terminate immediately and return a proper response
    }
}
/* 
 * $dscpf_options = get_option( 'dscpf_options' ) // Array of All Options
 * $permalink_prefix = $dscpf_options['permalink_prefix']; //get the option
 */