<?php

/*
 * class handles the database entry creation and clear
 */
class PilotPressFbDatabase{
	static function init(){
		register_activation_hook(FBPILOTPRESS_FILE, array(get_class(), 'activatePlugin'));
	}
	
	static function activatePlugin(){
		global $wpdb;
		$table = self::getFbTable();
		$sql_1 = "CREATE TABLE IF NOT EXISTS $table(
				`user_id` bigint unsigned NOT NULL,
				`fb_id` varchar(50) NOT NULL,
				UNIQUE(user_id),
				UNIQUE(fb_id)
								 			 			
			)";
		if(!function_exists('dbDelta')) :
			include ABSPATH . 'wp-admin/includes/upgrade.php';
		endif;
		dbDelta($sql_1);
	}
	
	//return facbook table
	static function getFbTable(){
		global $wpdb;
		return $wpdb->prefix . 'fbpilotpress';
	}
	
	
	/*
	 * update user facebook account
	 */
	static function updateUserFbAccount($fbId, $user){
		global $wpdb;
		$table = self::getFbTable();
		if(self::fbAccountExists($fbId, $user)){
			$wpdb->update($table, array('fb_id'=>$fbId), array('user_id'=>$user->ID), array('%s'), array('%d'));
		}
		else{
			$wpdb->insert($table, array('fb_id'=>$fbId, 'user_id'=>$user->ID), array('%s', '%d'));
		}
	}
	
	/*
	 * boolean
	 */
	static function fbAccountExists($fbId, $user){
		global $wpdb;
		$table = self::getFbTable();
		return $wpdb->get_var("SELECT fb_id FROM $table WHERE user_id = '$user->ID' ");		
	}
	
	//return the facebook id
	static function get_facebookId($user){
		global $wpdb;
		$table = self::getFbTable();
		return $wpdb->get_var("SELECT fb_id FROM $table WHERE user_id = '$user->ID' ");
	}
	
	
	//remove the facebook account
	static function deleteUserFbAccount($user){
		global $wpdb;
		$table = self::getFbTable();
		$wpdb->query("DELETE FROM $table WHERE user_id = '$user->ID' ");
	}
}