<?php
/*
Plugin Name: Oberules
Plugin URI: https://github.com/Obewyn/Oberules
Description: This simple plugin allows the WordPress site administrator to enforce minimal password requirements on its user.  You can specify a minimal password length.  You can also demand that users input uppercase characters, digits or special characters.  This only effects password changes.  Existing passwords will not be validated.
Version: 0.1
Author: Obewyn
Author URI: https://github.com/Obewyn
Text Domain: Oberules

Copyright 2014  obewyn  (email : Obewyn@aol.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA



*/

global $obe-rules_error;
$obe-rules_error = false;

//Wrapper function for obe-rules
function obe-rules_check($user, &$pass1, &$pass2) {
	obe-rules(&$pass1, &$pass2);
}

// Validate password
function obe-rules(&$pass1, &$pass2) 
{
	global $obe-rules_error;
	
	
	if ($pass1 != $pass2 || $pass1 == '') {
		// If the passwords do not match or if no password is provided, we'll let WP throw its own error.
		return;
	}
	
	//Check password length
	$min_len = get_option("min_len");
	if (strlen($pass1) < $min_len) {
		$obe-rules_error = sprintf(__('<strong>ERROR</strong>: Your new password must be at least %d characters long.','obe-rules') , $min_len);
		return;
	}
	
	//Check if password as lowercase and upper case char
	$require_letter = get_option("require_letter");
	if ($require_letter == "lower_upper" && !( preg_match('/[A-Z]/', $pass1) && preg_match('/[a-z]/', $pass1) ) )  {
		$obe-rules_error = __('<strong>ERROR</strong>: Your new password must contain at least one uppercase letter and one lowercase letter.','obe-rules');
		return;
	} else if ($require_letter == "any_letter" && !preg_match('/[a-zA-Z]/', $pass1) )  {
		$obe-rules_error = __('<strong>ERROR</strong>: Your new password must contain at least one letter.','obe-rules');
		return;
	}
	
	//Check if password as a digit
	$require_digit = ( get_option("require_digit") == "checked" );
	if ($require_digit && !preg_match('/\d/', $pass1))  {
		$obe-rules_error = __('<strong>ERROR</strong>: Your new password must contain at least one digit.','obe-rules');
		return;
	}
	
	//Check if password as a punctuation character
	$require_punctuation = ( get_option("require_punctuation") == "checked" );
	if ($require_punctuation && !preg_match('/[`!"?$%^&*()_\-+={[}\]:;@\'~#|<>,.\/]/', $pass1))  {
		$obe-rules_error = __('<strong>ERROR</strong>: Your new password must contain at least one punctuation character among the following.','obe-rules') . '<br />` ! " ? $ % ^ & * ( ) _ - + = { [ } ] : ; @ \' ~ # | &lt; , &gt; . /';
		return;
	}
	
}

//Return validation errors
function obe-rules_error($errors) 
{
	global $obe-rules_error;
	
	if ($obe-rules_error) {
		$errors->add( 'pass', $obe-rules_error, array( 'form-field' => 'pass1' ) );
	}
}

// Add plugin to settings menu
function obe-rules_admin_menu() {  
	add_options_page("Oberules", "Oberules", 'install_plugins', __FILE__, "obe-rules_settings"); 
	add_action( 'admin_init', 'obe-rules_register_settings' );

}

// Register options
function obe-rules_register_settings() {
	register_setting( 'obe-rules', 'min_len' );
	register_setting( 'obe-rules', 'require_letter' );
	register_setting( 'obe-rules', 'require_digit' );
	register_setting( 'obe-rules', 'require_punctuation' );
}

// Display Settings form
function obe-rules_settings() {
	include('obe-rules_admin.php');
}    

//Called by the WP profile page
add_action('check_passwords','obe-rules_check', 10, 3);
add_action('user_profile_update_errors','obe-rules_error', 10, 1);

//Called by Wordpress to add link to option menu
add_action('admin_menu', 'obe-rules_admin_menu');  

//Provided for third party developper conveniance, to prevent possible conflicts with 'check_passwords' or 'user_profile_update_errors'
add_action('obe-rules','obe-rules', 10, 2);
add_action('obe-rules_error','obe-rules_error', 10, 1);

// Load the plugin textdomain
load_plugin_textdomain('obe-rules', '', 'obe-rules');

?>