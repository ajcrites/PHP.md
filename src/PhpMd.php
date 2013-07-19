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
   const ST_CODE = 'ST_CODE';
   const ST_UL = 'ST_UL';
   const ST_LI = 'ST_LI';
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
               $this->changeState(self::ST_PRE);
            }
            if ($token->get() == '`') {
               $this->tree->appendNode('code');
               $this->changeState(self::ST_CODE);
            }
            if ($token->get() == '*') {
               $this->tree->appendNode('ul');
               $this->changeState(self::ST_UL);
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
            //Now accepting value for the head
            else {
               $this->value .= $token->get();
               $this->restoreState();
               $this->changeState(self::ST_HEAD_VALUE);
               $collect = '';
            }
         }
         //We have a header .. fill it with this value
         else if ($this->getState() == self::ST_HEAD_VALUE) {
            //.. Until newline indicating completion
            if ($token->get() == "\n") {
               $this->value = str_replace($collect, '', $this->value);
               $this->completeNode();
            }
         }
         // Tabbed in state.  Markdown does not apply here
         else if ($this->getState() == self::ST_PRE) {
            // After a newline, the tabbed state ends unless there is another tab
            if ($token->get() == "\n") {
               $this->changeState(self::ST_DEDENT_TAB);
            }
            else {
               $this->value .= $token->get();
            }
         }
         //Expecting a tab to continue the tabbed state
         else if ($this->getState() == self::ST_DEDENT_TAB) {
            $this->restoreState();
            //Newlines are allowed in the tabbed state without being tabbed in
            if ($token->get() == "\n") {
               $this->value .= "\n";
            }
            //The next line did not tab, so complete the tabbed-in stuff
            else if ($token->get() != "\t") {
               $this->completeNode();
            }
         }
         //Code blocks are simple.  Wrap with backtick
         else if ($this->getState() == self::ST_CODE) {
            if ($token->get() == '`') {
               $this->completeNode();
            }
         }
         else if ($this->getState() == self::ST_UL) {
            //Valueless list item should not be ul-wrapped
            if ($token->get() == "\n") {
               $this->tree->updateNode('TEXT');
               $this->tree->appendValue('*');
               $this->restoreState();
            }
            //Add whitespace to value, but non-whitespace is still required to create ul
            else if ($token->get() == " " || $token->get() == "\t") {
               $this->value .= $token->get();
            }
            else {
               $this->value .= $token->get();
               $this->changeState(self::ST_LI);
            }
         }
         //List item is terminated by newline
         //TODO it should actually be terminated by dedent
         else if ($this->getState() == self::ST_LI) {
            if ($token->get() == "\n") {
               $this->completeNode();
            }
            else {
               $this->value .= $token->get();
            }
         }
         else {
            $this->value .= $token->get();
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

   public function completeNode() {
      $this->tree->appendValue($this->value);
      $this->tree->completeNode();
      $this->value = '';
      $this->restoreState();
   }
}
?>
