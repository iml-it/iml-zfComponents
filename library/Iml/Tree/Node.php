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
 * Iml_Tree_Visitable_Interface
 */
require_once 'Iml/Tree/Visitable/Interface.php';

/**
 * Iml_Tree_Exception
 */
require_once 'Iml/Tree/Exception.php';


/**
 * Iml Tree Node
 * 
 * This class represents a node in a tree.
 * 
 * The methods that operate on nodes (fetchChildren, fetchPath, ...,
 * isSiblingOf) are all marshalled to calls on the tree (that is stored in the
 * $tree private variable) itself.
 * 
 * @category   Iml
 * @package    Iml_Tree
 * @copyright  Copyright (c) 2007-2008 Institute for Medical Education, 
 *             University of Bern (http://www.iml.unibe.ch)
 * @author     Michael Rolli <michael.rolli@iml.unibe.ch>
 * @license    http://creativecommons.org/licenses/by-sa/2.5/ch/     CC-By-Sa
 * 
 * @property-read string   $id          The ID that uniquely identifies a node
 * @property-read Iml_Tree_Abstract $tree The tree object that this node belongs to
 * @property      mixed    $data        The data belonging to a node
 * @property      bool     $dataFetched Whether the data for this node has been
 *                                      fetched. Should *only* be modified by
 *                                      data store implementations.
 * @property      bool     $dataStored  Whether the data for this node has been
 *                                      stored. Should *only* be modified by
 *                                      data store implementations.
 */
class Iml_Tree_Node implements Iml_Tree_Visitable_Interface
{
    /**
     * Holds the properties of this class.
     *
     * @var array(string => mixed)
     */
    private $_properties = array();

    /**
     * Constructs a new Iml_Tree_node object with ID $id on tree $tree.
     *
     * If a third argument is specified it is used as data for the new node.
     *
     * @param Iml_Tree_Abstract $tree
     * @param string            $id
     * @param mixed             $data
     */
    public function __construct(Iml_Tree_Abstract $tree, $id = null, $data = null)
    {
        
        $this->_properties['id'] = $id;
        $this->_properties['tree'] = $tree;

        if (null === $data) {
            $this->_properties['data'] = null;
            $this->_properties['dataFetched'] = false;
            $this->_properties['dataStored'] = true;
        } else {
            $this->_properties['data'] = $data;
            $this->_properties['dataFetched'] = true;
            $this->_properties['dataStored'] = false;
        }
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
            case 'data':
                if ($this->_properties['dataFetched'] === false) {
                    // fetch the data on the fly
                    $this->_properties['tree']->dataStore->fetchDataForNode($this);
                    $this->_properties['dataFetched'] = true;
                }
                // break intentionally missing
            case 'id':
            case 'dataFetched':
            case 'dataStored':
            case 'tree':
                return $this->_properties[$name];

        }
        // magic "function" to get data properties by object-style
        if ($this->_properties['dataFetched'] === false) {
            $this->_properties['tree']->dataStore->fetchDataForNode($this);
            $this->_properties['dataFetched'] = true;
        }
        if (array_key_exists($name, $this->_properties['data'])) {
            return $this->_properties['data'][$name];
        }
        throw new Iml_Tree_Exception('Property "' . $name . '" not found.' );
    }

    /**
     * Sets the property $name to $value.
     *
     * @throws Iml_Tree_Exception if the property does not exist.
     * @throws Iml_Tree_Exception if a read-only property is
     *         tried to be modified.
     * @throws Iml_Tree_Exception if trying to assign a wrong value to
     *         the property
     * @param string $name
     * @param mixed $value
     */
    public function __set($name, $value)
    {
        switch ($name) {
            case 'id':
            case 'tree':
                throw new Iml_Tree_Exception('Property "' . $name . '" is read-only.');
            case 'data':
                $this->_properties[$name] = $value;
                $this->_properties['dataStored'] = false;
                if ($this->_properties['dataFetched']) {
                    $this->_properties['tree']->dataStore->storeDataForNode($this);
                }
                $this->_properties['dataFetched'] = true;
                return;
            case 'dataFetched':
            case 'dataStored':
                if (!is_bool($value)) {
                    throw new Iml_Tree_Exception('Expected a boolean for property "' . $name . '", ' . gettype($value) . ' given.');
                }
                $this->_properties[$name] = $value;
                return;
        }
        if ($this->_properties['dataFetched'] === false) {
            $this->_properties['tree']->dataStore->fetchDataForNode($this);
            $this->_properties['dataFetched'] = true;
        }
        if (array_key_exists($name, $this->_properties['data'])) {
            $this->_properties['data'][$name] = $value;
            $this->_properties['dataStored'] = false;
            return;
        }
        throw new Iml_Tree_Exception('Property "' . $name . '" not found.');
    }

    /**
     * Returns true if the property $name is set, otherwise false.
     *
     * @param string $name     
     * @return boolean
     */
    public function __isset($name)
    {
        switch ($name) {
            case 'id':
            case 'tree':
            case 'data':
            case 'dataFetched':
            case 'dataStored':
                return isset($this->_properties[$name]);
            default:
                return false;
        }
    }

    /**
     * Implements the accept method for visiting.
     *
     * @param Iml_Tree_Visitor_Interface $visitor
     * @return Iml_Tree_Node fluent interface
     */
    public function accept(Iml_Tree_Visitor_Interface  $visitor)
    {
        $visitor->visit($this);
        foreach ($this->fetchChildren()->nodes as $childNode) {
            $childNode->accept($visitor);
        }
        return $this;
    }

    /**
     * Adds the node $node as child of the current node to the tree.
     *
     * @param Iml_Tree_Node $node
     * @return Iml_Tree_Node fluent interface
     */
    public function addChild(Iml_Tree_Node &$node)
    {
        $this->_properties['tree']->addChild($this->_properties['id'], $node);
        return $this;
    }

    /**
     * Returns all the children of this node.
     *
     * @return Iml_Tree_Nodelist
     */
    public function fetchChildren()
    {
        return $this->_properties['tree']->fetchChildren($this->_properties['id']);
    }

    /**
     * Returns all the nodes in the path from the root node to this node.
     *
     * @return Iml_Tree_Nodelist
     */
    public function fetchPath()
    {
        return $this->_properties['tree']->fetchPath($this->_properties['id']);
    }

    /**
     * Returns the parent node of this node.
     *
     * @return Iml_Tree_Node
     */
    public function fetchParent()
    {
        return $this->_properties['tree']->fetchParent($this->_properties['id']);
    }

    /**
     * Returns this node and all its children, sorted according to the
     * {@link http://en.wikipedia.org/wiki/Depth-first_search Depth-first sorting}
     * algorithm.
     *
     * @return Iml_Tree_Nodelist
     */
    public function fetchSubtreeDepthFirst()
    {
        return $this->_properties['tree']->fetchSubtreeDepthFirst($this->_properties['id']);
    }

    /**
     * Alias for fetchSubtreeDepthFirst().
     *
     * @see fetchSubtreeDepthFirst
     * @return Iml_Tree_Nodelist
     */
    public function fetchSubtree()
    {
        return $this->fetchSubtreeDepthFirst();
    }

    /**
     * Returns this node and all its children, sorted accoring to the
     * {@link http://en.wikipedia.org/wiki/Breadth-first_search Breadth-first sorting}
     * algorithm.
     *
     * @return Iml_Tree_Nodelist
     */
    public function fetchSubtreeBreadthFirst()
    {
        return $this->_properties['tree']->fetchSubtreeBreadthFirst($this->_properties['id']);
    }

    /**
     * Returns the number of direct children of this node.
     *
     * @return int
     */
    public function getChildCount()
    {
        return $this->_properties['tree']->getChildCount($this->_properties['id']);
    }

    /**
     * Returns the number of children of this node, recursively iterating over
     * the children.
     *
     * @return int
     */
    public function getChildCountRecursive()
    {
        return $this->_properties['tree']->getChildCountRecursive($this->_properties['id']);
    }

    /**
     * Returns the distance from the root node to this node.
     *
     * @return int
     */
    public function getPathLength()
    {
        return $this->_properties['tree']->getPathlength($this->_properties['id']);
    }

    /**
     * Returns whether this node has children.
     *
     * @return boolean
     */
    public function hasChildNodes()
    {
        return $this->_properties['tree']->hasChildNodes($this->_properties['id']);
    }

    /**
     * Returns whether this node is a direct child of the $parentNode node.
     *
     * @param Iml_Tree_Node $parentNode
     * @return boolean
     */
    public function isChildOf(Iml_Tree_Node $parentNode)
    {
        return $this->_properties['tree']->isChildOf($this->_properties['id'], $parentNode->_properties['id']);
    }

    /**
     * Returns whether this node is a direct or indirect child of the
     * $parentNode node.
     *
     * @param Iml_Tree_Node $parentNode
     *
     * @return boolean
     */
    public function isDescendantOf(Iml_Tree_Node $parentNode)
    {
        return $this->_properties['tree']->isDescendantOf($this->_properties['id'], $parentNode->_properties['id']);
    }

    /**
     * Returns whether this node, and the $child2Node node are are siblings
     * (ie, they share the same parent).
     *
     * @param Iml_Tree_Node $otherNode
     *
     * @return boolean
     */
    public function isSiblingOf(Iml_Tree_Node $otherNode)
    {
        return $this->_properties['tree']->isSiblingOf($this->_properties['id'], $otherNode->_properties['id']);
    }

    /**
     * Returns the text representation of a node (its ID).
     *
     * @return string
     * @ignore
     */
    public function __toString()
    {
        return (string) $this->_properties['id'];
    }

    public function save()
    {
        $this->_properties['tree']->dataStore->storeDataForNode($this);
    }
}
