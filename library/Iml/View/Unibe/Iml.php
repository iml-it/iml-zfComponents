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
        $this->adressfuss = 'Universit&auml;t Bern | Institut f&uuml;r Medizinische Lehre | Inselspital 37a | CH-3010 Bern | +41 (0)31 632 35 73';
        $this->footer = '&copy; Universit&auml;t Bern&#160;' . date('d.m.Y') . '&#160;&#124;&#160;<a href="http://www.iml.unibe.ch/metanavigation/impressum/" onfocus="blurLink(this);">Impressum</a>';
        parent::__construct($config);
    }

    public function render($name) {
        $this->breadcrumb = $this->getBreadCrumbNavigationString($this->language) . $this->breadcrumb;
        if (isset($this->metanavigation)) {
            $this->metanavigation = $this->getMetanavigation($this->language) . $this->metanavigation;
        } else {
            $this->metanavigation = $this->getMetanavigation($this->language);
        }
        return parent::render($name);
    }

    private function getBreadCrumbNavigationString($lang) {
        switch ($lang) {
            case 'de':
                return '<a href="http://www.unibe.ch/">home universit&auml;t</a> &gt; <a href="http://www.medizin.unibe.ch/">medizinische fakult&auml;t</a> &gt; <a href="http://www.iml.unibe.ch/">institut für medizinische lehre</a>&#160;>&#160;';
                break;
            case 'fr':
                return '<a href="http://www.unibe.ch/">universit&eacute;</a> &gt; <a href="http://www.medizin.unibe.ch/">facult&eacute; de m&eacute;dicine</a> &gt; <a href="http://www.iml.unibe.ch/">institut d\'enseignement m&eacute;dical</a>&#160;>&#160;';
                break;
            case 'en':
                return '<a href="http://www.unibe.ch/">home university</a> &gt; <a href="http://www.medizin.unibe.ch/">faculty of medicine</a> > <a href="http://www.iml.unibe.ch/">institute of medical education</a>&#160;>&#160;';
                break;
            default:
                return '';
                break;
        }
    }

    private function getMetanavigation($lang) {
        switch ($lang) {
            case 'de':
                return '<a href="http://www.iml.unibe.ch/metanavigation/kontakt/" onfocus="blurLink(this);">Kontakt</a>&#160;&#124;&#160;<a href="http://www.iml.unibe.ch/metanavigation/lageplan/" onfocus="blurLink(this);">Lageplan</a>&#160;&#124;&#160;<a href="http://www.iml.unibe.ch/metanavigation/sitemap/" onfocus="blurLink(this);">Sitemap</a>&#160;&#124;&#160;';
                break;
            case 'fr':
                return '<a href="http://www.iml.unibe.ch/fr/metanavigation/contact/" onfocus="blurLink(this);">Contact</a>&#160;&#124;&#160;<a href="http://www.iml.unibe.ch/fr/metanavigation/lageplan/" onfocus="blurLink(this);">Plan de situation</a>&#160;&#124;&#160;<a href="http://www.iml.unibe.ch/fr/metanavigation/sitemap/" onfocus="blurLink(this);">Sitemap</a>&#160;&#124;&#160;';
                break;
            case 'en':
                return '<a href="http://www.iml.unibe.ch/en/metanavigation/contact/" onfocus="blurLink(this);">Contact</a>&#160;&#124;&#160;<a href="http://www.iml.unibe.ch/en/metanavigation/location-map/" onfocus="blurLink(this);">Where to find us</a>&#160;&#124;&#160;<a href="http://www.iml.unibe.ch/en/metanavigation/sitemap/" onfocus="blurLink(this);">Sitemap</a>&#160;&#124;&#160;';
                break;
            default:
                return '';
                break;
        }
    }
}
