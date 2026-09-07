document.addEventListener('DOMContentLoaded', () => {
    // High-Impact Preloader Dismissal
    const preloader = document.getElementById('kendatPreloader');
    if (preloader) {
        const dismissPreloader = () => {
            if (!preloader.classList.contains('is-loaded')) {
                preloader.classList.add('is-loaded');
                setTimeout(() => {
                    preloader.style.display = 'none';
                }, 650);
            }
        };

        if (document.readyState === 'complete') {
            dismissPreloader();
        } else {
            window.addEventListener('load', dismissPreloader);
            setTimeout(dismissPreloader, 1200);
        }
    }

    // Truecaller Header Scroll Behavior (Transparent -> White)
    const siteHeader = document.querySelector('.site-header');
    if (siteHeader) {
        const handleScroll = () => {
            if (window.scrollY > 30) {
                siteHeader.classList.add('is-scrolled');
            } else {
                siteHeader.classList.remove('is-scrolled');
            }
        };
        window.addEventListener('scroll', handleScroll, { passive: true });
        handleScroll();
    }

    // Wickret-style masked hero text reveal
    const heroTitle = document.querySelector('.wickret-hero-title');
    if (heroTitle && !heroTitle.dataset.animated) {
        const heroLines = [
            ['The', "World's", 'Leading'],
            ['Enterprise', 'Software', '&', 'AI', 'Platform']
        ];

        heroTitle.textContent = '';
        heroTitle.dataset.animated = 'true';

        heroLines.forEach((lineWords, lineIndex) => {
            const line = document.createElement('span');
            line.className = 'wickret-title-line';

            lineWords.forEach((word, wordIndex) => {
                const wordMask = document.createElement('span');
                const wordEl = document.createElement('span');
                const delay = 160 + (lineIndex * 230) + (wordIndex * 70);

                wordMask.className = 'wickret-word-mask';
                wordEl.className = 'wickret-word';
                if (lineIndex === 1) {
                    wordEl.classList.add('wickret-gradient-text');
                }
                wordEl.style.setProperty('--hero-delay', `${delay}ms`);
                wordEl.textContent = word;

                wordMask.appendChild(wordEl);
                line.appendChild(wordMask);
            });

            heroTitle.appendChild(line);
        });
    }

    // Truecaller Animated Blue Stage Ticker (Matching Recording Video)
    const animStage = document.querySelector('.tc-blue-anim-stage');
    if (animStage) {
        const slides = animStage.querySelectorAll('.tc-anim-slide');
        if (slides.length > 1) {
            let activeIdx = 0;
            setInterval(() => {
                slides[activeIdx].classList.remove('active');
                activeIdx = (activeIdx + 1) % slides.length;
                slides[activeIdx].classList.add('active');
            }, 3200);
        }
    }

    // Mobile Navigation Toggle System
    const menuToggle = document.querySelector('.menu-toggle');
    const navMenu = document.querySelector('.nav');
    if (menuToggle && navMenu) {
        menuToggle.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            const isOpen = navMenu.classList.toggle('open');
            menuToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        });

        // Close menu when clicking outside
        document.addEventListener('click', (e) => {
            if (!siteHeader.contains(e.target) && navMenu.classList.contains('open')) {
                navMenu.classList.remove('open');
                menuToggle.setAttribute('aria-expanded', 'false');
            }
        });

        // Close menu when clicking any nav link
        navMenu.querySelectorAll('a').forEach((link) => {
            link.addEventListener('click', () => {
                navMenu.classList.remove('open');
                menuToggle.setAttribute('aria-expanded', 'false');
            });
        });
    }

    // Admin Sidebar Toggle System
    const adminToggle = document.querySelector('.admin-sidebar-toggle');
    const adminSidebar = document.querySelector('.admin-sidebar');
    if (adminToggle && adminSidebar) {
        adminToggle.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            adminSidebar.classList.toggle('open');
        });

        document.addEventListener('click', (e) => {
            if (!adminSidebar.contains(e.target) && !adminToggle.contains(e.target) && adminSidebar.classList.contains('open')) {
                adminSidebar.classList.remove('open');
            }
        });
    }

    // Wickret Quick Search Chips Pre-fill
    const heroSearchInput = document.getElementById('heroSearchInput');
    document.querySelectorAll('.wickret-chip').forEach((chip) => {
        chip.addEventListener('click', () => {
            const fillText = chip.getAttribute('data-fill');
            if (heroSearchInput && fillText) {
                heroSearchInput.value = fillText;
                heroSearchInput.focus();
                heroSearchInput.parentElement.classList.add('focused');
            }
        });
    });

    // Homepage Project Showcase
    const projectShowcase = document.querySelector('[data-project-showcase]');
    if (projectShowcase) {
        const slides = Array.from(projectShowcase.querySelectorAll('[data-project-slide]'));
        const dots = Array.from(projectShowcase.querySelectorAll('[data-project-dot]'));
        const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        let activeProject = slides.findIndex((slide) => slide.classList.contains('active'));
        let projectTimer = null;

        if (activeProject < 0) {
            activeProject = 0;
            slides[0]?.classList.add('active');
        }

        const showProject = (nextIndex) => {
            if (slides.length < 2 || nextIndex === activeProject) return;

            const currentSlide = slides[activeProject];
            const nextSlide = slides[(nextIndex + slides.length) % slides.length];

            currentSlide.classList.remove('active');
            currentSlide.classList.add('leaving');
            nextSlide.classList.remove('leaving');
            nextSlide.classList.add('active');

            activeProject = slides.indexOf(nextSlide);
            dots.forEach((dot, index) => {
                dot.classList.toggle('active', index === activeProject);
            });

            window.setTimeout(() => {
                currentSlide.classList.remove('leaving');
            }, reduceMotion ? 0 : 780);
        };

        const startProjectTimer = () => {
            if (reduceMotion || slides.length < 2) return;
            projectTimer = window.setInterval(() => {
                showProject(activeProject + 1);
            }, 4200);
        };

        dots.forEach((dot) => {
            dot.addEventListener('click', () => {
                window.clearInterval(projectTimer);
                showProject(Number(dot.dataset.projectDot));
                startProjectTimer();
            });
        });

        startProjectTimer();
    }

    // Password Input Visibility Toggle
    document.querySelectorAll('.password-toggle').forEach((toggle) => {
        toggle.addEventListener('click', (e) => {
            e.preventDefault();
            const input = toggle.parentElement.querySelector('input');
            if (input) {
                const isPassword = input.type === 'password';
                input.type = isPassword ? 'text' : 'password';
                toggle.setAttribute('aria-label', isPassword ? 'Hide password' : 'Show password');
            }
        });
    });

    // Project Detail Slider Carousel
    const sliderTrack = document.querySelector('.project-detail-track');
    const dots = document.querySelectorAll('.slider-dots button');
    if (sliderTrack && dots.length > 1) {
        let activeIndex = 0;
        const totalSlides = dots.length;

        function updateSlider(index) {
            activeIndex = (index + totalSlides) % totalSlides;
            sliderTrack.style.transform = `translateX(-${activeIndex * 100}%)`;
            dots.forEach((dot, i) => {
                if (i === activeIndex) {
                    dot.classList.add('active');
                } else {
                    dot.classList.remove('active');
                }
            });
        }

        dots.forEach((dot, index) => {
            dot.addEventListener('click', () => updateSlider(index));
        });

        setInterval(() => {
            updateSlider(activeIndex + 1);
        }, 3200);
    }

    // Project Details Interactive Gallery Switcher
    const mainProjectImg = document.getElementById('mainProjectImg');
    const thumbBtns = document.querySelectorAll('.project-thumb-btn');
    if (mainProjectImg && thumbBtns.length > 0) {
        thumbBtns.forEach((btn) => {
            btn.addEventListener('click', () => {
                const newSrc = btn.getAttribute('data-img-src');
                if (newSrc && mainProjectImg.src !== newSrc) {
                    mainProjectImg.style.opacity = '0.4';
                    setTimeout(() => {
                        mainProjectImg.src = newSrc;
                        mainProjectImg.style.opacity = '1';
                    }, 150);
                    thumbBtns.forEach(b => b.classList.remove('active'));
                    btn.classList.add('active');
                }
            });
        });
    }

    // Password Input Visibility Toggle with Global Event Delegation
    document.addEventListener('click', function (e) {
        const toggleBtn = e.target.closest('.password-toggle');
        if (toggleBtn) {
            e.preventDefault();
            e.stopPropagation();
            const fieldWrapper = toggleBtn.closest('.password-field') || toggleBtn.parentElement;
            if (fieldWrapper) {
                const input = fieldWrapper.querySelector('input');
                const eyeOpen = toggleBtn.querySelector('.icon-eye');
                const eyeOff = toggleBtn.querySelector('.icon-eye-off');

                if (input) {
                    const isPassword = input.type === 'password';
                    input.type = isPassword ? 'text' : 'password';
                    toggleBtn.setAttribute('aria-label', isPassword ? 'Hide password' : 'Show password');
                    if (eyeOpen && eyeOff) {
                        eyeOpen.style.display = isPassword ? 'none' : 'inline-flex';
                        eyeOff.style.display = isPassword ? 'inline-flex' : 'none';
                    }
                }
            }
        }
    });

    // Astonesoft-Style Interactive Dual-Ring Custom Cursor Engine
    const cursorDot = document.getElementById('customCursorDot');
    const cursorRing = document.getElementById('customCursorRing');

    if (cursorDot && cursorRing && window.matchMedia('(hover: hover) and (pointer: fine)').matches) {
        let mouseX = -100, mouseY = -100;
        let ringX = -100, ringY = -100;
        let isVisible = false;

        window.addEventListener('mousemove', (e) => {
            mouseX = e.clientX;
            mouseY = e.clientY;

            if (!isVisible) {
                cursorDot.style.opacity = '1';
                cursorRing.style.opacity = '1';
                isVisible = true;
            }

            cursorDot.style.transform = `translate3d(${mouseX}px, ${mouseY}px, 0) translate(-50%, -50%)`;
        }, { passive: true });

        function animateCursorRing() {
            if (isVisible) {
                ringX += (mouseX - ringX) * 0.18;
                ringY += (mouseY - ringY) * 0.18;
                cursorRing.style.transform = `translate3d(${ringX}px, ${ringY}px, 0) translate(-50%, -50%)`;
            }
            requestAnimationFrame(animateCursorRing);
        }
        requestAnimationFrame(animateCursorRing);

        const hoverTargetSelectors = 'a, button, input, select, textarea, .btn, .glass-card, .project-card, .admin-stat-card, .nav-link, .pill, .admin-nav-item, .wickret-shimmer-btn, .wickret-glass-btn, .table-card tr';

        document.addEventListener('mouseover', (e) => {
            if (e.target.closest(hoverTargetSelectors)) {
                document.body.classList.add('cursor-hover');
            }
        }, true);

        document.addEventListener('mouseout', (e) => {
            if (e.target.closest(hoverTargetSelectors)) {
                document.body.classList.remove('cursor-hover');
            }
        }, true);

        window.addEventListener('mousedown', () => document.body.classList.add('cursor-active'));
        window.addEventListener('mouseup', () => document.body.classList.remove('cursor-active'));

        document.addEventListener('mouseleave', () => {
            cursorDot.style.opacity = '0';
            cursorRing.style.opacity = '0';
            isVisible = false;
        });
    }
});
