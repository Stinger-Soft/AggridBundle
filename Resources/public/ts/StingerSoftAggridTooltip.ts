export class StingerSoftAggridTooltip {
    private static tooltip = [];

    public static getTooltip = function (tooltip: string, tooltipParams: any = {}) {
        //Default to null -> Uses the default formatter
        var aggridTooltip = null;
        if (tooltip in this.tooltip && typeof this.tooltip [tooltip] == 'function') {
            var finalTooltipParams = tooltipParams || {};
            aggridTooltip = this.tooltip[tooltip](finalTooltipParams);
        } else {
            console.warn('Tooltip "' + tooltip + '" not found! Returning agGrid default function');
        }
        return aggridTooltip;
    }

    public static registerTooltip(name: string, func: any) {
        this.tooltip[name] = func;
    }
}
// StingerSoftAggridTooltip.registerTooltip('DatePicker', DatePicker);