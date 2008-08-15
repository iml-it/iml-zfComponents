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
 * @author     Michael Rolli <michael@rollis.ch>
 * @license    http://creativecommons.org/licenses/by-sa/2.5/ch/     CC-By-Sa
 * @version    $Id$
 */

/**
 * Interfac Iml_Tree_Visitable_Interface
 */
require_once 'Iml/Tree/Visitable/Interface.php';

/**
 * Iml_Tree_Exception
 */
require_once 'Iml/Tree/Exception.php';


/**
 * Iml_Tree_Abstract is an abstract class from which all the tree implementations
 * inherit.
 * 
 * @category   Iml
 * @package    Iml_Tree
 * @copyright  Copyright (c) 2007-2008 Institute for Medical Education, 
 *             University of Bern (http://www.iml.unibe.ch)
 * @author     Michael Rolli <michael@rollis.ch>
 * @license    http://creativecommons.org/licenses/by-sa/2.5/ch/     CC-By-Sa
 */
abstract class Iml_Tree_Abstract implements Iml_Tree_Visitable_Interface
{
    /**
     * Holds the properties of this class.
     *
     * @var array(string=>mixed)
     */
    protected $_properties = array('nodeClassName' => 'Iml_Tree_Node', 
                                   'nodelistClassName' => 'Iml_Tree_Nodelist');

    /**
     * Returns the value of the property $name.
     *
     * @throws Iml_Tree_Exception if the property does not exist.
     * @param string $name
     * @return mixed
     * @ignore
     */
    public function __get($name)
    {
        switch ($name) {
            case 'dataStore':
            case 'treeStore':
            case 'nodeClassName':
            case 'nodelistClassName':
                return $this->_properties[$name];
        }
        throw new Iml_Tree_Exception('Property ' . $name . ' not found');
    }

    /**
     * Sets the property $name to $value.
     *
     * @throws Iml_Tree_Exception if the property does not exist.
     * @throws Iml_Tree_Exception if a read-only property is
     *         tried to be modified.
     * @throws Iml_Tree_Exception if trying to assign a wrong value to the
     *         property
     * @throws Iml_Tree_Exception if the class name passed as replacement
     *         for the ezcTreeNode classs does not inherit from the
     *         Iml_Tree_Node class.
     * @param string $name
     * @param mixed $value
     * @ignore
     */
    public function __set($name, $value)
    {
        switch ($name) {
            case 'dataStore':
               throw new Iml_Tree_Exception('Setting data store is not allowed.');
               break;
            case 'treeStore':
               throw new Iml_Tree_Exception('Setting tree store is not allowed.');
               break;
            case 'nodeClassName':
                if (!is_string($value)) {
                    throw new Iml_Tree_Exception('nodeClassName must be a string containing a class name.');
                }
                $parentClass = new ReflectionClass('Iml_Tree_Node');
                $handlerClass = new ReflectionClass($value);
                if ('Iml_Tree_Node' !== $value || $handlerClass->isSubclassOf($parentClass)) {
                    throw new Iml_Tree_Exception('nodeClassName must be a child class of Iml_Tree_Node');
                }
                $this->_properties[$name] = $value;
                break;
            case 'nodelistClassName':
                if (!is_string($value)) {
                    throw new Iml_Tree_Exception('nodelistClassName must be a string containing a class name.');
                }
                $parentClass = new ReflectionClass('Iml_Tree_Nodelist');
                $handlerClass = new ReflectionClass($value);
                if ('Iml_Tree_Nodelist' !== $value || $handlerClass->isSubclassOf($parentClass)) {
                    throw new Iml_Tree_Exception('nodelistClassName must be a child class of Iml_Tree_Nodelist');
                }
                $this->_properties[$name] = $value;
                break;
            default:
                throw new Iml_Tree_Exception('Property ' . $name . ' not found.');
        }
    }

    /**
     * Returns true if the property $name is set, otherwise false.
     *
     * @param string $name     
     * @return bool
     * @ignore
     */
    public function __isset($name)
    {
        switch ($name) {
            case 'treeStore':
            case 'dataStore':
            case 'nodeClassName':
            case 'nodelistClassName':
                return $this->_properties[$name];
        }
        return false;
    }

    /**
     * This method checks whether a node ID is valid to be used in a backend.
     *
     * @throws Iml_Tree_Exception if the node is not valid.
     * @param string $nodeId
     */
    public function checkNodeId($nodeId)
    {
        // Does not check anything
        $nodeId;
    }

    /**
     * Creates a new tree node with node ID $nodeId and $data.
     *
     * This method returns by default an object of the Iml_Tree_Node class, 
     * however if a replacement is configured through the nodeClassName property
     * an object of that class is returned instead. This object is guaranteed
     * to inherit from Iml_Tree_Node_Interface.
     *
     * @param string $nodeId
     * @param mixed  $data
     * @return Iml_Tree_Node
     */
    public function createNode($nodeId, $data)
    {
        $this->checkNodeID($nodeId);
        $className = $this->_properties['nodeClassName'];
        return new $className($this, $nodeId, $data);
    }

    /**
     * Implements the accept method for visiting.
     *
     * @param Iml_Tree_Visitor $visitor
     */
    public function accept(Iml_Tree_Visitor_Interface $visitor)
    {
        $visitor->visit($this);
        $this->getRootNode()->accept($visitor);
    }

    /**
     * Returns whether the node with ID $nodeId exists.
     *
     * @param string $nodeId
     * @return bool
     */
    abstract public function nodeExists($nodeId);

    /**
     * Returns the node identified by the ID $nodeId.
     *
     * @param string $nodeId
     * @throws Iml_Tree_Exception if there is no node with ID $nodeId
     * @return Iml_Tree_Node
     */
    public function fetchNodeById($nodeId)
    {
        if (!$this->nodeExists($nodeId)) {
            throw new Iml_Tree_Exception('The node with the id ' . $nodeId . ' is unknown.');
        }
        $nodeClassName = $this->_properties['nodeClassName'];
        $node = new $nodeClassName($this, $nodeId);

        return $node;
    }

    /**
     * Returns all the children of the node with ID $nodeId.
     *
     * @param string $nodeId
     * @return Iml_Tree_NodeList
     */
    abstract public function fetchChildren($nodeId);

    /**
     * Returns the parent node of the node with ID $nodeId.
     *
     * @param string $nodeId
     * @return Iml_Tree_Node
     */
    abstract public function fetchParent($nodeId);

    /**
     * Returns all the nodes in the path from the root node to the node with ID
     * $nodeId, including those two nodes.
     *
     * @param string $nodeId
     * @return Iml_Tree_NodeList
     */
    abstract public function fetchPath($nodeId);

    /**
     * Alias for fetchSubtreeDepthFirst().
     *
     * @param string $nodeId
     * @return Iml_Tree_NodeList
     */
    abstract public function fetchSubtree($nodeId);

    /**
     * Returns the node with ID $nodeId and all its children, sorted according to
     * the {@link http://en.wikipedia.org/wiki/Breadth-first_search Breadth-first sorting}
     * algorithm.
     *
     * @param string $nodeId
     * @return Iml_Tree_NodeList
     */
    abstract public function fetchSubtreeBreadthFirst($nodeId);

    /**
     * Returns the node with ID $nodeId and all its children, sorted according to
     * the {@link http://en.wikipedia.org/wiki/Depth-first_search Depth-first sorting}
     * algorithm.
     *
     * @param string $nodeId
     * @return Iml_Tree_NodeList
     */
    abstract public function fetchSubtreeDepthFirst($nodeId);

    /**
     * Returns the number of direct children of the node with ID $nodeId.
     *
     * @param string $nodeId
     * @return int
     */
    abstract public function getChildCount($nodeId);

    /**
     * Returns the number of children of the node with ID $nodeId, recursively.
     *
     * @param string $nodeId
     * @return int
     */
    abstract public function getChildCountRecursive($nodeId);

    /**
     * Returns the distance from the root node to the node with ID $nodeId.
     *
     * @param string $nodeId
     * @return int
     */
    abstract public function getPathLength($nodeId);

    /**
     * Returns whether the node with ID $nodeId has children.
     *
     * @param string $nodeId
     * @return bool
     */
    abstract public function hasChildNodes($nodeId);

    /**
     * Returns whether the node with ID $childId is a direct child of the node
     * with ID $parentId.
     *
     * @param string $childId
     * @param string $parentId
     * @return bool
     */
    abstract public function isChildOf($childId, $parentId);

    /**
     * Returns whether the node with ID $childId is a direct or indirect child
     * of the node with ID $parentId.
     *
     * @param string $childId
     * @param string $parentId
     * @return bool
     */
    abstract public function isDescendantOf($childId, $parentId);

    /**
     * Returns whether the nodes with IDs $child1Id and $child2Id are siblings
     * (ie, they share the same parent).
     *
     * @param string $child1Id
     * @param string $child2Id
     * @return bool
     */
    abstract public function isSiblingOf($child1Id, $child2Id);

    /**
     * Sets a new node as root node, this also wipes out the whole tree.
     *
     * @param Iml_Tree_Node $node
     * @return void
     */
    abstract public function setRootNode(Iml_Tree_Node &$node);

    /**
     * Returns the root node.
     *
     * @return Iml_Tree_Node
     */
    abstract public function getRootNode();

    /**
     * Adds the node $childNode as child of the node with ID $parentId.
     *
     * @param string $parentId
     * @param Iml_Tree_Node $childNode
     */
    abstract public function addChild($parentId, Iml_Tree_Node &$childNode);

    /**
     * Deletes the node with ID $nodeId from the tree, including all its children.
     *
     * @param string $nodeId
     */
    abstract public function delete($nodeId);

    /**
     * Moves the node with ID $nodeId as child to the node with ID $targetParentId.
     *
     * @param string $nodeId
     * @param string $targetParentId
     */
    abstract public function move($nodeId, $targetParentId);

    /**
     * Copies all the children of node $fromNode to node $toNode recursively.
     *
     * This method copies all children recursively from $fromNode to $toNode.
     * The $fromNode belongs to the $from tree and the $toNode to the $to tree.
     * Data associated with the nodes is copied as well from the store
     * associated with the $from tree to the $to tree.
     *
     * @param Iml_Tree_Abstract $from
     * @param Iml_Tree_Abstract $to
     * @param Iml_Tree_Node     $fromNode
     * @param Iml_Tree_Node     $toNode
     * @return void
     */
    private static function _copyChildren(Iml_Tree_Abstract $from, 
                                          Iml_Tree_Abstract $to, 
                                          Iml_Tree_Node $fromNode, 
                                          Iml_Tree_Node $toNode)
    {
        $nodeClassName = $this->_properties['nodeClassName'];
        $children = $fromNode->fetchChildren();

        foreach (new Iml_Tree_Nodelist_Iterator($from, $children, true) as $childNode) {
            $fromChildNode = $from->fetchNodeById($childNode->id);
            $toChildNode = new $nodeClassName($this, $childNode->id, $childNode->data);
            $toNode->addChild($toChildNode);
            self::_copyChildren($from, $to, $fromChildNode, $toChildNode);
        }
    }

    /**
     * Copies the tree in $from to the empty tree in $to.
     *
     * This method copies all the nodes, including associated data from the
     * used data store, from the tree $from to the tree $to.  Because this
     * function uses internally setRootNode() the target tree will be cleared
     * out automatically. The method will not check whether the $from and $to
     * trees share the same database table or data store, so make sure they are
     * different to prevent unexpected behavior.
     *
     * @param Iml_Tree_Abstract $from
     * @param Iml_Tree_Abstract $to
     * @return void
     */
    public static function copy(Iml_Tree_Abstract $from, Iml_Tree_Abstract $to)
    {
        $fromRootNode = $from->getRootNode();
        $to->setRootNode(new Iml_Tree_Node($to, $fromRootNode->id, $fromRootNode->data));
        $toRootNode = $to->getRootNode();
        self::copyChildren($from, $to, $fromRootNode, $toRootNode);
    }
}