<?php

class DSCPF_Settings {
	public $dscpf_options;

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'dscpf_add_plugin_page' ) );
		add_action( 'admin_init', array( $this, 'dscpf_page_init' ) );
        add_action( 'admin_footer', array($this, 'dscpf_action_client_ajax') ); // Write the ajax to flush rewrite rule
        add_action( 'wp_ajax_dscpf_flush_rewrite_rule', array($this, 'dscpf_action_server_ajax' )); //ajax admin handler

	}

	public function dscpf_add_plugin_page() {
		add_management_page(
			'DSCPF', // page_title
			'DSCPF', // menu_title
			'manage_options', // capability
			'dscpf-settings', // menu_slug
			array( $this, 'dscpf_create_tool_page' ), // function
			1000000 // position
		);
	}

	public function dscpf_create_tool_page() {
		$this->dscpf_options = get_option( 'dscpf_option_name' ); ?>
		<div class="wrap">
			<h2>DSCPF</h2>
			<p></p>
			<?php settings_errors(); ?>

			<form method="post" action="options.php">
				<?php
					settings_fields( 'dscpf_option_group' );
					do_settings_sections( 'dscpf-admin' );
                    ?>
                    <label for="flush_rewrite_rule_btn">You must hit this button to activate the fix =>&nbsp;</label>
                    <input id="flush_rewrite_rule_btn" type="button" name="btn_flush_rewrite_rule" value="Flush rewrite rule">
                    <span id="dscpf_response"></span>
                    <?php
					submit_button();
				?>
			</form>
		</div>
	<?php }

	public function dscpf_page_init() {
		register_setting(
			'dscpf_option_group', // option_group
			'dscpf_option_name', // option_name
			array( $this, 'dscpf_sanitize' ) // sanitize_callback
		);
		
		add_settings_section(
			'dscpf_setting_section', // id
			'Settings Section', // title
			array( $this, 'dscpf_section_info' ), // callback 
			'dscpf-admin' // page
		);

        add_settings_field(
			'permalink_prefix', // id
			'Permalink category prefix', // title
			array( $this, 'permalink_prefix' ), // callback output field
			'dscpf-admin', // page
			'dscpf_setting_section' // section
		);
	}

	public function dscpf_sanitize($input) {
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
			'<input class="regular-text" type="text" name="dscpf_option_name[permalink_prefix]" id="permalink_prefix" value="%s">',
			isset( $this->dscpf_options['permalink_prefix'] ) ? esc_attr( $this->dscpf_options['permalink_prefix']) : ''
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
                    $("#dscpf_response").html(response).css("color", "green");
                });
            });

        });
        </script> <?php
    }
    
    public function dscpf_action_server_ajax() {
        if ( isset( $_REQUEST['_wpnonce'] ) && wp_verify_nonce( $_REQUEST['_wpnonce'], 'wpdocs-my-nonce' ) && current_user_can( 'manage_options') ) {
   
            flush_rewrite_rules();
            echo "Flushed the rules";
             
          } else {
           
            echo "Security check";

           
          }
        wp_die(); // this is required to terminate immediately and return a proper response
    }
}
/* 
 * Retrieve this value with:
 * $dscpf_options = get_option( 'dscpf_option_name' ) // Array of All Options
 * $permalink_prefix = $dscpf_options['permalink_prefix']; //get the option
 */