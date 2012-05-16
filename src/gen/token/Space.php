<?php
class Space implements Tokenable {
   private $spaces;

   public function __construct($spaces) {
      $this->spaces = $spaces;
   }

   public function append($chars) {}

   public function get() {
      return str_repeat(' ', $this->spaces);
   }
}
?>
