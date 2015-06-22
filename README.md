![Logo](https://cloud.githubusercontent.com/assets/4998038/7528096/370297ba-f51b-11e4-9c26-5d01ac66fc4d.png)

[![License](https://poser.pugx.org/alorel/alo-framework/license?format=plastic)](LICENSE) [![NuGet release]
(http://img.shields.io/nuget/v/AloFramework.svg?label=nuget&style=plastic)](https://www.nuget.org/packages/AloFramework/) [![Packagist release](https://img.shields.io/packagist/v/alorel/alo-framework.svg?style=plastic&label=packagist-stable)](https://packagist.org/packages/alorel/alo-framework) [![Packagist pre-release](https://img.shields.io/packagist/vpre/alorel/alo-framework.svg?style=plastic&label=packagist-dev)](https://packagist.org/packages/alorel/alo-framework) [![NuGET downloads](http://img.shields.io/nuget/dt/AloFramework.svg?label=nuget&style=plastic)](https://www.nuget.org/packages/AloFramework/) [![Packagist downloads](https://img.shields.io/packagist/dt/alorel/alo-framework.svg?style=plastic&label=packagist)](https://packagist.org/packages/alorel/alo-framework)


2.0: [![Release build status](https://travis-ci.org/Alorel/alo-framework.svg?branch=2.0)](https://travis-ci.org/Alorel/alo-framework)  [![Release code quality](https://cloud.githubusercontent.com/assets/4998038/8272585/378efc2a-1841-11e5-9fd7-7af369c8b40b.png)](https://insight.sensiolabs.com/projects/3a3aec8e-7593-47ed-a0ae-19f428c0e879) develop: [![Dev Build Status](https://travis-ci.org/Alorel/alo-framework.svg?branch=develop)](https://travis-ci.org/Alorel/alo-framework)  [![SensioLabsInsight](https://insight.sensiolabs.com/projects/3a3aec8e-7593-47ed-a0ae-19f428c0e879/mini.png)](https://insight.sensiolabs.com/projects/3a3aec8e-7593-47ed-a0ae-19f428c0e879) master: [![Mater Build Status](https://travis-ci.org/Alorel/alo-framework.svg?branch=master)](https://travis-ci.org/Alorel/alo-framework)

----------

# Table of Contents #

* [What is this?](#what-is-this)
* [Licence](#licence)
* [Structure](#structure)
	* [General](#general)
	* [Namespaces](#namespaces)
* [Workflow](#workflow)
	* [Main Concept](#main-concept)
	* [The global Alo class](#the-global-alo-class)
	* [Controllers](#controllers)
	* [Views](#views)
	* [Global Autoload](#global-autoload)
* [Routing](#routing)
* [Logging](#logging)
* [Session Management](#session-management)
* [Localisation](#localisation)
* [Initial setup](#initial-setup)
* [Updating](#updating)
* [Running tests](#running-tests)
* [Latest Changes](#latest-changes)
* [External Libraries](#external-libraries)
* [Supporting The Project](#supporting-the-project)
* [Other Alo Products](#other-alo-products)

----------

# What is this? #
AloFramework is an incredibly lightweight and flexible MVC framework for PHP 5.4+. It has a lot of built-in functionality including but not limited to:

* Memcached & Redis wrappers
* Database connection class
* MySQL,Redis or Memcached-based session managers
* HTML form validator
* Code testing suite
* Email sender
* Crontab editor
* Object-oriented cURL wrapper
* Object-oriented file manager
* Remote SFTP file manager
* Code profiler
* CLI tools
* Localisation tools
* File management utilities

^[TOC](#table-of-contents)

----------

# Licence #
This product is licenced under the [GNU General Public Licence Version 3](https://www.gnu.org/copyleft/gpl.html)

^[TOC](#table-of-contents)

----------

# Structure #
## General ##
You will find code documentation under the **docs** directory, some setup scripts under **setup** and source files under **src**.
In **src** the main components are **app*, **resources**, **sys** and files under the directory root.

* **sys** contains all the framework files
* **resources** is where you're meant to put all your images, css and whatnot
* **app** is where your code should go
* **.htaccess.sample** contains the contents for mod_rewrite: if you have access to your web server's file system copy these to the global config file. If not, rename it to **.htaccess**.
* **index.php** contains some core constants

^[TOC](#table-of-contents)

## Namespaces ##
The **class**, **trait** and **interface** directories found under *src/app* and *src/sys* follow a namespaced structure, e.g. the class **Alo\Db\MySQL** would be found in the file *class/alo/db/mysql.php*. Please not that **all directory and file names should be lowercase**.

^[TOC](#table-of-contents)

----------

# Workflow #
## Main concept ##
For most projects, you will want to write your own classes that extend those of the framework's. That way you will be completely safe from losing any code during a framework upgrade. The built-in autoloader will automatically load any required interfaces found in **app/class**, **app/interface**, **sys/class** and **sys/interface**.

^[TOC](#table-of-contents)

## The global Alo class ##
This class is always loaded by default and contains static references to objects which are *in most cases* used as singletons. You should try to load most of your classes into its static properties, e.g. you will usually only need one database connection, so you can assign it to Alo::$db and access it from anywhere in your code.

^[TOC](#table-of-contents)

## Controllers ##
All controllers must go under **app/controllers** (accessible via the **DIR_CONTROLLERS** constant), have the **Controller** namespace and extend the class **Alo\Controller\AbstractController**. To make this easier, you can write your own Abstract controller and extend that of Alo\ from within, for example:

**app/class/controller/abstractcontroller.php**
```
namespace Controller;

class AbstractController extends Alo\Controller\AbstractController {
   //Your code
}
```

**app/class/controller/home.php**
```
namespace Controller;

class Home extends AbstractController {
   //Your code
}
```

Only **public, non-abstract, non-static methods will be used for routing**. The default method for most controllers is **index()**. Controllers can be in any of **DIR_CONTROLLERS**' subdirectories.

^[TOC](#table-of-contents)

## Views ##
Any view can be loaded via **Alo\Controller\AbstractController**'s protected method **loadView**:
```
/**
  * Loads a view
  *
  * @author Art <a.molcanovas@gmail.com>
  * @param string  $name   The name of the view without ".php".
  * @param array   $params Associative array of parameters to pass on to the view
  * @param boolean $return If set to TRUE, will return the view, if FALSE,
  *                        will echo it
  * @return null|string
  */
  protected function loadView($name, $params = [], $return = false) {
     // Code
  }
```
This will load a view under **app/view/$name.php**. You can provide parameters to pass on to the view via **$params**, e.g. if you pass on **['foo' => 'bar']** and echo **$foo** in the view, the output will be **bar**. If instead of echoing the output you want to retrieve it, provide **$return** with **true**. Each view can be reused during the same execution.

^[TOC](#table-of-contents)

## Global Autoload ##
You can create **app/core/autoload.php** which will be loaded before your controller is initialised. Use this file for any global includes in your project.

^[TOC](#table-of-contents)

----------

# Routing #

All routing is done in the **router.php** config file. By default, if a route is not found, the router will look in your **controllers** directory's root for an automatically calculated route: **www.domain.com/controller/method/arg1/arg2[...]/argN**. This can be overwritten in the **$routes** array, where the array keys are case-insensitive regular expressions for the request URI (without delimiters or modifiers) and the values are configuration arrays containing the following keys (bold values mean the default values if the key is not set):

* dir => the controller subdirectory **[./]**
* class => which controller class to load **[$default_controller] or URL-based**
* method => which method to load **[index or URL-based]**
* args => array of arguments to pass on to the method. These can be hardcoded values or placeholders (*'\$1', '\$2'* etc) which will replace items in the regular expression (key). **[empty array]**

^[TOC](#table-of-contents)

----------

# Logging #
All logging is done via the global static class **\Log**'s public methods - please refer to the documentation. You will can set the logging level (during the [Initial Setup](#initial-setup) phase described below).

^[TOC](#table-of-contents)

----------

# Session Management #
There are several session management wrappers available, all of which implement the standard **SessionHandlerInterface**. To use a manager of your choosing (located under the **Alo\Session** namespace) simply call the corresponding manager's static **init()** method, e.g.:

```
use Alo\Session\MemcadhedSession;
use Alo\Session\RedisSession;
use Alo\Session\MySQLSession;

MemcachedSession::init(); // initialise Memcached session
RedisSession::init(); // or a Redis session
MySQLSession::init(); // or a MySQL session
```

The session managers are dependent on the **MySQL**, **RedisWrapper** and **MemcachedWrapper** respectively. These can be assigned to **Alo::$db** or **Alo::$cache** or passed on as a reference to the init() method. **There is no need to call session_start() as the handler does this for you**.

^[TOC](#table-of-contents)

----------

# Localisation #
Localisation is handles via **Alo\Locale**. After you've loaded the table (from the SQL file in the setup directory) call the fetch() method, specifying the page IDs to pull. If using **ALO_LOCALE_FETCH_ALL**, this parameter does not matter. After the fetch you can access your keys via **__get()**, i.e. to get the key 'foo' use **echo $localeInstance->foo**. 

^[TOC](#table-of-contents)

----------

# Initial setup #
* You will want to copy the contents of **sys/config** into **app/config**. Open them and set the values as appropriate.
* Open **index.php**, scroll to **// ===== General setup BEGIN =====** and set the values as you please.
* Next you'll want to run the appropriate files under **setup** if you are using that functionality.
* If you have access to your web server's file system, copy the contents of **.htaccess.sample** to the configuration file; if you don't, rename it to **.htaccess**

^[TOC](#table-of-contents)

----------

# Updating #
Updates are applied by following these 6 steps:

1. Look through the changelog for to see if any changes will cause issues (e.g. a deprecated method being removed), prepare your code if necessary.
2. Make a copy of your **index.php** file.
3. Delete the above, as well as the **sys** directory.
4. Extract the new code. It will never contain files other than blank **index.html**s and **sample.php**s under **app/**, so no application code will be overwritten.
5. If there are any changes to **.htaccess.sample**, merge them with your version.
6. Re-apply your personal settings under **index.php**'s **// ===== General setup BEGIN =====**

^[TOC](#table-of-contents)

----------

# Running tests #

When running PHPUnit tests be sure to use the **phpunit.php** bootstrap file from the root directory. It will make sure that all classes are loaded correctly and that the framework behaviour is altered to not interfere with the tests.
*Please note that in PHPUNIT mode the framework does not automatically initialise the Router - you will have to run the code below to test a controller:*
```
$router = new \Alo\Controller\Router();
$router->init(); //Or ->initNoCall() if you just want to initialise it, but not call the relevant controller
```

If testing sessions please use **phpunit --stderr** to the test status output does not cause header errors.

^[TOC](#table-of-contents)

----------

# Latest changes #
See [changelog.md](changelog.md) for a full changelog of previous versions.
## 2.1-alpha.1 (2015-06-22) ##

Moved global functions

* escapeHTML5() was removed. Use Security::unXss()
* **includeifexists()**, **includeonceifexists()** & **serverIsWindows()** have been removed and are now static 
methods of **Alo**.

Moved namespaces

* The **Alo\Statics** namespace has been removed. All its classes are now found in the **Alo** namespace.



^[TOC](#table-of-contents)

----------

# External Libraries #
AloFramework uses the following external libraries for its functionality:

* [PHPMailer](https://github.com/PHPMailer/PHPMailer/) for email support
* [Kint](http://raveren.github.io/kint/) for debug output

^[TOC](#table-of-contents)

# Supporting the Project #
Any support is greatly appreciated - whether you're able to [send a Paypal donation](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=ANUYFVBA2XS3G), [become a ClixSense referral](http://www.clixsense.com/?r=4639931&c=alo-wamp&s=102) or simply [drop an email](mailto:a.molcanovas@gmail.com) I'll be very grateful. :)

^[TOC](#table-of-contents)

# Other Alo Products #
You can find other products of mine at [alorel.weebly.com](http://alorel.weebly.com)

^[TOC](#table-of-contents)
