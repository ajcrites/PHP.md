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

      $state = array('ST_ROOT');
      while ($token = $this->lexer->get()) {
         if ($token->get() == '\\') {
            $this->value .= $this->lexer->getRaw();
         }
         else if ($state[0] == 'ST_ROOT') {
            if ($token->get() == '#') {
               $this->tree->appendNode('h1');
               $collect = '#';
               array_unshift($state, 'ST_HEAD');
            }
            if ($token->get() == "\t") {
               $this->tree->appendNode('pre');
               array_unshift($state, 'ST_PRE');
            }
         }
         else if ($state[0] == 'ST_HEAD') {
            if ($token->get() == '#') {
               $collect .= '#';
               $this->tree->updateHeader();
            }
            else if ($token->get() == "\n") {
               $this->tree->completeNode();
               array_shift($state);
            }
            else {
               $this->value .= $token->get();
               array_shift($state);
               array_unshift($state, 'ST_HEAD_VALUE');
               $collect = '';
            }
         }
         else if ($state[0] == 'ST_HEAD_VALUE') {
            if ($token->get() == "\n") {
               str_replace($collect, '', $this->value);
               $this->tree->appendValue($this->value);
               $this->tree->completeNode();
               $this->value = '';
               array_shift($state);
            }
         }
         else if ($state[0] == 'ST_PRE') {
            if ($token->get() == "\n") {
               array_unshift($state, 'ST_DEDENT_TAB');
            }
            else {
               $this->value .= $token->get();
            }
         }
         else if ($state[0] == 'ST_DEDENT_TAB') {
            array_shift($state);
            if ($token->get() != "\t") {
               array_shift($state);
               $this->tree->appendValue($this->value);
               $this->tree->completeNode();
               $this->value = '';
            }
         }
      }
   }
}
?>
