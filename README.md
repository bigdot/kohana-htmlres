# HTMLRes module

A generalistic module for styles and scripts (or whatever) with dependency tracking and conflicts
resolving and a collector feature.

The module works by creating a collector where you add your scripts, and then, printing it (echoing).
Collectors have a name, and you can look at them as groups of nodes. The items added are called nodes.
This module has been created for styles and scripts but internally there is no diference between these two.
The difference is made by the rendering callback that is used.

The front-end class (helper) is HTMLRes.
The object front-end class is HTMLNodeCollector.

## HTMLNodeCollector

Objects of this class have the role of aiding in adding an element to the registry easily.

The objects are ment to be created by the static front-end HTMLRes.

Getting or creating a collector:

```PHP
$collector = HTMLRes::collector('my_colector');
```
`

this will create and return a new collector invoking the factory, or return the existing one if it already exists.

You can pass options to the registry of the collector as a second parameter.
It is recommended to store the collector in the controller somewhere,
although you can get them at any time using HTMLRes::collector(<name>);
<code>
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
</code>
this will set a new collector that will print script links when echoed using
the conveniance HTMLRes::script_node_link($node) static function, that can be
used as a render function for nodes (unses HTML::script()).
[Note: For more on rendering nodes and registries , see HTMLRes_HTMLNodeReg class]

Afterwads you can start adding scripts to your collector, and there are a few ways
to do that (called forms from now on):
[Note: further examples are in the context of the above controller class definition]
<code>
protected function _add_main_scripts_to( $coll ) // scripts collector, no reference needed
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

->main_functions() // will add node with name "main_functions" and default parameters
->forms(); // same as above


// You can set scripts as properties, where the value assigned, if it's not an array,
// it will be passed as <code> array( $value ) </code> as the first argument to the collector
// function, otherwise, if it's an array, it is passed as is;

$coll->ajax = true;
// same as $coll->ajax(true); // true is actually there by default, so it's unneeded
// same as $coll->{'ajax'}();
// same as $coll->___collect('ajax');
// same as $coll->___collect_assoc(array('name'=>'ajax'));
// same as $coll('ajax');
// same as $coll->ajax = array('ajax');
// same as $coll->ajax = array('name' => 'ajax');
// this above is overrinding the name , you can actually do $coll->add = array('name'=>'ajax');
// same as $coll['ajax'] = true;
// same as $coll->{'ajax'} = true;
// :)


// If one is from another folder

$coll->response_decoder = array('prefix'=>'application/vendor/extra/scripts/');


// since it implements ArrayAcccess, you can also use this method

$coll['notices'] = array('prefix'=>'application/vendor/extra/scripts/');


// You can set options individually or bulk.
// In case you use your server hosted scripts , and that is, the src parameter is set to true and
// it is built form prefix + name + extension, you can load more than one script at
// a time from a specific directory

$coll['main|widgets|animations'] = array('prefix'=>'application/vendor/ads/scripts/');

// this above, will explode the name by "|" and add each one with the parameters passed;
// this feature is available in all forms where the language permits it ( all except symbol calls);
}
</code>

Rendereing the collector return the result of the registry's __toString
(and that is $registry->render())
In your view can do <code> <?php echo $scripts; ?> </code>

The class has some utility functions named in a way that it is very unlikely to
conflict with one of your node names, and that is with 3 underscores appended "___".
These will be called "supermagic" methods or functions from now on in this doc.


