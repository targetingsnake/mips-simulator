/**
 * Here are stored all necessary global settings
 */
class Settings {

    /**
     * @private state if memory addresses are displayed as hex numbers
     */
    private i_MemoryHexAdresses: boolean;

    /**
     * @private chosen pipeline variante
     */
    private i_pipeline_variante: number;

    /**
     * @private chosen jump prediction
     */
    private i_jump_prediction: number;

    /**
     * @private defines if result forward is used
     */
    private i_result_forward: boolean;

    /**
     * @private defines number of parallel pipelines
     */
    private i_pipelines: number;

    /**
     * @private defines number of displayed cycles
     */
    private i_displayed_interval: number;

    /**
     * @private defines if all commands should be displayed
     */
    private i_displayAll: boolean;

    /**
     * initializes variables
     */
    public constructor() {
        this.i_MemoryHexAdresses = false;
        this.i_result_forward = false;
        this.i_pipeline_variante = 0;
        this.i_jump_prediction = 0;
        this.i_pipelines = 4;
        this.i_displayed_interval = 19;
        this.i_displayAll = false;
    }

    /**
     * returns state of MemoryHexAdress
     */
    public get MemoryHexAdresses(): boolean {
        return this.i_MemoryHexAdresses;
    }

    /**
     * sets state of MemoryHexAdress
     */
    public set MemoryHexAdresses(new_value: boolean) {
        this.i_MemoryHexAdresses = new_value;
        Main.updateView();
    }

    /**
     * returns pipeline variant
     */
    public get PipelineVariant(): number {
        return this.i_pipeline_variante;
    }

    /**
     * sets pipeline variant
     */
    public set PipelineVariant(new_value: number) {
        this.i_pipeline_variante = new_value;
    }

    /**
     * returns jump prediction variant
     */
    public get JumpPrediction(): number {
        return this.i_jump_prediction;
    }

    /**
     * sets jump prediction variant
     */
    public set JumpPrediction(new_Value: number) {
        this.i_jump_prediction = new_Value;
    }

    /**
     * returns if result forward is used
     */
    public set ResultForward(new_value: boolean) {
        this.i_result_forward = new_value;
    }

    /**
     * sets if result forward is used
     */
    public get ResultForward(): boolean {
        return this.i_result_forward;
    }

    /**
     * returns amount of parallel pipeline
     */
    public get Pipelines(): number {
        return this.i_pipelines;
    }

    /**
     * sets amount of parallel pipeline
     */
    public set Pipelines(new_value: number) {
        this.i_pipelines = new_value;
    }

    /**
     * returns amount of displayed cycled
     */
    public get DisplayedInterval(): number{
        return this.i_displayed_interval;
    }

    /**
     * sets amount of displayed cycled
     */
    public set DisplayedInterval(new_value: number){
        this.i_displayed_interval = new_value;
        Main.loadTakt();
    }

    /**
     * returns if all executed commands are displayed
     */
    public get DisplayAllCommands(): boolean {
        return this.i_displayAll;
    }

    /**
     * sets if all executed commands are displayed
     */
    public set DisplayAllCommands(new_value: boolean){
        this.i_displayAll = new_value;
        Main.loadTakt();
    }
}