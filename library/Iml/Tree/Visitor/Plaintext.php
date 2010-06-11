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

/**
 * An implementation of the Iml_Tree_Visitor_Interface that generates a
 * plain text representation of a tree structure.
 * 
 * @category   Iml
 * @package    Iml_Tree
 * @subpackage Visitor
 * @copyright  Copyright (c) 2007-2008 Institute for Medical Education, 
 *             University of Bern (http://www.iml.unibe.ch)
 * @author     Michael Rolli <michael.rolli@iml.unibe.ch>
 * @license    http://creativecommons.org/licenses/by-sa/2.5/ch/     CC-By-Sa
 */
class Iml_Tree_Visitor_Plaintext implements Iml_Tree_Visitor_Interface
{
    /**
     * Represents the ASCII symbol set.
     */
    const SYMBOL_ASCII = 1;

    /**
     * Represents the UTF-8 symbol set.
     */
    const SYMBOL_UTF8 = 2;

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

    /**
     * Constructs a new Iml_Tree_Visitor_Plaintext visualizer using $symbolCharset
     * as character set.
     *
     * This class only supports 'ascii' and 'utf-8' as character sets. Default is
     * 'utf-8'.
     *
     * @see SYMBOL_UTF8
     * @see SYMBOL_ASCII
     *
     * @param int $symbolCharset
     */
    public function __construct($symbolCharset = self::SYMBOL_UTF8)
    {
        switch ($symbolCharset) {
            case self::SYMBOL_ASCII:
                $symbols = array( '|', '+', '-', '+' );
                break;
            case self::SYMBOL_UTF8:
            default:
                $symbols = array( '│', '├', '─', '└' );
        }
        $this->symbolPipe   = $symbols[0];
        $this->symbolTee    = $symbols[1];
        $this->symbolLine   = $symbols[2];
        $this->symbolCorner = $symbols[3];
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
    private function doChildren($id, $level = 0, $levelLast = array())
    {
        $text = '';

        $children = $this->edges[$id];
        $numChildren = count($children);

        $count = 0;
        foreach ($children as $child) {
            $count++;
            for ( $i = 0; $i < $level; $i++ ) {
                if (isset($levelLast[$i]) && $levelLast[$i]) {
                    $text.= '  ';
                } else {
                    $text.= "{$this->symbolPipe} ";
                }
            }
            if ($count != $numChildren) {
                $text .= "{$this->symbolTee}{$this->symbolLine}";
                $levelLast[$level] = false;
            } else {
                $text .= "{$this->symbolCorner}{$this->symbolLine}";
                $levelLast[$level] = true;
            }
            $text .= $child['data']['label'] . "\n";
            $text .= $this->doChildren( $child['id'], $level + 1, $levelLast );
        }
        return $text;
    }

    /**
     * Returns the text representatation of a tree.
     *
     * @return string
     * @ignore
     */
    public function __toString()
    {
        //echo var_dump($this->edges); exit;
        $tree = '(root)';
        $tree.= $this->doChildren(null);
        return $tree;
    }
}