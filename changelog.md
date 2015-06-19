# 2.0-beta (pending) #
**Added features**

* Localisation support added! See [README.md](README.md#Localisation)
* LOG_LEVEL_WARNING is now defined in index.php and is the default logging level. Log::warning() method introduced.

**Bugs fixed**

* MemcachedWrapper->getAll() now returns correct results when running the Windows version of Memcache
* AbstractDB can now reuse Alo::$cache instead of instantiating a new class

**PSR-1 standards-compliant renames**

Code review required:

* Global functions
   * server_is_windows() --> serverIsWindows()
   * timestamp_precise() --> timestampPrecise()
   * lite_debug() --> debugLite()
   * escape_html5() --> escapeHTML()
* AbstractController
   * http_error() renamed to httpError()
* Router
   * Gettable variables renamed in camelCase (applies to getters too)
   * is_cli_request() --> isCliRequest()
   * is_ajax_request() --> isAjaxRequest()
   * Config file
      * $error_controller_class --> $errorControllerClass
      * $default_controller --> $defaultController
* Alo
   * $form_validator --> $formValidator
* Log
   * log_level() --> logLevel()
* IO
   * echo_lines() --> echoLines()
   * open_file_default() --> openFileDefault()
* AbstractCache
   * is_available() --> isAvailable()
* Format
   * is_ipv4_ip() --> isIpv4()
* Email
   * is_email() --> isEmailAddress()
* Security
   * un_xss() --> unXss()
   * ascii_rand() --> asciiRand()
* File
   * convert_size() --> convertSize()
   * get_extension() --> getExtensionStatically()
* Curl
   * setopt_array() --> setoptArray()
* Profiler
   * diff_on_key() --> diffOnKey()

No code review required:

* Static constructors are now in camelCase - no implications as of PHP 5.6.9

**Config constants**

* ADDED | ALO_SESSION_SECURE: Determines whether the session cookie should only be sent via SSL.
* ADDED | ALO_MYSQL_CHARSET: Determines the connection charset.
* ADDED | ALO_SESSION_HANDLER: Determines which session handler will be used

**Globals**

* Global shorthands added for trigger_error():
	* phpError($msg)
	* phpWarning($msg)
	* phpNotice($msg)
	* phpDeprecated($msg)
	
**Major functionality changes**

* Sessions
   * SQLSession, Alo::$session & Alo::loadSession() have been removed
   * Sessions are now invoked via the handler's static init() method, e.g. to initialise a MySQL session you would call MySQLSession::init(). Any dependencies' instances, e.g. MySQL or RedisWrapper can be passed as a parameter or used from Alo::$db/Alo::$cache respectively
   * The session handler to be used will is now defined in config/session.php with ALO_SESSION_HANDLER
   * Sessions will now be used in the standard PHP way of $_SESSION. There is no need to do session_start(), only ::init().
	
**Removed deprecated items**

* Alo\File
* Testing suite
* FileException
	
**Misc Functionality/feature changes**

* PDO now uses ERRMODE_EXCEPTION instead of ERRMODE_WARNING
* Most classes now have self::$this so you can globally reference their last instances - useful for singletons.
* SampleErrorController->error()'s $message parameter removed as it was unused
* AbstractController->httpError() no longer has a die() statement to stop script execution once called. It only suppresses output now.
* MemcachedSession, RedisSession and MySQLSession constructors now throw a LibraryException instead.

**Other/Minor**

* A plethora of code quality improvements with the help of SensioLabs Insights
* Sample .htaccess file renamed to .htaccess.sample

# 1.1.1 (2015-05-31) #
**Misc**

* You can now include a global autoload file in app/core/autoload.php (this file is not created by default). It will be included before your controller is instantiated, so you can put any global project variables here.

**Added global functions**

* includeifexists(): performs an include() operation only if a file exists to avoid E_NOTICE errors
* includeonceifexists(): as above, but with include_once()

# 1.1 (2015-05-30) #
**Gracefully deprecated**

* \Alo\File has been moved to \Alo\FileSystem\File. The original class is now deprecated, but extends the new one.
* FileException is now deprecated. Use FileSystemException.

**Deprecated**

* Testing suite deprecated. Tools like [PHPUnit](https://phpunit.de/) serve as a far better alternative to test your code.

**Email**

* Some of PHPMailer's methods were overridden to return $this instead of bool
* attachContent() method added to attach content as opposed to a file from disc
* getDebugOutput() added. Sending an email now adds output to an array which can be fetched

**Classes added**

* Alo\IO\Downloader: downloads an external resource to disc and echoes progress
* Alo\Windows\Service: Windows service manager
* Alo\Cahce\RedisWrapper
* Alo\Session\RedisSession

**Exception changes**

* OSException added
* Cron now throws an OSException with the code OSException::E_UNSUPPORTED if instantiated from Windows

**Misc**

* Many classes now offer static constructors
* Deprecated classes no longer have a static in Alo
* Default session prefix changed

# 1.0 (2015-05-08) #

**Reworked**

* Router fully reworked. Refer to README for documentation on the new router.

**Major bugfixes**

* Fixed the error message (and subsequent die() statement) when the log level is set to LOG_LEVEL_DEBUG.

**Misc bugfixes**

* Changed the event definition in setup/create_session_table_and_cleaner.sql to use the correct table name
* Debug output in MemcachedWrapper->getAllMemcache() removed
* Logger & error handler paths fixed for PHPUNIT
* Cookie clease tweaked for PHPUNIT
* Fixed exception throwing if-else order in MemcachedSession. It now makes sure the MemcachedWrapper class is loaded and all its static properties are loaded before calling is_available()
* Fixed multiple validation errors in the testing suite
* Crontab should now correctly reload if it fetches an empty string
* Fixed code line detection in log messages

**Added features**

* It's now possible to force a session write operation during runtime via AbstractSession->forceWrite()
* clear(), getTokenExpected() and getTokenActual() and refreshToken() added to AbstractSession
* clearCrontab() method at crontab editor
* Possibility to automatically commit crontab changes. Use with caution!
* getAtIndex() method in Cron
* \Alo\Statics\Security class added
* __isset() and __unset() added to all classes that had __get() or __set() defined
* \Alo\Statics\Format::is_ipv4_ip() method added

**Removed items**

* Format::isBoolean()

**Miscellaneous**

* Switched to ApiGen as the code documentation provider
* SQLSession now uses the ALO_SESSION_TABLE_NAME instead of self::TABLE_NAME
* json encode/decode removed from MemcachedSession write/fetch()
* FormValidator documentation edit
* Cron editor now throws an exception if you try to invoke it on a Windows machine
* PHPUNIT_RUNNING constant definition moved out of source code. It now simply checks if the constant is defined in the bootstrap file.
* The global functions getFingerprint(), getUniqid(), escape() moved to \Alo\Statics\Security. The escape() function has been renamed to un_xss() in the class.
* DIR_CONTROLLERS constant added

# 0.2.1 (2015-05-05) #
**Major bugfixes**

* Fixed error where config files wouldn't be loaded correctly
* Fixed errors with the legacy Memcache getAll() alternative

**Misc bugfixes**

* Fixed router so it doesn't mistake an in-app ReflectionException with one that's caused by trying to initialise an invalid controller/method
* Added chdir() in index.php if it's a CLI request to be completely sure the path is correct for CLI requests.
* Debug backtrace removed from error controller
* \debug() output removed from MemcachedWrapper->getAllMemcache()

**Added features**

* Added an option in the router to initialise without attempting to call the controller
* __get() and __set() methods in AbstractCache
* It's now possible to get the last hash generated by AbstractDB via the getLastHash() method

**Other**

* PHPUNIT_RUNNING constant introduced for when you're running PHPUnit tests. This alters the automatic code flow in the sys files so they do not interfere.
* Realised that array shorthands were introduced in 5.4, not 5.3, so the framework description needs some updating...

# 0.2 (2015-05-04) #
* Trait support added - see app/traits
* Kint external library updated
* Slashes in paths replaced with DIRECTORY_SEPARATOR
* Profiling class added
* The Router now has a lot more getters
* Error, autoloading and exception handlers moved to \Alo\Handler
* Committed function tester (forgot about it earlier)
* Fixed an error where any uncaught exception would force the 404 error page.
* Many more statics added to \Alo
* Error divs prettified
