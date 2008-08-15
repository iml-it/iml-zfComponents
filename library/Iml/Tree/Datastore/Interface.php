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
 * Iml_Tree_Datastore_Interface is an interface describing the methods that a 
 * tree data storage module should implement.
 * 
 * @category   Iml
 * @package    Iml_Tree
 * @subpackage Datastore
 * @copyright  Copyright (c) 2007-2008 Institute for Medical Education, 
 *             University of Bern (http://www.iml.unibe.ch)
 * @author     Michael Rolli <michael.rolli@iml.unibe.ch>
 * @license    http://creativecommons.org/licenses/by-sa/2.5/ch/     CC-By-Sa
 */
interface Iml_Tree_Datastore_Interface
{
    /**
     * Deletes the data for the node $node from the data store.
     *
     * @param Iml_Tree_Node $node
     */
    public function deleteDataForNode(Iml_Tree_Node $node);

    /**
     * Deletes the data for all the nodes in the node list $nodeList.
     *
     * @param Iml_Tree_Nodelist $nodeList
     */
    public function deleteDataForNodes(Iml_Tree_Nodelist $nodeList);

    /**
     * Deletes the data for all the nodes in the store.
     */
    public function deleteDataForAllNodes();

    /**
     * Retrieves the data for the node $node from the data store and assigns it
     * to the node's 'data' property.
     *
     * @param Iml_Tree_Node $node
     */
    public function fetchDataForNode(Iml_Tree_Node &$node);

    /**
     * Retrieves the data for all the nodes in the node list $nodeList and
     * assigns this data to the nodes' 'data' properties.
     *
     * @param Iml_Tree_Nodelist $nodeList
     */
    public function fetchDataForNodes(Iml_Tree_Nodelist $nodeList);

    /**
     * Stores the data in the node to the data store.
     *
     * @param Iml_Tree_Node $node
     */
    public function storeDataForNode(Iml_Tree_Node $node);
}
