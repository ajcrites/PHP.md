<?php
class Node implements Nodable {
   private $parent;
   private $children = array();
   private $tag;
   private $value;

   public function __construct($tag, Nodable $parent) {
      $this->parent = $parent;
      $this->tag = $tag;
   }

   public function updateType($type) {
      $this->type = $type;
   }

   /**
    * The header starts at 'h1' if it's not a header type and increments
    * from there.  If the type goes past h6, it will continue, allowing
    * Additional header markers to be set even though they have no effect
    * h7+ is to be treated as p
    */
   public function updateHeader() {
      if (strpos($this->type, 'h') != 0) {
         $this->type = 'h1';
      }
      else {
         $count = (int)str_replace('h', '', $this->type);
         $count++;
         $this->type = "h$count";
      }
   }

   public function value($value) {
      $this->children[] = new Text($value);
   }

   public function append(Nodable $node) {
      $this->children[] = $node;
   }

   public function parent() {
      return $this->parent;
   }

   public function emit() {
      $str = "<$this->tag>";
      foreach ($this->children as $node) {
         $str .= $node->emit();
      }
      return $str;
   }
}
?>
