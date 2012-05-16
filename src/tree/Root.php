<?php
/**
 * The purpose of this file is to implement the ROOT node class
 * @author Andrew Crites <andrew@gleim.com>
 * @copyright 2012
 * @package PHP.md
 */ 

/**
 * The root node is typeless and has no parent
 */
class Root implements Nodable {
   private $children = array();

   public function updateType($type) {}
   public function updateHeader() {}
   public function value() {}

   public function append(Nodable $node) {
      $this->children[] = $node;
   }

   public function parent() {
      return $this;
   }

   public function emit() {
      $str = '';
      foreach ($this->children as $node) {
         $str .= $node->emit();
      }
      return $str;
   }
}
?>
