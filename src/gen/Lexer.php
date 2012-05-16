<?php
/**
 * The purpose of this file is to define the PHP.md lexer class
 * @author Andrew Crites <explosion-pills@aysites.com>
 * @copyright 2012
 * @package PHP.md
 */

/**
 * Split a string of CSS into tokens
 */
final class Lexer {
   /**#@+
    * Representations of token types
    */
   const SPACE = ' ';
   const NEWLINE = "\n\r";
   const TAB = "\t";
   const BLOCK = '#=-[]*1234567890>_{}+.!`()';
   const ESCAPE = '\\';
   /**#@-*/

   /**
    * @var Scanner for retrieving source as characters
    */
   private $scanner;

   /**
    * Create a lexer with the given scanner
    */
   public function __construct(Scanner $scanner) {
      $this->scanner = $scanner;
   }

   /**
    * Retrieve the next token
    * @throws LexerUnknownTokenException
    */
   public function get() {

      //Get the next character.  It is possible for a previous token to end right before EOF
      try {
         $c = $this->getChar();
         $c1 = $c[0];
         $c2 = $c[1];
      }
      catch (ScannerIllegalLookaheadException $sille) {
         return null;
      }

      if ($this->in($c1, self::NEWLINE)) {
         return new Newline;
      }
      if ($this->in($c1, self::SPACE)) {
         $num = 1;
         while ($this->in($c2, self::SPACE) && $c2 != Scanner::EOF) {
            $c = $this->getChar();
            $c2 = $c[1];
            $num++;
            /**
             * 4 space tab stop
             * TODO may be nice to have configuration control this
             */
            if ($num == 4) {
               return new Tab;
            }
         }
         return new Space($num);
      }
      if ($this->in($c1, self::TAB)) {
         return new Tab;
      }
      if ($this->in($c1, self::ESCAPE)) {
         return new Escape;
      }
      if ($this->in($c1, self::BLOCK)) {
         $token = new Block;
         $token->append($c1);
         return $token;
      }
      $token = new Value;
      $token->append($c1);

      while (!$this->isComponent($c2) && $c2 != Scanner::EOF) {

         $c = $this->getChar();
         $c1 = $c[0];
         $c2 = $c[1];

         $token->append($c1);
      }

      return $token;
   }

   public function getRaw() {
      try {
         $c = $this->getChar();
         $c1 = $c[0];
      }
      catch (ScannerIllegalLookaheadException $sille) {
         return null;
      }

      $token = new Value;
      $token->append($c1);
      return $token;
   }

   /**
    * Retrieve the next characters from the scanner
    */
   public function getChar() {
      $c1 = $this->scanner->get();
      try {
         $c2 = $this->scanner->peek(1);
      }
      catch (ScannerIllegalLookaheadException $e) {
         $c2 = null;
      }
      return array($c1, $c2);
   }

   /**
    * Determine whether the provided character is in the set of characters
    * Helps to read this as char.in(set)
    * @param string
    * @param string
    */
   public function in($char, $set) {
      return strpos($set, $char) !== false;
   }

   public function isComponent($char) {
      return $this->in($char, self::SPACE . self::NEWLINE . self::TAB . self::BLOCK . self::ESCAPE);
   }
}

class LexerException extends PhpMdException {}
class LexerScanErrorException extends LexerException {}
class LexerUnknownTokenException extends LexerScanErrorException {
   public function __construct($message = null, $code = 0) {
      $message = "Unknown token encountered during scanning"
         . ($message ? ": $message" : '')
      ;

      parent::__construct($message, $code);
   }

   public function setToken($char) {
      $this->message .= " Token was $char";
   }
}
?>
