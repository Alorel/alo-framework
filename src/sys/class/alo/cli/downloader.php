<?php

   namespace Alo\CLI;

   use Alo\Curl;
   use Alo\FileSystem\File;

   if(!defined('GEN_START')) {
      http_response_code(404);
   } else {

      /**
       * Downloads an external resource to disk, echoing the progress
       *
       * @author Art <a.molcanovas@gmail.com>
       */
      class Downloader {

         /**
          * Static reference to the last instance of the class
          *
          * @var Downloader
          */
         static $this;
         /**
          * Curl handler
          *
          * @var Curl
          */
         protected $curl;
         /**
          * Download destination
          *
          * @var string
          */
         protected $dest;
         /**
          * Timestamp when we last reported the status
          *
          * @var int
          */
         protected $lastReportTime;
         /**
          * The last reported status
          *
          * @var string
          */
         protected $lastReportStatus;
         /**
          * Output
          *
          * @var resource
          */
         protected $fp;
         /**
          * Number of times the status has been reported
          *
          * @var int
          */
         protected $reportCount;

         /**
          * Instantiates the class
          *
          * @author Art <a.molcanovas@gmail.com>
          *
          * @param string $source      Download source
          * @param string $destination Download destination
          */
         function __construct($source, $destination) {
            $this->dest        = $destination;
            $this->reportCount = 0;

            $this->curl = new Curl($source);
            $this->curl->setProgressFunction([$this, 'progressFunction']);

            self::$this = &$this;
         }

         /**
          * The progress function
          *
          * @author Art <a.molcanovas@gmail.com>
          *
          * @param resource $resource     Coulsn't find documentation on this one, most likely the curl resource
          * @param int      $downloadSize How much we are downloading
          * @param int      $downloaded   How much we have downloaded
          * @param int      $uploadSize   How much we are uploading
          * @param int      $uploaded     How much we have uploaded
          */
         function progressFunction($resource, $downloadSize, $downloaded, $uploadSize, $uploaded) {
            $ed = $size = 0;

            if($downloadSize > 0 && $downloaded > 0) {
               $ed   = $downloaded;
               $size = $downloadSize;
            } elseif($uploadSize > 0 && $uploaded > 0) {
               $ed   = $uploaded;
               $size = $uploadSize;
            }

            if($ed && $size && $this->reportCount++ != 0) {
               /** @noinspection PhpDeprecationInspection */
               $status = File::convertSize($ed) . '/' . File::convertSize($size) . ' downloaded ['
                         . round(($ed / $size) * 100, 3) . ' %]';

               $time = time();
               if($status != $this->lastReportStatus && ($time != $this->lastReportTime || $ed == $size)) {
                  $this->lastReportTime   = $time;
                  $this->lastReportStatus = $status;
                  echo $status . PHP_EOL;
               }

               $this->reportCount++;
            }

            //Unnecessary, but stops the IDE from thinking the variable is unused
            unset($resource);
         }

         /**
          * Starts the download
          *
          * @author Art <a.molcanovas@gmail.com>
          * @return bool Whther the download was successful (on the Curl side)
          */
         function download() {
            if(file_exists($this->dest)) {
               unlink($this->dest);
            }

            $this->fp = fopen($this->dest, 'w');
            $this->curl->setopt(CURLOPT_FILE, $this->fp);
            $this->curl->exec();
            fclose($this->fp);

            $errno = $this->curl->errno();

            if($errno === CURLE_OK) {
               return true;
            } else {
               echo $this->curl->error() . PHP_EOL;

               return false;
            }
         }
      }
   }
