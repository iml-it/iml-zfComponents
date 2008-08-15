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
 * @author     Michael Rolli <michael@rollis.ch>
 * @license    http://creativecommons.org/licenses/by-sa/2.5/ch/     CC-By-Sa
 * @version    $Id$
 */

/**
 * Iml_Tree_Abstract
 */
require_once 'Iml/Tree/Abstract.php';

/**
 * Iml_Tree_Structurestore_Exception
 */
require_once 'Iml/Tree/Backend/Db/Exception.php';

/**
 * Iml_Tree_Backend_Db_Abstract contains common methods for the different database tree backends.
 * 
 * @property   array of configuration options:
 *             - treeStore: tree backend to use, must be child class
 *                          of Zend_Db_Table_Abstract; or a string denoting
 *                          a class name implementing Zend_Db_Table_Abstract
 *             - dataStore: Datastore backend to use, must implement
 *                          Iml_Tree_Datastore_Interface; or a string denoting
 *                          a class name implementing Zend_Db_Table_Abstract
 * 
 * @category   Iml
 * @package    Iml_Tree
 * @subpackage Backend
 * @copyright  Copyright (c) 2007-2008 Institute for Medical Education, 
 *             University of Bern (http://www.iml.unibe.ch)
 * @author     Michael Rolli <michael@rollis.ch>
 * @license    http://creativecommons.org/licenses/by-sa/2.5/ch/     CC-By-Sa
 */
abstract class Iml_Tree_Backend_Db_Abstract extends Iml_Tree_Abstract
{
    const TREESTORE = 'treeStore';
    const DATASTORE = 'dataStore';

    /**
     * @todo documentation
     */
    public function __construct($config = array())
    {
        foreach ($config as $key => $value) {
            switch ($key) {
                case self::TREESTORE:
                    $this->_properties['treeStore'] = $value;
                    break;
                case self::DATASTORE:
                    $this->_properties['dataStore'] = $value;
                    break;
                default:
                    // ignore unrecognized configuration directive
                    break;
            }
        }
        $this->_setup();
        $this->init();
    }

    /**
     * Turnkey for initialization of a tree and datastore object.
     * Calls other protected methods for individual tasks, to make it easier
     * for a subclass to override parts of the setup logic.
     *
     * @return void
     */
    protected function _setup()
    {
        $this->_setupTreeStore();
        $this->_setupDataStore();
    }

    /**
     * Initalize the tree store
     * 
     * @return void
     * @throw Iml_Tree_Backend_Db_Exception if no valid tree store can be set up
     */
    protected function _setupTreeStore()
    {
        if (!array_key_exists('treeStore', $this->_properties)) {
            throw new Iml_Tree_Backend_Db_Exception('You must specify a treeStore.');
        }
        switch (true) {
            case ($this->treeStore instanceof Zend_Db_Table_Abstract):
                break;
            case (is_string($this->treeStore)):
                try {
                    Zend_Loader::loadClass($this->treeStore);
                    $this->treeStore = new $this->treeStore;
                } catch (Zend_Exception $e) {
                    throw new Iml_Tree_Backend_Db_Exception('Unable to initialize the tree store: ' . $e->getMessage());
                }
                break;
            default:
                throw new Iml_Tree_Backend_Db_Exception('Unable to initialize the tree store: ' . $e->getMessage());
        }
        // @todo test primary key, may not be a compound primary key
        // @todo test columns of table for availablity of specific columns
        return;
    }

    /**
     * Initalize the data store
     * 
     * @return void
     * @throw Iml_Tree_Backend_Db_Exception if no valid data store can be set up
     */
    protected function _setupDataStore()
    {
        switch (true) {
            case ($this->dataStore instanceof Iml_Tree_Datastore_Interface):
                break;
            case (is_string($this->dataStore)):
                try {
                    Zend_Loader::loadClass($this->dataStore);
                    $this->dataStore = new $this->dataStore;
                } catch (Zend_Exception $e) {
                    throw new Iml_Tree_Backend_Db_Exception('Unable to initialize the data store: ' . $e->getMessage());
                }
                break;
            default:
                throw new Iml_Tree_Backend_Db_Exception('Unable to initialize the data store: ' . $e->getMessage());
        }
        return;
    }

    /**
     * Initialize object
     *
     * Called from {@link __construct()} as final step of object instantiation.
     *
     * @return void
     */
    public function init()
    {
    }
    
    /**
     * This method checks whether the node id for a new node
     * is set to null. For database backend the id of a new node
     * must be null as the id is generated on save.
     *
     * @throws Iml_Tree_Exception if the node is not valid.
     * @param string $nodeId
     */
    public function checkNodeId($nodeId)
    {
        // Does not check anything
        if (null !== $nodeId) {
            throw new Iml_Tree_Backend_Db_Exception('nodeId must be null for db-based treeStores.');
        }
    }

    /**
     * Returns whether the node with ID $nodeId exists as tree node.
     *
     * @param string $nodeId
     * @return bool
     */
    public function nodeExists($nodeId)
    {
        return count($this->treeStore->find($nodeId)) ? true : false;
    }

    /**
     * Returns the parent node of the node with ID $id.
     *
     * This method returns null if there is no parent node.
     *
     * @param string $nodeId
     * @return Iml_Tree_Node
     */
    public function fetchParent($nodeId)
    {
        $className = $this->nodeClassName;
        $pid = $this->_getParentId($nodeId);
        return $pid !== null ? new $className($this, $pid) : null;        
    }

    /**
     * Returns the root node.
     *
     * This methods returns null if there is no root node.
     *
     * @return Iml_Tree_Node
     */
    public function getRootNode()
    {
        $className = $this->nodeClassName;
        $row = $this->treeStore->fetchRow('pid IS NULL');
        return ($row) ? new $className($this, $row->id) : null;
    }

    /**
     * Returns the ID of parent of the node with ID $childId.
     *
     * @param string $childId
     * @return string
     */
    protected function _getParentId($nodeId)
    {
        $rowset = $this->treeStore->find($nodeId);
        if (count($rowset) == 0) {
            throw new Iml_Tree_Exception('Node with id ' . $nodeId . 'cannot be found.');
        }
        return $rowset->current()->pid;
    }
}
