'use strict';

import {Affix} from "./Filters/Affix";
import {Dungeon} from "./Filters/Dungeon";
import {Faction} from "./Filters/Faction";
import {Level} from "./Filters/Level";
import {Specialization} from "./Filters/Specialization";

export class Filters {

    constructor(filters) {
        this.aElements = document.querySelectorAll('a[data-type="filter"]');
        this.container = document.getElementById('filter');
        this.filters = filters;
        this.instances = {};
        this.observer = new MutationObserver(this.onObserved.bind(this));
        this.reset = document.querySelector('a[data-type="reset"]');
        this.subContainers = document.querySelectorAll('div[data-type="filter"]');


        this.subContainers.forEach(container => {
                this.observer.observe(container, {childList: true});
                container.addEventListener('update', this.onUpdateFilter.bind(this, container.id));
            }
        );
        this.aElements.forEach(aElement =>
            aElement.addEventListener('click', this.onClickAElement.bind(this, aElement.dataset.option))
        );
        this.reset.addEventListener('click', this.onClickReset.bind(this));
        this.updateReset();
    }

    isEmpty() {
        let isEmpty = true;
        Object.values(this.filters).forEach(filter => {
            if (filter.length) {
                isEmpty = false;
            }
        });

        return isEmpty;
    }

    onClickAElement(option, event) {
        event.preventDefault();

        this.aElements.forEach(aElement => {
            if (aElement.dataset.option === option) {
                aElement.classList.toggle('selected');
                return;
            }
            aElement.classList.remove('selected');
        });
        this.subContainers.forEach(container => {
            if (container.dataset.option === option) {
                container.classList.toggle('hidden');
                return;
            }
            container.classList.add('hidden');
        });
    }

    onClickReset(event) {
        event.preventDefault();
        Object.values(this.instances).forEach(instance => instance.reset());
        this.aElements.forEach(aElement => aElement.classList.remove('selected'));
        this.subContainers.forEach(container => container.classList.add('hidden'));
        this.filters = {};
        this.reset.classList.add('hidden');

        const customEvent = new CustomEvent('update', {detail: this.filters});
        this.container.dispatchEvent(customEvent);

    }

    onObserved(mutationList) {
        switch (mutationList[0].target.id) {
            case 'specialization':
                this.instances.spec = new Specialization(this.filters.specialization);
                break;
            case 'dungeon':
                this.instances.dungeon = new Dungeon(this.filters.dungeon);
                break;
            case 'affix':
                this.instances.affix = new Affix(this.filters.affix);
                break;
            case 'level':
                this.instances.level = new Level(this.filters.level);
                break;
            case 'faction':
                this.instances.faction = new Faction(this.filters.faction);
        }
        if (Object.keys(this.instances).length === 5) {
            this.observer.disconnect();
        }
    }

    onUpdateFilter(filter, event) {
        this.filters[filter] = event.detail;

        const customEvent = new CustomEvent('update', {detail: this.filters});
        this.container.dispatchEvent(customEvent);
        this.updateReset();

    }

    updateReset() {
        if (this.isEmpty()) {
            this.reset.classList.add('hidden');
            return;
        }
        this.reset.classList.remove('hidden');
    }
}