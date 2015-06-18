<?php

   namespace Alo;

   use Alo\Statics\Security;
   use PHPMailer;

   if(!defined('GEN_START')) {
      http_response_code(404);
   } else {

      require_once DIR_SYS . 'external' . DIRECTORY_SEPARATOR . 'email' . DIRECTORY_SEPARATOR . 'class.phpmailer.php';
      require_once DIR_SYS . 'external' . DIRECTORY_SEPARATOR . 'email' . DIRECTORY_SEPARATOR . 'PHPMailerAutoload.php';

      \Alo::loadConfig('email');

      /**
       * Mail wrapper for the external PHPMailer library
       *
       * @author Art <a.molcanovas@gmail.com>
       * @link   https://github.com/PHPMailer/PHPMailer
       */
      class Email extends PHPMailer {

         /**
          * Static reference to the last instance of the class
          *
          * @var Email
          */
         static $this;

         /**
          * Array of debug outputs, each send operation representing a key/value pair
          *
          * @var array
          */
         protected $debugOutput;

         /**
          * Array of content attachments to clean afterwards
          *
          * @var array
          */
         protected $attachedContent;

         /**
          * Instantiates the class
          *
          * @author Art <a.molcanovas@gmail.com>
          *
          * @param boolean $exceptions Should we throw external exceptions?
          */
         function __construct($exceptions = false) {
            parent::__construct($exceptions);

            if(ALO_EMAIL_ERR_LANG != 'en') {
               $this->setLanguage(ALO_EMAIL_ERR_LANG);
            }

            $this->isSMTP(ALO_EMAIL_USE_SMTP);
            $this->Host       = ALO_EMAIL_HOSTS;
            $this->SMTPAuth   = ALO_EMAIL_AUTH;
            $this->Username   = ALO_EMAIL_USERNAME;
            $this->Password   = ALO_EMAIL_PASSWORD;
            $this->SMTPSecure = ALO_EMAIL_SECURE;
            $this->Port       = ALO_EMAIL_PORT;
            $this->From       = ALO_EMAIL_FROM_DEFAULT_ADDR;
            $this->FromName   = ALO_EMAIL_FROM_DEFAULT_NAME;
            $this->Subject    = ALO_EMAIL_SUBJECT_DEFAULT;
            $this->isHTML(ALO_EMAIL_HTML_ENABLED);

            self::$this = &$this;
         }

         /**
          * Instantiates the class
          *
          * @author Art <a.molcanovas@gmail.com>
          *
          * @param boolean $exceptions Should we throw external exceptions?
          *
          * @return Email
          */
         static function email($exceptions = false) {
            return new Email($exceptions);
         }

         /**
          * Checks if the supplied string is an email
          *
          * @author Art <a.molcanovas@gmail.com>
          *
          * @param string $str The input
          *
          * @return boolean
          */
         static function isEmailAddress($str) {
            if(!is_string(($str))) {
               return false;
            } else {
               return preg_match('/^[a-z\.\-_0-9]+@[a-z\.\-_0-9]+\.[a-z]{2,3}$/is', $str) == 1;
            }
         }

         /**
          * Destructor. Performs cleanup operations
          *
          * @author Art <a.molcanovas@gmail.com>
          */
         function __destruct() {
            $this->cleanup();
            parent::__destruct();
         }

         /**
          * Cleans up attached content
          *
          * @author Art <a.molcanovas@gmail.com>
          * @return Email
          */
         function cleanup() {
            if(!empty($this->attachedContent)) {
               foreach($this->attachedContent as $file) {
                  if(file_exists($file)) {
                     unlink($file);
                  }
               }

               $this->attachedContent = [];
            }

            return $this;
         }

         /**
          * Adds a recipient address
          *
          * @author Art <a.molcanovas@gmail.com>
          *
          * @param string $address The address to add
          * @param string $name    Optionally, the recipient's name
          *
          * @return Email
          * @throws \phpmailerException
          */
         function addAddress($address, $name = '') {
            parent::addAddress($address, $name);

            return $this;
         }

         /**
          * Adds a BCC address
          *
          * @author Art <a.molcanovas@gmail.com>
          *
          * @param string $address The address
          * @param string $name    Their name
          *
          * @throws \phpmailerException
          * @return Email
          */
         function addBCC($address, $name = '') {
            parent::addBCC($address, $name);

            return $this;
         }

         /**
          * Adds a CC address
          *
          * @author Art <a.molcanovas@gmail.com>
          *
          * @param string $address The address
          * @param string $name    Their name
          *
          * @return Email
          * @throws \phpmailerException
          */
         function addCC($address, $name = '') {
            parent::addCC($address, $name);

            return $this;
         }

         /**
          * Adds reply-to data
          *
          * @author Art <a.molcanovas@gmail.com>
          *
          * @param string $to   Reply-to address
          * @param string $name Reply-to name
          *
          * @return Email
          * @throws \phpmailerException
          */
         function addReplyTo($to, $name = '') {
            parent::addReplyTo($to, $name);

            return $this;
         }

         /**
          * Create a message and send it.
          *
          * @author Art <a.molcanovas@gmail.com>
          * @throws \phpmailerException
          * @return boolean false on error - See the ErrorInfo property for details of the error.
          */
         function send() {
            ob_start();
            $send                = parent::send();
            $this->debugOutput[] = ob_get_clean();

            return $send;
         }

         /**
          * Attempts to attach not a file from the disk, but generated contents
          *
          * @author Art <a.molcanovas@gmail.com>
          *
          * @param string $name    The attachment filename
          * @param string $content The contents
          *
          * @return bool
          * @throws \Exception
          * @throws \phpmailerException
          */
         function attachContent($name, $content) {
            $destFilename = Security::getUniqid('md5', 'email_attachment');
            $dest         = DIR_TMP . $destFilename;

            if(file_exists($dest)) {
               //try again
               return $this->attachContent($name, $content);
            } elseif(file_put_contents($dest, $content) !== false) {
               $this->attachedContent[] = $dest;

               return $this->addAttachment($dest, $name);
            } else {
               return false;
            }
         }

         /**
          * Returns the debug output from calls to $this->send()
          *
          * @author Art <a.molcanovas@gmail.com>
          * @return array
          */
         function getDebugOutput() {
            return $this->debugOutput;
         }
      }
   }
