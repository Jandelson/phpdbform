<?php
class phpdbform_theme_base {
	var $name = "base";
	var $header_code;
	var $width;
	var $title;

	function __construct()
	{
		$this->width = 500;
	}

	function draw_menu() {}
	function draw_adm_header( $title ) {}
	function draw_adm_footer() {}
}
?>