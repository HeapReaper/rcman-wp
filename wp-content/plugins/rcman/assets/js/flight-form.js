/**
 * RC Manager – public flight registration form.
 * Port of the inline scripts of Kiyomi's member_reg_new_flight view.
 */
(function () {
    'use strict';

    var STORAGE_NAME = 'rcman_pilot_name';
    var STORAGE_ANSWER = 'rcman_anti_bot';

    function storage() {
        try {
            var key = '__rcman_test__';
            window.localStorage.setItem(key, key);
            window.localStorage.removeItem(key);
            return window.localStorage;
        } catch (e) {
            return null;
        }
    }

    function pad(n) {
        return n < 10 ? '0' + n : String(n);
    }

    function amsterdamNow(addMinutes) {
        var now = new Date();
        var local = new Date(now.toLocaleString('en-US', { timeZone: 'Europe/Amsterdam' }));
        local.setMinutes(local.getMinutes() + (addMinutes || 0));
        return local;
    }

    function formatTime(date) {
        return pad(date.getHours()) + ':' + pad(date.getMinutes());
    }

    function formatDate(date) {
        return date.getFullYear() + '-' + pad(date.getMonth() + 1) + '-' + pad(date.getDate());
    }

    function init(form) {
        var store = storage();
        var name = form.querySelector('[name="name"]');
        var nameHelp = form.querySelector('#rcman-name-help');
        var date = form.querySelector('[name="date"]');
        var start = form.querySelector('[name="start_time"]');
        var end = form.querySelector('[name="end_time"]');
        var modelType = form.querySelector('[name="model_type"]');
        var powerClass = form.querySelector('[name="power_class"]');
        var antiBot = form.querySelector('[name="anti_bot"]');
        var antiBotWrap = form.querySelector('#rcman-anti-bot');

        var glider = form.getAttribute('data-glider');
        var noPower = form.getAttribute('data-no-power');

        if (!date.value) {
            date.value = formatDate(amsterdamNow());
        }

        if (store) {
            var savedName = store.getItem(STORAGE_NAME);
            if (savedName && !name.value) {
                name.value = savedName;
            }
            if (savedName && nameHelp) {
                nameHelp.hidden = true;
            }

            // After a successful submit the anti-bot answer is remembered on this device;
            // after a failed submit it is forgotten so the question shows again.
            var state = form.getAttribute('data-state');
            var savedAnswer = store.getItem(STORAGE_ANSWER);
            var pending = null;
            try {
                pending = window.sessionStorage.getItem(STORAGE_ANSWER);
                window.sessionStorage.removeItem(STORAGE_ANSWER);
            } catch (e) {
                pending = null;
            }
            if (state === 'success' && pending) {
                savedAnswer = pending;
                store.setItem(STORAGE_ANSWER, savedAnswer);
            } else if (state === 'error') {
                savedAnswer = null;
                store.removeItem(STORAGE_ANSWER);
            }
            if (savedAnswer) {
                antiBot.value = savedAnswer;
                antiBotWrap.hidden = true;
            }
        }

        form.querySelector('[data-rcman="today"]').addEventListener('click', function () {
            date.value = formatDate(amsterdamNow());
        });

        form.querySelector('[data-rcman="start"]').addEventListener('click', function () {
            start.value = formatTime(amsterdamNow(0));
            end.value = formatTime(amsterdamNow(5));
        });

        form.querySelector('[data-rcman="end"]').addEventListener('click', function () {
            end.value = formatTime(amsterdamNow(0));
        });

        // Gliders without motor always have 0W; other models never have 0W.
        var powerOptions = Array.prototype.slice.call(powerClass.options);
        function syncPowerOptions() {
            var isGlider = modelType.value === glider;
            powerOptions.forEach(function (option) {
                if (option.value === '') {
                    return;
                }
                option.hidden = isGlider ? option.value !== noPower : option.value === noPower;
            });
            if (isGlider) {
                powerClass.value = noPower;
            } else if (powerClass.value === noPower) {
                powerClass.value = '';
            }
        }
        modelType.addEventListener('change', syncPowerOptions);
        if (modelType.value) {
            syncPowerOptions();
        }

        form.addEventListener('submit', function () {
            if (!store) {
                return;
            }
            store.setItem(STORAGE_NAME, name.value);
            try {
                window.sessionStorage.setItem(STORAGE_ANSWER, antiBot.value);
            } catch (e) {
                // ignore
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        var forms = document.querySelectorAll('.rcman-flight-form');
        Array.prototype.forEach.call(forms, init);
    });
})();
