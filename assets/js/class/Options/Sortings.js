'use strict';


export class Sortings {
    constructor() {
        this.container = document.getElementById('sorting');

        this.container.addEventListener('click', this.onClickContainer.bind(this));
    }

    onClickContainer(event) {
        if (event.target.dataset.type !== 'sorting') {
            return;
        }
        event.preventDefault();

        const customEvent = new CustomEvent('update', {detail: event.target.dataset.sorting});
        this.container.dispatchEvent(customEvent);
    }
}