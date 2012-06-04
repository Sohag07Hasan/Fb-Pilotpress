<?php
/*
 * creates a login button
 * handles the membership login
 */

session_start();

//var_dump($_SESSION);

class FbPilotPress{
	
	static function init(){
		//add markup to footer
		//add_action('admin_footer', array(get_class(), 'fb_footer'));
		add_action('login_footer', array(get_class(), 'fb_footer'));
		
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
		add_action('wp_ajax_pilotpress_log', array(get_class(), 'ajax_pilotpress_login'));
		add_action('wp_ajax_nopriv_pilotpress_log', array(get_class(), 'ajax_pilotpress_login'));
		
		//add login button in login form
		add_action('login_form', array(get_class(), 'extend_login_form'));
		
		//add_action('wp_authenticate', array(get_class(), 'authenticate'), 100, 3);
		
		add_action('init', array(get_class(), 'log_data_to_pilotpress'));
	}
	
	static function authenticate($userdata){
		var_dump($userdata);
		exit;
	}
	
	/**
	* markup for footer
	* taken from here: http://developers.facebook.com/docs/guides/web
	*/
   static function fb_footer(){
	   $fb_info = self::get_fbapp_info();
	   self::pilotpress_login_update_fbuid();
	   self::pilotpress_login_handle();
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
						$fbuid = self::get_facebookId();
											
						if($fbuid){
						?>
							<p>
								<fb:profile-pic size="square" width="32" height="32" uid="<?php echo $fbuid; ?>" linked="true"></fb:profile-pic>
								<input type="button" class="button-primary" value="<?php _e('Disconnect from WordPress'); ?>" onclick="pilotpress_login_update_fbuid(1); return false;" />
							</p>
					        <?php
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
		$fbuid = (int)($_POST['fbuid']);
		if($fbuid){
			$fb_info = self::get_fbapp_info();			
			$facebook = new Facebook(array(
				'appId' => $fb_info['id'],
				'secret' => $fb_info['secret']
			));
			
			$fbuser = $facebook->getUser();
			if($fbuser){
				self::updateUserFbAccount($fbuser);
			}
			
		}
		
		else{
			self::deleteUserFbAccount();
		}
		
		echo  1;
		exit;
	}
	
	//add facebook account
	static function updateUserFbAccount($fbId){
		$user = wp_get_current_user();
		update_user_meta($user->ID, 'facebook_id', $fbId);
	}
	
	
	static function deleteUserFbAccount(){
		$user = wp_get_current_user();
		delete_user_meta($user->ID, 'facebook_id');
	}
	
	/*
	 * add login form in the login form
	 */
	static function extend_login_form(){
	?>
		<p class="pilotpress-ajax-message" style="display:none; background-color:#D16868; border: 1px solid #FF0000;">Sorry! This facebook account is not associated with any wp account</p>
		<br/><p><fb:login-button scope="email" v="2" size="large" onlogin="pilotpress_login_with_facebook();"><fb:intl><?php _e('Login with Facebook'); ?></fb:intl></fb:login-button></p>
		
	<?php
	}
	
	/*
	 * handles the login functionality
	 * if a wp account is connected with fb account, it helps to login with the facebook account
	 */
	static function pilotpress_login_handle(){
		?>
			<script>
				function pilotpress_login_with_facebook(){
					var ajax_url = '<?php echo admin_url("admin-ajax.php"); ?>';					
					var data = {
						action: 'pilotpress_log'						
					}
					
					jQuery.post(ajax_url, data, function(response) {
						if (response == '1') {
							location.reload(true);
						}
						else{
							jQuery('.pilotpress-ajax-message').show();							
						}
					});
				}
			</script>
		<?php
	}
	
	
	/*
	 * ajax login functionality 
	 */
	static function ajax_pilotpress_login(){
		$fb_info = self::get_fbapp_info();			
			$facebook = new Facebook(array(
				'appId' => $fb_info['id'],
				'secret' => $fb_info['secret']
			));
			
		$fbuser = $facebook->getUser();
		
		if($fbuser){			
			if(self::get_user_by_fbid($fbuser)){
				$_SESSION['pilotpress_fb_user'] = $fbuser;
				echo 1;
				exit;				
			}
			else{
				echo 0;
				exit;
			}
			
		}
		else{
			echo 0;
			exit;		
		}
		
		exit;
	}
	
	
	/*
	 * log data into pilorpress
	 */
	static function log_data_to_pilotpress(){
		if(isset($_SESSION['pilotpress_fb_user'])){
			$fb_id = $_SESSION['pilotpress_fb_user'];
			unset($_SESSION['pilotpress_fb_user']);			
			$wp_user_id = self::get_user_by_fbid($fb_id);	
			$_POST["wp-submit"] = true;
			$user = new WP_User($wp_user_id);
			var_dump($user);
			exit;			
		}
	}
	
	/*
	 * return wp_user_id
	 */
	static function get_user_by_fbid($fb_id){
		global $wpdb;
		return $wpdb->get_var( $wpdb->prepare("SELECT user_id FROM $wpdb->usermeta WHERE meta_key = 'facebook_id' AND meta_value = %s", $fb_id) );
	}
	
	/*
	 * returns facebook id
	 */
	static function get_facebookId(){
		$user = wp_get_current_user();			
		return get_user_meta($user->ID, 'facebook_id', true);
	}
	
}
