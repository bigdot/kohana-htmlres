# HTMLRes module


### Usage notes

A generalistic module for styles and scripts (or whatever) with dependency tracking and conflicts
resolving and a collector feature.

The module works by creating a collector where you add your scripts, and then, printing it (echoing).
Collectors have a name, and you can look at them as groups of nodes. The items added are called nodes.
This module has been created for styles and scripts but internally there is no diference between these two.
The difference is made by the rendering callback that is used.

The front-end class (helper) is HTMLRes.
The object front-end class is HTMLNodeCollector.

##### HTMLNodeCollector

Objects of this class have the role of aiding in adding an element to the registry easily.

The objects are ment to be created by the static front-end HTMLRes.

Getting or creating a collector:

```php
	$collector = HTMLRes::collector('my_colector');
```
<!---` -->

this will create and return a new collector invoking the factory, or return the existing one if it already exists.

You can pass options to the registry of the collector as a second parameter.
It is recommended to store the collector in the controller somewhere, although you can get
them at any time using  `` HTMLRes::collector(<name>); ``

```php
class Controller_Main extends Controller_Template {
	public $template='main';
	public function before()
	{
		// in the default Controller_Template this will create $this->template as a View object
		parent::before();
		// easy access to our collector
		$this->template->bind('scripts',$this->scripts);
		$this->scripts = HTMLRes::collector(
			'scripts',
			array(
				'prefix' => 'application/scripts/',
				'extension' => '.js',
				'render_node_callback' => array('HTMLRes','script_node_link')
			)
		);
		
		// see below for this one
		$this->_add_main_scripts_to($this->scripts);
	}
}
```
<!---` -->

this will set a new collector that will print script links when echoed using the conveniance
`` HTMLRes::script_node_link($node) `` static function, that can be used as a render function
for nodes (uses `` HTML::script() ``).


Afterwads you can start adding scripts to your collector, and there are a few ways
to do that (called forms from now on):

```php
protected function _add_main_scripts_to( $coll ) // scripts collector, see above
{
	// This would add jquery from CDN, the first argument of this form is the source
	// $coll->{'libs/jquery-1.7'}('http://code.jquery.com/jquery-latest.pack.js')
	
	
	$coll
	// This will create the src attribute from prefix + name + extension
	// resulting src="/application/scripts/libs/jquery-1.7.js"
	
	->{'libs/jquery-1.7'}()

	// In this context, where jquery is already loaded, we can add something that depends on it
	// [ i should probably do something about the versioning, but i don't see the point yet ]
	
	->{'libs/jqueryUI-1.8-custom.pack'}()
	
	// If no illegal characters you can pass the name as symbol (not expression as above)
	
	->my_script_1() // will add node with name "main_functions" and default parameters
	->my_script_2(); // same as above


	// You can set scripts as properties, where the value assigned, if it's not an array,
	// it will be passed as <code> array( $value ) </code> as the first argument to the collector
	// function, otherwise, if it's an array, it is passed as is;

	$coll->mys_script_3 = true;
		// same as $coll->my_script_3(true); // true is actually there by default, so it's unneeded
		// same as $coll->{'my_script_3'}();
		// same as $coll->___collect('my_script_3');
		// same as $coll->___collect_assoc(array('name'=>'my_script_3'));
		// same as $coll('my_script_3');
		// same as $coll->my_script_3 = array('my_script_3');
		// same as $coll->my_script_3 = array('name' => 'my_script_3');
			// this above is overrinding the name , 
				// you can actually do $coll->add = array('name'=>'my_script_3');
		// same as $coll['my_script_3'] = true;
		// same as $coll->{'my_script_3'} = true;
		// :)
	
	
	// If one is from another folder
	
	$coll->my_script_4 = array('prefix'=>'application/vendor/extra/scripts/');
	
	
	// since it implements ArrayAcccess, you can also use this method
	
	$coll['my_script_5'] = array('prefix'=>'application/vendor/extra/scripts/');


	// You can set options individually or bulk.
	// In case you use your server hosted scripts , and that is, the src parameter is set to true and
	// it is built form prefix + name + extension, you can load more than one script at
	// a time from a specific directory
	
	$coll['script1|script2|script3'] = array('prefix'=>'application/vendor/ads/scripts/');
	
	// this above, will explode the name by "|" and add each one with the parameters passed;
	// this feature is available in all forms where the language permits it ( all except symbol calls);
}
```
<!---` -->

Rendereing the collector return the result of the registry's `` __toString() `` (and that is `` $registry->render() ``)
In your view can do `` <?php echo $scripts_collector; ?> ``.

The class has some utility functions named in a way that it is very unlikely to
conflict with one of your node names, and that is with 3 underscores appended  ``___ ``.
These will be called "supermagic" methods or functions from now on these docs.


##### HTMLNodeReg (HTMLNodeRegistry)

The nodes registry.
It's meant to be used internally.

To get the list what should be displayed, the first thing to do is to get the nodes in order,
and that is done by `` ordered_list() `` function. Nodes are rendered using a `` render_node_callback ``,
that must be provided by the collector, the default just prints a html comment of the `` name `` and `` src ``
of the nodes. There are 2 conveniance functions in HTMLRes for styles and scripts. If ypu find another
usage for this module, i'll be happy to hear form you.

The rendering is done in the `` render() `` member method, which calls a `` render_registry_callback ``, which
in turn renders each node from the ordered list with the `` render_node_callback ``.

-------------------


### Why ?

Didn't found one + for fun


### Licence

LGPL 3


### End notes

Use it, fork it, be happy :) !

