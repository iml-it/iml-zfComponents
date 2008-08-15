<?php

/**
 * IML ZendFramework Components
 *
 * LICENSE
 *
 * This work is licensed under the Creative Commons Attribution-Share Alike 2.5
 * Switzerland License. To view a copy of this license, visit
 * http://creativecommons.org/licenses/by-sa/2.5/ch/ or send a letter to
 * Creative Commons, 171 Second Street, Suite 300, San Francisco, California,
 * 94105, USA.
 *
 * @category   Iml
 * @package    Iml_Tree
 * @copyright  Copyright (c) 2007-2008 Institute for Medical Education, 
 *             University of Bern (http://www.iml.unibe.ch)
 * @author     Michael Rolli <michael.rolli@iml.unibe.ch>
 * @license    http://creativecommons.org/licenses/by-sa/2.5/ch/     CC-By-Sa
 * @version    $Id$
 */


/**
 * Iml_Tree_Nodelist represents a lists of nodes.
 * 
 * The nodes in the list can be accessed through an array as this class
 * implements the ArrayAccess SPL interface. The array is indexed based on the
 * the node's ID. It's countable and implements a SeekableIterator.
 * 
 * Example:
 * <code>
 * <?php
 *     // Create a list with two elements
 *     $list = new Iml_Tree_Nodelist;
 *     $list->addNode(new Iml_Tree_Node( $tree, 'Leo' ) );
 *     $list->addNode(new Iml_Tree_Node( $tree, 'Virgo' ) );
 * 
 *     // Retrieve the list's size
 *     echo $list->size, "\n"; // prints 2
 * 
 *     // Find a node in the list
 *     $node = $list['Virgo'];
 * 
 *     // Add nodes in an alternative way
 *     $list['Libra'] = new Iml_Tree_Node( $tree, 'Libra' );
 * 
 *     // Remove a node from the list
 *     unset( $list['Leo'] );
 * 
 *     // Checking if a node exists
 *     if ( isset( $list['Scorpius'] ) )
 *     {
 *         // do something if it exists
 *     }
 * 
 *     // Use the associated data store to fetch the data for all nodes at once
 *     $list->fetchDataForNodes();
 * ?>
 * </code>
 * 
 * @category   Iml
 * @package    Iml_Tree
 * @copyright  Copyright (c) 2007-2008 Institute for Medical Education, 
 *             University of Bern (http://www.iml.unibe.ch)
 * @author     Michael Rolli <michael.rolli@iml.unibe.ch>
 * @license    http://creativecommons.org/licenses/by-sa/2.5/ch/     CC-By-Sa
 * @see Iml_Tree_Nodelist_Iterator
 *
 * @property-read string $size
 *                The number of nodes in the list.
 * @property-read array(string=>ezcTreeNode) $nodes
 *                The nodes belonging to this list.
 *
 * @package Tree
 * @version 1.0
 */
class Iml_Tree_Nodelist implements Countable, ArrayAccess, IteratorAggregate
{
    /**
     * Holds the nodes of this list.
     *
     * @var array(Iml_Tree_Node)
     */
    protected $_nodes;
    
    /**
     * How many data nodes there are.
     * 
     * @var integer
     */
    protected $_count = 0;
    
    /**
     * Constructs a new empty Iml_Tree_NodeList object.
     * 
     * @return void
     */
    public function __construct()
    {
        $this->_nodes = array();
    }

    /**
     * Returns the value of the property $name.
     *
     * @param string $name
     * @return mixed
     * @throws Iml_Tree_Exception if the property does not exist.
     */
    public function __get($name)
    {
        switch ($name) {
            case 'nodes':
                return $this->_nodes;
            case 'size':
                return $this->_count;
        }
        throw new Iml_Tree_Exception('Property "' . $name . '" could not be found.');
    }

    /**
     * Sets the property $name to $value.
     *
     * @param string $name
     * @param mixed $value
     * @return void
     * @throws Iml_Tree_Exception if the property does not exist.
     * @throws Iml_Tree_Exception if a read-only property is
     *         tried to be modified.
     */
    public function __set($name, $value)
    {
        switch ($name) {
            case 'nodes':
            case 'size':
                throw new Iml_Tree_Exception('Modifying property "' . $name . '" is not allowed.');
            default:
                throw new Iml_Tree_Exception('Property "' . $name . '" could not be found.');
        }
    }

    /**
     * Returns whether a node with the ID $nodeId exists in the list.
     *
     * This method is part of the SPL ArrayAccess interface.
     *
     * @param  string $nodeId
     * @return bool
     */
    public function offsetExists($nodeId)
    {
        return array_key_exists($nodeId, $this->_nodes);
    }

    /**
     * Returns the node with the ID $nodeId.
     * Required by interface ArrayAccess.
     *
     * @param  string $nodeId
     * @return Iml_Tree_Node
     */
    public function offsetGet($nodeId)
    {
        return $this->_nodes[$nodeId];
    }

    /**
     * Adds a new node with node ID $nodeId to the list.
     * Required by interface ArrayAccess.
     * 
     * @param  string      $nodeId
     * @param  Iml_Tree_Node $data
     * @return void
     * @throws Iml_Tree_Exception if the data to be set as array
     *         element is not an instance of Iml_Tree_Node
     * @throws Iml_Tree_Exception if the array index $nodeId does not
     *         match the tree node's ID that is stored in the $data object
     */
    public function offsetSet($nodeId, $node)
    {
        $this->addNode($node);
    }

    /**
     * Removes the node with ID $nodeId from the list.
     * Required by interface ArrayAccess.
     *
     * @param string $nodeId
     * @return integer number of elements in nodelist
     */
    public function offsetUnset($nodeId)
    {
        unset($this->_nodes[$nodeId]);
        return $this->_count--;
    }

    /**
     * Adds the node $node to the list.
     *
     * @param Iml_Tree_Node $node
     * @return integer number of elements in nodelist
     */
    public function addNode(Iml_Tree_Node $node)
    {
        $this->_nodes[$node->id] = $node;
        return $this->_count++;
    }

    /**
     * Fetches data for all nodes in the node list.
     * 
     * @return void
     */
    public function fetchDataForNodes()
    {
        // We need to use a little trick to get to the tree object. We can do
        // that through Iml_Tree_Node objects that are part of this list. We
        // can't do that when the list is empty. In that case we just return.
        if ($this->_count === 0) {
            return;
        }
        // Find a node in the list
        reset($this->_nodes);
        $node = current($this->_nodes);
        // Grab the tree and use it to fetch data for all nodes from the store
        $tree = $node->tree;
        $tree->store->fetchDataForNodes($this);
    }
    
    /**
     * Returns the number of nodes in the list.
     * Required for the Countable interface.
     * 
     * @return integer
     */
    public function count()
    {
        return $this->_count;
    }

    /**
     * Returns an iterator for the nodelist.
     * Required for the IteratorAggregate Interface
     */
    public function getIterator($prefetch = false)
    {
        // We need to use a little trick to get to the tree object. We can do
        // that through Iml_Tree_Node objects that are part of this list. We
        // can't do that when the list is empty. In that case we just return.
        if ($this->_count === 0) {
            return;
        }
        // Find a node in the list
        reset($this->_nodes);
        $node = current($this->_nodes);
        // Grab the tree and use it to fetch data for all nodes from the store
        $tree = $node->tree;
        return new Iml_Tree_Nodelist_Iterator($tree, $this, $prefetch);
    }
}
