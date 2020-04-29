'use strict';

import {Options} from "./class/Options";
import {Graphic} from "./class/Graphic";

function parseUrl() {
    let filters = {};
    let sorting = '';
    document.location.pathname.split('/').forEach(option => {
        option = option.split('-');
        if (option.length === 1 || option[0] === 'optimal') {
            return;
        }
        if (option[0] === 'sorting') {
            sorting = option[1];
            return;
        }
        const filter = option.shift();
        filters[filter] = option;
    });

    return {filters: filters, sorting: sorting};
}

document.addEventListener('DOMContentLoaded', () => {
    if (document.getElementById('filter') !== null) {
        const options = parseUrl();
        new Options(options.filters);
        new Graphic(options);
    }
});