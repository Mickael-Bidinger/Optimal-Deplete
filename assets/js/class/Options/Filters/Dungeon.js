'use strict';

import {AbstractFilter} from "./AbstractFilter";

export class Dungeon extends AbstractFilter {

    constructor(filters) {
        super('dungeon', filters);
    }
}