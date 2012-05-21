<?php
final class PhpMd implements Parser {
   /**
    * @var Lexer lexer for the markdown file
    */
   private $lexer;

   /**
    * @var Tree to be emitted as HTML
    */
   private $tree;

   /**
    * @var string accumulated text value to be added to the next block
    */
   private $value = '';

   /**
    * @var array parser state stack
    * The state stack is managed backwards, with newer states on top
    * This makes it easier to reference the zeroth state
    */
   private $state = array(ST_ROOT);

   /**#@+
    * Parse States
    */
   const ST_ROOT = 'ST_ROOT';
   const ST_HEAD = 'ST_HEAD';
   const ST_HEAD_VALUE = 'ST_HEAD_VALUE';
   const ST_PRE = 'ST_PRE';
   const ST_DEDENT_TAB = 'ST_DEDENT_TAB';
   /**#@-*/

   public function __construct(Lexer $lexer, Tree $tree) {
      $this->lexer = $lexer;
      $this->tree = $tree;
   }

   public static function createFromFile($file) {
      return self::createFromString(file_get_contents($file));
   }

   public static function createFromString($string) {
      return new self(new Lexer(new Scanner($string)), new Tree);
   }

   public function parse() {

      while ($token = $this->lexer->get()) {
         //If the token is an escape, add the next token to the current value
         if ($token->get() == '\\') {
            $this->value .= $this->lexer->getRaw();
         }
         //The base state -- add to the root node
         else if ($this->getState() == self::ST_ROOT) {
            //Indicates a header level in the root node
            if ($token->get() == '#') {
               $this->tree->appendNode('h1');
               $collect = '#';
               $this->changeState(self::ST_HEAD);
            }
            //Indentation is a pre block
            if ($token->get() == "\t") {
               $this->tree->appendNode('pre');
               $this->changeState(self::PRE);
            }
         }
         //While in the head state..
         else if ($this->getState() == self::ST_HEAD) {
            //Increase (or decrease?) header level
            if ($token->get() == '#') {
               $collect .= '#';
               $this->tree->updateHeader();
            }
            //Newline cancels the header
            //TODO this should probably do something else
            else if ($token->get() == "\n") {
               $this->tree->completeNode();
               $this->restoreState();
            }
            //
            else {
               $this->value .= $token->get();
               $this->restoreState();
               $this->changeState(self::ST_HEAD_VALUE);
               $collect = '';
            }
         }
         else if ($this->getState() == self::ST_HEAD_VALUE) {
            if ($token->get() == "\n") {
               str_replace($collect, '', $this->value);
               $this->tree->appendValue($this->value);
               $this->tree->completeNode();
               $this->value = '';
               $this->restoreState();
            }
         }
         else if ($this->getState() == self::ST_PRE) {
            if ($token->get() == "\n") {
               $this->changeState(self::ST_DEDENT_TAB);
            }
            else {
               $this->value .= $token->get();
            }
         }
         else if ($this->getState() == self::ST_DEDENT_TAB) {
            $this->restoreState();
            if ($token->get() != "\t") {
               $this->restoreState();
               $this->tree->appendValue($this->value);
               $this->tree->completeNode();
               $this->value = '';
            }
         }
      }
   }

   public function getState() {
      return $this->state[0];
   }

   public function changeState($to) {
      array_unshift($this->state, $to);
   }

   public function restoreState() {
      array_shift($this->state);
   }
}
?>
