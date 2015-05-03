[![Latest Stable Version](https://poser.pugx.org/alorel/alo-framework/v/stable)](https://packagist.org/packages/alorel/alo-framework) [![Total Downloads](https://poser.pugx.org/alorel/alo-framework/downloads)](https://packagist.org/packages/alorel/alo-framework) [![Latest Unstable Version](https://poser.pugx.org/alorel/alo-framework/v/unstable)](https://packagist.org/packages/alorel/alo-framework) [![License](https://poser.pugx.org/alorel/alo-framework/license)](https://packagist.org/packages/alorel/alo-framework) [![Docs](https://readthedocs.org/projects/alo-framework/badge/?version=latest)](https://alo-framework.readthedocs.org)

----------

# Table of Contents #
1. [What is this?](#what-is-this)
2. [Licence](#licence)
3. [Structure](#structure) [[General](#general) | [Namespaces](#namespaces)] 
4. [Workflow](#workflow) [[Main Concept](#main-concept) | [The global Alo class](#the-global-alo-class) [Controllers](#controllers) | [Views](#views)]
5. [Logging](#logging)
6. [Initial setup](#initial-setup)
7. [Updating](#updating)
8. [Latest Changes](#latest-changes)
9. [External Libraries](#external-libraries)

----------

# What is this? #
AloFramework is an incredibly lightweight and flexible MVC framework for PHP 5.3+. It has a lot of built-in functionality including but not limited to:

* Memcached wrapper
* Database connection class
* MySQL or Memcached-based session manager
* HTML form validator
* Code testing suite
* Email sender
* Crontab editor
* Object-oriented cURL wrapper
* Object-oriented file manager
* Remote SFTP file manager
* Code testing suite
* Code profiler

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
* **.htaccess** makes sure requests get routed correctly
* **index.php** contains some core constants

^[TOC](#table-of-contents)

## Namespaces ##
The **class**, **trait** and **interface** directories found under *src/app* and *src/sys* follow a namespaced structure, e.g. the class **Alo\Db\MySQL** would be found in the file *class/alo/db/mysql.php*. Please not that **all directory and file names should be lowercase*.

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
All controllers must go under **app/class/controller**, have the **Controller** namespace and extend the class **Alo\Controller\AbstractController**. To make this easier, you can write your own Abstract controller and extend that of Alo\ from within, for example:

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

Only **public, non-abstract, non-static methods will be used for routing**. The default method for most controllers is **index()**;

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

----------

# Logging #
All logging is done via the global static class **\Log**'s public methods - please refer to the documentation. You will can set the logging level (during the [Initial Setup](#initial-setup) phase described below).

^[TOC](#table-of-contents)

----------

# Initial setup #
* You will want to copy the contents of **sys/config** into **app/config**. Open them and set the values as appropriate. Next, open **index.php**, scroll to **// ===== General setup BEGIN =====** and set the values as you please.
* Next you'll want to run the appropriate files under **setup** if you are using that functionality.

^[TOC](#table-of-contents)

----------

# Updating #
Updates are applied by following these 6 steps:

1. Look through the changelog for to see if any changes will cause issues (e.g. a deprecated method being removed), prepare your code if necessary.
2. Make a copy of your **index.php** and **.htaccess** files.
3. Delete the above, as well as the **sys** directory.
4. Extract the new code. It will never contain files other than blank **index.html**s and **sample.php**s under **app/**, so no application code will be overwritten.
5. If there are any changes to **.htaccess**, merge them with your version.
6. Re-apply your personal settings under **index.php**'s **// ===== General setup BEGIN =====**

^[TOC](#table-of-contents)

----------

# Latest changes #
See [changelog.md](changelog.md) for a full changelog of previous versions.
## 0.2 (pending) ##

* Trait support added - see app/traits
* Kint external library updated
* Slashes in paths replaced with DIRECTORY_SEPARATOR
* Profiling class added
* The Router now has a lot more getters
* Error, autoloading and exception handlers moved to \Alo\Handler
* Committed function tester (forgot about it earlier)
* Fixed an error where any uncaught exception would force the 404 error page.

^[TOC](#table-of-contents)

----------

# External Libraries #
AloFramework uses the following external libraries for its functionality:

* [PHPMailer](https://github.com/PHPMailer/PHPMailer/) for email support
* [Kint](http://raveren.github.io/kint/) for debug output

^[TOC](#table-of-contents)