export class StingerSoftAggridStyler {
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    private static styler: Record<string, any> = {}

    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    public static getStyler = function (this: typeof StingerSoftAggridStyler, styler: string): any {
        //Default to null -> Uses the default styler
        if (styler in this.styler && typeof this.styler [styler] === 'function') {
            this.styler[styler]();
        } else {
            console.warn(`Styler "${styler}" not found! Returning agGrid default function`);
        }
        return NoOp();
    }

    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    public static registerStyler(name: string, func: any): void {
        this.styler[name] = func;
    }
}

// eslint-disable-next-line @typescript-eslint/no-explicit-any
export function NoOp(): (_params: any) => void {
    // eslint-disable-next-line @typescript-eslint/no-explicit-any, @typescript-eslint/no-empty-function
    return function (_params: any) {}
}
StingerSoftAggridStyler.registerStyler('NoOp', NoOp);
