/**
 * this class represents a memory cell
 */
class MemoryCell {
    /**
     * @private name of memory cell
     */
    private i_name: number;

    /**
     * @private value of memory cell
     */
    private i_value: number;

    /**
     * constructor of memory cell
     * @param {number} name name der memory cell
     * @param {number} value value of memory cell
     */
    public constructor(name: number, value: number) {
        this.i_name = name;
        this.i_value = value;
    }

    /**
     * return name of memory cell
     */
    public get name() : string {
        if (Main.Settings.MemoryHexAdresses) {
            return "0x" + ("000000" + this.i_name.toString(16)).substr(-6);
        }
        return ("000000" + this.i_name.toString()).substr(-6);
    }

    /**
     * return value of memory cell
     */
    public get value() : number {
        return this.i_value;
    }

    /**
     * sets new value of a memory cell
     * @param {number} new_value new value of memory cell
     */
    public set value(new_value: number) {
        this.i_value = new_value;
    }
}