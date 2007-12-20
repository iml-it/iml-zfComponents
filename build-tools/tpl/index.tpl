<?php

if(!file_exists('./project.xml')) {
    die('No project.xml can be found, check your build.xml');
}

require_once '../auth.php';
$auth->setUsername();
if(!$auth->hasReadAccess('iml-zfComponents', '/')) {
    die('You don\'t have access to this repository');
}

$project = new SimpleXMLElement(file_get_contents('./project.xml'));

$action = isset($_GET['action']) ? $_GET['action'] : 'overview';

?>
<html>
    <head>
        <title><?php echo $project->project[0]['title']; ?></title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
        <link type="text/css" rel="stylesheet" href="../css/styles.css"/>
    </head>
    <body>
        <div id="container">
            <h1><a href="index.php">phpUnderControl</a></h1>

            <div id="info">
                <a id="changelog">Revision
                    <strong><?php $nodelist = $project->xpath("//svninfo/property[@name = 'Revision']"); echo $nodelist[0]; ?></strong>
                    <?php echo getChangelog(15); ?>
                </a>
                <br />
                By <strong><?php $nodelist = $project->xpath("//svninfo/property[@name = 'Last Changed Author']"); echo $nodelist[0]; ?></strong>
                <br />
                At <?php $nodelist = $project->xpath("//svninfo/property[@name = 'Last Changed Date']"); echo getDateTime($nodelist[0]); ?>
                <br />
            </div>

            <ul class="tabnavigation">
                <li<?php echo ($action=='overview') ? ' class="selected"' : '' ?>><a href="index.php">Overview</a></li>
                <li<?php echo ($action=='unittests') ? ' class="selected"' : '' ?>><a href="index.php?action=unittests">Tests</a></li>
                <li<?php echo ($action=='codesniffer') ? ' class="selected"' : '' ?>><a href="index.php?action=codesniffer">CodeSniffer</a></li>
                <li<?php echo ($action=='apidoc') ? ' class="selected"' : '' ?>><a href="index.php?action=apidoc">API Documentation</a></li>
            </ul>

<?php
switch ($action) {
    case 'overview':
        echo '<h2>SVN Summary</h2>';
        echo '<div class="svn-summary">';
        echo getChangelog(5);
        echo '</div>';
        echo '<hr />';
        if (file_exists('reports/phpcs-brief.html')) {
            echo  file_get_contents('reports/phpcs-brief.html');
        } else {
            echo '<h2>This project doesn\'t do code sniffing</h2>';
        }
        echo '<hr />';
        if (file_exists('reports/phpunit2-brief.html')) {
            echo  file_get_contents('reports/phpunit2-brief.html');
        } else {
            echo "<h2>This project doesn't have tests.</h2>";
        }
        break;
    case 'unittests':
        if (file_exists('reports/phpunit2-noframes.html')) {
            echo  file_get_contents('reports/phpunit2-noframes.html');
        } else {
            echo "<h2>This project doesn't have tests.</h2>";
        }
        break;
    case 'codesniffer':
        if (file_exists('reports/phpcs-details.html')) {
            echo  file_get_contents('reports/phpcs-details.html');
        } else {
            echo '<h2>This project doesn\'t do code sniffing</h2>';
        }
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
?>
            <div id="footer">
                Inspired by <a href="http://www.phpundercontrol.org">phpUnderControl</a>
                by <a href="http://www.manuel-pichler.de/">Manuel Pichler</a>. Reduced and adapted
                by <a href="http://www.iml.unibe.ch/">Michael Rolli</a>., IML
            </div>
        </div>
    </body>
</html>
<?php
}

function getDateTime($svndate) {
    $parts = explode(' ', $svndate);
    $date = explode('-', $parts[0]);
    $time = explode(':', $parts[1]);
    $timestamp = mktime($time[0], $time[1], $time[2], $date[1], $date[2], $date[0]);
    return strftime ("%a, %d %b %Y %H:%M:%S %Z", $timestamp);
}

function getChangelog($num=0) {
    global $project;
    $content = '<ul>';
    $logentries = $project->xpath('//svnlog/entry');
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
