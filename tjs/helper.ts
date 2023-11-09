/**
 * This class contains some helper functions
 */
class Helper {

    /**
     * creates a table row of memory or register
     * @param {string} name name of row
     * @param {int} val value to display
     * @param {int} id identifier of cells
     */
    public static createTableRowRegisterSpeicher(name: string, val: number, id: string): string {
        let result = '<tr id="' + id + '">';
        result += '<th scope="row">' + name + '</th>';
        if (val >= 0) {
            result += '<td> 0x' + ("00000000" + val.toString(16)).substr(-8).toUpperCase() + '</td>';
        } else {
            let val2 = 4294967295 + 1 + val;
            result += '<td> 0x' + ("00000000" + val2.toString(16)).substr(-8).toUpperCase() + '</td>';
        }
        result += '<td>' + val.toString() + '</td>';
        result += '</tr>';
        return result;
    }

    /**
     * creates multiple rows of memory or register
     * @param {string} namePrefix Prefix with which name will be generated
     * @param {Array<number>} values values of displayed registers or memory cells
     * @param {string} idPrefix Prefix with which id will be generated
     * @param {number} count count of Rows to create
     * @param {number} offset offset of numbering
     */
    public static createMultipleRows(namePrefix: string, values: Array<number>, idPrefix: string, count: number, offset: number): string {
        let result = "";
        for (let i = 0; i < count; i++) {
            let nameid = i + offset;
            let value = values[i];
            result += Helper.createTableRowRegisterSpeicher(namePrefix + nameid.toString(), value, idPrefix + i.toString());
        }
        return result;
    }

    /**
     * creates a row for memory table
     * @param {Memory} memory input with data type "Memory"
     */
    public static createSpeicherRows(memory: Memory) {
        let content = memory.content;
        let result = "";
        for (let i = 0; i < content.length; i++) {
            result += this.createTableRowRegisterSpeicher(content[i].name, content[i].value, "Speicher_" + content[i].name);
        }
        return result;
    }

    /**
     * creates header of programm-memory table
     * @param {number} start takt which display will start at
     * @param {number} end takt which display will end at
     */
    public static createProgrammTableHeader(start: number, end: number) {
        let header = document.getElementById('programmSourceTableHeader');
        let new_header = "<tr><th>ID</th><th class='programm-Memory-firstrow'>Befehl</th><th colspan='" + ((end - start) + 1 )+ "'>Takt</th></tr>";
        if ((start - end) != 0) {
            new_header += "<tr><th></th><th scope='col'></th>";
            for (let i = start; i < end + 1; i++) {
                new_header += "<th scope='col'>" + i + "</th>"
            }
            new_header += "</tr>";
        }
        header.innerHTML = new_header;
    }

    /**
     * adds rows for each command individual
     * @param {Array<{ command: string, history: Array<string> }>} programm programm data as large array
     * @param {number} start start of displayed commands
     * @param {number} end end of displayed commands
     */
    public static createProgrammTableRows(programm: Array<{ command: string, history: Array<string>, id: number, jump: boolean, pred: boolean }>, start: number, end:number) {
        let tablebody = document.getElementById('programmSourceTableBody');
        let body = "";
        let startcount = start;
        let programmMaxHistorie = programm.length <= 250 ? 0 : programm.length - 250;
        let marknext = false;
        let marker = false;
        for (let i = programmMaxHistorie; i < programm.length; i++) {
            let row = "";
            let EmptyRow = true;
            row += "<tr>";
            if (programm[i].id != -1 && programm[i].id != undefined) {
                row += "<td>" + (programm[i].id + 1) + "</td>";
            } else {
                row += "<td></td>";
            }
            row += "<th scope='row' class='programm-Memory-firstrow'>" + programm[i].command + "</th>";
            for (let j = startcount; j < end + 1; j++) {
                if (j in programm[i].history) {
                    if (programm[i].history[j] == "FL"){
                        row += "<td class='flushCell'></td>";
                    } else {
                        if (marknext && Main.Settings.JumpPrediction > 0){
                            if (marker) {
                                row += "<td class='predTrue'>" + programm[i].history[j] + "</td>";
                            } else {
                                row += "<td class='predFalse'>" + programm[i].history[j] + "</td>";
                            }
                            marknext = false;
                        } else {
                            row += "<td>" + programm[i].history[j] + "</td>";
                        }
                    }
                    EmptyRow = false;
                } else {
                    row += "<td></td>";
                }
            }
            if ("jump" in programm[i]){
                marknext = programm[i].jump;
                if (programm[i].jump){
                    marker = programm[i].pred;
                }
            } else {
                marknext = false;
                marker = false;
            }
            row += "</tr>";
            if (!EmptyRow || Main.Settings.DisplayAllCommands) {
                body += row;
            }
        }
        tablebody.innerHTML = body;
    }

    /**
     * sends an API request to a backend
     * @param {JSON} request structured request
     */
    public static sendApiRequest(request: { src: any; type: string }): { code: number, data: any, result: string } {
        let otherReq = new XMLHttpRequest();
        otherReq.open("POST", "api.php", false);
        otherReq.withCredentials = true;
        otherReq.setRequestHeader("Content-Type", "application/json");
        otherReq.send(JSON.stringify(request));
        let resp = otherReq.responseText;
        let result = {code: 1, data: "", result: ""};
        try {
            result = JSON.parse(resp);
        } catch (e) {
            alert("Es ist ein Fehler aufgetreten. Bitte wenden Sie sich an den Betreuer!");
            throw new Error("Something went badly wrong!");
        }
        if (result.code > 0) {
            alert("Es ist ein Fehler der Simulation aufgetreten. Bitte beheben Sie den Fehler für eine erfolgreiche Simulation!");
            throw new Error("Something went badly wrong!");
        }
        return result;
    }

    /**
     * resets program display
     */
    public static resetProgrammDisplay() : void
    {
        let tablebody = document.getElementById('programmSourceTableBody');
        tablebody.innerHTML = "";
        let header = document.getElementById('programmSourceTableHeader');
        header.innerHTML = "<tr><th>ID</th><th class='programm-Memory-firstrow'>Befehl</th><th>Takt</th></tr>";
    }
}
