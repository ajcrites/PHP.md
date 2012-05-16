<?php
/**
 * The purpose of this file is to define the HTML tree representation class
 * @author Andrew Crites <explosion-pills@aysites.com>
 * @copyright 2012
 * @package PHP.md
 */

/**
 * Graph representing the output HTML
 */
class Tree {
   /**
    * @var Node The mother of all nodes.  It has no node value of its own
    */
   private $root;

   /**
    * The current block node
    */
   private $current;

   public function __construct() {
      $this->root = new Root;
      $this->current = $this->root;
   }

   /**
    * Create a new block under the current block
    */
   public function appendNode($type) {
      $node = new Node($type, $this->current);
      $this->current->append($node);
      $this->current = $node;
   }

   /**
    * End the current node and return to the parent block
    */
   public function completeNode() {
      $this->current = $this->current->parent();
   }

   /**
    * Change the type of the current node
    */
   public function updateNode($type) {
      $this->current->updateType($type);
   }

   /**
    * Increment an h1 through h6 block
    */
   public function updateHeaderNode() {
      $this->current->updateHeader();
   }

   /**
    * Append text value to a block
    */
   public function appendValue($value) {
      $this->current->value($value);
   }

   /**
    * Create a new block at the same level of the current block
    */
   public function createBlock($type) {
      $this->completeNode();
      $this->current->appendNode($type);
   }

   public function emit() {
      return $this->root->emit();
   }
}
?>
