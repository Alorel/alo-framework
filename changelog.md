# 1.0 (pending) #
Major bugfixes

* Fixed error where config files wouldn't be loaded correctly

Misc bugfixes

* Fixed router so it doesn't mistake an in-app ReflectionException with one that's caused by trying to initialise an invalid controller/method
* Added chdir() in index.php if it's a CLI request to be completely sure the path is correct for CLI requests.
* Debug backtrace removed from error controller
* \debug() output removed from MemcachedWrapper->getAllMemcache()

Code style improvements

* Indentation changed in router config sample file

Other

* PHPUNIT_RUNNING constant introduced for when you're running PHPUnit tests. This alters the automatic code flow in the sys files so they do not interfere.

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