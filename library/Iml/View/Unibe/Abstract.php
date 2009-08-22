<?php

require_once('Zend/View.php');

abstract class Iml_View_Unibe_Abstract extends Zend_View
{
    public function __construct($config = array() ) {
        parent::__construct($config);
    }
    public function render($name) {
        /* set script path if not set */
        if(count($this->getScriptPaths()) == 0) {
	    throw new Exception('You have to set a script path with ->setScriptPath()');
        }
        /* default base template if not set */
        if($name == null) $name = 'base_template_apps.html';

        /* setting default values if property is not set by controller */
        if(!isset($this->pagetitle)) $this->pagetitle = 'no pagetitle set';
        if(!isset($this->meta)) $this->meta = '';
        if(!isset($this->cssfiles)) $this->cssfiles = '';
        if(!isset($this->identityText)) $this->identityText = 'no identity text set';
        if(!isset($this->breadcrumb)) $this->breadcrumb = 'no breadcrumb navigation set';
        if(!isset($this->metanavigation)) $this->metanavigation = '';
        if(!isset($this->localnavigation)) $this->localnavigation = 'no localnavigation set';
        if(!isset($this->maincontent)) $this->maincontent = 'no maincontent set';
        if(!isset($this->servicecontent)) $this->servicecontent = 'no servicecontent set';
        if(!isset($this->adressfuss)) $this->adressfuss = 'no adressfuss set';
        if(!isset($this->footer)) $this->footer = 'no footer set';

        $this->cleanLocalnavigation();

        return parent::render($name);
    }

    function createCSSMarkup($file, $media='all') {
        return '<link rel="stylesheet" href="' . $file . '" type="text/css" media="' . $media . '" />';
    }

    function cleanLocalnavigation() {
        $this->localnavigation = preg_replace('/(<\/?[^>]*>)\s+(<\/?\w[^>]*>)/u', '\1\2', $this->localnavigation);;
    }

}
