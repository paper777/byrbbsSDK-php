<?php
require_once("Oauth.class.php");
require_once("ErrorCase.class.php");
require_once('../wp-load.php');

$oauth = new Oauth();
$error = new ErrorCase();
$access_params = $oauth->callback();

if(isset($access_params['error'])){
	$error->showError("10001", $access_params['error']);
	exit();
}

$userinfo = $oauth->get_info($access_params['access_token']);
if(isset($userinfo['request'])){
	$error->showError("10001", $userinfo['msg']);
	exit();
}


$userdata = array(
	'user_pass' => wp_generate_password(),
	'user_login' => $userinfo['id'],
	'display_name' => $userinfo['user_name'],
	'user_email' => $userinfo['id'].'@byr.com'
);

if(!function_exists('wp_insert_user')){
	include_once( ABSPATH . WPINC . '/registration.php' );
} 

global $wpdb;
$sql = "SELECT ID FROM $wpdb->users WHERE user_login = '%s'";
$wpuid =  $wpdb->get_var($wpdb->prepare($sql, $userinfo['id']));

if(!$wpuid){
	if($userinfo['id']){
		$wpuid = wp_insert_user($userdata);

		if($wpuid){
			update_usermeta($wpuid, 'byrid', $userinfo['id']);
			$byr_array = array (
				"oauth_access_token" => $access_params['access_token'],
				"oauth_refresh_token" => $access_params['refresh_token'],
			);
			update_usermeta($wpuid, 'byrdata', $byr_array);
		}
	}
} else {
	update_usermeta($wpuid, 'byrid', $userinfo['id']);
	$byr_array = array (
		"oauth_access_token" => $access_params['access_token'],
		"oauth_refresh_token" => $access_params['refresh_token'],
	);
	update_usermeta($wpuid, 'byrdata', $byr_array);
}

if($wpuid) {
	wp_set_auth_cookie($wpuid, true, false);
	wp_set_current_user($wpuid);
}
wp_safe_redirect( user_admin_url() );
?>
