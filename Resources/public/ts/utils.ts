export function deepFind(obj: object, path: string) {
    if (obj === undefined) {
        return null;
    }
    var paths = path.split('.')
        , current = obj
        , i;

    for (i = 0; i < paths.length; ++i) {
        if (current[paths[i]] == undefined) {
            return null;
        } else {
            current = current[paths[i]];
        }
    }
    return current;
}

export function deepSet(obj: object, path: string, value: any) {
    if (path.length === 1) {
        obj[path] = value;
        return;
    }
    deepSet(obj[path[0]], path.slice(1), value);
}

export function isConstructor(f) {
    try {
        new f();
    } catch (err) {
        // verify err is the expected error and then
        return false;
    }
    return true;
}
