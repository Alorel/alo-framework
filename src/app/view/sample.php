<div>I am the sample view. If you pass me the variable 'foo' I will display its value here:<span style="font-weight:bold"><?= $foo ?></span></div>
<div>The sample router config file should contain the following:<pre>
   //Controller called for error page handling
   $error_controller_class = 'SampleErrorController';

   //The default controller if one isn't supplied
   $default_controller = 'sample';

   //Routes array
   $routes = [
      'cart/checkout'                 => [
         'dir'    => 'sample',
         'class'  => 'cart',
         'method' => 'checkout'
      ],
      'sample-me/?([^/]*)/?([^/]*)/?' => [
         'class'  => 'sample',
         'method' => 'echoer',
         'args'   => ['$1', '$2']
      ],
      'sample/([^/]+)/([^/]+)/?'        => [
         'method' => 'noclass',
         'args'   => ['hardcoded', '$2']
      ]
   ];
   </pre>
   So let's try visiting some of the URLs to see what they do! You'll find the sample controller files under app/controllers.
   <ul><?php
         $urls = ['/cart/checkout', '/cart/checkout/', '/sample-me/', '/sample-me/foo', '/sample-me/foo/bar/', '/sample/' . urlencode('THIS IS THE VALUE I WANT HERE') . '/bar/'];
         foreach($urls as $url) {
            echo '<li><a href="' . $url . '" target="_blank">' . $url . '</a></li>';
         } ?></ul>
</div>