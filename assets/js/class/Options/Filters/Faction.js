'use strict';

import {AbstractFilter} from "./AbstractFilter";

export class Faction extends AbstractFilter {

    constructor(filters) {
        super('faction', filters);
    }

}