<?php

   unlink('docs/index.md');
   copy('README.md', 'docs/index.md');