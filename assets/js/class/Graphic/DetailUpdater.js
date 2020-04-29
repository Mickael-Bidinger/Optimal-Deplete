'use strict';

import * as utilities from "../../lib/utilities";

export class DetailUpdater {

    constructor() {
        this.container = document.querySelector('#svg > div');
        this.details = document.getElementById('details');
        this.detail = document.getElementById('detail');
        this.horizontalLine = document.getElementById('horizontal-line');
        this.verticalLine = document.getElementById('vertical-line');
        this.observer = new MutationObserver(this.onObserved.bind(this));
        this.stats = [];

        this.observer.observe(this.details, {childList: true});
        this.container.addEventListener('mousemove', this.onMousemove.bind(this));
        this.container.addEventListener('mouseenter', this.onMouseenter.bind(this));
        this.container.addEventListener('mouseleave', this.onMouseleave.bind(this));
    }

    getIndex(event, offset) {
        const index = Math.round((event.pageY - offset.top - 23) / 29);

        if (index > this.stats.length - 1) {
            return this.stats.length - 1;
        }
        if (index < 0) {
            return 0;
        }

        return index;
    }

    getPositions(event, offset) {
        const positions = {line: {}, detail: {}};
        const right = ((this.container.offsetWidth) + offset.right) - this.detail.offsetWidth - 10;
        let left = event.pageX - offset.left;
        let top = event.pageY - offset.top;

        positions.line.left = `${left}px`;
        positions.line.top = `${top}px`;

        top -= this.detail.offsetHeight;
        left += 70;
        top -= 30;

        positions.detail.left = `min(${left}px, ${right}px)`;
        positions.detail.top = `${top}px`;

        return positions;
    }

    onMouseenter() {
        if (!this.stats.length) {
            return;
        }
        this.verticalLine.classList.remove('hidden');
        this.horizontalLine.classList.remove('hidden');
    }

    onMouseleave() {
        if (!this.stats.length) {
            return;
        }
        this.verticalLine.classList.add('hidden');
        this.horizontalLine.classList.add('hidden');
        this.detail.classList.add('hidden');
    }

    onMousemove(event) {
        if (!this.stats.length) {
            return;
        }
        const offset = utilities.getOffset(this.container);
        const index = this.getIndex(event, offset);

        this.detail.classList.add('hidden');
        this.detail.removeAttribute('id');
        this.detail.replaceWith(this.stats[index]);
        this.detail.classList.remove('hidden');
        this.detail = this.stats[index];
        this.detail.id = 'detail';

        const positions = this.getPositions(event, offset);

        this.verticalLine.style.left = positions.line.left;
        this.horizontalLine.style.top = positions.line.top;
        this.detail.style.left = positions.detail.left;
        this.detail.style.top = positions.detail.top;

    }

    onObserved() {
        this.stats = [];
        this.details.childNodes.forEach(child => this.stats.push(child.cloneNode(true)));
    }

}