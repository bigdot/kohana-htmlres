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

class HTMLRes_HTMLNodeReg implements Countable {
	/* * * * *
	 * 
	 * The nodes registry.
	 * 
	 */
	
	
	
	/* * 
	 * Containers.
	 * 
	 * */
	protected
	$_nodes = array(),
	$_order = array();
	
	
	
	/* * 
	 * Options.
	 * Holds the prefix to be passed to import function in case it is not supplied,
	 * and the rendering callbacks.
	 * 
	 * */
	protected
	$_options = array(
		'prefix' => '',
		'extension' => '',
		'render_node_callback' => array('HTMLNodeReg','render_node_callback'),
		'render_registry_callback' => array('HTMLNodeReg','render_registry_callback'),
		'attrs' => array()
	);
	
	
	
	/* * 
	 * These are self explanatory. (the first will probably dissappear in the future)
	 * 
	 * */
	public static function new_instance(){ return new static; }
	public static function factory(){ return self::new_instance(); }
	
	
	
	/* * 
	 * What is this ?!
	 * 
	 * */
	public function nodes( array $nodes = NULL)
	{
		if ($nodes === NULL) return $this->_nodes;
		foreach ($nodes as $node) {
			$this->node($node);
		}
		return $this;
	}
	
	
	
	/* * 
	 * Or this ??!?
	 * 
	 * */
	public function node( $node_or_name, $override = false, $create_if_not_exists = false)
	{
		if (is_string($nn = $node_or_name) and $this->has($nn)) return $this->get($nn);
		elseif (is_string($nn) and ( ! $this->has($nn)) and $create_if_not_exists and $this->can_register(HTMLNode::factory($nn))) $this->register($new);
		elseif (is_object($nn) and ($nn instanceof HTMLNode) and (($this->has($nn) and $override) or ( ! $this->has($nn))) and $this->can_register($nn)) $this->register($nn);
		return $this;
	}
	
	
	
	/* * 
	 * Get a list of names of the current registered nodes.
	 * 
	 * */
	public function names()
	{
		return array_keys($this->_nodes);
	}
	
	
	
	/* * 
	 * Check if a node exists in the registry.
	 * <code>
	 * 		if ($registry->has($name)) {
	 * 			// do the dew
	 * 		}
	 * </code>
	 * 
	 * @params 1
	 * @param <mixed> $node_or_name
	 * 			- <object#HTMLNode> $name_or_node % node to check for ( it searches by name)
	 * 			- <string> $name_or_node % name of the node to look for
	 * @return <bool> 
	 * 
	 * */
	public function has( $node_or_name )
	{
		$name = is_string($nn = $node_or_name) ? $nn : $nn->name();
		return isset($this->_nodes[$name]);
	}
	
	
	
	/* * 
	 * Try to fetch a node in the registry by name
	 * <code>
	 * 		if (($node = $registry->get($name)) !== false) {
	 * 			// do the dew with $node
	 * 		}
	 * </code>
	 * 
	 * @params 1
	 * @param <mixed> $node_or_name
	 * 			- <object#HTMLNode> $name_or_node % node to check for ( it searches by name)
	 * 			- <string> $name_or_node % name of the node to look for
	 * @return <mixed> 
	 * 			- <object#HTMLNode> $node % node object in the registry on success
	 * 			- <bool> false % on failure
	 * 
	 * */
	public function get( $node_or_name )
	{
		if ($this->has($nn = $node_or_name)) {
			return $this->_nodes[ (is_string($nn)?$nn:$nn->name()) ];
		}
		return false;
	}
	
	
	
	/* * 
	 * Option getter and setter.
	 * 
	 * */
	public function option($tag, $value = NULL)
	{
		if ($value === NULL) {
			if ((is_string($tag)) and (isset($this->_options[$tag]))) return $this->_options[$tag];
			return NULL;
		}
		if (is_string($tag)) $this->_options[$tag] = $value;
		return $this;
	}
	
	
	
	/* * 
	 * Options getter and setter.
	 * The options are merged, not replaced.
	 * 
	 * */
	public function options( array $options = NULL)
	{
		if ($options === NULL) return $this->_options;
		$this->_options = array_merge($this->_options, $options);
		return $this;
	}
	
	
	
	
	/* * 
	 * Callback getter.
	 * Appends _callback to the tag and tries to get the option,
	 * 
	 * */
	public function callback($tag)
	{
		$null = function(){ return ''; };
		if (is_string($tag) and (strlen($tag = $tag.'_callback') > 9) and is_callable($callback = $this->option($tag)))
			return $callback;
		return $null;
	}
	
	
	
	/* * 
	 * Default callback for node render
	 * 
	 * */
	public static function render_node_callback( HTMLNode $node )
	{
		return "<!-- {$node->name()} src=\"{$node->src()}\" -->\n";
	}
	
	
	
	/* * 
	 * Default callback for registry render
	 * 
	 * */
	public static function render_registry_callback( HTMLNodeReg $reg )
	{
		$list = $reg->ordered_list();
		$callback = $reg->callback('render_node');
		$res = '';
		foreach($list as $node) {
			$res .= (string)call_user_func_array($callback,array($node));
		}
		return $res;
	}
	
	
	
	/* * 
	 * Render method.
	 * Calls the callback render_registry_callback option.
	 * 
	 * */
	public function render($mute_exceptions = true)
	{
		if ($mute_exceptions) {
			try {
				return (string)call_user_func_array($this->callback('render_registry'),array($this));
			} catch (Exception $e) {
				return '';
			}
		}
		return (string)call_user_func_array($this->callback('render_registry'),array($this));
	}
	
	
	
	/* * 
	 * MagicMethod.
	 * Calls render()
	 * 
	 * */
	public function __toString()
	{
		return $this->render();
	}
	
	
	
	/* * 
	 * Tests to see if a node can be unregistered,
	 * The second parameter represents the priority of the node that tries to unset this one.
	 * 
	 * */
	public function can_unregister( HTMLNode $node, $unsetter_prio = NULL) 
	{
		if ($unsetter_prio === NULL) $unsetter_prio = $node->prio();
		if (( ! is_int($unsetter_prio)) or (is_int($node->prio()) and ($unsetter_prio < $node->prio()))) return false;
		if (($needers = $this->_who_needs($node)) !== false) {
			foreach($needers as $needee) {
				if ( ! $needee->has_prio()) continue;
				if ($needee->prio() > $unsetter_prio) return false;
				if ( ! $this->can_unregister($needee, $unsetter_prio)) return false;
			}
		}
		return true;
	}
	
	
	
	/* * 
	 * Unregisters a node if this is possible.
	 * Returns true on succes and false on faliure.
	 * 
	 * */
	public function unregister($node_or_name, $unsetter_prio = NULL)// $prio = true  to force unset (not recommended)
	{
		if ( ! $this->has($node_or_name)) return false;
		$node = is_object($node_or_name) ? $node_or_name : $this->get($node_or_name); 
		if (($unsetter_prio === true) or ($this->can_unregister($node,$unsetter_prio))) {
			$this->_kill($node, true);
			return true;
		}
		return false;
	}
	
	
	
	/* * 
	 * Tests to see if a node can be registered. 
	 * If set to true, the second parameter makes the method return a mixed key list of things that
	 * must be done before registering the node, and that is: dependencies that must be registered first,
	 * and conflicts that must be unregistered. It returns bool otherwise,
	 * 
	 * */
	public function can_register( HTMLNode $node , $return_todo_list = false)
	{
		if ($this->has($node)) return false;
		if (($to_kill_exs = $this->_passes_existing_cons($node)) === false) return false;
		if (($to_kill_own = $this->_passes_own_cons($node)) === false) return false;
		if (($missing = $this->_passes_deps($node)) === false) return false;
		$result = array(
			($to_kill = array_merge(
				( ($to_kill_exs === true) ? array() : $to_kill_exs ),
				( ($to_kill_own === true) ? array() : $to_kill_own )
			)),
			(($missing = ($missing === true) ? array() : $missing) ),
			'to_kill' => $to_kill,
			'missing' => $missing
		);
		return $return_todo_list ? $result : true;
	}
	
	
	
	/* * 
	 * Registers a node it this is possible.
	 * True on success, false on faliure.
	 * 
	 * */
	public function register( HTMLNode $node, $create_missing_deps = false)
	{
		if (($to_do = $this->can_register($node,true)) === false) return false;
		list($goodby,$hello) = $to_do;
		foreach($goodby as $sorry) $this->_kill($sorry,false);
		$this->_reindex_order();
		if ($create_missing_deps) {
			foreach($hello as $new) {
				if ( ! $this->register($new, true)) return false;
			}
			$this->_new_order($node);
			$this->_nodes[$node->name()] = $node;
			return true;
		}
		// this is same as
		// 
		// empty($hello)
		// ? true 		// no deps to add so it's ok to register
		// : false; 	// otherwise
		return empty($hello);
	}
	
	
	
	/* * 
	 * Internal.
	 * Node killer
	 * 
	 * */
	protected function _kill( HTMLNode $node, $reindex = true)
	{
		if ($this->has($node)) {
			$node->index(false);
			if (($oi = array_search($node->name(),$this->_order,true)) !== false) unset($this->_order[$oi]);
			unset($this->_nodes[$node->name()]);
		}
		return $reindex ? $this->_reindex_order() : $this;
	}
	
	
	
	/* * 
	 * Internal.
	 * This method checks to see if node's dependencies can be registered.
	 * Returns true if there are no missing deps, false if there's a proble,
	 * or an array of missing deps.
	 * 
	 * */
	protected function _passes_deps( HTMLNode $node)
	{	
		$deps = $node->deps();
		if (empty($deps)) return true;
		$missing = array();
		foreach($deps as $depname) {
			if ( ! $this->has($depname)) {
				$dep = HTMLNode::factory($depname,true,$node->attrs(),$node->prio(),NULL,NULL,$node->prefix(),$node->extension());
				if ( ! $this->can_register($dep)) return false;
				$missing[] = $dep;
			}
		}
		return empty($missing) ? true : $missing;
	}
	
	
	
	/* * 
	 * Internal.
	 * Find nodes that need another node.
	 * 
	 * */
	public function _who_needs($node_or_name)
	{
		if (($node = $this->get($node_or_name)) === false) return false;
		$needers = array();
		foreach ($this->_nodes as $name => $needer) {
			if ($needer->depends_on($node)) $needers[] = $needer;
		}
		return empty($needers) ? false : $needers;
	}
	
	
	
	/* * 
	 * Internal.
	 * Check the node's conflicts against the existing nodes.
	 * 
	 * */
	protected function _passes_own_cons( HTMLNode $node )
	{
		$cons = $node->cons();
		if (empty($cons)) return true;
		$low_cons = array();
		foreach ($cons as $conname){
			if ( ! $this->has($conname)) continue;
			$con = $this->get($conname);
			if ( ! $this->can_unregister($con,$node->prio())) return false;
			$low_cons[] = $con;
		}
		return empty($low_cons) ? true : $low_cons;
	}
	
	
	
	/* * 
	 * Internal.
	 * Check to see if any node conflicts with this one
	 * 
	 * */
	protected function _passes_existing_cons( HTMLNode $node )
	{
		$low_cons = array();
		if (($e_cons = $this->_existing_cons_with($node)) !== false) {
			if ( ! $node->has_prio()) return false;
			foreach($e_cons as $e_con) {
				if ( ! $this->can_unregister($e_con,$node->prio())) return false;
				$low_cons[] = $e_con;
			}
		}
		return empty($low_cons) ? true : $low_cons;
	}
	
	
	
	/* * 
	 * Internal.
	 * Gets a list of nodes in the registry that conflict with this node
	 * 
	 * */
	protected function _existing_cons_with( HTMLNode $node)
	{
		$e_cons = array();
		foreach ($this->_nodes as $cname => $cnode){
			if ( $cnode->conflicts_with($node) ) $e_cons[] = $cnode;
		}
		return empty($e_cons) ? false : $e_cons;
	}
	
	
	
	/* * 
	 * Internal.
	 * Main order resolver function.
	 * 
	 * */
	protected function _new_order( HTMLNode $node )
	{
		if ($this->get($node) !== false) return false;
		return $this->_order_insert($node, $this->_order_find_a_position_for($node) );
	}
	
	
	
	/* * 
	 * Internal.
	 * Order setter. Insets a node in the order array considering the specified index
	 * or at he bottom if null of false. True inserts the node at he top.
	 * 
	 * */
	protected function _order_insert( HTMLNode $node, $index = NULL)
	{
		$name = $node->name();
		$validindex = (($index === NULL) or is_bool($index) or is_int($index));
		if (( ! $validindex) or ($index === NULL) or ($index === false) or empty($this->_order)) {
			$this->_reindex_order()->_order_insert_bottom($node);
		} elseif($index === true) {
			$this->_reindex_order()->_order_insert_top($node);
		} else {
			reset($this->_order);
			$first = key($this->_order);
			end($this->_order);
			$last = key($this->_order);
			if ($index <= $first) $this->_order_insert_top($node);
			elseif ($index > $last) $this->_order_insert_bottom($node);
			else $this->_order_insert_at($index,$node);
		}
		return $this;
	}
	
	
	
	/* * 
	 * Internal.
	 * Does what it says.
	 * 
	 * */
	protected function _order_insert_top( HTMLNode $node)
	{
		array_unshift($this->_order,$node->name());
		$node->index(array_search($node->name(),$this->_order,true));
		return $this;
	}
	
	
	
	/* * 
	 * Internal.
	 * Same as the above
	 * 
	 * */
	protected function _order_insert_bottom( HTMLNode $node)
	{
		$this->_order[] = $node->name();
		$node->index(array_search($node->name(),$this->_order,true));
		return $this;
	}
	
	
	
	/* * 
	 * Internal.
	 * Inset a node at specified index moving everythig down.
	 * 
	 * */
	protected function _order_insert_at($index, HTMLNode $node)
	{
		$name = $node->name();
		$bottom = array_splice($this->_order,$index);
		$this->_order[$index] = $name;
		$this->_order = array_merge($this->_order,$bottom);
		$this->_reindex_order();
		$node->index(array_search($node->name(),$this->_order,true));
		return $this;
	}
	
	
	
	/* * 
	 * Internal.
	 * Reindex the order array and re-set the node's index property.
	 * 
	 * */
	protected function _reindex_order()
	{
		ksort($this->_order);
		$this->_order = array_values($this->_order);
		foreach($this->_order as $i => $name) {
			if (($node = $this->get($name)) !== false) $node->index($i);
		}
		return $this;
	}
	
	
	/* * 
	 * Internal.
	 * Finds a positions in the order list for a node considering his conflicts and dependencies.
	 * 
	 * */
	protected function _order_find_a_position_for( HTMLNode $node)
	{
		if (empty($this->_nodes) or empty($this->_order)) return false; 		// append the node in this case
		$prio = $node->prio();
		if ( ! is_int($prio)) { 	// if node doesn't have priority
			if ( ! $node->has_deps()) {		// if no deps to move it forward
				return $this->_order_find_first_prio_index(); // insert before the prioritized nodes
			} else {						// if we have deps, let's get deps with priority
				$deps = $node->deps();
				$depprios = array();
				foreach ($deps as $depname) {
					if ((($dep = $this->get($depname)) !== false) and $dep->has_prio()) $depprios[$depname] = $dep->prio();
				}
				if (empty($depprios)) return $this->_order_find_first_prio_index(); // if no deps with priority, insert before the prioritized nodes 
				foreach($this->_order as $i => $name) { // otherwise, inset after last dep
					if (array_key_exists($name,$depprios)) $last_dep_name = $name;
				}
				return $this->_order_find_next_of($last_dep_name);
			}
		} else {	// otherwise, if node has priority
			$nprios = array(); // get nodes with priority
			foreach($this->_order as $i => $name) { 
				$n = $this->get($name); 
				if ((($n = $this->get($name)) !== false) and ($n->has_prio())) $nprios[ $name ] = $n->prio();
			}
			if (empty($nprios)) return false; // if no nodes with prio, append at bottom
			//asort($nprios,SORT_NUMERIC);
			$nprios = array_reverse($nprios,true);
			$found = false; // walk the priorities in reverse order to find a place for ours
			foreach($nprios as $name => $nprio) {
				if ($prio >= $nprio) { $found = true; $lower_prio_name = (string)$name; $lower_prio = $nprio; break;} // search for a node with lower priority
			}
			if ( ! $found) { // if did not found a node with a lower priority, let's check deps that move the node
				if ( ! $node->has_deps()) { // if no deps 
					return $this->_order_find_first_prio_index(); // insert at the top of prioritized nodes
				} else { // otherwise, let's find deps with priority
					$deps = $node->deps();
					$depprios = array();
					foreach ($deps as $depname) {
						if ((($dep = $this->get($depname)) !== false) and $dep->has_prio()) $depprios[$depname] = $dep->prio();
					}
					if (empty($depprios)) return $this->_order_find_first_prio_index(); // if deps without prio, insert at top of prioritized nodes
					foreach($this->_order as $i => $name) { // otherwise, inset after last dep
						if (array_key_exists($name,$depprios)) $last_dep_name = $name;
					}
					return $this->_order_find_next_of($last_dep_name);
				}
			} else { // otherwise, if we found a node with a lower priority
				if ( ! $node->has_deps()) { // if no deps 
					return $this->_order_find_next_of($lower_prio_name); // insert after the lower priority node
				} else { // otherwise, let's find deps with priority
					$deps = $node->deps();
					$depprios = array();
					foreach ($deps as $depname) {
						if ((($dep = $this->get($depname)) !== false) and $dep->has_prio()) $depprios[$depname] = $dep->prio();
					}
					if (empty($depprios)) return $this->_order_find_next_of($lower_prio_name); // if deps without prio, insert after the lower priority node
					foreach($this->_order as $i => $name) { // otherwise, find last dep
						if (isset($depprios[$name])) $last_dep_name = $name;
					}
					$last_dep_prio = $depprios[$last_dep_name];
					if ($last_dep_prio > $lower_prio) return $this->_order_find_next_of($last_dep_name);
					return $this->_order_find_next_of($lower_prio_name);
				}
			}
		}
	}
	
	
	
	/* * 
	 * Internal.
	 * Does what it says.
	 * 
	 * */
	protected function _order_find_first_prio_index()
	{
		if (empty($this->_nodes)) return false;
		foreach($this->_order as $i => $name) {
			if ($this->get($name)->has_prio()) return $i;
		}
		return false;
	}
	
	
	
	/* * 
	 * Internal.
	 * Find the next index in the order array after the specified node.
	 * 
	 * */
	protected function _order_find_next_of($nodename = NULL)
	{
		if (empty($this->_order) or ( ! is_string($nodename))) return false;
		$order = $this->_order;
		$key = false;
		reset($order);
		$curr = current($order);
		while($curr !== false) {
			if ($curr === $nodename){
				next($order);
				$key = key($order);
				break;
			}
			$curr = next($order);
		}
		return is_int($key) ? $key : false;
	}
	
	
	
	/* * 
	 * Returns a list of nodes in order
	 * 
	 * */
	public function ordered_list()
	{
		$list = array();
		ksort($this->_order);
		foreach($this->_order as $i => $name){
			$list[$name] = $this->get($name);
		}
		return $list;
	}
	
	
	
	/* * 
	 * Imports a node from an array spec.
	 * 
	 * */
	public function import( $elem, $create_missing_deps = true )
	{
		if ( ! (is_string($elem) or is_array($elem))) return $this;
		if (is_string($elem) and strlen($elem)) {
			$pref = (string)$this->option('prefix');
			$ext = (string)$this->option('extension');
			$attrs = count($attrs = $this->option('attrs')) ? $attrs : NULL;
			if ($this->can_register($node = HTMLNode::factory($elem,true,$attrs,false,NULL,NULL,$pref,$ext)))
				$this->register( $node , $create_missing_deps);
		}
		elseif (is_array($elem)) {
			if (empty($elem)) return false;
			
			$name = NULL;
			if ( ! isset($elem['name'])) {
				foreach($elem as $k => $v) {
					if (is_int($k) and is_string($v) and strlen($v)) { $name = $v; unset($elem[$k]); break; }
				}
			} else $name = $elem['name'];
			if ( ! (is_string($name) and strlen($name))) return false;
			
			$src = NULL;
			if ( ! isset($elem['src'])) {
				foreach($elem as $k => $v) {
					if (is_int($k) and is_string($v)) { $src = $v; unset($elem[$k]); break; }
				}
			} else $src = $elem['src'];
			if ( ! is_string($src)) $src = true;
			
			$attrs = NULL;
			if ( ! isset($elem['attrs'])) {
				foreach($elem as $k => $v) {
					if (is_int($k) and is_array($v)) { $attrs = $v; unset($elem[$k]); break; }
				}
			} else $attrs = $elem['attrs'];
			if ( ! is_array($attrs)) $attrs = array();
			if (count($opt_attrs = (array)$this->option('attrs'))) $attrs = array_merge($opt_attrs,$attrs);
			
			$prio = NULL;
			if ( ! (isset($elem['prio']) or isset($elem['priority']))) {
				foreach($elem as $k => $v) {
					if (is_int($k) and (is_int($v) or ($v === false))) { $prio = $v; unset($elem[$k]); break; }
				}
			} else $prio = isset($elem['priority']) ? $elem['priority'] : $elem['prio']; 
			if ( ! is_int($prio)) $prio = false;
			
			$cons = NULL;
			if ( ! (isset($elem['cons']) or isset($elem['conflicts']))) {
				foreach($elem as $k => $v) {
					if (is_int($k) and is_array($v)) { $cons = $v; unset($elem[$k]); break; }
				}
			} else $cons = isset($elem['conflicts']) ? $elem['conflicts'] : $elem['cons']; 
			if ( ! is_array($cons)) $cons = NULL;
			
			$deps = NULL;
			if ( ! (isset($elem['deps']) or isset($elem['depends']))) {
				foreach($elem as $k => $v) {
					if (is_int($k) and is_array($v)) { $deps = $v; unset($elem[$k]); break; }
				}
			} else $deps = isset($elem['depends']) ? $elem['depends'] : $elem['deps']; 
			if ( ! is_array($deps)) $deps = NULL;
			
			$pref = (string)$this->option('prefix');
			$ext = (string)$this->option('extension');
			if ($this->can_register($node = HTMLNode::factory($name,$src,$attrs,$prio,$cons,$deps,$pref,$ext)));
				$this->register($node, $create_missing_deps);
		}
		return $this;
	}
	
	/* * 
	 * Countable implementation
	 * 
	 * */
	public function count() { return count($this->_nodes); }
	
}

