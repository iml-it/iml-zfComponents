<?php

require_once('Zend/View.php');

abstract class Iml_View_Unibe_Abstract extends Zend_View
{
    protected $language = 'de';

    public function __construct($config = array()) {
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
        $this->searchButtonText = $this->getSearchButtonText($this->language);
        if(!isset($this->pagetitle)) $this->pagetitle = 'no pagetitle set';
        if(!isset($this->meta)) $this->meta = '';
        if(!isset($this->cssfiles)) $this->cssfiles = '';
        if(!isset($this->identityText)) $this->identityText = 'no identity text set';
        if(!isset($this->breadcrumb)) $this->breadcrumb = 'no breadcrumb navigation set';
        if(!isset($this->globalnavigation)) $this->globalnavigation = $this->getGlobalNavigationString($this->language);
        if(!isset($this->metanavigation)) $this->metanavigation = '';
        if(!isset($this->localnavigation)) $this->localnavigation = 'no localnavigation set';
        if(!isset($this->maincontent)) $this->maincontent = 'no maincontent set';
        if(!isset($this->servicecontent)) $this->servicecontent = 'no servicecontent set';
        if(!isset($this->adressfuss)) $this->adressfuss = 'no adressfuss set';
        if(!isset($this->footer)) $this->footer = 'no footer set';

        $this->cleanLocalnavigation();

        return parent::render($name);
    }

    public function createCSSMarkup($file, $media='all') {
        return '<link rel="stylesheet" href="' . $file . '" type="text/css" media="' . $media . '" />';
    }

    public function cleanLocalnavigation() {
        $this->localnavigation = preg_replace('/(<\/?[^>]*>)\s+(<\/?\w[^>]*>)/u', '\1\2', $this->localnavigation);;
    }

    public function setLanguage($lang) {
        if (!in_array($lang, array('de', 'en', 'fr'))) {
            throw new RuntimeException('Unsupported language ' . $lang);
        }
        $this->language = $lang;
    }

    private function getSearchButtonText($lang) {
        switch ($lang) {
            case 'de':
                return 'Suchen';
                break;
            case 'fr':
                return 'Chercher';
                break;
            case 'en':
                return 'Search';
                break;
            default:
                return 'Suchen';
                break;
        }
    }

    private function getGlobalNavigationString($lang) {
        switch ($lang) {
            case 'de':
                return '<ul id="udm">
                    <li id="Studium"><a href="http://www.unibe.ch/studium/">Studium</a></li>
                    <li id="Campus"><a href="http://www.unibe.ch/campus/">| Campus</a></li>
                    <li id="Bibliotheken"><a href="http://www.unibe.ch/bibliotheken/">| Bibliotheken</a></li>
                    <li id="Forschung"><a href="http://www.unibe.ch/forschung/">| Forschung</a></li>
                    <li id="Organisation"><a href="http://www.unibe.ch/organisation/">| Organisation</a></li>
                    <li id="Arbeiten"><a href="http://www.unibe.ch/arbeiten/">| Arbeiten an der Uni</a></li>
                    <li id="Oeffentlichkeit"><a href="http://www.unibe.ch/oeffentlichkeit/">| &Ouml;ffentlichkeit</a></li>
                 </ul>';
                break;
            case 'fr':
                return '<ul id="udm">
                    <li id="Studium"><a href="etudes/index.html">Etudier</a></li>
                    <li id="Bibliotheken"><a href="http://www.unibe.ch/fra/biblio.html">| Biblioth&egrave;ques </a></li>
                    <li id="Forschung"><a href="http://www.unibe.ch/fra/recherche.html">| Recherche </a></li>
                    <li id="Oeffentlichkeit"><a href="http://www.unibe.ch/fra/portrait/index.html">| Portrait </a></li>
                </ul>';
                break;
            case 'en':
                return '<ul id="udm">
                    <li id="Studium"><a href="http://www.unibe.ch/eng/studies/index.html">Studying</a></li>
                    <li id="Bibliotheken"><a href="http://www.unibe.ch/eng/libraries.html">| Libraries</a></li>
                    <li id="Forschung"><a href="http://www.unibe.ch/eng/research.html">| Research</a></li>
                    <li id="Oeffentlichkeit"><a href="http://www.unibe.ch/eng/about/index.html">| About us</a></li>
                </ul>';
                break;
            default:
                return '';
                break;
        }
    }
}
