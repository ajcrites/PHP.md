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
    * @var string accumulated value to be added to the next block
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

      while ($token = $this->lexer->get()) {
         if ($token->get() == '\\') {
            $value .= $this->lexer->getRaw();
         }
         else {
            if ($token->get() == '#') {
               $block = 'h1';
            }
         }
      }
   }
}
?>
