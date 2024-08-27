export class StingerSoftAggridKeyCreator {
    private static keyCreators = [];

    public static getKeyCreator = function (keyCreator: string, keyCreatorParams: any = {}) {
        //Default to null -> Uses the default formatter
        var aggridKeyCreator = null;
        if (keyCreator in this.keyCreators && typeof this.keyCreators [keyCreator] == 'function') {
            var finalKeyCreatorParams = keyCreatorParams || {};
            aggridKeyCreator = this.keyCreators[keyCreator](finalKeyCreatorParams);
        } else {
            console.warn('KeyCreator "' + keyCreator + '" not found! Returning agGrid default function');
        }
        return aggridKeyCreator;
    }

    public static registerKeyCreator(name: string, func: any) {
        this.keyCreators[name] = func;
    }
}

export function UserCreator(keyCreatorParams) {
    return function (params) {
        if (params.value !== "" && typeof params.value !== "undefined" && params.value !== null) {
            return params.value.firstname + "|" + params.value.surname;
        }
    };
};
StingerSoftAggridKeyCreator.registerKeyCreator('UserCreator', UserCreator);