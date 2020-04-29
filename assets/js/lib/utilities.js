'use strict';

export function get(pagePath, callback) {
    const request = new XMLHttpRequest();
    request.open('GET', getRequestUrl(pagePath));
    request.send();

    request.addEventListener('load', () => {
        if (request.status === 200) {
            callback(request.responseText);
        } else {
            console.log('Erreur de la réponse à la requête : %d (%s)', request.status, request.statusText);
        }
    });
}

export function getJSON(pagePath, callback) {
    const request = new XMLHttpRequest();
    request.open('GET', getRequestUrl(pagePath), false);
    request.responseType = 'json';
    request.send('myarray=something');

    if (request.status === 200) {
        callback(JSON.parse(request.responseText));
    } else {
        console.log('Erreur de la réponse à la requête : %d (%s)', request.status, request.statusText);
    }
}

export function getOffset(element) {
    const offset = {top: 0, right: 0, bottom: 0, left: 0};
    let childRight = element.offsetWidth;
    let childBottom = element.offsetHeight;

    while (element) {
        offset.top += element.offsetTop;
        offset.right += element.offsetWidth - childRight;
        offset.bottom += element.offsetHeight - childBottom;
        offset.left += element.offsetLeft;
        childRight = element.offsetLeft + element.offsetWidth;
        childBottom = element.offsetTop + element.offsetHeight;
        element = element.offsetParent;
    }

    return offset;
}

export function getRequestUrl(path) {
    if (document.location.pathname.includes('public')) {
        return `${document.location.origin}/projects/optimal-deplete/public/${path}`;
    }
    return `${document.location.origin}/${path}`;
}

export function post(pagePath, values, callback) {
    const request = new XMLHttpRequest();
    request.open('POST', getRequestUrl(pagePath));
    request.setRequestHeader('Content-Type', 'application/json');
    request.send(JSON.stringify(values));

    request.addEventListener('load', () => {
        if (request.status === 200) {
            callback(request.responseText);
        } else {
            console.log('Erreur de la réponse à la requête : %d (%s)', request.status, request.statusText);
        }
    });
}

export function postJSON(pagePath, values, callback) {
    const request = new XMLHttpRequest();
    request.open('POST', getRequestUrl(pagePath));
    request.setRequestHeader('Content-Type', 'application/json');
    request.send(JSON.stringify(values));

    request.addEventListener('load', () => {
        if (request.status === 200) {
            callback(JSON.parse(request.responseText));
        } else {
            console.log('Erreur de la réponse à la requête : %d (%s)', request.status, request.statusText);
        }
    });
}