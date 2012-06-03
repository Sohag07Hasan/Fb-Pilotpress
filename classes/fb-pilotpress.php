<?php
/*
 * creates a login button
 * handles the membership login
 */

class FbPilotPress{
	
	static function init(){
		//add markup to footer
		add_action('wp_footer', array(get_class(), 'fb_footer'));
		add_action('admin_print_footer_scripts', array(get_class(), 'fb_footer'));
		//add_action('admin_enqueue_scripts', array(get_class(), 'facebook_header'), 20);
		// add the section on the user profile page
		add_action('profile_personal_options', array(get_class(), 'add_facebook_status'));
		
		// fix up the html tag to have the FBML extensions
		add_filter('language_attributes', array(get_class(), 'support_fbhtml'));
		
		//facebook settings
		add_action('admin_menu', array(get_class(), 'create_options_page'));
		
		//ajax manipulation to connect facebook account with the wp
		add_action('wp_ajax_pilotpress_update_fbuid', array(get_class(), 'ajax_pilotpress_update_fbuid'));
	}
	
	/**
	* markup for footer
	* taken from here: http://developers.facebook.com/docs/guides/web
	*/
   static function fb_footer(){
	   $fb_info = self::get_fbapp_info();
	   self::pilotpress_login_update_fbuid();
	?> 
		<div id="fb-root"></div>
		<script>
			
			window.fbAsyncInit = function() {
				FB.init({
				appId: "<?php echo $fb_info['id']; ?>",
				cookie: true,
				xfbml: true,
				oauth: true
				});
				
				/*
				FB.Event.subscribe('auth.login', function(response) {
				window.location.reload();
				});
				FB.Event.subscribe('auth.logout', function(response) {
				window.location.reload();
				});
				*/
			};
			
			(function() {
				var e = document.createElement('script'); e.async = true;
				e.src = document.location.protocol +
				'//connect.facebook.net/en_US/all.js';
				document.getElementById('fb-root').appendChild(e);
			}());
			
		</script>
		
	<?php
	}
	
	/*
	 * including the javascript sdk
	 */
	static function facebook_header(){		
		wp_enqueue_script( 'facebook_connect_js_functions', 'http://connect.facebook.net/en_US/all.js', array('jquery') );
	}
	
	
	/*
	 * shows if a facebook is associated with a profile 
	 * if not, it will show an interface to connect with facebook
	 */
	static function add_facebook_status($profile){
		?>
		<table class="form-table">
			<tr>
				<th><label> Facebook Connect </label></th>
				<td>
					<?php
						$fbuid = get_user_meta($profile->ID, 'fb_u_id', true);
						if($fbuid){
							echo 'connected';
						}
						else{
						?>
							<p><fb:login-button scope="email" v="2" size="large" onlogin="pilotpress_login_update_fbuid(0);"><fb:intl><?php _e('Connect this WordPress account to Facebook'); ?></fb:intl></fb:login-button></p>
						<?php
						}
					?>
				</td>
			</tr>
		</table>
		<?php
	}
	
	/*
	 * helper function to handle the fb html tags
	 */
	static function support_fbhtml($lang){
		return ' xmlns:fb="http://ogp.me/ns/fb#" xmlns:og="http://ogp.me/ns#" ' . $lang;
	}
	
	/*
	 * options page to set up the facebook application creditentials
	 */
	static function create_options_page(){
		add_options_page('Pilotpress with facebook accout', 'Fb+Pilotpress', 'manage_options', 'fb-pilotpress-plugin', array(get_class(), 'options_page_content'));
	}
	
	/*
	 * options page to inser the facebook application credintials
	 */
	static function options_page_content(){
		self::save_fbapp_info();
		$fb_info = self::get_fbapp_info();
		
		include FBPILOTPRESS_DIR . '/includes/options-page-content.php';
	}
	
	/*
	 * save the facebook credentials
	 */
	static function save_fbapp_info(){
		if($_POST['fb-app-submission'] == "Y") :
			$data = array(
				'id' => trim($_POST['fb-app-id']),
				'secret' => trim($_POST['fb-app-secret'])
			);
		update_option('facbookapp_info', $data);
		endif;
	}
	
	
	/*
	 * returns the facebook info
	 */
	static function get_fbapp_info(){
		return get_option('facbookapp_info');
	}
	
	/*
	 * ajax functions while trying to connect facebook account with the current wp account from profile edit page
	 */
	static function pilotpress_login_update_fbuid(){
		if(defined('IS_PROFILE_PAGE')) :
		?>
			<script type="text/javascript">
				function pilotpress_login_update_fbuid(disconnect) {
					var ajax_url = '<?php echo admin_url("admin-ajax.php"); ?>';
					if (disconnect == 1) {
						var fbuid = 0;
					} else {
						var fbuid = 1; // it gets it from the cookie
					}
					var data = {
						action: 'pilotpress_update_fbuid',
						fbuid: fbuid
					}
					jQuery.post(ajax_url, data, function(response) {
						if (response == '1') {
							location.reload(true);
						}
					});
				}
		</script>
		<?php
		endif;
	}
	
	/*
	 * ajax handler function to save the userid into database
	 */
	static function ajax_pilotpress_update_fbuid(){
		$user = wp_get_current_user();
		$fbuid = (int)($_POST['fbuid']);
		if($fbuid){
			$fb_info = self::get_fbapp_info();			
			$facebook = new Facebook(array(
				'appId' => $fb_info['id'],
				'secret' => $fb_info['secret']
			));
			
			$fbuser = $facebook->getUser();
			if($fbuser){
				update_usermeta($user->ID, 'fb_u_id', $fbuser);
			}
			echo 1;
			exit;
		}
	}
	
}