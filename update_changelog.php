<?php

   if (count($_SERVER['argv']) != 3) {
      trigger_error('Please supply the source file as the first parameter and the output file as the 2nd', E_USER_ERROR);
   } else {
      $source_file = $_SERVER['argv'][1];
      $target_file = $_SERVER['argv'][2];

      if (!file_exists($source_file)) {
         trigger_error('Source file not found', E_USER_ERROR);
      } elseif (!file_exists($target_file)) {
         trigger_error('Target file not found', E_USER_ERROR);
      } else {
         //Get the latest changelog
         $changelog_contents_full = file_get_contents($source_file);
         $first_changelog_contents = preg_split('/#\s*.+\s*#\s*\n+/iu', $changelog_contents_full)[1];
         $first_changelog_title = trim(str_replace('#', '', explode("\n", $changelog_contents_full)[0]));

         //Get README.md
         $readme_delimiter = '----------';
         $readme_contents_full = file_get_contents($target_file);
         $readme_exploded = explode($readme_delimiter, $readme_contents_full);
         $changelog_index = false;

         //Get the index of the changelog
         foreach ($readme_exploded as $i => $v) {
            if (stripos($v, '# Latest changes #') !== false) {
               $changelog_index = $i;
               break;
            }
         }

         if ($changelog_index === false) {
            trigger_error('Changelog index not found in README', E_USER_ERROR);
         } else {
            $contents = &$readme_exploded[$changelog_index];
            $delim_string = 'See [changelog.md](changelog.md) for a full changelog of previous versions.';
            $contents_exploded = explode($delim_string, $contents);

            $contents_exploded[1] = "\n## $first_changelog_title ##\n$first_changelog_contents\n";

            if (substr($contents_exploded[1], -2) != "\n\n") {
               $contents_exploded[1] .= "\n";
            }

            $contents = implode($delim_string, $contents_exploded);

            //Save our contents
            $fp = @fopen($target_file, 'w');

            if (!$fp) {
               trigger_error('Failed to fopen ' . $target_file, E_USER_ERROR);
            } else {
               flock($fp, LOCK_EX);
               fwrite($fp, implode($readme_delimiter, $readme_exploded));
               flock($fp, LOCK_UN);
               fclose($fp);
            }
         }
      }
   }