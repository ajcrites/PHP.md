<?php
/**
 * The purpose of this file is to create a simple file scanner with some error handling
 * @author Andrew Crites <explosion-pills@aysites.com>
 * @copyright 2012
 * @package PHP.md
 */

/**
 * Simple scanner
 */
final class Scanner {
   /**
    * End of file representation.  It is purposely more than one character.
    */
   const EOF = 'End-Of-File';

   /**
    * @var string css source code
    */
   private $source;

   /**
    * @var int end index of the source
    */
   private $end;

   /**
    * @var int current index in the source
    */
   private $index = -1;

   /**
    * Create a scanner from source
    */
   public function __construct($source) {
      $this->source = str_split($source);
      $this->end = strlen($source) - 1;
   }

   /**
    * Retrieve the next character from the source and move the scanner forward
    */
   public function get() {
      $this->index++;

      if ($this->index > $this->end) {
         return self::EOF;
      }
      else {
         $char = $this->source[$this->index];
         if ($char === '') {
            throw new ScannerEmptySourceException;
         }
         return $char;
      }
   }

   /**
    * Look at characters in the source without moving the scanner
    * @param int
    * @return string
    * @throws ScannerIllegalLookaheadException
    */
   public function lookahead($amount) {
      $index = $this->index + $amount;
      $this->validateLookahead($index, $amount);

      $chars = '';
      for (; $amount > 0; $amount--) {
         $chars .= $this->peek($amount);
      }
      return $chars;
   }

   /**
    * Look at a character in the source without moving the scanner
    * @param int
    * @return string
    * @throws ScannerIllegalLookaheadException
    */
   public function peek($amount) {
      $index = $this->index + $amount;
      $this->validateLookahead($index, $amount);

      if ($index > $this->end) {
         return self::EOF;
      }
      return $this->source[$index];
   }

   /**
    * Determine whether a character exists at the requested index
    * @param int
    * @param int
    * @return bool
    * @throws ScannerIllegalLookaheadException
    */
   public function validateLookahead($index, $amount) {
      if ($index > $this->end + 1) {
         $sille = new ScannerIllegalLookaheadException;
         $sille->setLookahead($amount, $this->end + 1);
         throw $sille;
      }
      return true;
   }
}

class ScannerException extends PhpMdException {}
class ScannerEmptySourceException extends ScannerException {
   public function __construct($message = null, $code = 0) {
      $message = "Empty character encountered when scanning source.  This is illegal.  Source may be empty"
         . ($message ? ": $message" : '')
      ;

      parent::__construct($message, $code);
   }
}
class ScannerIllegalLookaheadException extends ScannerException {
   public function __construct($message = null, $code = 0) {
      $message = "Attempting to look at index that extends beyond the end of the source"
         . ($message ? ": $message" : '')
      ;

      parent::__construct($message, $code);
   }

   public function setLookahead($amount, $end) {
      $this->message .= " (amount requested: $amount.  End of file at $end)";
   }
}
?>
