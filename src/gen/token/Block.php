<?php
class Block implements Tokenable {
   private $chars = '';

   public function append($chars) {
      $this->chars .= $chars;
   }

   public function get() {
      return $this->chars;
   }
}
?>
