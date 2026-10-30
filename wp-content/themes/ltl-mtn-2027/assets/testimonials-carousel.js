/**
 * Testimonials Carousel Functionality
 */
(function() {
    'use strict';

    function initCarousel(wrapper) {
        var track = wrapper.querySelector('.testimonials-carousel-track');
        var slides = wrapper.querySelectorAll('.carousel-slide');
        var prevBtn = wrapper.querySelector('.carousel-prev');
        var nextBtn = wrapper.querySelector('.carousel-next');

        if (!track || slides.length === 0) {
            return;
        }

        var currentIndex = 0;
        var timer = null;
        var isAutoplay = wrapper.getAttribute('data-autoplay') === 'true';
        var speedSeconds = parseFloat(wrapper.getAttribute('data-speed')) || 5;
        var intervalMs = speedSeconds * 1000;

        function getVisibleCount() {
            var width = window.innerWidth;
            if (width <= 640) {
                return 1;
            } else if (width <= 1024) {
                return 2;
            } else {
                return 3;
            }
        }

        function getMaxIndex() {
            var visible = getVisibleCount();
            return Math.max(0, slides.length - visible);
        }

        function updatePosition() {
            var maxIndex = getMaxIndex();
            if (currentIndex > maxIndex) {
                currentIndex = maxIndex;
            }
            if (currentIndex < 0) {
                currentIndex = 0;
            }

            var slide = slides[0];
            var slideWidth = slide.getBoundingClientRect().width;
            var gap = 0;
            if (slides.length > 1) {
                var firstRight = slides[0].getBoundingClientRect().right;
                var secondLeft = slides[1].getBoundingClientRect().left;
                gap = Math.max(0, secondLeft - firstRight);
            }

            var offset = currentIndex * (slideWidth + gap);
            track.style.transform = 'translateX(-' + offset + 'px)';
        }

        function nextSlide() {
            var maxIndex = getMaxIndex();
            if (maxIndex === 0) {
                return;
            }
            if (currentIndex >= maxIndex) {
                currentIndex = 0;
            } else {
                currentIndex++;
            }
            updatePosition();
        }

        function prevSlide() {
            var maxIndex = getMaxIndex();
            if (maxIndex === 0) {
                return;
            }
            if (currentIndex <= 0) {
                currentIndex = maxIndex;
            } else {
                currentIndex--;
            }
            updatePosition();
        }

        function startAutoplay() {
            if (!isAutoplay) {
                return;
            }
            stopAutoplay();
            timer = setInterval(function() {
                nextSlide();
            }, intervalMs);
        }

        function stopAutoplay() {
            if (timer) {
                clearInterval(timer);
                timer = null;
            }
        }

        if (nextBtn) {
            nextBtn.addEventListener('click', function() {
                nextSlide();
                startAutoplay();
            });
        }

        if (prevBtn) {
            prevBtn.addEventListener('click', function() {
                prevSlide();
                startAutoplay();
            });
        }

        // Pause on hover
        wrapper.addEventListener('mouseenter', stopAutoplay);
        wrapper.addEventListener('mouseleave', startAutoplay);

        // Pause when tab is not visible
        document.addEventListener('visibilitychange', function() {
            if (document.hidden) {
                stopAutoplay();
            } else {
                startAutoplay();
            }
        });

        // Touch swipe support
        var touchStartX = 0;
        var touchEndX = 0;
        wrapper.addEventListener('touchstart', function(e) {
            if (e.changedTouches && e.changedTouches.length > 0) {
                touchStartX = e.changedTouches[0].clientX;
            }
        }, { passive: true });

        wrapper.addEventListener('touchend', function(e) {
            if (e.changedTouches && e.changedTouches.length > 0) {
                touchEndX = e.changedTouches[0].clientX;
                var diff = touchStartX - touchEndX;
                if (Math.abs(diff) > 40) {
                    if (diff > 0) {
                        nextSlide();
                    } else {
                        prevSlide();
                    }
                    startAutoplay();
                }
            }
        }, { passive: true });

        // Window resize
        var resizeTimeout;
        window.addEventListener('resize', function() {
            clearTimeout(resizeTimeout);
            resizeTimeout = setTimeout(function() {
                updatePosition();
            }, 100);
        });

        // Initial layout and start autoplay
        updatePosition();
        // Run updatePosition again once fonts/images load
        window.addEventListener('load', updatePosition);
        startAutoplay();
    }

    function initAll() {
        var carousels = document.querySelectorAll('.testimonials-carousel-wrapper');
        for (var i = 0; i < carousels.length; i++) {
            initCarousel(carousels[i]);
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAll);
    } else {
        initAll();
    }
})();
