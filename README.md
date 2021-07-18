# php-super-cache
Simple PHP cache mechanism which provides 200X Faster Caching than other PHP cache mechanisms like Redis/Memcache/APC in PHP &amp; HHVM. SuperCache uses the normal file system to store values. This method is faster than Redis/Memcache because all those serialize and unserialize objects.

[![GitHub tag](https://img.shields.io/github/tag/shabeer-ali-m/php-super-cache.svg?style=flat-square)](https://github.com/shabeer-ali-m/php-super-cache/releases)

## Installation
Via Composer
``` bash
composer require smart-php/super-cache
```

### Basic Usage

```php
<?php
require __DIR__.'/vendor/autoload.php';
use SuperCache\SuperCache as sCache;

//Saving cache value with a key
// sCache::cache('<key>')->set('<value>');
sCache::cache('myKey')->set('Key_value');

//Retrieving cache value with a key
echo sCache::cache('myKey')->get();
?>
```

### Cache Folder 
By default the cache will save in the `tmp` folder. Please make sure that the `tmp` folder has write access.
You can set a custom folder for the cache:
```php
sCache::setPath('youfolder/tempfolder/');
```
or
```php
define('SuperCache_PATH','youfolder/tempfolder/');
```


#### Advanced Options
##### Locking

Lock your data to readonly so that the data won't be overwritten.
```php
sCache::cache('myKey')->set('my_value')->lock();
//setting new value
sCache::cache('myKey')->set('new_value');
echo sCache::cache('myKey')->get(); //output : my_value
//unlocking
sCache::cache('myKey')->unlock()->set('new_value');
echo sCache::cache('myKey')->get(); //output : new_value
```

##### Options
```php
//options
sCache::cache('myKey')->set('my_value')->options([
    'expiry'    =>  time()+600, //time to expire
    'lock'      =>  true    //alternative method to lock or unlock
    'custom'    =>  'your customer attribute value'
]);

//isValid (To check for a valid key or to check whether it has expired or not)
sCache::cache('myKey')->isValid(); //true or false

//To get all option values
print_r(sCache::cache('myKey')->getOptions()); //array

//destroy
sCache::cache('myKey')->destroy();

//clearAll (Clear all cache values)
sCache::cache('myKey')->clearAll();
```

### Cache Your Class

You can cache any class to super cache. To bind data you need to use the trait class `State` by adding `use State`, or you can do it via your custom set_state.

```php
use SuperCache\SuperCache as sCache;
use SuperCache\State as State;

sCache::setPath('youCacheLocation');

Class MyClass 
{
    /**
     * This trait class will bind data from cache to your class
     */
    use State;

    private $firstName;

    private $lastName;

    public function setName($firstName, $lastName)
    {
        $this->firstName = $firstName;
        $this->lastName = $lastName;
    }

    public function getName()
    {
        return $this->firstName . " ". $this->lastName;
    }
}

// Creating Object of your class
$myObject = new MyClass;
$myObject->setName("John", "Doe");

// Saving your object in SuperCache
sCache::cache('myObject')->set($myObject);

// Retrieving your object from Cache 
$cacheObject = sCache::cache('myObject')->get();
echo $myObject->getName();
```

## License
The MIT License (MIT). Please see [License File](LICENSE) for more information.
