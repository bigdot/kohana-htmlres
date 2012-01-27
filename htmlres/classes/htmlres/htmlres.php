<?php 
/* * * * *
 * 
 * Copyright 2011,2012 Florin-Tiberiu Iacob.
 * 
 * This file is part of HTMLRes.
 * 
 * [Note]
 * 		HTMLRes is a Kohana module.
 * 		Kohana is a PHP HMVC framework.
 * 		See http://kohanaframework.org .
 * 		Kohana is free software, and copyrighted by it's authors.
 * [End Note]
 * 
 * HTMLres is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * HTMLRes is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public License
 * along with HTMLRes.  If not, see <http://www.gnu.org/licenses/>.
 * 
 * The GNU Lesser General Public License should be in a file named "LICENSE".
 * 
 * */
 
defined('SYSPATH') or die('NO Direct Script Acccess !');

class HTMLRes_HTMLres {
	/* * 
	 * Module's front-end.
	 * 
	 * The only important function is collector(), which returns an existing or new
	 * node collector.
	 * [Node: see HTMLRes_HTMLNodeCollector for usage]
	 * 
	 * */
	
	
	protected static
	$_collectors = array();
	

	public static function script_node_link( HTMLNode $node )
	{
		return HTML::script($node->src(),$node->attrs());
	}
	
	
	
	public static function style_node_link( HTMLNode $node )
	{
		return HTML::style($node->src(),$node->attrs());
	}
	
	
	
	public static function collector($name = 'default', array $options = NULL)
	{
		if ( ! self::collector_exists($name)) {
			self::$_collectors[$name] = HTMLNodeCollector::factory($name,$options);
		}
		$coll = self::$_collectors[$name]; 
		return ($options === NULL or empty($options)) ? $coll : $coll->___options($options);
	}
	
	
	public static function collector_exists($name)
	{
		return isset(self::$_collectors[$name]);
	}
	
	
}

?>
