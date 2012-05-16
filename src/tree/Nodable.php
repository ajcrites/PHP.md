<?php
interface Nodable {
   /**
    * Change the type (html tag)
    * @var string
    */
   function updateType($type);

   /**
    * Increment an h1 through h6 header
    */
   function updateHeader();

   /**
    * Add non-element value
    */
   function value();

   /**
    * Append a child node
    */
   function append(Nodable $node);

   /**
    * Retrieve the parent node of this node
    */
   function parent();
}
?>
