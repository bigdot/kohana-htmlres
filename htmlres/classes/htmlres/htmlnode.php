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

class HTMLRes_HTMLNode {
	/* * 
	 * 
	 * Node Class.
	 * 
	 */
	
	
	protected
	$_name = NULL,
	$_index = false;
	
	public
	$src = '',
	$attrs = array(),
	$prio = false,
	$deps = array(),
	$cons = array(),
	$prefix = '',
	$extension = '';
	

	public static function new_instance(){ return new static; }
	
	
	protected function init_instance($name, $src, $attrs, $prio, $cons, $deps, $prefix, $ext)
	{ 
		if ( ! strlen($name)) return NULL;
		$this->_name = $name;
		return $this->prefix($prefix)->extension($ext)->src($src)->prio($prio)->attrs($attrs)->cons($cons)->deps($deps);
	}
	
	
	
	public function __get($name)
	{
		if ($name === 'name') return $this->_name;
		throw new Exception("Member does not exist: \"$name\"! ");
	}
	
	
	
	public static function factory($name, $src = true, array $attrs = NULL, $prio = false, array $cons = NULL, array $deps = NULL, $prefix = '', $extension = '')
	{
		return self::new_instance()->init_instance( (string)$name, $src, (array)$attrs, $prio, (array)$cons, (array)$deps, (string)$prefix, (string)$extension );
	}
	
	
	
	public function name()
	{
		return $this->_name;
	}
	
	
	
	public function attrs(array $new_attrs = NULL, $replace = false)
	{
		if ($new_attrs === NULL) return $this->attrs;
		if ($replace) $this->attrs = $new_attrs;
		else $this->attrs = array_merge($this->attrs, $new_attrs);
		return $this;
	}
	
	
	
	public function src($src = NULL)
	{
		if ($src === NULL) return $this->src;
		$build = false;
		if ( ! is_string($src)) {
			if ($src === true) $build = true;
			$src = ''; 
		}
		if ( ! strlen($src)) {
			if ($build) $src = $this->prefix . $this->name . $this->extension;
			else $src = '#';
		}
		$this->src = $src;
		return $this;
	}
	
	
	
	public function prio($prio = NULL)
	{
		if ($prio === NULL) return $this->prio;
		if ( ! is_int($prio)) $this->prio = false;
		else $this->prio = $prio;
		return $this;
	}
	
	
	
	public function prefix($pref = NULL)
	{
		if ($pref === NULL) return $this->prefix;
		if (is_string($pref)) $this->prefix = $pref;
		else $this->prefix = '';
		return $this;
	}
	
	
	
	public function extension($ext = NULL)
	{
		if ($ext === NULL) return $this->extension;
		if (is_string($ext)) $this->extension = $ext;
		else $this->extension = '';
		return $this;
	}
	
	
	
	public function deps(array $deps = NULL)
	{
		if ($deps === NULL) return $this->deps;
		else $this->deps = $this->_valideps($deps, $this->cons);
		return $this;
	}
	
	
	
	public function cons(array $cons = NULL)
	{
		if ($cons === NULL) return $this->cons;
		$this->cons = $this->_valicons($cons);
		return $this;
	}
	
	
	
	protected function _str_only($arr)
	{
		foreach ($arr as $elem) {
			if ( ! is_string($elem)) return false;
		}
		return true;
	}
	
	
	
	protected function _valicons($cons)
	{
		if ( ! $this->_str_only($cons)) return array();
		$newcons = array();
		foreach($cons as $con) {
			if (($con !== $this->name()) and (array_search($con,$newcons) === false)) $newcons[] = $con;
		}
		return $newcons;
	}
	
	
	
	protected function _valideps($deps,$cons)
	{
		if ( ! $this->_str_only($cons)) return array();
		$newdeps = array();
		foreach($deps as $dep) {
			if (($dep !== $this->name()) and (array_search($dep,$newdeps,true) === false) and (array_search($dep,$cons,true) === false)) $newdeps[] = $dep;
		}
		return $newdeps;
	}
	
	
	
	
	public function has_prio()
	{
		return is_int($this->prio);
	}
	
	
	
	public function has_deps()
	{
		return ( ! empty($this->deps));
	}
	
	
	
	public function index($index = NULL)
	{
		if ( ! (is_int($index) or ($index === false))) return $index;
		$this->_index = $index;
		return $this;
	}
	
	
	//public function depends_on($name)
	public function depends_on( HTMLNode $node)
	{
		//return (is_string($name) and (array_search($name,$this->deps,true) !==false));
		return (array_search($node->name(),$this->deps,true) !==false);
	}
	
	
	
	//public function conflicts_with($name)
	public function conflicts_with( HTMLNode $node)
	{
		//return (is_string($name) and (array_search($name,$this->cons,true) !==false));
		return (array_search($node->name(),$this->cons,true) !==false);
	}
	
	
	
	public function conflicts_with_named($name)
	{
		return (is_string($name) and (array_search($name,$this->cons,true) !==false));
	}
	
}
