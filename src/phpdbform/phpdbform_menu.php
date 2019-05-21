<?php
/**************************************
 * phpdbform_menu                     *
 **************************************
 * menu control                       *
 * - Control menu and access          *
 *                                    *
 * Paulo Assis <paulo@phpdbform.com>  *
 * 2003 - 06 - 20                     *
 **************************************/
if(strstr($_SERVER["PHP_SELF"], "/phpdbform/phpdbform_menu.php"))  die ("You can't access this file directly...");

// this class can be used as a base class for any other kind of access
// the access class should give all functions and vars defined here!
class phpdbform_menu
{
	var $menu = array();
	var $last_group = "";

	function add_group( $name, $min_level )
	{
		if( !isset($this->menu[$name]) )
		{
			$this->menu[$name] = array( "level"=>$min_level, "items"=>array());
			$this->last_group = $name;
		}
	}

	function add_item( $name, $file, $min_level=0, $group="" )
	{
		if( empty($this->last_group) ) return;
		if( empty($group) ) $group = $this->last_group;
		if( isset($this->menu[$group]["items"][$name]) ) return;
		if( $min_level < $this->menu[$group]["level"] ) $min_level = $this->menu[$group]["level"];
		$this->menu[$group]["items"][$name] = array( "level"=>$min_level, "file"=>$file );
	}

	function get_menu( $level )
	{
		$menu = array();
		if( empty($this->menu) ) return $menu;

		reset( $this->menu );
		while( $item = each($this->menu) )
		{
			if( $level < $item[1]["level"] ) continue;
			$menu[$item[0]] = array();
			while( $subitem = each($item[1]["items"]) )
			{
				if( $level < $subitem[1]["level"] ) continue;
				$menu[$item[0]][$subitem[0]] = $subitem[1]["file"];
			}
			if( empty($menu[$item[0]]) ) unset($menu[$item[0]]);
		}
		return $menu;
	}
}
?>