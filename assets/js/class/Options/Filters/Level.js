'use strict';

import {AbstractFilter} from "./AbstractFilter";

export class Level extends AbstractFilter {
    constructor(filters) {
        super('level', filters);
        this.maxInput = this.container.querySelector('[data-type="level-max"]');
        this.minInput = this.container.querySelector('[data-type="level-min"]');

        this.realMax = parseInt(this.maxInput.dataset.max);
        this.realMin = parseInt(this.minInput.dataset.min);
        this.max = 9999;
        this.min = 0;
        this.previousMax = this.realMax;
        this.previousMin = this.realMin;

        this.maxInput.addEventListener('input', this.onInput.bind(this, 'max'));
        this.minInput.addEventListener('input', this.onInput.bind(this, 'min'));

        this.updateMinMax();
    }

    getMax(input) {
        this.previousMax = this.max;
        let value = input.value.replace(/\D/g, '');

        if (value === '') {
            this.max = this.realMax;
            input.value = '';
            return;
        }

        value = parseInt(value);
        if (value > 9999) {
            input.value = '9999';
            this.max = this.realMax;
            return;
        }

        input.value = value.toString();
        if (value > this.realMax) {
            this.max = this.realMax;
            return;
        }

        this.max = value;
    }

    getMin(input) {
        this.previousMin = this.min;
        let value = input.value.replace(/\D/g, '');

        if (value === '') {
            this.min = this.realMin;
            input.value = '';
            return;
        }

        value = parseInt(value);
        if (value < 0) {
            input.value = '0';
            this.min = this.realMin;
            return;
        }

        input.value = value.toString();
        if (value < this.realMin) {
            this.min = this.realMin;
            return;
        }

        this.min = value;
    }

    isSequential() {
        if (!this.selectedFilters.length) {
            return false;
        }
        this.selectedFilters.sort((a, b) => a - b);
        return this.selectedFilters.every((a, i, aa) => !i || parseInt(aa[i - 1]) === a - 1);
    }

    onClickFilter(filter, event) {
        super.onClickFilter(filter, event);
        this.updateMinMax();
    }

    onInput(type, event) {
        const input = event.target;
        if (type === 'max') {
            this.getMax(input);
        }
        if (type === 'min') {
            this.getMin(input);
        }

        if (this.previousMax !== this.max || this.previousMin !== this.min) {
            this.updateSelectedFilters();
            this.updateChecked();
            this.dispatchEvent();
        }
    }

    reset() {
        super.reset();
        this.resetInput();
    }

    resetInput() {
        this.maxInput.value = '';
        this.minInput.value = '';
        this.max = 9999;
        this.min = 0;
        this.previousMax = this.realMax;
        this.previousMin = this.realMin;
    }

    setMax(max) {
        this.maxInput.value = max;
        this.max = max;
        this.previousMax = max;
    }

    setMin(min) {
        this.minInput.value = min;
        this.min = min;
        this.previousMin = min;
    }

    updateMinMax() {
        if (this.isSequential()) {
            this.setMax(Math.max(...this.selectedFilters));
            this.setMin(Math.min(...this.selectedFilters));
            return;
        }
        this.resetInput();
    }

    updateSelectedFilters() {
        Object.values(this.filters).forEach(levelElement => {
            const level = levelElement.dataset.level;
            if ((level >= this.min && level <= this.max) || (level <= this.min && level >= this.max)) {
                levelElement.classList.add('selected');
                if (this.selectedFilters.includes(level)) {
                    return;
                }
                this.selectedFilters.push(level);
                return;
            }
            levelElement.classList.remove('selected');
            const index = this.selectedFilters.indexOf(level);
            if (index !== -1) {
                this.selectedFilters.splice(index, 1);
            }
        });

        return this;
    }

}