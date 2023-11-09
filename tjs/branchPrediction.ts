/**
 * represents jump prediction in frontend
 */
class branchPrediction {

    /**
     * @private data of jump prediction
     */
    private data: Array<{ Name: number, state: number }>;

    /**
     * Initializes variables
     */
    public constructor() {
        this.data = new Array<{ Name: number; state: number }>();
        for (let i = 0; i < 64; i++) {
            this.data[i] = {
                Name: i + 1,
                state: 0
            };
        }
    }

    /**
     * generates HTML table for current jump prediction state
     */
    public createTable(): string {
        let result = "";
        for (let i = 0; i < 64; i++) {
            result = result + "<tr>";
            result = result + "<td>" + this.data[i].Name + "</td>";
            result = result + "<td>" + this.data[i].state + "</td>";
            result = result + "</tr>";
        }
        return result;
    }

    /**
     * loads certain jump prediction state
     * @param {Array<number>} input state of jump prediction
     */
    public loadData(input: Array<number>): void {
        try {
            for (let i = 0; i < 64; i++) {
                if (i in input) {
                    this.data[i].state = input[i];
                } else {
                    this.data[i].state = 0;
                }
            }
        } catch (e) {
            for (let i = 0; i < 64; i++) {
                this.data[i].state = 0;
            }
        }
    }
}