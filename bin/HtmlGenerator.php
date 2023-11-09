<?php

if (!defined('NICE_PROJECT')) {
    die('Permission denied.');
}

/**
 * Contains class for generater header-entried and navbar and stuff
 */
class HtmlGenerator
{
    /**
     * @var array[] $js_map defines all necessary JavaScript files to load
     */
    private static $js_map = array(
        array(
            "path" => "jse/jquery-3.2.1",
            "debug" => true
        ),
        array(
            "path" => "jse/popper",
            "debug" => true
        ),
        array(
            "path" => "jse/popper-base",
            "debug" => true
        ),
        array(
            "path" => "jse/popper-lite",
            "debug" => true
        ),
        array(
            "path" => "jse/bootstrap.bundle",
            "debug" => true
        ),
        array(
            "path" => "jse/bootstrap",
            "debug" => true
        ),
        array(
            "path" => "jse/codemirror",
            "debug" => true
        ),
        array(
            "path" => "tjs/helper",
            "debug" => true
        ),
        array(
            "path" => "tjs/main",
            "debug" => true
        ),
        array(
            "path" => "tjs/memory",
            "debug" => true
        ),
        array(
            "path" => "tjs/memoryCell",
            "debug" => true
        ),
        array(
            "path" => "tjs/ButtonExecute",
            "debug" => true
        ),
        array(
            "path" => "tjs/Settings",
            "debug" => true
        ),
        array(
            "path" => "tjs/branchPrediction",
            "debug" => true
        )
    );

    /**
     * @var array[] $css_map defines an array of all needed css-files
     */
    private static $css_map = array(
        array(
            "path" => "csse/bootstrap",
            "debug" => true
        ),
        array(
            "path" => "csse/bootstrap-grid",
            "debug" => true
        ),
        array(
            "path" => "csse/bootstrap-reboot",
            "debug" => true
        ),
        array(
            "path" => "csse/bootstrap-utilities",
            "debug" => true
        ),
        array(
            "path" => "csse/codemirror",
            "debug" => true
        ),
        array(
            "path" => "css/main",
            "debug" => true
        )
    );

    /**
     * generate Header-Tags with all needed Information
     * @param string $name
     * @param array $additional
     */
    public static function GenerateHeaderTags(string $name = '', array $additional = array())
    {
        echo '<meta charset="UTF-8">';
        echo '<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">';
        echo '<link rel="icon" type="image/x-icon" href="favicon.ico">';
        if ($name != '') {
            echo '<title>' . config::$TAGLINE_CAPTION . ' - ' . $name . '</title>';
        } else {
            echo '<title>' . config::$TAGLINE_CAPTION . '</title>';
        }
        foreach (self::$css_map as $css_file) {
            echo '<link rel="stylesheet" type="text/css" href="' . $css_file['path'] . (!$css_file['debug'] || !config::$DEBUG ? '.min' : '') . '.css">';
        }
        foreach (self::$js_map as $js_file) {
            echo '<script type="text/javascript" src="' . $js_file['path'] . (!$js_file['debug'] || !config::$DEBUG ? '.min' : '') . '.js"></script>';
        }

        if (sizeof($additional) > 0) {
            foreach ($additional as $line) {
                $href = $line['hrefmin'] ?? $line['href'];  //php 7 syntax
                if (config::$DEBUG) {
                    $href = $line['href'];
                }
                switch ($line['type']) {
                    case 'style':
                    case 'css':
                    case 'link':
                        echo '<link rel="' . $line['rel'] . '" href="' . $href . '"' . (($line['typeval'] ?? '') ? ' type="' . $line['typeval'] . '"' : '') . '>';
                        break;
                    case 'js':
                    case 'script':
                        echo '<script type="' . $line['typeval'] . '" src="' . $href . '" ></script>';
                        break;
                }
            }
        }
    }

    /**
     * generates Navbar
     */
    public static function generateNavbar()
    {
        ?>
        <nav class="navbar navbar-expand-lg navbar-light bg-light px-3">
            <a class="navbar-brand" href="index.php"><?php echo config::$MAIN_CAPTION ?></a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent"
                    aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav mr-auto">
                    <li class="navbar-nav mr-auto">
                        <a class="nav-link pointer-link-navbar" href="sim.php">Simulator</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link pointer-link-navbar" onclick="ButtonExecute.OpenSettingsModal()">Einstellungen</a>
                    </li>
                    <li class="navbar-nav mr-auto">
                        <a class="nav-link pointer-link-navbar" target="_blank" href="help.php">Hilfe</a>
                    </li>
                </ul>
            </div>
        </nav>
        <div class="modal fade overflow-auto" id="SettingsModal" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header d-inline-flex align-items-baseline rounded-top-7">
                        <h5>Einstellungen</h5>
                        <button type="button" class="btn" data-dismiss="modal"
                                onclick="$('#SettingsModal').modal('hide');">
                            <img src="images/times-solid.svg" width="14px">
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="tab-content" id="myTabContent">
                            <div class="tab-pane fade show active" id="MeineKommentareTb" role="tabpanel">
                                <h7 class="text-decoration-underline">Darstellungsoptionen</h7>
                                <br/>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="MemoryAdressHexCheckbox"
                                           onchange="ButtonExecute.MemoryHexAddrCheckChanged();">
                                    <label class="form-check-label" for="MemoryAdressHexCheckbox">Speicheradressen in
                                        Hex-Darstellung</label>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="DisplayAllPgmCommandsCheckbox"
                                           onchange="ButtonExecute.DisplayAllCommandsInPgmHistChanged();">
                                    <label class="form-check-label" for="DisplayAllPgmCommandsCheckbox">Alle ausgeführten Befehle anzeigen</label>
                                </div>
                                <div class="form-group">
                                    <label for="DisplayedTaktSpanInput">Anzahl der dargestellten Takte</label>
                                    <input type="number" class="form-control" id="DisplayedTaktSpanInput"
                                           aria-describedby="DisplayedTaktSpanHelp" value="19"
                                           onchange="ButtonExecute.displayedtaktSpanChanged()">
                                    <small id="DisplayedTaktSpanHelp" class="form-text text-muted">Hier bitte die Anzahl der dargestellten Takte angeben.</small>
                                </div>
                                <br />
                                <h7 class="text-decoration-underline">Simulator-Optionen</h7>
                                <br />
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="ResultForwardCheckbox"
                                           onchange="ButtonExecute.ResultForwardChanged();" disabled="disabled">
                                    <label class="form-check-label" for="ResultForwardCheckbox">Nutzung von
                                        Result-Forwarding</label>
                                </div>
                                <div class="mt-2">
                                    <h7>Pipeline</h7>
                                    <select class="form-select mt-1" aria-label="Default select example"
                                            id="PipelineSelectSim" onchange="ButtonExecute.PipelineChanged()">
                                        <option selected value="0">Keine Pipeline</option>
                                        <option value="1">Standard Pipeline</option>
                                        <option value="2">Superskalare Pipeline - In-Order</option>
                                        <option value="3">Superskalare Pipeline - Out-of-Order</option>
                                    </select>
                                </div>
                                <div class="mt-2">
                                    <h7>Sprungvorhersage</h7>
                                    <select class="form-select mt-1" aria-label="Default select example"
                                            id="JumpSelectSim" onchange="ButtonExecute.JumpChanged()" disabled="disabled">
                                        <option selected value="0">Keine Sprungvorhersage</option>
                                        <option value="1">1-Bit Sprungvorhersage</option>
                                        <option value="2">2-Bit Sprungvorhersage</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="PipelineCountInput">Anzahl paralleler Piplines</label>
                                    <input type="number" class="form-control" id="PipelineCountInput"
                                           aria-describedby="PipelineCountInputHelp" value="4"
                                           onchange="ButtonExecute.PipelineCountChanged()" disabled="disabled">
                                    <small id="PipelineCountInputHelp" class="form-text text-muted">Hier bitte die
                                        Anzahl der Parallelen Pipelines angeben.</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal"
                                onclick="$('#SettingsModal').modal('hide');">Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * generates redirects
     * @param string $url url where redirect should refer to
     * @param false $permanent states if redirect should be permanent
     */
    public static function redirect($url, $permanent = false)
    {
        header('Location: ' . $url, true, $permanent ? 301 : 302);
        die();
    }
}
