export class StingerSoftAggridTooltip {
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    private static tooltip: Record<string, any> = {}

    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    public static getTooltip = function (this: typeof StingerSoftAggridTooltip, tooltip: string, tooltipParams: any = {}): any {
        //Default to null -> Uses the default formatter
        let aggridTooltip = null;
        if (tooltip in this.tooltip && typeof this.tooltip [tooltip] === 'function') {
            const finalTooltipParams = tooltipParams || {};
            aggridTooltip = this.tooltip[tooltip](finalTooltipParams);
        } else {
            console.warn(`Tooltip "${tooltip}" not found! Returning agGrid default function`);
        }
        return aggridTooltip;
    }

    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    public static registerTooltip(name: string, func: any): void {
        this.tooltip[name] = func;
    }
}
// StingerSoftAggridTooltip.registerTooltip('DatePicker', DatePicker);
