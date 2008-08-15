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
 * @subpackage Nodelist
 * @copyright  Copyright (c) 2007-2008 Institute for Medical Education, 
 *             University of Bern (http://www.iml.unibe.ch)
 * @author     Michael Rolli <michael.rolli@iml.unibe.ch>
 * @license    http://creativecommons.org/licenses/by-sa/2.5/ch/     CC-By-Sa
 * @version    $Id$
 */

/**
 * Iml_Tree_Nodelist_Iterator implements a n iterator over an Iml_Tree_Nodelist.
 *
 * The iterator is instantiated with both an implementation of an Iml_Tree and
 * an Iml_Tree_Nodelist object. It can be used to iterate over all the nodes
 * in a list.
 *
 * Example:
 * <code>
 * <?php
 *     // fetch all the nodes in a subtree as an Iml_Tree_Nodelist
 *     $nodeList = $tree->fetchSubtree('pan');
 *     foreach (new Iml_Tree_Nodelist_Iterator($tree, $nodeList) as $nodeId => $data)
 *     {
 *         // do something with the node ID and data - data is fetched on
 *         // demand
 *     }
 * ?>
 * </code>
 *
 * Data for the nodes in the node lists is fetched on demand, unless
 * the "prefetch" argument is set to true. In that case the iterator will
 * fetch the data when the iterator is instantiated. This reduces the number
 * of queries made for database and persistent object based data stores, but
 * increases memory usage.
 *
 * Example:
 * <code>
 * <?php
 *     // fetch all the nodes in a subtree as an Iml_Tree_Nodelist
 *     $nodeList = $tree->fetchSubtree('Uranus');
 *     // instantiate an iterator with pre-fetching enabled
 *     foreach (new Iml_Tree_Nodelist_Iterator($tree, $nodeList, true) as $nodeId => $data) {
 *         // do something with the node ID and data - data is fetched when
 *         // the iterator is instatiated.
 *     }
 * ?>
 * </code>
 * 
 * @category   Iml
 * @package    Iml_Tree
 * @subpackage Nodelist
 * @copyright  Copyright (c) 2007-2008 Institute for Medical Education, 
 *             University of Bern (http://www.iml.unibe.ch)
 * @author     Michael Rolli <michael@rollis.ch>
 * @license    http://creativecommons.org/licenses/by-sa/2.5/ch/     CC-By-Sa
 */
class Iml_Tree_Nodelist_Iterator implements Iterator
{
    /**
     * Holds the nodes of this list.
     *
     * @var array(Iml_Tree_Node)
     */
    private $_nodeList = array();

    /**
     * Holds a link to the tree that contains the nodes that are iterated over.
     *
     * This is used for accessing the data store so that data can be fetched
     * on-demand.
     *
     * @var Iml_Tree
     */
    private $_tree;
    
    /**
     * Validity of internal pointer.
     * 
     * @var boolean
     */
    private $_valid = false;

    /**
     * Constructs a new Iml_Tree_Nodelist_Iterator object over $nodeList.
     *
     * The $tree argument is used so that data can be fetched on-demand.
     *
     * @param Iml_Tree          $tree
     * @param Iml_Tree_Nodelist $nodeList
     * @param boolean           $prefetch
     * @return void
     */
    public function __construct(Iml_Tree_Abstract $tree, Iml_Tree_Nodelist $nodeList, $prefetch = false)
    {
        $this->_tree = $tree;
        if ($prefetch) {
            $this->_tree->_properties['dataStore']->fetchDataForNodes($nodeList);
        }
        $this->_nodeList = $nodeList->nodes;
    }

    /**
     * Rewind the Iterator to the first element.
     * Similar to the reset() function for arrays in PHP.
     * Required by interface Iterator
     * 
     * @return Iml_Tree_Nodelist_Iterator Fluent Interface
     */
    public function rewind()
    {
        reset($this->_nodeList);
        if (count($this->_nodeList)) {
            $this->_valid = true;
        } else {
            $this->_valid = false;
        }
    }

    /**
     * Returns the node id of the current node.
     *
     * @return string
     */
    public function key()
    {
        return key($this->_nodeList);
    }

    /**
     * Returns the current node.
     * Similar to the current() function for arrays in PHP.
     * Required by interface Iterator
     *
     * @return mixed
     */
    public function current()
    {
        $node = current($this->_nodeList);
        return $node;
    }

    /**
     * Advances the internal pointer to the next node in the nodelist.
     * Similar to the next() function for arrays in PHP.
     * Required by interface Iterator
     * 
     * @return void
     */
    public function next()
    {
        $nextElement = next($this->_nodeList);
        if ($nextElement === false) {
            $this->_valid = false;
        }
    }

    /**
     * Returns whether the internal pointer is still valid.
     * It returns false when the end of list has been reached.
     * Required by interface Iterator
     *
     * @return bool
     */
    public function valid()
    {
        return $this->_valid;
    }
}
