export class StingerSoftAggridKeyCreator {
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    private static keyCreators: Record<string, any> = {}

    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    public static getKeyCreator = function (this: typeof StingerSoftAggridKeyCreator, keyCreator: string, keyCreatorParams: any = {}): any {
        //Default to null -> Uses the default formatter
        let aggridKeyCreator = null;
        if (keyCreator in this.keyCreators && typeof this.keyCreators [keyCreator] === 'function') {
            const finalKeyCreatorParams = keyCreatorParams || {};
            aggridKeyCreator = this.keyCreators[keyCreator](finalKeyCreatorParams);
        } else {
            console.warn(`KeyCreator "${keyCreator}" not found! Returning agGrid default function`);
        }
        return aggridKeyCreator;
    }

    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    public static registerKeyCreator(name: string, func: any): void {
        this.keyCreators[name] = func;
    }
}

// eslint-disable-next-line @typescript-eslint/no-explicit-any
export function UserCreator(_keyCreatorParams: any): (params: any) => string | undefined {
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    return function (params: any): string | undefined {
        if (params.value !== "" && typeof params.value !== "undefined" && params.value !== null) {
            return `${params.value.firstname}|${params.value.surname}`;
        }
        return undefined;
    };
}
StingerSoftAggridKeyCreator.registerKeyCreator('UserCreator', UserCreator);
