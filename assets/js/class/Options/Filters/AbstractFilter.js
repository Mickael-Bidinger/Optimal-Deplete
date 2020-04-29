'use strict';

export class AbstractFilter {

    constructor(type, filters) {
        if (this.constructor === AbstractFilter) {
            throw new TypeError('Abstract class "AbstractFilter" cannot be instantiated directly');
        }
        this.type = type;

        this.aElement = document.querySelector(`a[data-option="${this.type}"]`);
        this.container = document.getElementById(this.type);
        this.filters = {};
        this.selectedFilters = filters === undefined ? [] : filters;

        this.container.querySelectorAll(`a[data-type="${this.type}"]`).forEach(element => {
            const id = element.dataset[this.type];
            this.filters[id] = element;
            element.addEventListener('click', this.onClickFilter.bind(this, id));
        });

        this.selectedFilters.forEach(id => this.filters[id].classList.toggle('selected'));
        this.updateChecked();
    }

    dispatchEvent() {
        if (this.selectedFilters.length === Object.keys(this.filters).length) {
            this.container.dispatchEvent(new CustomEvent('update', {detail: []}));
            return;
        }
        this.container.dispatchEvent(new CustomEvent('update', {detail: this.selectedFilters}));
    }

    isEmpty() {
        return this.selectedFilters.length === 0;
    }

    onClickFilter(id, event) {
        event.preventDefault();
        this.toggle(id);
        this.dispatchEvent();
        this.updateChecked();
    }

    reset() {
        this.aElement.classList.remove('checked');
        Object.values(this.filters).forEach(filter => filter.classList.remove('selected'));
        this.selectedFilters = [];

        return this;
    }

    toggle(id) {
        this.filters[id].classList.toggle('selected');
        const index = this.selectedFilters.indexOf(id);

        if (index !== -1) {
            this.selectedFilters.splice(index, 1);
            return this;
        }

        this.selectedFilters.push(id);
        return this;
    }

    updateChecked() {
        if (this.isEmpty()) {
            this.aElement.classList.remove('checked');
            return;
        }
        this.aElement.classList.add('checked');
    }
}