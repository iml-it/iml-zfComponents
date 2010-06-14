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
 * @subpackage Visitor
 * @copyright  Copyright (c) 2007-2008 Institute for Medical Education, 
 *             University of Bern (http://www.iml.unibe.ch)
 * @author     Michael Rolli <michael.rolli@iml.unibe.ch>
 * @license    http://creativecommons.org/licenses/by-sa/2.5/ch/     CC-By-Sa
 * @version    $Id$
 */

require_once 'Iml/Tree/Visitor/Interface.php';

/**
 * An implementation of the Iml_Tree_Visitor_Interface that generates an
 * XHTML text representation of a tree structure.
 * 
 * @category   Iml
 * @package    Iml_Tree
 * @subpackage Visitor
 * @copyright  Copyright (c) 2007-2008 Institute for Medical Education, 
 *             University of Bern (http://www.iml.unibe.ch)
 * @author     Michael Rolli <michael.rolli@iml.unibe.ch>
 * @license    http://creativecommons.org/licenses/by-sa/2.5/ch/     CC-By-Sa
 */
class Iml_Tree_Visitor_Dojodata implements Iml_Tree_Visitor_Interface
{
    /**
     * Holds all the edges of the graph.
     *
     * @var array(string=>array(string))
     */
    protected $edges = array();

    /**
     * Holds the root ID
     *
     * @var string
     */
    protected $root = null;
    
    static protected $_serial = 0;

    /**
     * Constructs a new Iml_Tree_Visitor_Xhtml visualizer.
     * 
     * @param array $options xhtml options to set
     */
    public function __construct($options = array())
    {
        
    }
    
    /**
     * Formats a node's data
     * 
     * It is just a simple method, that provide an easy way to change the way
     * on how data is formatted when this class is extended. The data is passed
     * in the $data argument and wether the node should be highlighted is passed
     * in the $highlight argument.
     * 
     * @param mixed $data
     * @param bool $highlight
     * @return string
     */
    protected function formatData($data, $highlight)
    {
        return $data;
    }

    /**
     * Visits the node and sets the the member variables according to the node
     * type and contents.
     *
     * @param Iml_Tree_Visitable_Interface $visitable
     * @return bool
     */
    public function visit(Iml_Tree_Visitable_Interface $visitable)
    {
        if ($visitable instanceof Iml_Tree_Abstract) {
            // do nothing
        }

        if ($visitable instanceof Iml_Tree_Node) {
            if ($this->root === null) {
                $this->root = $visitable->id;
            }

            $parent = $visitable->fetchParent();
            if ($parent) {
                $this->edges[$parent->id][] = array('id' => $visitable->id, 'data' => $visitable->data);
            }
        }
        return true;
    }

    /**
     * Loops over the children of the node with ID $id.
     *
     * This methods loops over all the node's children and adds the correct
     * layout for each node depending on the state that is collected in the
     * $level and $levelLast variables.
     *
     * @param string $id
     * @param int    $level
     * @param array(int=>bool) $levelLast
     *
     * @return string
     */
    private function doChildren($id)
    {
        $container = array();
        if (isset($this->edges[$id])) {
            $children = $this->edges[$id];
    
            foreach ($children as $child) {
                $structure = array();
                $structure['id'] = ++self::$_serial;
                $structure['dbId'] = $child['id'];
                foreach ($child['data'] as $key => $value) {
                    $structure[$key] = $this->formatData($value, false);
                }
                $structure['children'] = $this->doChildren($child['id']);
                if (null === $structure['children']) {
                    unset($structure['children']);
                }
                $container[] = $structure;
            }
            return $container;
        }
    }

    /**
     * Returns the text representatation of a tree.
     *
     * @return string
     * @ignore
     */
    public function __toString()
    {
        throw new Iml_Tree_Exception('__toString is not implemented; instead use toArray()');
    }
    
    public function toArray()
    {
        return $this->doChildren(1);
    }
}
