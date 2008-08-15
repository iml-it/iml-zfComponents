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
 * Abstract class that implements methods to access tree data using the
 * nested set model.
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
class Iml_Tree_Backend_Db_Nestedset extends Iml_Tree_Backend_Db_Adjacency
{
    /**
     * Returns all the nodes in the path from the root node to the node with ID
     * $nodeId, including those two nodes.
     *
     * @param string $nodeId
     * @return Iml_Tree_NodeList
     */
    public function fetchPath($nodeId)
    {
        $nodeClassName = $this->nodeClassName;
        $nodelistClassName = $this->nodelistClassName;
        
        $info = $this->treeStore->info();
        $name = $info[Zend_Db_Table_Abstract::NAME];
        // SELECT parent.id
        // FROM indexTable as node,
        //      indexTable as parent
        // WHERE
        //     node.lft BETWEEN parent.lft AND parent.rgt
        //     AND
        //     node.id  = $nodeId
        // ORDER BY parent.lft DESC
        $select = $this->treeStore->select();
        $select->from(array('node' => $name), 'parent.id')
               ->join(array('parent' => $name), new Zend_Db_Expr('node.lft BETWEEN parent.lft AND parent.rgt'), array())
               ->where($this->treeStore->getAdapter()->quoteInto('node.id = ?', $nodeId))
               ->order('parent.lft DESC');

        $nodes = $this->treeStore->fetchAll($select);

        $nodeList = new $nodelistClassName();
        foreach ($nodes as $node)
        {
            $nodeList->addNode(new $nodeClassName($this, $node->id));
        }
        return $nodeList;      
    }

    /**
     * Returns the node with ID $nodeId and all its children, sorted according to
     * the {@link http://en.wikipedia.org/wiki/Depth-first_search Depth-first sorting}
     * algorithm.
     *
     * @param string $nodeId
     * @return Iml_Tree_NodeList
     */
    public function fetchSubtreeDepthFirst($nodeId)
    {
        $nodeClassName = $this->nodeClassName;
        $nodelistClassName = $this->nodelistClassName;

        // Fetch parent information
        list($left, $right) = $this->_fetchNodeInformation($nodeId);

        // Fetch subtree
        //   SELECT id
        //   FROM indexTable
        //   WHERE lft BETWEEN $left AND $right
        //   ORDER BY lft
        $select = $this->treeStore->select();
        $select->from($this->treeStore, array('id'))
               ->where(new Zend_Db_Expr(sprintf('lft BETWEEN %s AND %s', $this->treeStore->getAdapter()->quote($left), $this->treeStore->getAdapter()->quote($right))))
               ->order('lft');

        $nodes = $this->treeStore->fetchAll($select);
        $nodeList = new $nodelistClassName();
        foreach ($nodes as $node)
        {
            $nodeList->addNode(new $nodeClassName($this, $node->id));
        }
        return $nodeList;    
    }

    /**
     * Returns the distance from the root node to the node with ID $nodeId.
     *
     * @param string $nodeId
     * @return int
     */
    public function getPathLength($nodeId)
    {
        $path = $this->fetchPath($nodeId);
        return count($path->nodes) - 1;
    }

    /**
     * Returns whether the node with ID $childId is a direct or indirect child
     * of the node with ID $parentId.
     *
     * @param string $childId
     * @param string $parentId
     * @return bool
     */
    public function isDescendantOf($childId, $parentId)
    {
        $path = $this->fetchPath($childId);

        if (isset($path[$parentId]) && ($childId !== $parentId)) {
            return true;
        }
        return false;
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
        // Create new root node
        //   INSERT INTO indexTable
        //   SET parent_id = null,
        //       id = $node->id,
        //       lft = 1, rgt = 2
        $data = array(
            'lft' => 1,
            'rgt' => 2,
        );
        $record = $this->treeStore->fetchNew($data);
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

        // start a transaction explicitly
        $this->treeStore->getAdapter()->beginTransaction();

        try {
                
            // Fetch parent's information
            list($left, $right, $width) = $this->_fetchNodeInformation($parentId);
    
            // Update left and right values to account for new subtree
            $this->_updateNestedValuesForSubtreeAddition($right, 2);
    
            // Add new node
            if ($width == 2) {
                // parent node is leaf and has therefore no children
                $newLeft = $left + 1;
                $newRight = $left + 2;
            } else {
                // parent node has already children
                $newLeft = $right;
                $newRight = $right + 1;
            }
    
            // INSERT INTO indexTable
            // SET parent_id = $parentId,
            //     pid = $parentId,
            //     lft = $newLeft,
            //     rgt = $newRight
            $data = array(
                'pid' => $parentId,
                'lft' => $newLeft,
                'rgt' => $newRight,
            );
            $record = $this->treeStore->fetchNew($data);
            $recordId = $record->save();
            
            // all went well, commit the changes
            $this->treeStore->getAdapter()->commit();
        } catch (Zend_Db_Exception $e) {
            // transaction failed
            $this->treeStore->getAdapter()->rollBack();
            throw new Iml_Tree_Backend_Db_Exception('AddChild() failed: ' . $e->getMessage() . $e->getTraceAsString());
        }
        // create a new node object of the row saved above and store data for it
        $node = new $nodeClassName($this, $recordId, $node->data);
        $this->dataStore->storeDataForNode($node);
        
    }

    /**
     * Deletes the node with ID $nodeId from the tree, including all its children.
     *
     * @param string $nodeId
     */
    public function delete($nodeId)
    {
        // start a transaction explicitly
        $this->treeStore->getAdapter()->beginTransaction();
        
        // try renumbering
        try {
                
            // Delete all data for the deleted nodes
            $nodeList = $this->fetchSubtreeDepthFirst($nodeId);
            $this->dataStore->deleteDataForNodes($nodeList);
    
            // Fetch node information
            list($left, $right, $width) = $this->_fetchNodeInformation($nodeId);
    
            // DELETE FROM indexTable
            // WHERE lft BETWEEN $left and $right
            $where = new Zend_Db_Expr(sprintf('lft BETWEEN %s AND %s', $this->treeStore->getAdapter()->quote($left), $this->treeStore->getAdapter()->quote($right)));
            $this->treeStore->delete($where);
    
            // Update the left and right values to account for the removed subtree
            $this->_updateNestedValuesForSubtreeDeletion($right, $width);
            
            // all went well, commit the changes
            $this->treeStore->getAdapter()->commit();
        } catch (Zend_Db_Exception $e) {
            // transaction failed
            $this->treeStore->getAdapter()->rollBack();
            throw new Iml_Tree_Backend_Db_Exception('delete() failed: ' . $e->getMessage() . $e->getTraceAsString());
        }
    }

    /**
     * Moves the node with ID $nodeId as child to the node with ID $targetParentId.
     *
     * @param string $nodeId
     * @param string $targetParentId
     */
    public function move($nodeId, $targetParentId)
    {
        // start a transaction explicitly
        $this->treeStore->getAdapter()->beginTransaction();
        
        // try renumbering
        try {
            // Get the nodes that are to be moved in the subtree
            $nodeIds = array();
            foreach ($this->fetchSubtreeDepthFirst($nodeId)->nodes as $node ) {
                $nodeIds[] = $node->id;
            }
    
            // Update parent ID for the node
            //   UPDATE indexTable
            //   SET pid = $targetParentId
            //   WHERE id = $nodeId
            $data = array(
                'pid' => $db->quote($targetParentId),
            );
            $where = $this->treeStore->getAdapter()->quoteInto('id = ?', $nodeId);
            $this->treeStore->update($data, $where);
    
            // Fetch node information
            list($origLeft, $origRight, $origWidth) = $this->_fetchNodeInformation($nodeId);
    
            // Update the nested values to account for the moved subtree (delete part)
            $this->_updateNestedValuesForSubtreeDeletion($origRight, $origWidth);
    
            // Fetch node information
            list($targetParentLeft, $targetParentRight, $targerParentWidth) = $this->_fetchNodeInformation($targetParentId);
    
            // Update the nested values to account for the moved subtree (addition part)
            $this->_updateNestedValuesForSubtreeAddition($targetParentRight, $origWidth, $nodeIds);
    
            // Update nodes in moved subtree
            $adjust = $targetParentRight - $origLeft;
    
            // UPDATE indexTable
            // SET lft = lft + $adjust,
            //     rgt = rgt + $adjust
            // WHERE id in $nodeIds
            $data = array(
                'lft' => new Zend_Db_Expr($hits->treeStore->getAdapter()->quoteInto('lft + ?', $adjust)),
                'rgt' => new Zend_Db_Expr($hits->treeStore->getAdapter()->quoteInto('rgt + ?', $adjust)),
            );
            $where = new Zend_Db_Expr('id IN (' . implode(',', $nodeIds) . ')');
            $this->treeStore->update($data, $where);

            // all went well, commit the changes
            $this->treeStore->getAdapter()->commit();
        } catch (Zend_Db_Exception $e) {
            // transaction failed
            $this->treeStore->getAdapter()->rollBack();
            throw new Iml_Tree_Backend_Db_Exception('move() failed: ' . $e->getMessage() . $e->getTraceAsString());
        }
    }
    
    /**
     * Updates the left and right values of the nodes that are added while
     * adding a whole subtree as child of a node.
     *
     * The method does not update nodes where the IDs are in the $excludedIds
     * list.
     *
     * @param int $right
     * @param int $width
     * @param array(string) $excludedIds
     */
    protected function _updateNestedValuesForSubtreeAddition($right, $width, $excludedIds = array() )
    {
            // Move all the right values + $width for nodes where the the right value >=
            // the parent right value:
            //   UPDATE indexTable
            //   SET rgt = rgt + $width
            //   WHERE rgt >= $right
            $data = array(
                'rgt' => new Zend_Db_Expr($this->treeStore->getAdapter()->quoteInto('rgt + ?', $width)),
            );
            $where = array();
            $where[] = $db->quoteInto('rgt >= ?', $right);
            if (count($excludedIds)) {
                $where[] = new Zend_Db_Expr('id NOT IN (' . implode(',', $excludedIds) . ')');
            }
            $this->treeStore->update($data, $where);
    
            // Move all the left values + $width for nodes where the the right value >=
            // the parent left value
            //   UPDATE indexTable
            //   SET lft = lft + $width
            //   WHERE lft >= $right
            $data = array(
                'lft' => new Zend_Db_Expr($db->quoteInto('lft + ?', $width)),
            );
            $where = array();
            $where[] = $db->quoteInto('lft >= ?', $right);
            if (count($excludedIds)) {
                $where[] = new Zend_Db_Expr('id NOT IN (' . implode(',', $excludedIds) . ')');
            }
            $this->treeStore->update($data, $where);
    }

    /**
     * Updates the left and right values in case a subtree is deleted.
     *
     * @param int $right
     * @param int $width
     */
    protected function _updateNestedValuesForSubtreeDeletion($right, $width)
    {

        // Move all the right values + $width for nodes where the the right
        // value > the parent right value
        //   UPDATE indexTable
        //   SET rgt = rgt - $width
        //   WHERE rgt > $right
        $data = array(
            'rgt' => new Zend_Db_Expr($this->treeStore->getAdapter()->quoteInto('rgt - ?', $width)),
        );
        $where = $db->quoteInto('rgt > ?', $right);
        $this->treeStore->update($data, $where);

        // Move all the right values + $width for nodes where the the left
        // value > the parent right value
        //   UPDATE indexTable
        //   SET lft = lft - $width
        //   WHERE lft > $right
        $data = array(
            'lft' => new Zend_Db_Expr($this->treeStore->getAdapter()->quoteInto('lft - ?', $width)),
        );
        $where = $db->quoteInto('lft > ?', $right);
        $this->treeStore->update($data, $where);
    }

    /**
     * Returns the left, right and width values for the node with ID $nodeId as an
     * array.
     *
     * The format of the array is:
     * - 0: left value
     * - 1: right value
     * - 2: width value (right - left + 1)
     *
     * @param string $nodeId
     * @return array(int)
     */
    protected function _fetchNodeInformation($nodeId)
    {
        // SELECT lft, rgt, rgt-lft+1 as width
        // FROM indexTable
        // WHERE id = $nodeId
        $row = $this->treeStore->fetchRow(array('id = ?' => $nodeId));
        return array($row->lft, $row->rgt, $row->rgt - $row->lft + 1);
        
    }
}