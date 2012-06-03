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
	}
	
	/**
	* markup for footer
	* taken from here: http://developers.facebook.com/docs/guides/web
	*/
   static function fb_footer(){
	?> 
		<div id="fb-root"></div>
		<script>
			
			window.fbAsyncInit = function() {
				FB.init({
				appId: '',
				cookie: true,
				xfbml: true,
				oauth: true
				});
				FB.Event.subscribe('auth.login', function(response) {
				window.location.reload();
				});
				FB.Event.subscribe('auth.logout', function(response) {
				window.location.reload();
				});
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
						$fbuid = get_user_meta($profile->ID, 'fb_uid', true);
						if($fbuid){
							echo 'connected';
						}
						else{
							echo '<fb:login-button></fb:login-button>';
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

}