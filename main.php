<?php
/*
 * plugin name: Facebook Login with Pilotpress
 * author: Mahibul Hasan Sohag
 * plugin uri: http://juicefeast.com/
 * author uri: http://sohag07hasan.elance.com
 * Description: It makes a way to login with facebook to a membership site and it send request in pilotpress
 */

define('FBPILOTPRESS_DIR', dirname(__FILE__));
define('FBPILOTPRESS_URI', plugins_url('', __FILE__));
define('FBPILOTPRESS_SDK', FBPILOTPRESS_DIR . '/php-sdk/src');


include FBPILOTPRESS_DIR . '/classes/fb-pilotpress.php';
FbPilotPress::init();


?>