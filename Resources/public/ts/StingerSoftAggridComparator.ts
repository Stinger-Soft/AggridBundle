export class StingerSoftAggridComparator {
    private static comparator = [];

    public static getComparator = function (comparator) {
        //Default to null -> Uses the default comparator
        var aggridComparator = null;
        if (comparator in this.comparator && typeof this.comparator [comparator] == 'function') {
            aggridComparator = this.comparator[comparator];
        } else {
            console.warn('Comparator "' + comparator + '" not found! Returning agGrid default function');
        }
        return aggridComparator;
    }

    public static registerComparator(name: string, func: any) {
        this.comparator[name] = func;
    }
}

export function DefaultComparator(valueA, valueB, nodeA, nodeB, isInverted) {
    if (valueA === null && valueB === null) {
        return 0;
    }
    if (valueA === null) {
        return 1;
    }
    if (valueB === null) {
        return -1;
    }
    if (valueA < valueB) {
        return -1;
    }
    if (valueB < valueA) {
        return 1;
    }
    return 0;
};
StingerSoftAggridComparator.registerComparator('DefaultComparator', DefaultComparator);

/**
 *
 * @return {number}
 * @constructor
 */
export function ValueComparator(valueA, valueB, nodeA, nodeB, isInverted) {
    let comparableValueA = null;
    if (valueA !== null && typeof valueA !== 'undefined') {
        comparableValueA = typeof valueA === 'object' && valueA.hasOwnProperty('value') ? valueA.value : valueA;
    }
    let comparableValueB = null;
    if (valueB !== null && typeof valueB !== 'undefined') {
        comparableValueB = typeof valueB === 'object' && valueB.hasOwnProperty('value') ? valueB.value : valueB;
    }
    return DefaultComparator(comparableValueA, comparableValueB, nodeA, nodeB, isInverted);
}
StingerSoftAggridComparator.registerComparator('ValueComparator', ValueComparator);

/**
 *
 * @return {number}
 * @constructor
 */
export function DisplayValueComparator(valueA, valueB, nodeA, nodeB, isInverted) {
    let comparableValueA = null;
    if (valueA !== null && typeof valueA !== 'undefined') {
        comparableValueA = typeof valueA === 'object' && valueA.hasOwnProperty('displayValue') ? valueA.displayValue : valueA;
    }
    let comparableValueB = null;
    if (valueB !== null && typeof valueB !== 'undefined') {
        comparableValueB = typeof valueB === 'object' && valueB.hasOwnProperty('displayValue') ? valueB.displayValue : valueB;
    }
    return DefaultComparator(comparableValueA, comparableValueB, nodeA, nodeB, isInverted);
}
StingerSoftAggridComparator.registerComparator('DisplayValueComparator', DisplayValueComparator);

/**
 *
 * @return {number}
 * @constructor
 */
export function DateComparator(valueA, valueB, nodeA, nodeB, isInverted) {
    var dateA = null;
    var dateB = null;
    if (valueA !== null && valueA !== undefined && valueA.value !== null && valueA.value !== undefined) {
        dateA = valueA.value.hasOwnProperty('date') ? new Date(valueA.value.date) : null;
    }
    if (valueB !== null && valueB !== undefined && valueB.value !== null && valueB.value !== undefined) {
        dateB = valueB.value.hasOwnProperty('date') ? new Date(valueB.value.date) : null;
    }
    return DefaultComparator(dateA, dateB, nodeA, nodeB, isInverted);
}
StingerSoftAggridComparator.registerComparator('DateComparator', DateComparator);

/**
 * Useful in set filters for mapped columns where the order is predefined by the php backend
 *
 * @return {number}
 * @constructor
 */
export function NoopComparator(valueA, valueB, nodeA, nodeB, isInverted) {
    return 0;
}
StingerSoftAggridComparator.registerComparator('NoopComparator', NoopComparator);