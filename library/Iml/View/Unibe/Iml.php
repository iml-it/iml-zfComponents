<?php

require_once('Iml/View/Unibe/Abstract.php');

class Iml_View_Unibe_Iml extends Iml_View_Unibe_Abstract
{
    public function __construct($config = array() ) {
        $this->pagetitle = 'Institu für Medizinische Lehre';
        $this->cssfiles = '        <link href="css/unibe_iml.css" rel="stylesheet" type="text/css"/>
        <!--[if lte IE 7]>
        <link href="css/explorer/iehacks_med.css" rel="stylesheet" type="text/css" />
        <![endif]-->';
        $this->identityText = 'Institut für Medizinische Lehre';
        $this->breadcrumb = '<a href="http://www.medizin.unibe.ch">medizinische fakult&auml;t</a> > <a href="http://www.iml.unibe.ch">institut für medizinische lehre</a>&#160;>&#160;';
        $this->metanavigation = '<a href="http://www.iml.unibe.ch/metanavigation/kontakt/" onfocus="blurLink(this);">Kontakt</a>&#160;&#124;&#160;<a href="http://www.iml.unibe.ch/metanavigation/lageplan/" onfocus="blurLink(this);">Lageplan</a>&#160;&#124;&#160;<a href="http://www.iml.unibe.ch/metanavigation/sitemap/" onfocus="blurLink(this);">Sitemap</a>&#160;&#124;&#160;';
        $this->adressfuss = 'Universit&auml;t Bern | Institut f&uuml;r Medizinische Lehre | Inselspital 37a | CH-3010 Bern | +41 (0)31 632 35 73';
        $this->footer = '&copy; Universit&auml;t Bern&#160;' . date('d.m.Y') . '&#160;&#124;&#160;<a href="http://www.iml.unibe.ch/metanavigation/impressum/" onfocus="blurLink(this);">Impressum</a>';
        parent::__construct($config);
    }
}
