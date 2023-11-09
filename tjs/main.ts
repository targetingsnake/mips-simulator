/**
 * This file contains all necessary functions for a properly working simulator
 */

window.onload = function () {
    Main.init();
}

/**
 * main class for displaying simulation and execute it
 */
class Main {

    /**
     * @private base memory of simulator
     */
    private static base_memory: Memory;

    /**
     * @private holds CodeMirror editor as variable
     */
    private static code_editor;

    /**
     * @private holds data of Branch history table
     */
    private static branch_history_table;

    /**
     * @private holds all necessary and optional Settings
     */
    private static i_settings: Settings;

    /**
     * @private holds all information from simulation
     */
    private static i_run: { code: number, data: any, result: string };

    /**
     * @private defines current displayed takt
     */
    private static i_displayed_takt : number;

    /**
     * initialises the Simulator
     */
    public static init() {
        this.i_settings = new Settings();
        this.base_memory = new Memory();
        this.branch_history_table = new branchPrediction();
        Main.initRegisterTable();
        Main.updateMemoryTable();
        Main.updateBranchHistoryTable();
        let programmEditor = document.getElementById('programmInput') as HTMLTextAreaElement;
        this.code_editor = CodeMirror.fromTextArea(programmEditor, {lineNumbers: true});
        this.i_displayed_takt = 0;
    }

    /**
     * initialises the register Table
     */
    public static initRegisterTable() {
        let registerTable = document.getElementById('registerSourceTable');
        let tableBody = Helper.createTableRowRegisterSpeicher('$zero', 0, 'zeroRegister');
        tableBody += Helper.createTableRowRegisterSpeicher('$at', 0, 'tempAssemblerRegister');
        tableBody += Helper.createMultipleRows("$v", [0, 0], "functionResult", 2, 0);
        tableBody += Helper.createMultipleRows("$a", [0, 0, 0, 0], "ProcedureCallReg", 4, 0)
        tableBody += Helper.createMultipleRows("$t", [0, 0, 0, 0, 0, 0, 0, 0], "temporaryVariables", 8, 0)
        tableBody += Helper.createMultipleRows("$s", [0, 0, 0, 0, 0, 0, 0, 0], "longTermVariables", 8, 0)
        tableBody += Helper.createMultipleRows("$t", [0, 0], "temp2ndVariables", 2, 8)
        tableBody += Helper.createMultipleRows("$k", [0, 0], "reserverdRegister", 2, 0);
        tableBody += Helper.createTableRowRegisterSpeicher('$gp', 0, 'DatasegmentPointer');
        tableBody += Helper.createTableRowRegisterSpeicher('$sp', 0, 'stackPointer');
        tableBody += Helper.createTableRowRegisterSpeicher('$fp', 0, 'framePointer');
        tableBody += Helper.createTableRowRegisterSpeicher('$ra', 0, 'jumpBackAddress');
        registerTable.innerHTML = tableBody
    }

    /**
     * updates register table
     * @param {Array<{ reg: number, val: number }>} Register_input register data
     */
    public static updateRegisterTable(Register_input: Array<{ reg: number, val: number }>): void {
        let Register = {};
        for (let i = 0; i < 32; i++) {
            if (i in Register_input) {
                Register[i] = Register_input[i];
            } else {
                Register[i] = {reg: i, val: 0};
            }
        }
        let registerTable = document.getElementById('registerSourceTable');
        let tablebody = Helper.createTableRowRegisterSpeicher('$zero', Register[0].val, 'zeroRegister');
        tablebody += Helper.createTableRowRegisterSpeicher('$at', Register[1].val, 'tempAssemblerRegister');
        tablebody += Helper.createMultipleRows("$v", [Register[2].val, Register[3].val], "functionResult", 2, 0);
        tablebody += Helper.createMultipleRows("$a", [Register[4].val, Register[5].val, Register[6].val, Register[7].val], "ProcedureCallReg", 4, 0)
        tablebody += Helper.createMultipleRows("$t", [Register[8].val, Register[9].val, Register[10].val, Register[11].val, Register[12].val, Register[13].val, Register[14].val, Register[15].val], "temporaryVariables", 8, 0)
        tablebody += Helper.createMultipleRows("$s", [Register[16].val, Register[17].val, Register[18].val, Register[19].val, Register[20].val, Register[21].val, Register[22].val, Register[23].val], "longTermVariables", 8, 0)
        tablebody += Helper.createMultipleRows("$t", [Register[24].val, Register[25].val], "temp2ndVariables", 2, 8)
        tablebody += Helper.createMultipleRows("$k", [Register[26].val, Register[27].val], "reserverdRegister", 2, 0);
        tablebody += Helper.createTableRowRegisterSpeicher('$gp', Register[28].val, 'DatasegmentPointer');
        tablebody += Helper.createTableRowRegisterSpeicher('$sp', Register[29].val, 'stackPointer');
        tablebody += Helper.createTableRowRegisterSpeicher('$fp', Register[30].val, 'framePointer');
        tablebody += Helper.createTableRowRegisterSpeicher('$ra', Register[31].val, 'jumpBackAddress');
        registerTable.innerHTML = tablebody
    }

    /**
     * @private updates branch history table
     */
    private static updateBranchHistoryTable(){
        let bhtTable = document.getElementById("BhtSourceTable");
        bhtTable.innerHTML = this.branch_history_table.createTable();
    }

    /**
     * @private updates View of website
     */
    public static updateView() {
        this.updateMemoryTable();
    }

    /**
     * initialises memory table
     */
    public static updateMemoryTable() {
        let memory_table = document.getElementById('speicherSourceTable');
        memory_table.innerHTML = Helper.createSpeicherRows(this.base_memory);
    }

    /**
     * resets the simulator and initialize it new
     */
    public static reset() {
        if (!confirm("Do you want to really reset the Simulator?")) {
            return;
        }
        Helper.resetProgrammDisplay();
        this.i_run = undefined;
        this.i_settings = new Settings();
        this.base_memory = new Memory();
        this.branch_history_table = new branchPrediction();
        Main.initRegisterTable();
        Main.updateMemoryTable();
        Main.updateBranchHistoryTable();
        this.i_displayed_takt = 0;
    }

    /**
     * requests a complete simulation
     */
    public static simulate() {
        let srcCode = this.code_editor.getValue();
        let json = {
            type: "run",
            src: srcCode,
            pipeline: Main.Settings.PipelineVariant,
            jumpPred: Main.Settings.JumpPrediction,
            res_fwd: Main.Settings.ResultForward,
            pipelines: Main.Settings.Pipelines
        };
        let result = Helper.sendApiRequest(json);
        this.i_run = result;
        this.i_displayed_takt = result.data.counter
        this.loadTakt()
    }

    /**
     * load certain tact of simulation
     */
    public static loadTakt() : void
    {
        let takt = this.i_displayed_takt;
        let start = 0;
        let end = takt;
        let displayedWide = Main.Settings.DisplayedInterval;
        if (displayedWide % 2 == 0){
            displayedWide = displayedWide + 1;
        }
        let half = Math.floor(displayedWide/2);
        let shiftEnd = 0;
        let shiftStart = 0;
        if (half >= takt) {
            shiftEnd = half - takt;
        }
        if (this.i_run.data.counter - takt <= half) {
            shiftStart = half - (this.i_run.data.counter - takt);
        }
        start = takt - ( half + shiftStart);
        end = takt + half + shiftEnd;
        if (start < 0) {
            start = 0;
        }
        if (end > this.i_run.data.counter) {
            end = this.i_run.data.counter;
        }
        Helper.createProgrammTableHeader(start, end);
        Helper.createProgrammTableRows(this.i_run.data.programm, start, end);
        this.base_memory.loadData(this.i_run.data.memory[takt]);
        this.branch_history_table.loadData(this.i_run.data.bht[takt]);
        this.updateMemoryTable();
        this.updateBranchHistoryTable();
        this.updateRegisterTable(this.i_run.data.register[takt]);
        let displayTakt = document.getElementById('CurrentTaktDisplay');
        displayTakt.innerText = takt + "";
    }

    /**
     * make one step forward in the simulation
     */
    public static forward() {
        if (this.i_displayed_takt < this.i_run.data.counter){
            this.i_displayed_takt = this.i_displayed_takt + 1;
            this.loadTakt();
        }
    }

    /**
     * make one step backward in the simulation
     */
    public static backwards() {
        if (this.i_displayed_takt > 0){
            this.i_displayed_takt = this.i_displayed_takt - 1;
            this.loadTakt();
        }
    }

    /**
     * returns settings
     */
    public static get Settings(): Settings {
        return this.i_settings;
    }

    /**
     * sets settings
     * @param {Settings} new_Value new Settings
     */
    public static set Settings(new_Value: Settings) {
        this.i_settings = new_Value;
        this.updateView();
    }
}
