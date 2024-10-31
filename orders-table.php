<?php
/*
	Plugin Name: Orders Table
	Plugin URI: 
	Description: The plugin allows you to make a convenient order page for your site. It is perfect for a store, hotel or a simple master of billets. Adjust it as it should and achieve the desired result.
	Version: 1.2
	Author: Somonator
	Author URI:
*/

/*  
	Copyright 2016  Alexsandr (email: somonator@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the order of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

if (!defined('ABSPATH')) exit;

require_once('inc/functions.php');

/**
* Add new post type.
*/
new ot_post_type();

/**
* New post status for post type.
*/
new ot_new_post_status();

/**
* Create options page for pligin.
*/
new ot_option_page();

/**
* Create meta boxes with options.
*/
new ot_meta_box();

/**
* Includes scripts and styles plugin.
*/
new ot_includes();

/**
* Create shortcode for diplay orders table.
*/
new ot_dislpay_front();