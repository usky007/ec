/**
 * ZXC class and namespace utilities
 *
 * This file defines a single global symbol named "ZXC".
 * ZXC refers to a namespace object, and all utility functions
 * are stored as properties of this namespace.
 */

// define undefined
var undefined;

// Make sure we haven't already been loaded
var ZXC;
if (ZXC && (typeof ZXC != "object" || ZXC.NAME))
    throw new Error("Namespace 'ZXC' already exists");

ZXC = {};

// This is some metainformation about this namespace
ZXC.NAME = "ZXC";    // The name of this namespace

// Now start adding symbols to the namespace
ZXC.globalNS = this;  		// So we can always refer to the global scope
ZXC.NSs = { "ZXC": ZXC }; // Module name->namespace map.
ZXC.Classes = {};				// Class  name->class map.
ZXC.AsPrototype = {};

// Core function toString() implementation
ZXC.CoreFunction = function (name) {
  return new Function("return 'function " + name + "() {\\n  [core code]\\n}'");
}

/**
 * Use this function to ensure namespace object created and valid.
 *
 * This function checks whether namespace exists and creates if not.
 * It also does useful error checking to ensure that the
 * name does not conflict with any previously loaded module. It
 * throws an error if the namespace already exists and version dismatchs
 * if specified.
 */
ZXC.Namespace = function(name) {
    // Check name for validity.  It must exist, and must not begin or
    // end with a period or contain two periods in a row.
    if (!name) throw new Error("ZXC.Namespace( ): name required");
    if (name.charAt(0) == '.' ||
        name.charAt(name.length-1) == '.' ||
        name.indexOf("..") != -1)
        throw new Error("ZXC.Namespace( ): illegal name: " + name);

    // Check if namespace with the same name is already defined
    if (name in ZXC.NSs) {
    	return ZXC.NSs[name];
    }

	// Break the name at periods and create the object hierarchy we need
    var parts = name.split('.');

    // For each namespace component, either create an object or ensure that
    // an object by that name already exists.
    var container = ZXC.globalNS;
    for (var i = 0; i < parts.length; i++) {
        var part = parts[i];
        // If there is no property of container with this name, create
        // an empty object.
        if (!container[part]) container[part] = {};
        else if (typeof container[part] != "object") {
            // If there is already a property, make sure it is an object
            var n = parts.slice(0,i).join('.');
            throw new Error(n + " already exists and is not a valid namespace");
        }
        container = container[part];
    }

    // The last container traversed above is the namespace we need.
    var namespace = container;

    // It is an error to define a namespace twice. It is okay if our
    // namespace object already exists, but it must not already have a
    // NAME property defined, which is unlikely to happen and the only
    // possibility of it is that something else defined it.
    if (namespace.NAME)
    	throw new Error("Unrecognized namespace "+name+" is already defined");

    // Initialize name field and export utility of the namespace,
    namespace.NAME = name;
    namespace.Export = ZXC.Export;

    // Register this namespace in the map of all namespaces
    ZXC.NSs[name] = namespace;

    // Return the namespace object to the caller
    return namespace;
}
ZXC.Require = function(namespace) {
	if (!namespace)
		throw new Error("ZXC.Require( ): argument invalid")
	else if (namespace.constructor == String &&
		!ZXC.NSs[namespace] && !ZXC.Classes[namespace])
		throw new Error("ZXC.Require( ): " + namespace + " is required");
}
ZXC.Defined = function(namespace) {
	if (!namespace)
		return false;
	else if (namespace.constructor == String &&
		!ZXC.NSs[namespace] && !ZXC.Classes[namespace])
		return false;
	return true;
}
/**
 * This function helps namespaces to declare export symbols
 * that may accessible to import function.
 */
ZXC.Export = function(classes) {
	// EXPORT not exist? new one.
	if (!this.EXPORT)
		this.EXPORT = new Array();

	// add classes to export list.
	if (classes.constructor == String)
		this.EXPORT.push(classes);
	else
		for (var i = 0; i < classes.length; i++)
			this.EXPORT.push(classes[i]);

	// if classes passed as argument list, add to export list.
	if (arguments.length > 1) {
		for (var i = 1; i < arguments.length; i++)
			this.EXPORT.push(arguments[i]);
    }
}

/**
 * This function imports symbols from a specified namespace. By default, it
 * imports them into the global namespace, but you may specify a different
 * destination as the second argument.
 *
 * If no symbol are explicitly specified, the symbols in the EXPORT
 * array of the module will be imported.
 *
 * To import an explicitly specified symbol, pass its fullname as string
 * as first argument instead of its namespace, symbols will be imported
 * only if they are listed in EXPORT arrays.
 */
ZXC.Import = function(from, to) {
    // Make sure that the namespace is correctly specified. We expect the
    // namespace object but string is acceptable, specially for single
    // symbol import.
    var ns = from;
    var symbol = null;
    if (typeof from == "string")
    {
     	ns = ZXC.NSs[from];
     	if (!ns) {
     		// may be a string that specified a symbol, try it
     		var delimiterIdx = from.lastIndexOf(".");
     		if (delimiterIdx < 0)
     			throw new Error("ZXC.Import( ): illegal symbol name: " + from);
     		symbol = from.substring(delimiterIdx + 1);
     		ns = ZXC.NSs[from.substring(0, delimiterIdx)];
     	}
    }
    if (!ns || typeof ns != "object")
        throw new Error("ZXC.Import( ): illegal namespace");

    // The source namespace may be followed by an optional destination namespace;
    if (!to)
    	to = ZXC.globalNS; // Default destination

	// If no EXPORT array defined, abort.
	if (!ns.EXPORT)
		return null;

	if (!symbol) {
    	for(var i = 0; i < from.EXPORT.length; i++) {
            to[from.EXPORT[i]] = from[from.EXPORT[i]];
        }
        return null;
    }
    else if (!(symbol in ns)) {
    	// Make sure symbol exists
        throw new Error("ZXC.Import( ): symbol " + s + " is not defined");
   	}
   	else {
    	// We have an explicitly specified symbol to import
    	for(var i = 0; i < ns.EXPORT.length; i++)
        	if (symbol == ns.EXPORT[i])
        	{
        		to[symbol] = ns[symbol];
        		return ns[symbol];
        	}
    }
};

/**
 * Class( ) -- a utility function for defining JavaScript classes.
 *
 * This function expects a single object as its only argument.  It defines
 * a new JavaScript class based on the data in that object and returns the
 * constructor function of the new class.  This function handles the repetitive
 * tasks of defining classes: setting up the prototype object for correct
 * inheritance, copying methods from other types, and so on.
 *
 * The object passed as an argument should have some or all of the
 * following properties:
 *
 *      name: The name of the class being defined.
 *            If specified, this value will be stored in the classname
 *            property of the prototype object.
 *
 *    extend: The constructor of the class to be extended. If omitted,
 *            the Object( ) constructor will be used. This value will
 *            be stored in the superclass property of the prototype object.
 *
 * construct: The constructor function for the class. If omitted, a new
 *            empty function will be used. This value becomes the return
 *            value of the function, and is also stored in the constructor
 *            property of the prototype object.
 *
 *   methods: An object that specifies the instance methods (and other shared
 *            properties) for the class. The properties of this object are
 *            copied into the prototype object of the class. If omitted,
 *            an empty object is used instead. Properties named
 *            "classname", "superclass", and "constructor" are reserved
 *            and should not be used in this object.
 *
 *   statics: An object that specifies the static methods (and other static
 *            properties) for the class. The properties of this object become
 *            properties of the constructor function. If omitted, an empty
 *            object is used instead.
 *
 *   borrows: A constructor function or array of constructor functions.
 *            The instance methods of each of the specified classes are copied
 *            into the prototype object of this new class so that the
 *            new class borrows the methods of each specified class.
 *            Constructors are processed in the order they are specified,
 *            so the methods of a class listed at the end of the array may
 *            overwrite the methods of those specified earlier. Note that
 *            borrowed methods are stored in the prototype object before
 *            the properties of the methods object above. Therefore,
 *            methods specified in the methods object can overwrite borrowed
 *            methods. If this property is not specified, no methods are
 *            borrowed.
 *
 *  provides: A constructor function or array of constructor functions.
 *            After the prototype object is fully initialized, this function
 *            verifies that the prototype includes methods whose names and
 *            number of arguments match the instance methods defined by each
 *            of these classes. No methods are copied; this is simply an
 *            assertion that this class "provides" the functionality of the
 *            specified classes. If the assertion fails, this method will
 *            throw an exception. If no exception is thrown, any
 *            instance of the new class can also be considered (using "duck
 *            typing") to be an instance of these other types.  If this
 *            property is not specified, no such verification is performed.
 **/
ZXC.Class = function(data) {
	// Check redefinition
	if (ZXC.Classes[data.name])
		return ZXC.Classes[data.name];
    // Extract the fields we'll use from the argument object.
    // Set up default values.
    
    var classname = data.name;
    var superclass = data.extend || Object;
    var constructor = function(_P) {
    	// Implement a default constructor only for prototype use.
    	if (data.construct && _P !== ZXC.AsPrototype) {
    		data.construct.apply(this, arguments);
    	}
    }
    var methods = data.methods || {};
    var statics = data.statics || {};
    var borrows;
    var provides;

    // TODO:normalize constructor

    // Borrows may be a single constructor or an array of them.
    if (!data.borrows) borrows = [];
    else if (data.borrows instanceof Array) borrows = data.borrows;
    else borrows = [ data.borrows ];

    // Ditto for the provides property.
    if (!data.provides) provides = [];
    else if (data.provides instanceof Array) provides = data.provides;
    else provides = [ data.provides ];

    // Create the object that will become the prototype for our class.
    var proto = data.extend ? new superclass(ZXC.AsPrototype) : new Object();
    
    // Delete any noninherited properties of this new prototype object.
    for(var p in proto)
        if (proto.hasOwnProperty(p)) delete proto[p];

    // Borrow methods from "mixin" classes by copying to our prototype.
    for(var i = 0; i < borrows.length; i++) {
        var c = borrows[i];
        // Copy method properties from prototype of c to our prototype
        for(var p in c.prototype) {
            if (typeof c.prototype[p] != "function") continue;
            proto[p] = c.prototype[p];
        }
    }
    // set resource if available
    if (ZXC.Resource) {
    	constructor.resource = function(code, lang) {
    		if (lang)
    			return ZXC.Resource.getResource().langEntry(lang, constructor.classname, code);
    		else
    			return ZXC.Resource.getResource().entry(constructor.classname, code);
    	};
    	proto.resource = constructor.resource;
    }

    // Copy instance methods to the prototype object
    // This may overwrite methods of the mixin classes
    for(var p in methods) proto[p] = methods[p];

    // Set up the reserved "constructor", "superclass", and "classname"
    // properties of the prototype.
    proto.constructor = constructor;
    proto.superclass = superclass;
    // classname is set only if a name was actually specified.
    if (classname) {
    	constructor.classname = classname;
    	proto.classname = classname;
    	var delimiterIdx = classname.lastIndexOf(".");
     	if (delimiterIdx < 0)
     		proto[classname] = constructor;
     	else
     		proto[classname.substring(delimiterIdx + 1)] = constructor;
    }

    // Verify that our prototype provides all of the methods it is supposed to.
    for(var i = 0; i < provides.length; i++) {  // for each class
        var c = provides[i];
        for(var p in c.prototype) {   // for each property
            if (typeof c.prototype[p] != "function") continue;  // methods only
            if (p == "constructor" || p == "superclass") continue;
            // Check that we have a method with the same name and that
            // it has the same number of declared arguments.  If so, move on
            if (p in proto &&
                typeof proto[p] == "function" &&
                proto[p].length == c.prototype[p].length) continue;
            // Otherwise, throw an exception
            throw new Error("Class " + classname + " does not provide method "+
                            c.classname + "." + p);
        }
    }

    // Associate the prototype object with the constructor function
    constructor.prototype = proto;

    // Copy static properties to the constructor
    for(var p in statics) constructor[p] = data.statics[p];

	// Register this class in the map of all namespaces
    ZXC.Classes[classname] = constructor;
    eval(classname + " = constructor;");

    // Finally, return the constructor function
    return constructor;
};
/**
 * Callback( ) -- a utility class for defining JavaScript callback functions.
 *
 * Constructor:
 * 1 function(String)
 * 2 function(Function)
 * 3 function(Object, String)
 * 4 function(Object, Function)
 *
 * Create:
 * var cb = ZXC.Callback(args); // or
 * var cb = new ZXC.Callback(args)
 *
 * Call:
 * cb(args);
 */
ZXC.Callback = function(obj, func) {
	// Allow instantiation without the 'new' keyword
	if (!(this instanceof ZXC.Callback))
		return new ZXC.Callback(obj, func);

	this.obj = func !== undefined ? obj : null;
	this.func = func || obj;
	this.type = 0;
	if (this.func)
		this.type += 1;
	if (this.func instanceof Function)
		this.type += 1;
	if (this.obj instanceof Object)
		this.type += 2;
	else
		this.obj = null;
	// return a function and preserve reference to Callback obj for debug and gc.
	var callback = this;
	var func = function() {
		return callback.invoke(arguments);
	};
	func.constructor = this.constructor;
    func.toString = ZXC.CoreFunction("ZXC.Callback");
	return func;
};
ZXC.Callback.prototype.invoke = function(params) {
	var func = this.func;
	switch (this.type) {
	case 1:
		eval ("func = " + func);
		if (!(func instanceof Function))
			return;
		break;
	case 2: break;
	case 3:
		if (this.obj[func] instanceof Function)
			func = this.obj[func];
		else return;
		break;
	case 4: break;
	default:
		return;
	}
	return func.apply(this.obj, params);
};

/**
 * Event( ) -- a utility class for defining JavaScript multicast event.
 *
 * Original idea come from Qomolangma OpenProject, thanks to Aimingoo(aim@263.net).
 *
 * Constructor:
 * function([callback1[, callback2[...]]]);
 *
 * Create:
 * var ev = ZXC.Event(args); // or
 * var ev = new ZXC.Event(args);
 *
 * Call:
 * ev(args);
 *
 * Callback implementation notes:
 * If callbacks return any value. last value will returns.
 * Return (new) ZXC.Event.preventDefault() to skip early added callbacks.
 */
ZXC.Event = function() {
  var name = ZXC.CoreFunction("ZXC.Event");
  var funcs = ['add', 'clear', 'close'];
  var GetHandle = {};

  var all = {
    length : 0,
    search : function(ME) {
      var i = ME(GetHandle), me = all[i];
      if (me && me.event==ME) return me;
    }
  };

  function add(foo) {
    var e = all.search(this);
    if (e && foo) e.push(foo);
  }

  function clear() {
    var e = all.search(this);
    if (e) while (e.length>0) delete e[--e.length];
  }

  function close() {
    var e = all.search(this);
    if (e) {
      for (var i=0; i<funcs.length; i++) delete this[funcs[i]];
      delete e.event;
    }
  }

  function run(handle, args) {
  	// reversely call handlers
  	var e = all[handle], v, v2;
    for (var i=e.length - 1; i >= 0 ; i--) {
      if ((v2 = e[i].apply(this, args)) !== undefined) {
        if (v2 instanceof ZXC.Event.preventDefault) {
          if (v2.result !== undefined) v = v2.result;
          break;
        }
        v = v2;
      }
    }
    //last valid result, or undefined.
    return v;
  }

  function _Event() {
  	if (!(this instanceof _Event)) {
  		var e = new _Event();
  		for (i=0; i<arguments.length; i++)
  			arguments[i] && e.add(arguments[i]);
  		return e;
  	}

    // get a handle and init MuEvent Object
    var handle = all.length++;
    var ME = function(_E) {
      if (_E==GetHandle) return handle;
      if (all[handle].length > 0) return run.call(this, handle, arguments)
    }
    ME.constructor = _Event;
    ME.toString = name;
    all[handle] = this;

    // "this" is the new obj instance
    this.event = ME;

    var f, i = 0;
    while (f = funcs[i++]) ME[f] = eval(f);       // public Event Methods, avoid creating new method instance.
    for (i = 0; i < arguments.length; i++)
    	arguments[i] && ME.add(arguments[i]);  // init event cast list

    return ME;
  }

  // hide implement for funcs[]
  for (var f, i=0; i<funcs.length; i++) {
    eval(f=funcs[i]).toString = ZXC.CoreFunction("ZXC.Event." + f);
  }

  _Event.toString = name;
  _Event.prototype.length = 0;
  _Event.prototype.push = function(foo) {
    this[this.length++] = foo;
  };
  return _Event;
}();
ZXC.Event.preventDefault = function(val) {
	var cls = ZXC.Event.preventDefault;
	if (this instanceof cls) {
		this.result = val;
	}
	else {
		return new cls(val);
	}
};
