/**
 * this class represents the memory
 */
class Memory {

    /**
     * @private holds all memory Cell with name and value of this memory
     */
    private i_memory: Array<MemoryCell>;

    /**
     * inits Memory-Class with 31 empty memory cells
     */
    public constructor() {
        this.i_memory = new Array<MemoryCell>();
        for (let i = 0; i < 32; i++) {
            let name = i * 4;
            this.i_memory[i] = new MemoryCell(name, 0);
        }
    }

    /**
     * loads new Data into structure
     * @param input input data as array
     */
    public loadData(input: Array<{ adr: number, val: number }>) {
        this.i_memory = new Array<MemoryCell>();
        try{
            for (let i = 0; i < 32; i++) {
                let name = i * 4;
                if (name in input) {
                    this.i_memory[i] = new MemoryCell(input[name].adr, input[name].val);
                } else {
                    this.i_memory[i] = new MemoryCell(name, 0);
                }
            }
        } catch (e) {
            for (let i = 0; i < 32; i++) {
                let name = i * 4;
                this.i_memory[i] = new MemoryCell(name, 0);
            }
        }
    }

    /**
     * returns this memory
     * @return {Array<MemoryCell>} structured array
     */
    public get content() {
        return this.i_memory;
    }

    /**
     * returns count of cells in this memory
     * @return {number} count of cells
     */
    public get length() {
        return this.i_memory.length;
    }
}