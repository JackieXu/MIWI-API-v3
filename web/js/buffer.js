(function () {
    "use strict";

    var $ = function(id) {
            return document.getElementById(id);
        },
        $$ = function (element) {
            return document.createElement(element);
        },
        getFormValues = function () {
            return 'userId=' + userSelector.value +
                'interestId' + interestSelector.value +
                'title' + formSelector.elements['title'] +
                'body' + formSelector.elements['body'] +
                'date' + formSelector.elements['date'];
        },
        createBufferRow = function (title, date) {
            var rowElement = $$('tr'),
                titleElement = $$('td'),
                dateElement = $$('td');

            titleElement.innerHTML = title;
            dateElement.innerHTML = date;

            rowElement.appendChild(titleElement);
            rowElement.appendChild(dateElement);

            return rowElement;
        },
        addToBuffer = function (form, bufferContainer) {
            var xhr = new XMLHttpRequest(),
                formData = new FormData(form);

            console.log(formData);

            xhr.addEventListener('load', function () {
                getBuffer(formData.get('user'), bufferContainer);
            }, false);

            xhr.open('post', 'bulk-up/boost');
            xhr.send(getFormValues());
        },
        cleanBuffer = function (bufferContainer) {
            bufferContainer.innerHTML = '';
        },
        getBuffer = function (userId, bufferContainer) {
            var xhr = new XMLHttpRequest();

            xhr.addEventListener('load', function () {
                var data = JSON.parse(xhr.responseText);

                if (data) {
                    cleanBuffer(bufferContainer);
                    if (data['posts'].length === 0) {
                        bufferContainer.appendChild(createBufferRow('Nothing in buffer', 'at the moment'));
                    }
                    data['posts'].forEach(function (item) {
                        bufferContainer.appendChild(createBufferRow(item['title'], item['date']));
                    });
                }
            }, false);

            xhr.open('get', 'bulk-up/' + userId + '/buffer');
            xhr.send();
        },
        userSelector = $('user'),
        formSelector = $('buffer-form'),
        bufferContainer = $('buffer-body'),
        interestSelector = $('interest');

    userSelector.addEventListener('change', function () {
        if (!isNaN(this.value)) {
            getBuffer(this.value, bufferContainer);
        }
    }, false);

    formSelector.addEventListener('submit', function (event) {
        event.stopPropagation();
        event.preventDefault();

        addToBuffer(this, bufferContainer);

        return false;
    }, false);
}());