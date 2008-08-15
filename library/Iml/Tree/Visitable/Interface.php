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
 * @subpackage Visitable
 * @copyright  Copyright (c) 2007-2008 Institute for Medical Education, 
 *             University of Bern (http://www.iml.unibe.ch)
 * @author     Michael Rolli <michael.rolli@iml.unibe.ch>
 * @license    http://creativecommons.org/licenses/by-sa/2.5/ch/     CC-By-Sa
 * @version    $Id$
 */

/**
 * Interface Iml_Tree_Visitable
 * 
 * Interface for visitable tree elements that can be visited
 * by Iml_Tree_Visitor implementations for processing using the
 * Visitor design pattern.
 * 
 * @category   Iml
 * @package    Iml_Tree
 * @subpackage Visitable
 * @copyright  Copyright (c) 2007-2008 Institute for Medical Education,  
 *             University of Bern (http://www.iml.unibe.ch)
 * @author     Michael Rolli <michael.rolli@iml.unibe.ch>
 * @license    http://creativecommons.org/licenses/by-sa/2.5/ch/     CC-By-Sa
 */
interface Iml_Tree_Visitable_Interface
{
    /**
     * Accepts the visitor.
     *
     * @param Iml_Tree_Visitor_Interface $visitor
     */
    public function accept(Iml_Tree_Visitor_Interface $visitor);
}
