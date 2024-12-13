import {driver} from "driver.js";
import {initCssSelector} from './css-selector.js';

document.addEventListener('livewire:initialized', async function () {

    initCssSelector();

    let pluginData;
    let tours = [];
    let highlights = [];

    function waitForElement(selector, callback) {
        if (document.querySelector(selector)) {
            callback(document.querySelector(selector));
            return;
        }

        const observer = new MutationObserver(function (mutations) {
            if (document.querySelector(selector)) {
                callback(document.querySelector(selector));
                observer.disconnect();
            }
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    }

    function parseId(params) {
        if (Array.isArray(params)) {
            return params[0];
        } else if (typeof params === 'object') {
            return params.id;
        }

        return params;
    }

    Livewire.dispatch('filament-tour::load-elements', {request: window.location})

    Livewire.on('filament-tour::loaded-elements', function (data) {
        pluginData = data;
        pluginData.tours.forEach((tour) => {
            tours.push(tour);

            if (!localStorage.getItem('tours')) {
                localStorage.setItem('tours', "[]");
            }
        });

        selectTour(tours);

        pluginData.highlights.forEach((highlight) => {
            if (highlight.route === window.location.pathname) {
                //TODO Add a more precise/efficient selector
                waitForElement(highlight.parent, function (selector) {
                    selector.parentNode.style.position = 'relative';

                    let tempDiv = document.createElement('div');
                    tempDiv.innerHTML = highlight.button;

                    tempDiv.firstChild.classList.add(highlight.position);

                    selector.parentNode.insertBefore(tempDiv.firstChild, selector)
                });

                highlights.push(highlight);
            }
        });
    });

    function selectTour(tours, startIndex = 0) {
        for (let i = startIndex; i < tours.length; i++) {
            let tour = tours[i];
            let conditionAlwaysShow = tour.alwaysShow;
            let conditionRoutesIgnored = tour.routesIgnored;
            let conditionRouteMatches = tour.route === window.location.pathname;
            let conditionVisibleOnce = !pluginData.only_visible_once ||
                (pluginData.only_visible_once && !localStorage.getItem('tours').includes(tour.id));

            if (
                (conditionAlwaysShow && conditionRoutesIgnored) ||
                (conditionAlwaysShow && !conditionRoutesIgnored && conditionRouteMatches) ||
                (conditionRoutesIgnored && conditionVisibleOnce) ||
                (conditionRouteMatches && conditionVisibleOnce)
            ) {
                openTour(tour);
                break;
            }
        }
    }

    Livewire.on('filament-tour::open-highlight', function (params) {
        const id = parseId(params);
        let highlight = highlights.find(element => element.id === id);

        if (highlight) {
            driver({
                overlayColor: localStorage.theme === 'light' ? highlight.colors.light : highlight.colors.dark,

                onPopoverRender: (popover, {config, state}) => {
                    popover.title.innerHTML = "";
                    popover.title.innerHTML = state.activeStep.popover.title;

                    if (!state.activeStep.popover.description) {
                        popover.title.firstChild.style.justifyContent = 'center';
                    }

                    let contentClasses = "dark:text-white fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 mb-4";

                    popover.footer.parentElement.classList.add(...contentClasses.split(" "));
                },
            }).highlight(highlight);

        } else {
            console.error(`Highlight with id '${id}' not found`);
        }
    });

    Livewire.on('filament-tour::open-tour', function (params) {
        const id = parseId(params);
        let tour = tours.find(element => element.id === `tour_${id}`);

        if (tour) {
            openTour(tour);
        } else {
            console.error(`Tour with id '${id}' not found`);
        }
    });

    function openTour(tour) {

        let steps = JSON.parse(tour.steps);

        if (steps.length > 0) {

            const driverObj = driver({
                allowClose: true,
                disableActiveInteraction: true,
                overlayColor: localStorage.theme === 'light' ? tour.colors.light : tour.colors.dark,
                nextBtnText: tour.nextButtonLabel,
                prevBtnText: tour.previousButtonLabel,
                doneBtnText: tour.doneButtonLabel,
                showProgress: tour.showProgress,
                progressText: tour.progressText,
                popoverClass: tour.popoverClass,
                onDeselected: ((element, step, {config, state}) => {

                }),
                onCloseClick: ((element, step, {config, state}) => {
                    if (state.activeStep && (!state.activeStep.uncloseable || tour.uncloseable))
                        driverObj.destroy();

                    if (!localStorage.getItem('tours').includes(tour.id)) {
                        localStorage.setItem('tours', JSON.stringify([...JSON.parse(localStorage.getItem('tours')), tour.id]));
                    }
                }),
                onDestroyStarted: ((element, step, {config, state}) => {
                    if (state.activeStep && !state.activeStep.uncloseable && !tour.uncloseable) {
                        driverObj.destroy();
                    }
                }),
                onDestroyed: ((element, step, {config, state}) => {

                }),
                onNextClick: ((element, step, {config, state}) => {
                    if (tours.length > 1 && driverObj.isLastStep()) {
                        let index = tours.findIndex(objet => objet.id === tour.id);

                        if (index !== -1 && index < tours.length - 1) {
                            let nextTourIndex = index + 1;
                            selectTour(tours, nextTourIndex);
                        }
                    }

                    if (driverObj.isLastStep()) {
                        if (!localStorage.getItem('tours').includes(tour.id)) {
                            localStorage.setItem('tours', JSON.stringify([...JSON.parse(localStorage.getItem('tours')), tour.id]));
                        }
                        driverObj.destroy();
                    }

                    if (step.events) {
                        if (step.events.notifyOnNext) {
                            new FilamentNotification()
                                .title(step.events.notifyOnNext.title)
                                .body(step.events.notifyOnNext.body)
                                .icon(step.events.notifyOnNext.icon)
                                .iconColor(step.events.notifyOnNext.iconColor)
                                .color(step.events.notifyOnNext.color)
                                .duration(step.events.notifyOnNext.duration)
                                .send();
                        }

                        if (step.events.dispatchOnNext) {
                            Livewire.dispatch(step.events.dispatchOnNext.name, step.events.dispatchOnNext.params);
                        }

                        if (step.events.clickOnNext) {
                            document.querySelector(step.events.clickOnNext).click();
                        }

                        if (step.events.redirectOnNext) {
                            window.open(step.events.redirectOnNext.url, step.events.redirectOnNext.newTab ? '_blank' : '_self');
                        }
                    }
                    driverObj.moveNext();
                }),
                onPopoverRender: (popover, {config, state}) => {
                    if (state.activeStep.uncloseable || tour.uncloseable) {
                        document.querySelector(".driver-popover-close-btn").remove();
                    }

                    let nextButton = document.querySelector(".driver-popover-footer .driver-popover-next-btn");
                    nextButton.style.setProperty('--c-400', 'var(--primary-400');
                    nextButton.style.setProperty('--c-500', 'var(--primary-500');
                    nextButton.style.setProperty('--c-600', 'var(--primary-600');
                },
                steps: steps,
            });

            driverObj.drive();
        }
    }
});
