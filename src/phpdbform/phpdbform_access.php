<?php
/**************************************
 * phpdbform_access                   *
 **************************************
 * Access / login control             *
 * - Control access and login         *
 * Uses _SESSION:                     *
 *   bool logged                      *
 *   string user                      *
 *   int level                        *
 *   string domain                    *
 *                                    *
 * Paulo Assis <paulo@phpdbform.com>  *
 * 2003 - 06 - 19                     *
 **************************************/
if(strstr($_SERVER["PHP_SELF"], "/phpdbform/phpdbform_access.php"))  die ("You can't access this file directly...");

// this class can be used as a base class for any other kind of access
// the access class should give all functions and vars defined here!
class phpdbform_access
{
	// array [login] = array( password=>, level=> )
	var $users;
	// used to check domain for login, usefull if there are several
	// applications with different logins at the same domain
	// use any string you want
	var $domain;

	function __construct( &$users, $domain )
	{
		$this->users = $users;
		$this->domain = $domain;
		if( !isset($_SESSION["dbform"]["login"]) )
		{
			$_SESSION["dbform"]["login"]["user"] = "";
			$_SESSION["dbform"]["login"]["logged"] = false;
			$_SESSION["dbform"]["login"]["level"] = 0;
			$_SESSION["dbform"]["login"]["domain"] = $domain;
		} else {
			if( $_SESSION["dbform"]["login"]["domain"] != $domain ) logout();
		}
	}

	function do_login( $user, $passwd )
	{
		$this->logout();
		if( !isset($this->users[$user]) ) return false;
		if( $this->users[$user]["password"] != $passwd ) return false;
		$_SESSION["dbform"]["login"]["user"] = $user;
		$_SESSION["dbform"]["login"]["logged"] = true;
		$_SESSION["dbform"]["login"]["level"] = $this->users[$user]["level"];
		return true;
	}

	function check_login( $min_level )
	{
		if( !$_SESSION["dbform"]["login"]["logged"] ) return false;
		return ($_SESSION["dbform"]["login"]["level"] >= $min_level );
	}

	function logout()
	{
		$_SESSION["dbform"]["login"]["user"] = "";
		$_SESSION["dbform"]["login"]["logged"] = false;
		$_SESSION["dbform"]["login"]["level"] = 0;
	}

	function get_user()
	{
		return $_SESSION["dbform"]["login"]["user"];
	}

	function get_domain()
	{
		return $_SESSION["dbform"]["login"]["domain"];
	}

	function get_level()
	{
		return $_SESSION["dbform"]["login"]["level"];
	}
}
?>