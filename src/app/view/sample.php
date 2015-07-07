<div>I am the sample view. If you pass me the variable 'foo' I will display its value here:<span
        style="font-weight:bold"><?= isset($foo) ? $foo : '' ?></span></div>
<div>The sample router config file should contain the following:<pre>
   //Controller called for error page handling
   $errorControllerClass = 'SampleErrorController';

   //The default controller if one isn't supplied
   $defaultController = 'sample';

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
    So let's try visiting some of the URLs to see what they do! You'll find the sample controller files under
    app/controllers. The URLs will differ depending on whether you've renamed <span style="font-weight:bold">
        .htaccess.sample</span> to <span style="font-weight:bold">
        .htaccess</span> or not.
    <table border="1" cellpadding="1" cellspacing="1" style="border-collapse:collapse">
        <thead>
        <tr>
            <th>.htaccess.sample</th>
            <th>.htaccess</th>
        </tr>
        </thead>
        <tbody><?php
            $urls = ['/cart/checkout',
                     '/cart/checkout/',
                     '/sample-me/',
                     '/sample-me/foo',
                     '/sample-me/foo/bar/',
                     '/sample/' . urlencode('THIS IS THE VALUE I WANT HERE') . '/bar/'];

            foreach ($urls as $url) {
                ?>
                <tr>
                    <td><a href="/index.php<?= $url ?>" target="_blank">/index.php<?= $url ?></a></td>
                    <td><a href="<?= $url ?>" target="_blank"><?= $url ?></a></td>
                </tr>
            <?php
            }
        ?></tbody>
    </table>
</div>
