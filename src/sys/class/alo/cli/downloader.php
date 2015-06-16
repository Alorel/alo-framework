<?php

   namespace Alo\CLI;

   use Alo\cURL;
   use Alo\File;

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
          * cURL handler
          *
          * @var cURL
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
         protected $last_report_time;
         /**
          * The last reported status
          *
          * @var string
          */
         protected $last_report_status;
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
         protected $report_count;

         /**
          * Instantiates the class
          *
          * @author Art <a.molcanovas@gmail.com>
          *
          * @param string $source      Download source
          * @param string $destination Download destination
          */
         function __construct($source, $destination) {
            $this->dest         = $destination;
            $this->report_count = 0;

            $this->curl = new cURL($source);
            $this->curl->setProgressFunction([$this, 'progressFunction']);

            self::$this = &$this;
         }

         /**
          * The progress function
          *
          * @author Art <a.molcanovas@gmail.com>
          *
          * @param resource $resource      Coulsn't find documentation on this one, most likely the curl resource
          * @param int      $download_size How much we are downloading
          * @param int      $downloaded    How much we have downloaded
          * @param int      $upload_size   How much we are uploading
          * @param int      $uploaded      How much we have uploaded
          */
         function progressFunction($resource, $download_size, $downloaded, $upload_size, $uploaded) {
            $ed = $size = 0;

            if($download_size > 0 && $downloaded > 0) {
               $ed   = $downloaded;
               $size = $download_size;
            } elseif($upload_size > 0 && $uploaded > 0) {
               $ed   = $uploaded;
               $size = $upload_size;
            }

            if($ed && $size && $this->report_count++ != 0) {
               $status = File::convert_size($ed) . '/' . File::convert_size($size) . ' downloaded ['
                         . round(($ed / $size) * 100, 3) . ' %]';

               $time = time();
               if($status != $this->last_report_status && ($time != $this->last_report_time || $ed == $size)) {
                  $this->last_report_time   = $time;
                  $this->last_report_status = $status;
                  echo $status . PHP_EOL;
               }

               $this->report_count++;
            }

            //Unnecessary, but stops the IDE from thinking the variable is unused
            unset($resource);
         }

         /**
          * Starts the download
          *
          * @author Art <a.molcanovas@gmail.com>
          * @return bool Whther the download was successful (on the cURL side)
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