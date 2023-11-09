<?php
/**
 * help page of simulator
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
<div class="container p-3">
    <h1>Unterstütze Befehle</h1>
    Alle unten aufgelisteten Befehle werden durch den Simulator unterstützt. Dabei werden folgende Abkürzungen verwendet:
    <table class="table table-striped">
        <thead>
        <tr>
            <th scope="col">Abkürzung</th>
            <th scope="col">Bedeutung</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <th scope="row">RD</th>
            <td>Zielregister</td>
        </tr>
        <tr>
            <th scope="row">RS1</th>
            <td>Quellregister 1</td>
        </tr>
        <tr>
            <th scope="row">RS2</th>
            <td>Quellregister 2</td>
        </tr>
        <tr>
            <th scope="row">LA</th>
            <td>Label eines Sprungzieles</td>
        </tr>
        <tr>
            <th scope="row">IM</th>
            <td>Immediate (Konstante)</td>
        </tr>
        <tr>
            <th scope="row">PC</th>
            <td>Programmzeiger</td>
        </tr>
        <tr>
            <th scope="row">RAM</th>
            <td>Arbeitsspeicher (Random Access Memory)</td>
        </tr>
        </tbody>
    </table>
    <h2>Speicher- und Ladebefehle</h2>
    <table class="table table-striped">
        <thead>
        <tr>
            <th scope="col">Befehl</th>
            <th scope="col">Parameter</th>
            <th scope="col">Operation</th>
            <th scope="col">Bedeutung</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <th scope="row">lw</th>
            <td>RD, Adresse</td>
            <td>RD := RAM[Adresse]</td>
            <td>Load Word</td>
        </tr>
        <tr>
            <th scope="row">sw</th>
            <td>RS1, Adresse</td>
            <td>RAM[Adresse] := RS1</td>
            <td>Save Word</td>
        </tr>
        </tbody>
    </table>
    <h2>Additions- und Subtraktionsbefehle</h2>
    <table class="table table-striped">
        <thead>
        <tr>
            <th scope="col">Befehl</th>
            <th scope="col">Parameter</th>
            <th scope="col">Operation</th>
            <th scope="col">Bedeutung</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <th scope="row">add</th>
            <td>RD, RS1, RS2</td>
            <td>RD := RS1 + RS2</td>
            <td>Addition mit Überlauf</td>
        </tr>
        <tr>
            <th scope="row">addi</th>
            <td>RD, RS1, IM</td>
            <td>RD := RS1 + IM</td>
            <td>Addition Immediate mit Überlauf</td>
        </tr>
        <tr>
            <th scope="row">addu</th>
            <td>RD, RS1, RS2</td>
            <td>RD := RS1 + RS2</td>
            <td>Addition ohne Überlauf</td>
        </tr>
        <tr>
            <th scope="row">addiu</th>
            <td>RD, RS1, IM</td>
            <td>RD := RS1 + IM</td>
            <td>Addition Immediate ohne Überlauf</td>
        </tr>
        <tr>
            <th scope="row">sub</th>
            <td>RD, RS1, RS2</td>
            <td>RD := RS1 - RS2</td>
            <td>Subtraktion mit Überlauf</td>
        </tr>
        <tr>
            <th scope="row">subu</th>
            <td>RD, RS1, RS2</td>
            <td>RD := RS1 - RS2</td>
            <td>Subtraktion ohne Überlauf</td>
        </tr>
        </tbody>
    </table>

    <h2>Elementare logische Befehle</h2>
    <table class="table table-striped">
        <thead>
        <tr>
            <th scope="col">Befehl</th>
            <th scope="col">Parameter</th>
            <th scope="col">Operation</th>
            <th scope="col">Bedeutung</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <th scope="row">and</th>
            <td>RD, RS1, RS2</td>
            <td>RD := RS1 AND RS2</td>
            <td>and</td>
        </tr>
        <tr>
            <th scope="row">andi</th>
            <td>RD, RS1, IM</td>
            <td>RD := RS1 AND IM</td>
            <td>and Immediate</td>
        </tr>
        <tr>
            <th scope="row">nor</th>
            <td>RD, RS1, RS2</td>
            <td>RD := RS1 NOR RS2</td>
            <td>not or</td>
        </tr>
        <tr>
            <th scope="row">or</th>
            <td>RD, RS1, RS2</td>
            <td>RD := RS1 OR RS2</td>
            <td>or</td>
        </tr>
        <tr>
            <th scope="row">ori</th>
            <td>RD, RS1, IM</td>
            <td>RD := RS1 OR IM</td>
            <td>or Immediate</td>
        </tr>
        <tr>
            <th scope="row">xor</th>
            <td>RD, RS1, RS2</td>
            <td>RD := RS1 XOR RS2</td>
            <td>exclusive or</td>
        </tr>
        <tr>
            <th scope="row">xori</th>
            <td>RD, RS1, IM</td>
            <td>RD := RS1 XOR IM</td>
            <td>exclusive or Immediate</td>
        </tr>
        </tbody>
    </table>

    <h2>Vergleichsbefehle</h2>
    <table class="table table-striped">
        <thead>
        <tr>
            <th scope="col">Befehl</th>
            <th scope="col">Parameter</th>
            <th scope="col">Operation</th>
            <th scope="col">Bedeutung</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <th scope="row">sgt</th>
            <td>RD, RS1, RS2</td>
            <td>RD := 1, wenn RS1 > RS2 <br /> RD := 0, sonst</td>
            <td>set greater than</td>
        </tr>
        <tr>
            <th scope="row">sgtu</th>
            <td>RD, RS1, RS2</td>
            <td>RD := 1, wenn RS1 > RS2 <br /> RD := 0, sonst</td>
            <td>set greater than unsigned</td>
        </tr>
        <tr>
            <th scope="row">slt</th>
            <td>RD, RS1, RS2</td>
            <td>RD := 1, wenn RS1 < RS2 <br /> RD := 0, sonst</td>
            <td>set less than</td>
        </tr>
        <tr>
            <th scope="row">sltu</th>
            <td>RD, RS1, RS2</td>
            <td>RD := 1, wenn RS1 < RS2 <br /> RD := 0, sonst</td>
            <td>set less than unsigned</td>
        </tr>
        <tr>
            <th scope="row">slti</th>
            <td>RD, RS1, IM</td>
            <td>RD := 1, wenn RS1 < IM <br /> RD := 0, sonst</td>
            <td>set less than Immediate</td>
        </tr>
        <tr>
            <th scope="row">sltui</th>
            <td>RD, RS1, IM</td>
            <td>RD := 1, wenn RS1 < IM <br /> RD := 0, sonst</td>
            <td>set less than unsigned Immediate</td>
        </tr>
        </tbody>
    </table>

    <h2>Schiebebefehle</h2>
    <table class="table table-striped">
        <thead>
        <tr>
            <th scope="col">Befehl</th>
            <th scope="col">Parameter</th>
            <th scope="col">Operation</th>
            <th scope="col">Bedeutung</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <th scope="row">sll</th>
            <td>RD, RS1, IM</td>
            <td>RD := RS1 << IM</td>
            <td>shift left logical</td>
        </tr>
        <tr>
            <th scope="row">sllv</th>
            <td>RD, RS1, RS2</td>
            <td>RD := RS1 << RS2</td>
            <td>shift left logical variable</td>
        </tr>
        <tr>
            <th scope="row">srl</th>
            <td>RD, RS1, IM</td>
            <td>RD := RS1 >> IM</td>
            <td>shift right logical</td>
        </tr>
        <tr>
            <th scope="row">srlv</th>
            <td>RD, RS1, RS2</td>
            <td>RD := RS1 >> RS2</td>
            <td>shift right logical variable</td>
        </tr>
        <tr>
            <th scope="row">sra</th>
            <td>RD, RS1, IM</td>
            <td>RD := RS1 &#247; 2<sup>IM</sup></td>
            <td>shift right arithmetic</td>
        </tr>
        <tr>
            <th scope="row">srav</th>
            <td>RD, RS1, RS2</td>
            <td>RD := RS1 &#247; 2<sup>RS2</sup></td>
            <td>shift right arithmetic variable</td>
        </tr>
        </tbody>
    </table>

    <h2>Sprungbefehle</h2>
    <table class="table table-striped">
        <thead>
        <tr>
            <th scope="col">Befehl</th>
            <th scope="col">Parameter</th>
            <th scope="col">Operation</th>
            <th scope="col">Bedeutung</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <th scope="row">b</th>
            <td>LA</td>
            <td>PC := Adresse(LA)</td>
            <td>Branch</td>
        </tr>
        <tr>
            <th scope="row">beq</th>
            <td>RS1, RS2, LA</td>
            <td>PC := Adresse(LA), falls RS1 = RS2</td>
            <td>Branch if equal</td>
        </tr>
        <tr>
            <th scope="row">beqz</th>
            <td>RS1, LA</td>
            <td>PC := Adresse(LA), falls RS1 = 0</td>
            <td>Branch if equal zero</td>
        </tr>
        <tr>
            <th scope="row">bne</th>
            <td>RS1, RS2, LA</td>
            <td>PC := Adresse(LA), falls RS1 &ne; RS2</td>
            <td>Branch if not equal</td>
        </tr>
        <tr>
            <th scope="row">bnez</th>
            <td>RS1, LA</td>
            <td>PC := Adresse(LA), falls RS1 &ne; 0</td>
            <td>Branch if equal not zero</td>
        </tr>
        <tr>
            <th scope="row">bgez</th>
            <td>RS1, LA</td>
            <td>PC := Adresse(LA), falls RS1 &ge; 0</td>
            <td>Branch if greater or equal zero</td>
        </tr>
        <tr>
            <th scope="row">bgtz</th>
            <td>RS1, LA</td>
            <td>PC := Adresse(LA), falls RS1 > 0</td>
            <td>Branch if greater than zero</td>
        </tr>
        <tr>
            <th scope="row">blez</th>
            <td>RS1, LA</td>
            <td>PC := Adresse(LA), falls RS1 &le; 0</td>
            <td>Branch if less or equal zero</td>
        </tr>
        <tr>
            <th scope="row">bltz</th>
            <td>RS1, LA</td>
            <td>PC := Adresse(LA), falls RS1 < 0</td>
            <td>Branch if less than zero</td>
        </tr>
        <tr>
            <th scope="row">j</th>
            <td>LA</td>
            <td>PC := Adresse(LA)</td>
            <td>Jump</td>
        </tr>
        </tbody>
    </table>
</div>
</body>
</html>
