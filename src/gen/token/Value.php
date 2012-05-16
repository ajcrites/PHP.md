<?php
/**
 * The purpose of this file is to define a value or "identifier" token
 * @author Andrew Crites <explosion-pills@aysites.com>
 * @copyright 2012
 * @package PHP.md
 */

/**
 * Value token class
 */
final class Value implements Tokenable {
   /**
    * @var string contents of the value
    */
   private $chars;

   public function append($chars) {
      $this->chars .= $chars;
   }

   /**
    * Retrieve the value
    * @return string
    */
   public function get() {
      return $this->chars;
   }
}
?>
