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

class HTMLRes_HTMLNodeCollector implements ArrayAccess,Countable {
	/* * * * *
	 * 
	 * This class is the object front-end of this module.
	 * Objects of this class have the role of aiding in adding an element to the registry easily.
	 *
	 */
	
	
	/* * 
	 * Holds the name of the collector
	 * 
	 * */
	protected
	$_name = NULL;
	
	/* * 
	 * Holds the registry
	 * 
	 * */
	protected
	$_registry = NULL;

	
	/* * 
	 * Too obvious
	 * 
	 * */
	protected function __construct(){ }
	
	
	
	/* * 
	 * Factory of the collector.
	 * It is not ment to be used directly.
	 * 
	 * */
	public static function factory($name, array $options = NULL)
	{
		if (HTMLRes::collector_exists($name)) return HTMLRes::collector($name)->___options((array)$options);
		$new = new static;
		$new->_name = $name;
		$new->_registry = HTMLNodeReg::factory();
		return $new;
	}
	
	
	
	/* * 
	 * Supermagic. sets or gets the options of the collector's registry.
	 * <code>
	 * 		$test = HTMLRes::collector('test');
	 * 		$options = $test->___options();
	 * 		$options['extension'] = '.css';
	 * 		$test->___options($options);
	 * </code>
	 * 
	 * @param <array> $options % the options to be set, <void> to get
	 * @return <mixed> 
	 * 		-- <object> $this if used as setter
	 * 		-- <array> $options if used as getter
	 * 
	 * */
	public function ___options(array $options = NULL)
	{
		if ($options === NULL) return $this->_registry->options();
		$this->_registry->options($options);
		return $this;
	}
	
	
	
	/* * 
	 * Supermagic. Main collector function.
	 * Gets an array of argumets that is passed to the import method of the registry.
	 * Options can be set temporarily for a single import, if given as a second argument.
	 * If the third bool argument is supplied and true, the new options will be permanent.
	 * Parses the name for a pipe sign "|", and if it finds one, it explodes and imports
	 * as many nodes as the explosion produces, with the name from the explosion, and the
	 * rest of the arguments.
	 * <code>
	 * 		// permanently set extension to ".css3" for this import and after
	 * 		$collector->___collect_assoc(array('shadows'),array('extension'=>'.css3'),true);
	 * 		// change options temporarily on these ones
	 * 		$collector->___collect_assoc(array('main|basic|default'),array('extension'=>'.css'));
	 * </code>
	 * 
	 * @param <array> $args % the arguments to pass to registry import (see HTMLRes_HTMLNodeReg)
	 * @return <object> $this
	 * 
	 * */
	public function ___collect_assoc(array $args, array $options = NULL, $make_permanent = false)
	{
		$old_options = $this->___options();
		if ($options !== NULL) $this->___options($options);
		$name = isset($args['name']) ? $args['name'] : $args[0];
		if ((strlen($name) > 3) and (strpos($name,'|') !== false)) {
			$multi = explode('|',$name);
			foreach($multi as $name) {
				$newargs = $args;
				if (isset($newargs['name'])) $newargs['name'] = $name;
				else { array_shift($newargs); array_unshift($newargs,$name); }
				$this->_registry->import($newargs);
			}
		} else $this->_registry->import($args);
		if (($options !== NULL) and ( ! $make_permanent)) $this->___options($old_options);
		return $this;
	}
	
	
	
	/* * 
	 * Supermagic. Wrapper for ___collect_assoc() that calls this latter with func_get_args,
	 * meaning that parameters can be passed directly as function arguments instead of them
	 * being passed wrapped in an array
	 * 
	 * */
	public function ___collect()
	{
		return $this->___collect_assoc(func_get_args());
	}
	
	
	
	/* * 
	 * Supermagic. Returns the registry.
	 * ( why !? i have no ideea ... it's not used anywhere)
	 * 
	 * @params none
	 * @return <object#HTMLNodeReg> $this->_registry
	 * 
	 * */
	public function ___getreg()
	{
		return $this->_registry;
	}
	
	
	
	/* * 
	 * Supermagic. Wrapper for the registry's ordered_list() method.
	 * 
	 * @params none
	 * @return <array> $nodes % array with the nodes in the registry
	 * 
	 * */
	public function ___getnodes()
	{
		return $this->_registry->ordered_list();
	}
	
	
	
	/* * 
	 * Supermagic. Chainable collector switcher
	 * 
	 * @params <string> $name % name of the collector to switch to or create
	 * @return <object#HTMLNodeCollector> $collector % the collector
	 * 
	 * */
	public function ___switch($name)
	{
		return HTMLRes::collector($name);
	}
	
	
	
	/* * 
	 * Supermagic. Wrapper for ___switch()
	 * 
	 * @params <string> $name % name of the collector to switch to or create
	 * @return <object#HTMLNodeCollector> $collector % the collector
	 * 
	 * */
	public function ___collector($name)
	{
		return $this->___switch($name);
	}
	
	
	
	/* * 
	 * MagicMethod.
	 * Checks the registry for a node with specified name;
	 * 
	 * */
	public function __isset($name)
	{
		return $this->_registry->has($name);
	}
	
	
	
	/* * 
	 * MagicMethod.
	 * Forces unregistration of a node in the registry if it exists.
	 * 
	 * */
	public function __unset($name)
	{
		$this->_registry->unregister($name,true);
	}
	
	
	
	/* * 
	 * MagicMethod.
	 * Collect Form: Function Call (symbol/expression)
	 * Collects an element by joining the elements recieved as parameters of the function,
	 * and adding the name of the function as the first element in the array.
	 * Passes everythisg to the main collector.
	 * 
	 * If the element is in an subdirectory or the file has dot "." in his name,
	 * you can call the function name as an expresiion that evaluates to a string.
	 * 
	 * */
	public function __call($name,$args)
	{
		array_unshift($args,$name);
		return $this->___collect_assoc($args);
	}
	
	
	/* * 
	 * MagicMethod.
	 * Collect Form: Property set (symbol/expression)
	 * Collects an element by joining the value recieved to be set with the name of 
	 * property to be set. If the value is not an array, than it is considered to be the
	 * second parameter, and the array is created form the name and the value in this order.
	 * Passes everythisg to the main collector.
	 * 
	 * If the element is in an subdirectory or the file has dot "." in his name,
	 * you can write the name of the propery to be set as an expresiion that evaluates to a string.
	 * 
	 * */
	public function __set($name,$value)
	{
		$args = is_array($value) ? $value : array($value);
		array_unshift($args,$name);
		$this->___collect_assoc($args);
		return $value;
	}
	
	
	
	/* * 
	 * MagicMethod.
	 * Same as ___collect()
	 * 
	 * */
	public function __invoke()
	{
		return $this->___collect_assoc(func_get_args());
	}
	
	
	
	/* * 
	 * MagicMethod.
	 * Renders the registry.
	 * 
	 * */
	public function __toString()
	{
		return (string)$this->_registry;
	}
	
	
	
	/* * 
	 * ArrayAccess implementation
	 * 
	 * */
	public function offsetExists($offset) { return isset($offset); }
	public function offsetUnset($offset) { unset($this->{$offset}); }
	public function offsetGet($offset) { return $this->{$offset} ; }
	public function offsetSet($offset,$value) { $this->{$offset} = $value; }
	
	/* * 
	 * Countable implementation
	 * 
	 * */
	public function count() { return count($this->_registy); }
	
}

