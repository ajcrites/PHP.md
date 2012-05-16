<?php
class Newline implements Tokenable {
   public function append($chars) {}

   public function get() {
      return "\n";
   }
}
?>
