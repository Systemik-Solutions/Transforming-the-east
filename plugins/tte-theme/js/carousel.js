class Carousel {

    /**
     * Carousel class constructor.
     *
     * @param {jQuery} container
     *   The carousel container element.
     */
    constructor(container) {
        this.container = container;
        // Total number of slides.
        this.slideCount = 0;
        // The index of the current active slide.
        this.currentSlideIndex = 0;
        // The slide data including the image 'src', 'alt', and 'caption' HTML.
        this.slideData = [];
        // The UI element of the slides container.
        this.sliderElement = null;
        // The UI element of the control container.
        this.controlElement = null;
        // The interval ID of the autoplay.
        this.autoplayIntervalID = null;
        // Contains the options.
        this.options = {
            enableExpand: !!container.data('enableExpand'),
            enableAutoplay: !!container.data('enableAutoplay')
        };
        this.initData();
        this.initUI();
        this.bindControlEvents();
        if (this.options.enableAutoplay) {
            this.startAutoplay();
        }
    }

    /**
     * Initialise the slide data from the raw HTML.
     */
    initData() {
        const $ = jQuery;
        const carousel = this;
        this.container.find('figure').each(function () {
            const image = $(this).find('img');
            const caption = $(this).find('figcaption');
            carousel.slideData.push({
                src: image.prop('src'),
                alt: image.prop('alt'),
                caption: caption.html()
            });
            carousel.slideCount++;
        });
    }

    /**
     * Initialise the carousel UI.
     */
    initUI() {
        const $ = jQuery;
        this.container.find('figure').hide();
        const uiContainer = $(`<div class="omaa-carousel-ui"></div>`);
        // Create slider elements.
        this.sliderElement = $(`<div class="omaa-carousel-slider"></div>`);
        for (const slideInfo of this.slideData) {
            let slideHtml = `<div class="omaa-carousel-slider-slide">`;
            slideHtml += `<div class="omaa-carousel-slider-slide-image">`;
            slideHtml += `<img src="${slideInfo.src}" alt="${slideInfo.alt}">`;
            slideHtml += `</div>`;
            slideHtml += `<div class="omaa-carousel-slider-slide-caption">`;
            slideHtml += `<div class="omaa-carousel-slider-slide-caption-icon"><img src="/wp-content/themes/omaa/image/textus_icon_logo.png" alt="OMAA logo"></div>`;
            slideHtml += `<div class="omaa-carousel-slider-slide-caption-text">${slideInfo.caption}</div>`;
            slideHtml += `</div>`;
            slideHtml += `</div>`;
            this.sliderElement.append(slideHtml);
        }
        uiContainer.append(this.sliderElement);
        // Create control elements
        this.controlElement = $(`<div class="omaa-carousel-control"></div>`);
        this.controlElement.append(`<div class="omaa-carousel-control-prev"><i class="fa fa-chevron-left" aria-hidden="true"></i></div>`);
        for (let i = 0; i < this.slideCount; i++) {
            this.controlElement.append(`<div class="omaa-carousel-control-indicator" data-slide-index="${i}"></div>`);
        }
        this.controlElement.append(`<div class="omaa-carousel-control-next"><i class="fa fa-chevron-right" aria-hidden="true"></i></div>`);
        if (this.options.enableExpand) {
            this.controlElement.append(`<div class="omaa-carousel-control-expand"><i class="fa fa-arrows-alt" aria-hidden="true"></i>Expand Image Gallery</div>`);
            this.controlElement.append(`<div class="omaa-carousel-control-expand-close"><i class="fa fa-times" aria-hidden="true"></i>Close</div>`);
        }
        uiContainer.append(this.controlElement);
        this.container.append(uiContainer);
        // Active the slide.
        this.activateSlide(this.currentSlideIndex);
    }

    /**
     * Bind events for controls.
     */
    bindControlEvents() {
        const $ = jQuery;
        const carousel = this;
        // Bind click event for previous button.
        this.controlElement.find('.omaa-carousel-control-prev').on('click', function () {
            if (carousel.currentSlideIndex > 0) {
                carousel.currentSlideIndex--;
                carousel.activateSlide(carousel.currentSlideIndex);
            }
        });
        // Bind click event for next button.
        this.controlElement.find('.omaa-carousel-control-next').on('click', function () {
            if (carousel.currentSlideIndex < carousel.slideCount - 1) {
                carousel.currentSlideIndex++;
                carousel.activateSlide(carousel.currentSlideIndex);
            }
        });
        // Bind click event for indicator buttons.
        this.controlElement.find('.omaa-carousel-control-indicator').on('click', function () {
            const index = $(this).data('slideIndex');
            if (index !== carousel.currentSlideIndex) {
                carousel.currentSlideIndex = index;
                carousel.activateSlide(carousel.currentSlideIndex);
            }
        });
        if (this.options.enableExpand) {
            // Bind click event for expand button.
            this.controlElement.find('.omaa-carousel-control-expand').on('click', function () {
                if (carousel.options.enableAutoplay) {
                    carousel.stopAutoplay();
                }
                carousel.container.addClass('omaa-carousel-expand');
            });
            // Bind click event for expand close button.
            this.controlElement.find('.omaa-carousel-control-expand-close').on('click', function () {
                carousel.container.removeClass('omaa-carousel-expand');
                if (carousel.options.enableAutoplay) {
                    carousel.startAutoplay();
                }
            });
        }

    }

    /**
     * Activate a slide by index.
     *
     * @param {int} index
     */
    activateSlide(index) {
        const $ = jQuery;
        this.sliderElement.find('.omaa-carousel-slider-slide').each(function () {
            $(this).hide();
        });
        const activeSlide = this.sliderElement.find('.omaa-carousel-slider-slide').eq(index);
        activeSlide.addClass('active');
        this.controlElement.find('.omaa-carousel-control-indicator').each(function () {
            $(this).removeClass('active');
        });
        this.controlElement.find('.omaa-carousel-control-indicator').eq(index).addClass('active');
        activeSlide.fadeIn(800);
    }

    /**
     * Forward to next slide, or the start if it has reached the end.
     */
    forward() {
        if (this.currentSlideIndex === this.slideCount - 1) {
            this.currentSlideIndex = 0;
        } else {
            this.currentSlideIndex++;
        }
        this.activateSlide(this.currentSlideIndex);
    }

    /**
     * Start the autoplay.
     */
    startAutoplay() {
        const carousel = this;
        this.autoplayIntervalID = setInterval(function () {
            carousel.forward();
        }, 4000);
    }

    /**
     * Stop the autoplay.
     */
    stopAutoplay() {
        if (this.autoplayIntervalID) {
            clearInterval(this.autoplayIntervalID);
        }
    }
}
