'use strict';

import {AbstractFilter} from "./AbstractFilter";

export class Affix extends AbstractFilter {
    constructor(filters) {
        super('affix', filters);
    }

}