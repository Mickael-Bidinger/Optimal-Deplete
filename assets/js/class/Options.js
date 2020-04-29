'use strict';

import {Filters} from './Options/Filters';
import {Sortings} from "./Options/Sortings";
import * as utilities from "../lib/utilities";

export class Options {

    constructor(filters) {
        this.updateHtml();
        new Filters(filters);
        new Sortings();
    }

    updateHtml() {
        const filters = [
            'specialization',
            'dungeon',
            'affix',
            'level',
            'faction',
        ];

        filters.forEach(filter => {
            const div = document.getElementById(filter);
            utilities.get(`filters/${filter}`, response => div.innerHTML = response);
        });
    }

}