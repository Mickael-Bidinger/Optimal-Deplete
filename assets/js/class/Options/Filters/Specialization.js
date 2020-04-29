'use strict';

import {AbstractFilter} from "./AbstractFilter";

export class Specialization extends AbstractFilter {

    constructor(filters) {
        super('specialization', filters);

        this.families = {class: {}, role: {}};
        this.specs = {};

        this.container.querySelectorAll('a[data-type="class"], a[data-type="role"]').forEach(element => {
            const type = element.dataset.type;
            const id = element.dataset[type];
            element.addEventListener('click', this.onClickFamily.bind(this, element));
            this.families[type][id] = {
                element: element,
                specElements: []
            };
        });
        this.container.querySelectorAll('a[data-type="specialization"]').forEach(element => {
            const roleId = element.dataset.role;
            const classId = element.dataset.class;
            const id = element.dataset.specialization;
            if (this.specs[id] === undefined) {
                this.specs[id] = {roleId: roleId, classId: classId, elements: []};
            }
            this.specs[id].elements.push(element);
            this.families.class[classId].specElements.push(element);
            this.families.role[roleId].specElements.push(element);
        });

        this.selectedFilters.forEach(id => this.addSpec(id));
    }

    addSpec(id) {
        const classId = this.specs[id].classId;
        const roleId = this.specs[id].roleId;

        this.specs[id].elements.forEach(spec => spec.classList.add('selected'));
        if (this.isFamilySelected('role', roleId)) {
            this.families.role[roleId].element.classList.add('selected');
        }
        if (this.isFamilySelected('class', classId)) {
            this.families.class[classId].element.classList.add('selected');
        }
        this.families.role[roleId].element.classList.add('partial');
        this.families.class[classId].element.classList.add('partial');

        if (!this.selectedFilters.includes(id)) {
            this.selectedFilters.push(id);
        }
    }

    dispatchEvent() {
        if (this.selectedFilters.length === Object.keys(this.filters).length / 2) {
            this.container.dispatchEvent(new CustomEvent('update', {detail: []}));
            return;
        }
        this.container.dispatchEvent(new CustomEvent('update', {detail: this.selectedFilters}));
    }

    isFamilyPartial(type, id) {
        return this.families[type][id].specElements.some(spec => this.selectedFilters.includes(spec.dataset.specialization));
    }

    isFamilySelected(type, id) {
        return this.families[type][id].specElements.every(spec => this.selectedFilters.includes(spec.dataset.specialization));
    }

    onClickFamily(element, event) {
        event.preventDefault();
        const type = element.dataset.type;
        const id = element.dataset[type];
        this.toggleFamily(type, id);

        this.dispatchEvent();

        if (this.isEmpty()) {
            this.aElement.classList.remove('checked');
            return;
        }
        this.aElement.classList.add('checked');
    }

    onClickFilter(id, event) {
        super.onClickFilter(id, event);

        if (this.selectedFilters.includes(id)) {
            this.addSpec(id);
            return;
        }
        this.removeSpec(id);
    }

    removeSpec(id) {
        const classId = this.specs[id].classId;
        const index = this.selectedFilters.indexOf(id);
        const roleId = this.specs[id].roleId;

        this.specs[id].elements.forEach(spec => spec.classList.remove('selected'));
        if (!this.isFamilyPartial('role', roleId)) {
            this.families.role[roleId].element.classList.remove('partial');
        }
        if (!this.isFamilyPartial('class', classId)) {
            this.families.class[classId].element.classList.remove('partial');
        }
        this.families.role[roleId].element.classList.remove('selected');
        this.families.class[classId].element.classList.remove('selected');

        if (index !== -1) {
            this.selectedFilters.splice(index, 1);
        }
    }

    reset() {
        super.reset();
        Object.values(this.families.class).forEach(_class => _class.element.classList.remove('selected', 'partial'));
        Object.values(this.families.role).forEach(role => role.element.classList.remove('selected', 'partial'));
        Object.values(this.specs).forEach(spec => spec.elements.forEach(element => element.classList.remove('selected')));
    }

    toggleFamily(type, id) {
        if (this.isFamilySelected(type, id)) {
            this.families[type][id].element.classList.remove('selected', 'partial');
            this.families[type][id].specElements.forEach(spec => this.removeSpec(spec.dataset.specialization));
            return;
        }
        this.families[type][id].element.classList.add('selected');
        this.families[type][id].specElements.forEach(spec => this.addSpec(spec.dataset.specialization));
    }

}