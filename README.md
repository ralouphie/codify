Codify PHP
==========

[![Build Status](https://travis-ci.org/ralouphie/codify.svg?branch=master)](https://travis-ci.org/ralouphie/codify)
[![Coverage Status](https://coveralls.io/repos/ralouphie/codify/badge.png?branch=master)](https://coveralls.io/r/ralouphie/codify?branch=master)
[![Latest Stable Version](https://poser.pugx.org/ralouphie/codify/v/stable.png)](https://packagist.org/packages/ralouphie/codify)
[![Latest Unstable Version](https://poser.pugx.org/ralouphie/codify/v/unstable.png)](https://packagist.org/packages/ralouphie/codify)
[![License](https://poser.pugx.org/ralouphie/codify/license.png)](https://packagist.org/packages/ralouphie/codify)

A simple PHP package for compiling and autoloading generated PHP files. It's useful when writing frameworks that generate and save code on-the-fly.

## Usage

```php
<?php

// Create a code store.
$store = new \Codify\Stores\Filesystem(
    'Some\\Name\\Space',
    'directory/to/save/code'
);

// Hook up code store to an autoloader and register it.
$autoloader = new \Codify\Autoloader($store);
$autoloader->register();

// Now you can save classes.
$class = 'Some\\Name\\Space\\Foo';
$store->save($class, 'namespace Some\\Name\\Space;
    class Foo {
        public function foo() { echo "foo"; }
    }
');

// And use the classes you save.
$instance = new $class;
$instance->foo(); // Outputs "foo".
```

## Dynamic Compilation

```php
<?php

$store = new \Codify\Stores\Filesystem(/* ... */);

// Create a compiled store that will generate code for missing classes
// under the code store's namespace.
$compiled_store = new CompiledStore($store, function ($class) {

    // Generate code.
    return $code_generated;
});

$autoloader = new \Codify\Autoloader($compiled_store);
$autoloader->register();


$missing_class = 'Some\\Name\\Space\\Bar';

// This will trigger the compiled store above.
$instance = new $missing_class;

// If the compile was successful, we can now use the object.
$instance->doSomething();

// To avoid fatal errors (compile skipped), 
// you can check if the class exists first.
if (class_exists($missing_class)) {
    // Compile was successful!
} else {
    // The compile was skipped.
}

```
