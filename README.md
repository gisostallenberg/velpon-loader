# Velpon loader

Velpon loader glues classes defined in a variable stack together.
See https://nl.wikipedia.org/wiki/Ceta-Bever for the origin of the name.

Please not that this package is able to *initialize* an arbitrary class stack for a variable defined stack. It is **not able to change** already initialized (constructed) classes.

This package is meant to be used in a BC manner for existing projects implementing this style of loading.
WARNING: It is strongly discouraged to use this for any new projects. Please use services, events and the like to be able to 'plug' into behavior.



## Installation
```bash
composer require gisostallenberg/velpon-loader
``` 

## Usage example
```php
use GisoStallenberg\VelponLoader\VelponLoader;

// Note that the stack order is reversed (ClassToPlug extends ClassToPlugPluginOne extends ClassToPlugPluginTwo)
VelponLoader::register([
    'plugin_two' => [
        'ClassToPlug' => 'ClassToPlugPluginTwo'
    ],
    'plugin_one' => [
        'ClassToPlug' => 'ClassToPlugPluginOne'
    ],
]);
```

Files:
```php
// ClassToPlug.php
class ClassToPlugPluggable {}

// ClassToPlugPluginOne.php
class ClassToPlugPluginOne extends ClassToPlugPluginOneVelpon {} 

// ClassToPlugPluginTwo.php
class ClassToPlugPluginTwo extends ClassToPlugPluginTwoVelpon {} 
```

## Todo
Protect abstract classes from constructing without a non abstract parent 
