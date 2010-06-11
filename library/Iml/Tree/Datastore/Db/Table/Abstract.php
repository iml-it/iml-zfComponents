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
 * @subpackage Datastore
 * @copyright  Copyright (c) 2007-2008 Institute for Medical Education, 
 *             University of Bern (http://www.iml.unibe.ch)
 * @author     Michael Rolli <michael.rolli@iml.unibe.ch>
 * @license    http://creativecommons.org/licenses/by-sa/2.5/ch/     CC-By-Sa
 * @version    $Id$
 */

/**
 * Iml_Tree_Datastore_Db_Interface
 */
require_once 'Iml/Tree/Datastore/Interface.php';

/**
 * Iml_Tree_Datastore_Exception
 */
require_once 'Iml/Tree/Datastore/Exception.php';

/**
 * Zend_Db_Table_Abstract
 */
require 'Zend/Db/Table/Abstract.php';

/**
 * Iml_Tree_Datastore_Db_Table is an abstract class of a tree node
 * data store that inherits from Zend_Db_Table_Abstract.
 * 
 * @category   Iml
 * @package    Iml_Tree
 * @subpackage Datastore
 * @copyright  Copyright (c) 2007-2008 Institute for Medical Education, 
 *             University of Bern (http://www.iml.unibe.ch)
 * @author     Michael Rolli <michael.rolli@iml.unibe.ch>
 * @license    http://creativecommons.org/licenses/by-sa/2.5/ch/     CC-By-Sa
 */
abstract class Iml_Tree_Datastore_Db_Table_Abstract extends Zend_Db_Table_Abstract implements Iml_Tree_Datastore_Interface
{
    /**
     * Init method to do some sanity checks.
     */
    public function init()
    {
        if (count($this->_primary) > 1) {
            throw new Iml_Tree_Datastore_Exception('Compound primary keys are not supported');
        }
    }

    /**
     * Deletes the data for the node $node from the data store.
     *
     * @param Iml_Tree_Node $node
     * @return void
     */
    public function deleteDataForNode(Iml_Tree_Node $node)
    {
        $this->delete($this->getAdapter()->quoteInto(current($this->_primary) . ' = ?', $node->id));
    }

    /**
     * Deletes the data for all the nodes in the node list $nodeList.
     *
     * @param Iml_Tree_Nodelist $nodeList
     * @return void
     */
    public function deleteDataForNodes(Iml_Tree_Nodelist $nodeList)
    {
        foreach (array_keys($nodeList->nodes) as $nodeId) {
            $this->delete($this->getAdapter()->quoteInto(current($this->_primary) . ' = ?', $nodeId));
        }
    }

    /**
     * Deletes the data for all the nodes in the store.
     */
    public function deleteDataForAllNodes()
    {
        $tableSpec = ($this->_schema ? $this->_schema . '.' : '') . $this->_name;
        $this->getAdapter()->delete($tableSpec);
    }

    /**
     * Retrieves the data for the node $node from the data store and assigns it
     * to the node's 'nodedata' property.
     *
     * @param Iml_Tree_Node $node
     * @return void
     * @throws Iml_Tree_Datastore_Exception if no data is available for node
     */
    public function fetchDataForNode(Iml_Tree_Node &$node)
    {
        // check for a new unsaved node
        if (!$node->id) {
            $row = $this->fetchNew();
        } else {
            $row = $this->fetchRow(array(current($this->_primary) . ' = ?' => $node->id));
            if (!$row) {
                throw new Iml_Tree_Datastore_Exception('Datastore is missing data for the node ' . $node->id);
            }
        }
        $nodeData = $row->toArray();
        unset($nodeData[current($this->_primary)]);
        $node->data = $nodeData;
    }

    /**
     * This method *tries* to fetch the data for all the nodes in the node list
     * $nodeList and assigns this data to the nodes' 'data' properties.
     *
     * @param Iml_Tree_Nodelist $nodeList
     * @return void
     */
    public function fetchDataForNodes(Iml_Tree_Nodelist $nodeList)
    {
        foreach ($nodeList->nodes as $node) {
            if ($node->dataFetched === false) {
                $this->fetchDataForNode($node);
            }
        }
    }

    /**
     * Stores the data in the node to the data store.
     *
     * @param Iml_Tree_Node $node
     * @return void
     * @throws Iml_Tree_Datastore_Exception if data could not be saved
     */
    public function storeDataForNode(Iml_Tree_Node $node)
    {
        // first we check if there is data for this node
        $row = $this->fetchRow(array(current($this->_primary) . ' = ?' => $node->id));
        if (!$row) {
            $row = $this->createRow(array(current($this->_primary) => $node->id));
        }
        // Add data
        $row->setFromArray($node->data);
        try {
            $row->save();
        } catch (Zend_Exception $e) {
            throw new Iml_Tree_Datastore_Exception('Data could not be saved for node ' . $node->id . ': ' . $e->getMessage());
        }
        $node->dataStored = true;
    }

}
