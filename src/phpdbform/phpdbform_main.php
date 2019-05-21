<?php
// Copyright 2002 - Coral Informática Ltda
// Paulo Assis
if(strstr($_SERVER["PHP_SELF"], "/phpdbform/phpdbform_main.php"))  die ("You can't access this file directly...");
session_start();

if( !isset($_SESSION["dbform"]) ) {
	$_SESSION["dbform"]["user"] = "";
	$_SESSION["dbform"]["logged"] = false;
}

require_once( "siteconfig.php" );
if( !preg_match("/^[a-z]+$/", $dbcfg["theme"]) ) $dbcfg["theme"] = "nt";

include("phpdbform/themes/{$dbcfg["theme"]}/theme.php");

if( isset($_GET["act"]) ) {
	if( $_GET["act"] == "logout" ) {
		$_SESSION["dbform"]["user"] = "";
		$_SESSION["dbform"]["logged"] = false;
	}
}

$erro = "";
if( isset($_POST["admLogin"]) ) {
	$admLogin = trim(strip_tags($_POST["admLogin"]));
	$admPasswd = trim(strip_tags($_POST["admPasswd"]));

	reset( $dbcfg["access"] );
	$logged = false;
	while( $uid = each($dbcfg["access"]) ) {
		if( $admLogin == $uid[0] && $admPasswd == $uid[1] ) {
			$logged = true;
			break;
		}
	}
	if( $logged ) {
		$_SESSION["dbform"]["user"] = $admLogin;
		$_SESSION["dbform"]["logged"] = true;
	} else {
		$_SESSION["dbform"]["user"] = "";
		$_SESSION["dbform"]["logged"] = false;
		$erro = "Invalid login and/or password.";
	}
}

function check_login()
{
	if( !$_SESSION["dbform"]["logged"] ) Header("Location: index.php");
}
// When you don't want to send any header code, use this global
$emptyHeader = "";
?>