<?php
/**
 * Starting page of simulator
 */
define('NICE_PROJECT', true);
require_once 'bin/inc.php';


?>
<!DOCTYPE html>
<html lang="de">
<head>
    <?php
    HtmlGenerator::GenerateHeaderTags();
    ?>
</head>
<body>
<?php
HtmlGenerator::generateNavbar();
?>
<div class="container container-simulator">
    <div class="row padding-left-zero">

        <div class="col-md-7 p-3">
            <div class="container container-simulator">
                <div class="row padding-left-zero">
                    <h3>Programm</h3>
                    <textarea class="sim-textarea" id="programmInput"></textarea>
                    <div class="btn-group width-100 mt-2" role="group" aria-label="Basic example">
                        <button type="button" class="btn btn-warning" onclick="Main.reset();">Reset</button>
                        <button type="button" class="btn btn-primary" onclick="Main.simulate();">Simulate</button>
                        <button type="button" class="btn btn-secondary" onclick="Main.backwards();">Schritt Rückwärts
                        </button>
                        <button type="button" class="btn btn-secondary" id="CurrentTaktDisplay"></button>
                        <button type="button" class="btn btn-secondary" onclick="Main.forward();">Schritt Vorwärts
                        </button>
                    </div>
                </div>
                <div class="row mt-3">
                    <h3>Programmspeicher</h3>
                    <div class="container-100vh-2">
                        <table class="table table-hover table-bordered">
                            <thead id="programmSourceTableHeader">
                            <tr>
                                <th scope="col">ID</th>
                                <th scope="col">Befehl</th>
                                <th scope="col">Takt</th>
                            </tr>
                            </thead>
                            <tbody id="programmSourceTableBody">

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-5 p-3">
            <h3>Register- und Speicherinhalt</h3>
            <ul class="nav nav-tabs" id="pills-tab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="pills-home-tab" data-bs-toggle="pill"
                            data-bs-target="#pills-home" type="button" role="tab" aria-controls="pills-home"
                            aria-selected="true">Register
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="pills-profile-tab" data-bs-toggle="pill"
                            data-bs-target="#pills-profile" type="button" role="tab" aria-controls="pills-profile"
                            aria-selected="false">Speicher
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="pills-profile-tab" data-bs-toggle="pill"
                            data-bs-target="#pills-bht" type="button" role="tab" aria-controls="pills-profile"
                            aria-selected="false">Branch History
                    </button>
                </li>
            </ul>
            <div class="tab-content container-100vh" id="RegisterAndSpeicherContent">
                <div class="tab-pane fade show active" id="pills-home" role="tabpanel" aria-labelledby="pills-home-tab">
                    <table class="table table-hover">
                        <thead>
                        <tr>
                            <th scope="col">Register</th>
                            <th scope="col">Hex-Wert</th>
                            <th scope="col">Dezimalwert</th>
                        </tr>
                        </thead>
                        <tbody id="registerSourceTable">

                        </tbody>
                    </table>
                </div>
                <div class="tab-pane fade" id="pills-profile" role="tabpanel" aria-labelledby="pills-profile-tab">
                    <table class="table table-hover">
                        <thead>
                        <tr>
                            <th scope="col">Speicherzelle</th>
                            <th scope="col">Hex-Wert</th>
                            <th scope="col">Dezimalwert</th>
                        </tr>
                        </thead>
                        <tbody id="speicherSourceTable">

                        </tbody>
                    </table>
                </div>
                <div class="tab-pane fade" id="pills-bht" role="tabpanel" aria-labelledby="pills-profile-tab">
                    <table class="table table-hover">
                        <thead>
                        <tr>
                            <th scope="col">Programmadresse</th>
                            <th scope="col">Zustand Sprungvorhersage</th>
                        </tr>
                        </thead>
                        <tbody id="BhtSourceTable">

                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>
</body>
</html>
