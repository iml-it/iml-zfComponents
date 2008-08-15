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
 * @subpackage Backend
 * @copyright  Copyright (c) 2007-2008 Institute for Medical Education, 
 *             University of Bern (http://www.iml.unibe.ch)
 * @author     Michael Rolli <michael.rolli@iml.unibe.ch>
 * @license    http://creativecommons.org/licenses/by-sa/2.5/ch/     CC-By-Sa
 * @version    $Id$
 */

/**
 * Iml_Tree_Backend_Db_Table_Abstract
 */
require_once 'Iml/Tree/Backend/Db/Abstract.php';

/**
 * Abstract class that implements methods to access tree data using the
 * adjacency list model.
 * 
 * Further technical reading:
 * http://dev.mysql.com/tech-resources/articles/hierarchical-data.html
 * 
 * @category   Iml
 * @package    Iml_Tree
 * @subpackage Backend
 * @copyright  Copyright (c) 2007-2008 Institute for Medical Education, 
 *             University of Bern (http://www.iml.unibe.ch)
 * @author     Michael Rolli <michael.rolli@iml.unibe.ch>
 * @license    http://creativecommons.org/licenses/by-sa/2.5/ch/     CC-By-Sa
 */
abstract class Iml_Tree_Backend_Db_Adjacency extends Iml_Tree_Backend_Db_Abstract
{
    /**
     * Returns all the direct children of the node with ID $nodeId.
     *
     * @param string $nodeId
     * @return Iml_Tree_Nodelist
     */
    public function fetchChildren($nodeId)
    {
        $nodeClassName = $this->nodeClassName;
        $nodelistClassName = $this->nodelistClassName;

        $nodeList = new $nodelistClassName();
        foreach ($this->_fetchChildRecords($nodeId) as $record)
        {
            $nodeList->addNode(new $nodeClassName($this, $record->id));
        }
        return $nodeList;
    }

    /**
     * Returns all the nodes in the path from the root node to the node with ID
     * $nodeId, including those two nodes.
     *
     * @param string $nodeId
     * @return Iml_Tree_Nodelist
     */
    public function fetchPath($nodeId)
    {
        
        $nodeClassName = $this->nodeClassName;
        $nodelistClassName = $this->nodelistClassName;

        $nodes = array();
        $nodes[] = new $nodeClassName($this, $nodeId);

        $nodeId = $this->_getParentId($nodeId);

        while ($nodeId != null)
        {
            $nodes[] = new $nodeClassName($this, $nodeId);
            $nodeId = $this->_getParentId($nodeId);
        }

        $nodeList = new $nodelistClassName();
        foreach (array_reverse($nodes) as $node)
        {
            $nodeList->addNode($node);
        }
        return $nodeList;
    }

    /**
     * Returns the node with id $nodeId and all its children, sorted according to
     * the {@link http://en.wikipedia.org/wiki/Depth-first_search Depth-first sorting}
     * algorithm.
     *
     * @param string $nodeId
     * @return Iml_Tree_Nodelist
     */
    public function fetchSubtreeDepthFirst($nodeId)
    {
        $nodeClassName = $this->nodeClassName;
        $nodelistClassName = $this->nodelistClassName;
        
        $nodeList = new $nodelistClassName();
        $nodeList->addNode(new $nodeClassName($this, $nodeId));
        $this->_addChildNodesDepthFirst($nodeList, $nodeId);
        return $nodeList;
    }

    /**
     * Alias for fetchSubtreeDepthFirst().
     *
     * @param string $nodeId
     * @return Iml_Tree_Nodelist
     */
    public function fetchSubtree($nodeId)
    {
        return $this->fetchSubtreeDepthFirst($nodeId);
    }

    /**
     * Returns the node with id $nodeId and all its children, sorted according to
     * the {@link http://en.wikipedia.org/wiki/Breadth-first_search Breadth-first sorting}
     * algorithm.
     *
     * @param string $nodeId
     * @return Iml_Tree_Nodelist
     */
    public function fetchSubtreeBreadthFirst($nodeId)
    {
        $nodeClassName = $this->nodeClassName;
        $nodelistClassName = $this->nodelistClassName;
        
        $nodeList = new $nodelistClassName();
        $nodeList->addNode(new $nodeClassName($this, $nodeId));
        $this->_addChildNodesBreadthFirst($nodeList, $nodeId);
        return $nodeList;
    }

    /**
     * Returns the number of direct children of the node with id $nodeId.
     *
     * @param string $nodeId
     * @return int
     */
    public function getChildCount($nodeId)
    {
        $select = $this->treeStore->select();
        $select->from($this->treeStore, array(new Zend_Db_Expr('count(id)')));
        $select->where('pid = ?', $nodeId);
        $stmt = $select->query();
        return (int) $stmt->fetchColumn(0);
    }

    /**
     * Returns the number of children of the node with id $nodeId, recursively.
     *
     * @param string $nodeId
     * @return int
     */
    public function getChildCountRecursive($nodeId)
    {
        $count = 0;
        $this->_countChildNodes($count, $nodeId);
        return $count;
    }

    /**
     * Returns the distance from the root node to the node with ID $nodeId.
     *
     * @param string $nodeId
     * @return int
     */
    public function getPathLength($nodeId)
    {
        $nodeId = $this->_getParentId($nodeId);
        $length = 0;

        while ($nodeId !== null) {
            $nodeId = $this->_getParentId($nodeId);
            $length++;
        }
        return $length;
    }

    /**
     * Returns whether the node with id $nodeId has children.
     *
     * @param string $nodeId
     * @return bool
     */
    public function hasChildNodes($nodeId)
    {
        return $this->getChildCount($nodeId) > 0;
    }

    /**
     * Returns whether the node with id $childId is a direct child of the node
     * with ID $parentId.
     *
     * @param string $childId
     * @param string $parentId
     * @return bool
     */
    public function isChildOf($childId, $parentId)
    {
        return $parentId === $this->_getParentId($childId);
    }

    /**
     * Returns whether the node with id $childId is a direct or indirect child
     * of the node with id $parentId.
     *
     * @param string $childId
     * @param string $parentId
     * @return bool
     */
    public function isDescendantOf($childId, $parentId)
    {
        $nodeId = $childId;
        do {
            $nodeId = $this->_getParentId($nodeId);
            if ( $parentId === $nodeId ) {
                return true;
            }
        } while ($nodeId !== null);
        return false;
    }

    /**
     * Returns whether the nodes with ids $child1Id and $child2Id are siblings
     * (ie, they share the same parent).
     *
     * @param string $child1Id
     * @param string $child2Id
     * @return bool
     */
    public function isSiblingOf($child1Id, $child2Id)
    {
        $nodeId1 = $this->_getParentId($child1Id);
        $nodeId2 = $this->_getParentId($child2Id);
        return $nodeId1 === $nodeId2 && $child1Id !== $child2Id;
    }

    /**
     * Sets a new node as root node, this also wipes out the whole tree.
     *
     * @param Iml_Tree_Node $node
     */
    public function setRootNode(Iml_Tree_Node &$node)
    {
        $nodeClassName = $this->nodeClassName;

        // first, truncate treeStore and dataStore
        $schema = $this->treeStore->info(Zend_Db_Table_Abstract::SCHEMA);
        $name = $this->treeStore->info(Zend_Db_Table_Abstract::NAME);
        $tableSpec = ($schema ? $schema . '.' : '') . $name;
        $this->treeStore->getAdapter()->query('TRUNCATE ' . $tableSpec);
        $this->dataStore->deleteDataForAllNodes();
        
        // create a new blank row, which automatically gets `pid IS NULL`
        $record = $this->treeStore->fetchNew();
        $recordId = $record->save();
        
        // create a new node object of the row saved above and store data for it
        $node = new $nodeClassName($this, $recordId, $node->data);
        $this->dataStore->storeDataForNode($node);
    }

    /**
     * Adds the node $childNode as child of the node with ID $parentId.
     *
     * @param string $parentId
     * @param Iml_Tree_Node $childNode
     */
    public function addChild($parentId, Iml_Tree_Node &$node)
    {
        $nodeClassName = $this->nodeClassName;

        if (null === $node->id) {
            // a new node
            $record = $this->treeStore->fetchNew();
        } else {
            $record = $this->treeStore->fetchRow(array('id' => $node->id));
        }
        $record->pid = $parentId;
        $recordId = $record->save();

        $node = new $nodeClassName($this, $recordId, $node->data);
        $this->dataStore->storeDataForNode($node);
    }

    /**
     * Deletes the node with id $nodeId from the tree, including all its children.
     *
     * @param string $nodeId
     */
    public function delete($nodeId)
    {
        if (!is_string($nodeId) && !is_int($nodeId)) {
            throw new Iml_Tree_Exception(get_class($this) . '::delete() The first argument should be an string or an integer, the node\'s id');
        }
        $nodeList = $this->fetchSubtree($nodeId);
        $this->_deleteNodes($nodeList);
        $this->dataStore->deleteDataForNodes($nodeList);
    }

    /**
     * Moves the node with id $nodeId as child to the node with id $targetParentId.
     *
     * @param string $nodeId
     * @param string $targetParentId
     */
    public function move($nodeId, $targetParentId)
    {
        $record = $this->treeStore->fetchRow(array('id' => $nodeId));
        $record->pid = $targetParentId;
        $record->save();
    }

    /**
     * Get all the children of the node with ID $nodeId.
     *
     * @param string $nodeId
     * @return Zend_Db_Rowset_Abstract
     */
    protected function _fetchChildRecords($nodeId)
    {
        $select = $this->treeStore->select();
        $select->where('pid = ?', $nodeId);
        return $this->treeStore->fetchAll($select);
    }

    /**
     * Adds the children nodes of the node with ID $nodeId to the
     * Iml_Tree_Nodelist $nodeList according to the
     * {@link http://en.wikipedia.org/wiki/Breadth-first_search Breadth-first sorting}
     * algorithm.
     *
     * @param Iml_Tree_Nodelist $nodeList
     * @param string $nodeId
     */
    protected function _addChildNodesBreadthFirst(Iml_Tree_Nodelist $nodeList, $nodeId)
    {
        $nodeClassName = $this->nodeClassName;

        $records = $this->_fetchChildRecords($nodeId);
        foreach ($records as $record) {
            $nodeList->addNode(new $nodeClassName($this, $record->id));
        }
        foreach ($records as $record) {
            $this->_addChildNodesBreadthFirst($nodeList, $record->id);
        }
    }

    /**
     * Adds the children nodes of the node with ID $nodeId to the
     * Iml_Tree_Nodelist $list according to the
     * {@link http://en.wikipedia.org/wiki/Depth-first_search Depth-first sorting}
     * algorithm.
     *
     * @param Iml_Tree_Nodelist $nodeList
     * @param string $nodeId
     */
    private function _addChildNodesDepthFirst(Iml_Tree_Nodelist $nodeList, $nodeId)
    {
        $nodeClassName = $this->nodeClassName;

        foreach ($this->_fetchChildRecords($nodeId) as $record) {
            $nodeList->addNode(new $nodeClassName($this, $record->id));
            $this->_addChildNodesDepthFirst($nodeList, $record->id);
        }
    }

    /**
     * Adds the number of children with for the node with ID $nodeId nodes to
     * $count, recursively.
     *
     * @param int $count
     * @param string $nodeId
     */
    protected function _countChildNodes(&$count, $nodeId)
    {
        foreach ($this->_fetchChildRecords($nodeId) as $record) {
            $count++;
            $this->_countChildNodes($count, $record->id);
        }
    }

    /**
     * Deletes all nodes in the node list $nodeList.
     *
     * @param Iml_Tree_Nodelist $nodeList
     */
    private function _deleteNodes(Iml_Tree_Nodelist $nodeList)
    {
        foreach ($nodeList as $node) {
            $this->treeStore->delete($this->treeStore->getAdapter()->quoteInto('id = ?', $node->id));
        }
    }
}
