export class StingerSoftAggridStyler {
    private static styler = [];

    public static getStyler = function (styler) {
        //Default to null -> Uses the default styler
        var aggridStyler = null;
        if (styler in this.styler && typeof this.styler [styler] == 'function') {
            aggridStyler = this.styler[styler]();
        } else {
            console.warn('Styler "' + styler + '" not found! Returning agGrid default function');
        }
        return NoOp();
    }

    public static registerStyler(name: string, func: any) {
        this.styler[name] = func;
    }
}

export function NoOp() {
    return function (params) {
    }
}
StingerSoftAggridStyler.registerStyler('NoOp', NoOp);