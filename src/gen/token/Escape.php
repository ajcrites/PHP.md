<?php
class Escape implements Tokenable {
   public function append($chars) {}

   public function get() {
      return '\\';
   }
}
?>
