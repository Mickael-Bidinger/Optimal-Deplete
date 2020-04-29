'use strict';

import {Updater} from "./Graphic/Updater";
import {DetailUpdater} from "./Graphic/DetailUpdater";

export class Graphic {

    constructor(options) {
        new Updater(options);
        new DetailUpdater();

    }

}