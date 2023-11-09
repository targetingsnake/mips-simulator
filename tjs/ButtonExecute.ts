/**
 * this class contains all functions for buttons, which are not essential for simulation
 */
class ButtonExecute {

    /**
     * opens settings modal
     */
    public static OpenSettingsModal(){
        $('#SettingsModal').modal('show');
    }

    /**
     * display memory addresses as hex
     */
    public static MemoryHexAddrCheckChanged() {
        let checkbox = document.getElementById('MemoryAdressHexCheckbox') as HTMLInputElement;
        Main.Settings.MemoryHexAdresses = checkbox.checked;
    }

    /**
     * display all commands
     */
    public static DisplayAllCommandsInPgmHistChanged() {
        let checkbox = document.getElementById('DisplayAllPgmCommandsCheckbox') as HTMLInputElement;
        Main.Settings.DisplayAllCommands = checkbox.checked;
    }

    /**
     * update chosen pipeline
     */
    public static PipelineChanged() {
        let pipeline_select = document.getElementById('PipelineSelectSim') as HTMLInputElement;
        let pipelineVal = parseInt(pipeline_select.value);
        Main.Settings.PipelineVariant = pipelineVal;
        let result_forward = document.getElementById('ResultForwardCheckbox') as HTMLInputElement;
        let jump_pred = document.getElementById('JumpSelectSim') as HTMLInputElement;
        let pipelinecount = document.getElementById('PipelineCountInput') as HTMLInputElement;
        switch (pipelineVal) {
            case 0:
            case 3:
                result_forward.setAttribute("disabled", "disabled");
                break;
            case 1:
            case 2:
                result_forward.removeAttribute("disabled");
                break;
        }
        switch (pipelineVal) {
            case 0:
                jump_pred.setAttribute("disabled", "disabled");
                break;
            case 3:
            case 1:
            case 2:
                jump_pred.removeAttribute("disabled");
                break;
        }
        switch (pipelineVal) {
            case 0:
            case 1:
                pipelinecount.setAttribute("disabled", "disabled");
                break;
            case 3:
            case 2:
                pipelinecount.removeAttribute("disabled");
                break;
        }
    }

    /**
     * update chosen jump prediction
     */
    public static JumpChanged() {
        let jump_select = document.getElementById('JumpSelectSim') as HTMLInputElement;
        Main.Settings.JumpPrediction = parseInt(jump_select.value);
    }

    /**
     * updates if jump prediction is active
     */
    public static ResultForwardChanged() {
        let checkbox = document.getElementById('ResultForwardCheckbox') as HTMLInputElement;
        Main.Settings.ResultForward = checkbox.checked;
    }

    /**
     * updates amount of parallel pipeline
     */
    public static PipelineCountChanged() {
        let count = document.getElementById('PipelineCountInput') as HTMLInputElement;
        let valuePipeline = parseInt(count.value);
        if (valuePipeline < 1) {
            count.value = "4";
            alert("Bitte eine Zahl Anzahl paralleler Pipelines größer 0 auswählen.")
            return;
        }
        Main.Settings.Pipelines = valuePipeline;
    }

    /**
     * updates displayed cycles
     */
    public static displayedtaktSpanChanged() {
        let count = document.getElementById('DisplayedTaktSpanInput') as HTMLInputElement;
        let valuePipeline = parseInt(count.value);
        if (valuePipeline < 10) {
            count.value = "9";
            valuePipeline = 9;
            alert("Bitte eine größere Anzahl der dargestellten Takte auswählen.")
        } else if (valuePipeline >= 100) {
            count.value = "99";
            valuePipeline = 99;
            alert("Bitte eine kleinere Anzahl der dargestellten Takte auswählen.")
        }
        Main.Settings.DisplayedInterval = valuePipeline;
    }
}
