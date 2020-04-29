'use strict';

import * as utilities from '../../lib/utilities';

export class Updater {

    constructor(options) {
        this.data = options;
        this.data.id = 0;
        this.details = document.getElementById('details');
        this.filter = document.getElementById('filter');
        this.graphic = document.getElementById('graphic-container');
        this.h3 = document.querySelector('#graphic h3');
        this.sorting = document.getElementById('sorting');

        this.request();
        this.filter.addEventListener('update', this.onUpdateOption.bind(this));
        this.sorting.addEventListener('update', this.onUpdateSorting.bind(this));
    }

    onAjax(response) {
        if (this.data.id !== response.id) {
            return;
        }
        this.details.innerHTML = response.details;
        this.graphic.innerHTML = response.graphic;
        this.h3.textContent = response.h3;
        this.sorting.innerHTML = response.sorting;
    }

    stringifyData() {
        let url = '';
        if (document.location.pathname.includes('public')) {
            url += '/projects/optimal-deplete/public';
        }
        Object.keys(this.data.filters).forEach(key => {
            url += this.data.filters[key].length ? '/' + key + '-' + this.data.filters[key].join('-') : '';
        });
        url += this.data.sorting === '' ? '' : '/sorting-' + this.data.sorting;
        url = url === '' ? '/' : url;

        return url;
    }

    onUpdateOption(event) {
        this.data.filters = event.detail;
        this.request();
    }

    onUpdateSorting(event) {
        this.data.sorting = event.detail;
        this.request();
    }

    request() {
        this.data.id++;
        window.history.replaceState('', document.title, this.stringifyData());
        utilities.postJSON('graphic', this.data, this.onAjax.bind(this));
    }

}