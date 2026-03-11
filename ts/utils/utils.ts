export function deepFind(obj: object|undefined, path: string): any {
    if (obj === undefined) {
        return null;
    }
    const paths = path.split('.');
    let current: any = obj;

    for (const segment of paths) {
        if (current[segment] === undefined || current[segment] === null) {
            return null;
        } else {
            current = current[segment];
        }
    }
    return current;
}

export function deepSet(obj: any, path: string, value: any): void {
    if (path.length === 1) {
        obj[path] = value;
        return;
    }
    deepSet(obj[path[0]], path.slice(1), value);
}

// eslint-disable-next-line @typescript-eslint/no-explicit-any
export function isConstructor(f: any): boolean {
    try {
         
        new f();
    } catch (_err) {
        // verify err is the expected error and then
        return false;
    }
    return true;
}
