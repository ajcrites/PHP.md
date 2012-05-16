<?php
class Text {
   private $text;

   public function __construct($text) {
      $this->text = $text;
   }

   public function emit() {
      return $this->text;
   }
}
?>
