<?php
/**
 * Starting page of website
 */
define('NICE_PROJECT', true);
require_once 'bin/inc.php';
HtmlGenerator::redirect("sim.php");
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <?php
    HtmlGenerator::GenerateHeaderTags('Startseite');
    ?>
</head>
<body>
<?php
HtmlGenerator::generateNavbar();
?>
</body>
</html>
