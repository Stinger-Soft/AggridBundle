export type ComparatorFunction = (valueA: unknown, valueB: unknown, nodeA: unknown, nodeB: unknown, isInverted?: boolean) => number;


export class StingerSoftAggridComparator {
    private static comparator: Record<string, ComparatorFunction> = {};

    public static getComparator = function (this: typeof StingerSoftAggridComparator, comparator: string): ComparatorFunction | null {
        //Default to null -> Uses the default comparator
        let aggridComparator: ComparatorFunction | null = null;
        if (comparator in this.comparator && typeof this.comparator[comparator] === 'function') {
            aggridComparator = this.comparator[comparator];
        } else {
            console.warn(`Comparator "${comparator}" not found! Returning agGrid default function`);
        }
        return aggridComparator;
    }

    public static registerComparator(name: string, func: ComparatorFunction): void {
        this.comparator[name] = func;
    }
}

// eslint-disable-next-line @typescript-eslint/no-explicit-any
export function DefaultComparator(valueA: any, valueB: any, _nodeA: unknown, _nodeB: unknown, _isInverted?: boolean): number {
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
}
StingerSoftAggridComparator.registerComparator('DefaultComparator', DefaultComparator);

/**
 *
 * @return {number}
 * @constructor
 */
// eslint-disable-next-line @typescript-eslint/no-explicit-any
export function ValueComparator(valueA: any, valueB: any, nodeA: unknown, nodeB: unknown, isInverted = false): number {
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
// eslint-disable-next-line @typescript-eslint/no-explicit-any
export function DisplayValueComparator(valueA: any, valueB: any, nodeA: unknown, nodeB: unknown, isInverted = false): number {
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
// eslint-disable-next-line @typescript-eslint/no-explicit-any
export function DateComparator(valueA: any, valueB: any, nodeA: unknown, nodeB: unknown, isInverted = false): number {
    let dateA = null;
    let dateB = null;
    if (valueA?.value !== null && valueA?.value !== undefined) {
        dateA = valueA.value.hasOwnProperty('date') ? new Date(valueA.value.date) : null;
    }
    if (valueB?.value !== null && valueB?.value !== undefined) {
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
export function NoopComparator(_valueA: unknown, _valueB: unknown, _nodeA: unknown, _nodeB: unknown, _isInverted = false): number {
    return 0;
}
StingerSoftAggridComparator.registerComparator('NoopComparator', NoopComparator);
