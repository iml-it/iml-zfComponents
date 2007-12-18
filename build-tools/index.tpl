<?php

if(!file_exists('./project.xml')) {
    die('No project.xml can be found, check your build.xml');
}

$project = new DOMDocument();
$project->load('./project.xml');
$xpath = new DOMXPath($project);
$entries = $xpath->query('//svninfo/property')->item(0)->nodeValue;
$simple = new SimpleXMLElement(file_get_contents('./project.xml'));

$action = isset($_GET['action']) ? $_GET['action'] : 'overview';

include '../header.php';

switch ($action) {
    case 'overview':
        echo '<h2>SVN Summary</h2>';
        echo '<div class="svn-summary">';
        echo getChangelog(5);
        echo '</div>';
        echo '<hr />';
        echo  file_get_contents('reports/phpcs-brief.html');
        echo '<hr />';
        echo  file_get_contents('reports/phpunit2-brief.html');
        break;
    case 'unittests':
        echo  file_get_contents('reports/phpunit2-noframes.html');
        break;
    case 'codesniffer':
        echo  file_get_contents('reports/phpcs-details.html');
        break;
    case 'apidoc':
        if (file_exists('./apidoc/index.html')) {
            echo '<iframe src="apidoc/index.html" width="100%" height="87%"></iframe>';
        } else {
            echo '<h2>No apidoc built.</h2>';
        }
        break;
    default:
        echo  'unknown view';
}

if($action != 'apidoc') {
    include '../footer.php';
}

function getDateTime($svndate) {
    $parts = explode(' ', $svndate);
    $date = explode('-', $parts[0]);
    $time = explode(':', $parts[1]);
    $timestamp = mktime($time[0], $time[1], $time[2], $date[1], $date[2], $date[0]);
    return strftime ("%a, %d %b %Y %H:%M:%S %Z", $timestamp);
}

function getChangelog($num=0) {
    global $simple;
    $content = '<ul>';
    $logentries = $simple->xpath('//svnlog/entry');
    $numLogentries = count($logentries);
    if($num > 0) {
        $numLogentries = $num;
    }
    for($i=0; $i<$numLogentries; $i++) {
        $content.= '<li><p>';
        $content.= 'Revision <strong>' . $logentries[$i]->revision . '</strong> ';
        $content.= 'by <strong>' . $logentries[$i]->author . '</strong> ';
        $content.= 'at ' . strftime("%a, %d %b %Y %H:%M:%S %Z", strtotime($logentries[$i]->date));
        $content.= '</p><p class="message">' . $logentries[$i]->msg . '</p></li>';
    }
    $content.= '</ul>';
    return $content;
}