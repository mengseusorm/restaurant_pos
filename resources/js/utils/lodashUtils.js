// Centralized lodash utility with lazy loading
class LodashUtils {
    static _lodash = null;

    static async loadLodash() {
        if (!this._lodash) {
            this._lodash = await import('lodash');
        }
        return this._lodash.default || this._lodash;
    }

    static async get(...methods) {
        const _ = await this.loadLodash();
        if (methods.length === 1) {
            return _[methods[0]];
        }
        return methods.reduce((acc, method) => {
            acc[method] = _[method];
            return acc;
        }, {});
    }

    static async debounce(func, wait) {
        const { debounce } = await this.get('debounce');
        return debounce(func, wait);
    }

    static async throttle(func, wait) {
        const { throttle } = await this.get('throttle');
        return throttle(func, wait);
    }

    static async cloneDeep(value) {
        const { cloneDeep } = await this.get('cloneDeep');
        return cloneDeep(value);
    }

    static async isEqual(value, other) {
        const { isEqual } = await this.get('isEqual');
        return isEqual(value, other);
    }

    static async uniq(array) {
        const { uniq } = await this.get('uniq');
        return uniq(array);
    }

    static async uniqBy(array, iteratee) {
        const { uniqBy } = await this.get('uniqBy');
        return uniqBy(array, iteratee);
    }

    static async groupBy(collection, iteratee) {
        const { groupBy } = await this.get('groupBy');
        return groupBy(collection, iteratee);
    }

    static async orderBy(collection, iteratees, orders) {
        const { orderBy } = await this.get('orderBy');
        return orderBy(collection, iteratees, orders);
    }

    static async find(collection, predicate, fromIndex = 0) {
        const { find } = await this.get('find');
        return find(collection, predicate, fromIndex);
    }

    static async filter(collection, predicate) {
        const { filter } = await this.get('filter');
        return filter(collection, predicate);
    }

    static async map(collection, iteratee) {
        const { map } = await this.get('map');
        return map(collection, iteratee);
    }

    static async forEach(collection, iteratee) {
        const { forEach } = await this.get('forEach');
        return forEach(collection, iteratee);
    }
}

export default LodashUtils;