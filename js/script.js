// Ezoix Tech Blog JavaScript
document.addEventListener('DOMContentLoaded', function() {
    console.log('Ezoix Tech Blog loaded');

    // ===== MOBILE MENU TOGGLE =====
    const mobileMenuToggle = document.querySelector('.mobile-menu-toggle');
    const mainNavigation = document.getElementById('main-navigation');
    
    if (mobileMenuToggle && mainNavigation) {
        mobileMenuToggle.addEventListener('click', function() {
            const isExpanded = this.getAttribute('aria-expanded') === 'true';
            this.setAttribute('aria-expanded', !isExpanded);
            mainNavigation.classList.toggle('active');
            
            // Update menu icon
            const menuIcon = this.querySelector('.menu-icon');
            if (menuIcon) {
                menuIcon.textContent = isExpanded ? '☰' : '✕';
            }
        });
        
        // Close menu when clicking outside
        document.addEventListener('click', function(event) {
            if (!mobileMenuToggle.contains(event.target) && !mainNavigation.contains(event.target)) {
                mobileMenuToggle.setAttribute('aria-expanded', 'false');
                mainNavigation.classList.remove('active');
                const menuIcon = mobileMenuToggle.querySelector('.menu-icon');
                if (menuIcon) {
                    menuIcon.textContent = '☰';
                }
            }
        });
    }

    // ===== LAZY LOAD IMAGES =====
    function lazyLoadImages() {
        const lazyImages = [].slice.call(document.querySelectorAll('img.lazy'));
        
        if ('IntersectionObserver' in window) {
            const lazyImageObserver = new IntersectionObserver(function(entries, observer) {
                entries.forEach(function(entry) {
                    if (entry.isIntersecting) {
                        const lazyImage = entry.target;
                        
                        // Load the image
                        if (lazyImage.dataset.src) {
                            lazyImage.src = lazyImage.dataset.src;
                        }
                        if (lazyImage.dataset.srcset) {
                            lazyImage.srcset = lazyImage.dataset.srcset;
                        }
                        
                        // Add loaded class
                        lazyImage.classList.remove('lazy');
                        lazyImage.classList.add('loaded');
                        
                        // Stop observing
                        lazyImageObserver.unobserve(lazyImage);
                    }
                });
            }, {
                rootMargin: '50px 0px',
                threshold: 0.1
            });

            lazyImages.forEach(function(lazyImage) {
                lazyImageObserver.observe(lazyImage);
            });
        } else {
            // Fallback for older browsers
            lazyImages.forEach(function(lazyImage) {
                if (lazyImage.dataset.src) {
                    lazyImage.src = lazyImage.dataset.src;
                }
                if (lazyImage.dataset.srcset) {
                    lazyImage.srcset = lazyImage.dataset.srcset;
                }
                lazyImage.classList.remove('lazy');
                lazyImage.classList.add('loaded');
            });
        }
    }
    
    // Initialize lazy loading
    lazyLoadImages();

    // ===== SCROLL TO TOP BUTTON =====
    const scrollToTopBtn = document.getElementById('scrollToTop');
    
    if (scrollToTopBtn) {
        function toggleScrollButton() {
            if (window.pageYOffset > 300) {
                scrollToTopBtn.classList.add('visible');
            } else {
                scrollToTopBtn.classList.remove('visible');
            }
        }
        
        // Initial check
        toggleScrollButton();
        
        // Listen to scroll events
        window.addEventListener('scroll', toggleScrollButton);
        
        // Click handler
        scrollToTopBtn.addEventListener('click', function() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    }

    // ===== INFINITE SCROLL (UPDATED FOR AUTOMATIC LOADING) =====
    let isLoading = false;
    let currentPage = 1;
    // Note: totalPages is set via wp_localize_script in functions.php
    let totalPages = window.ezoix_ajax?.total_pages || 1; 
    const postsContainer = document.getElementById('feed-container'); // Renamed from posts-container to match index.php
    const loadingIndicator = document.getElementById('feed-loading'); // Use the ID of the loading div
    const endMessage = document.getElementById('feed-end-message'); // Use the ID of the end message

    // *FIX 1: Hide loading indicator on page load*
    if (loadingIndicator) {
        loadingIndicator.style.display = 'none';
    }
    
    function loadMorePosts() {
        // *FIX 2: Stop loading if currently loading or at the end*
        if (isLoading || currentPage >= totalPages) return;
        
        isLoading = true;
        currentPage++;
        
        // Show loading indicator
        if (loadingIndicator) {
            loadingIndicator.style.display = 'block';
        }
        
        // Hide end message if it was shown previously
        if (endMessage) {
            endMessage.style.display = 'none';
        }
        
        // Make AJAX request
        fetch(window.ezoix_ajax.ajaxurl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                action: 'infinite_scroll_posts',
                page: currentPage,
                nonce: window.ezoix_ajax.nonce
            })
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.text();
        })
        .then(data => {
            if (data === 'no_more_posts' || currentPage >= totalPages) {
                // No more posts to load or reached calculated end
                if (endMessage) {
                    endMessage.style.display = 'block';
                }
                // Set totalPages to current page to prevent future calls
                totalPages = currentPage; 
            } else if (postsContainer) {
                // Add new posts to container
                postsContainer.insertAdjacentHTML('beforeend', data);
                
                // Lazy load new images
                lazyLoadImages();
            }
        })
        .catch(error => {
            console.error('Error loading posts:', error);
            
            // Show error message
            if (postsContainer) {
                postsContainer.insertAdjacentHTML('beforeend', 
                    '<p class="error-message">Sorry, there was an error loading more content. Please try again.</p>'
                );
            }
        })
        .finally(() => {
            isLoading = false;
            if (loadingIndicator) {
                loadingIndicator.style.display = 'none';
            }
        });
    }
    
    // *FIX 3: Removed button click handler as we are using automatic scroll*
    // if (loadMoreBtn) {
    //     loadMoreBtn.addEventListener('click', loadMorePosts);
    // }
    
    // Infinite scroll on window scroll (Existing logic is good, just ensuring it points to the right vars)
    function checkScroll() {
        if (isLoading || !postsContainer || currentPage >= totalPages) return;
        
        const lastPost = postsContainer.lastElementChild;
        if (!lastPost) return;
        
        // Check if the page is long enough to have content to load
        const scrollHeight = document.documentElement.scrollHeight;
        const clientHeight = document.documentElement.clientHeight;
        const scrollTop = document.documentElement.scrollTop;

        // Load more when user is 1000px from the bottom of the document
        // Using document scroll heights is more reliable for infinite scroll
        if (scrollTop + clientHeight >= scrollHeight - 1000) {
            loadMorePosts();
        }
    }
    
    // Throttle scroll event for performance
    let scrollTimeout;
    window.addEventListener('scroll', function() {
        clearTimeout(scrollTimeout);
        scrollTimeout = setTimeout(checkScroll, 100);
    });

    // ===== IMAGE ERROR HANDLING =====
    document.addEventListener('error', function(e) {
        if (e.target.tagName === 'IMG') {
            const img = e.target;
            
            // Add error class
            img.classList.add('image-error');
            
            // Replace with placeholder after a delay
            setTimeout(() => {
                if (img.classList.contains('image-error')) {
                    img.src = 'data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="400" height="200" viewBox="0 0 400 200"><rect width="400" height="200" fill="%23e6f0ff"/><text x="50%" y="50%" font-family="Arial" font-size="16" fill="%230066ff" text-anchor="middle" dy=".3em">Image Not Found</text></svg>';
                    img.style.opacity = '1';
                }
            }, 500);
        }
    }, true);

    // ===== ENHANCE ACCESSIBILITY =====
    // Add focus styles for keyboard navigation
    document.addEventListener('keyup', function(e) {
        if (e.key === 'Tab') {
            document.body.classList.add('keyboard-navigation');
        }
    });

    document.addEventListener('mousedown', function() {
        document.body.classList.remove('keyboard-navigation');
    });

    // ===== SMOOTH SCROLL FOR ANCHOR LINKS =====
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            const href = this.getAttribute('href');
            
            // Skip if it's just "#"
            if (href === '#') return;
            
            const target = document.querySelector(href);
            if (target) {
                e.preventDefault();
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
                
                // Update URL without page reload
                if (history.pushState) {
                    history.pushState(null, null, href);
                }
            }
        });
    });

    // ===== ENHANCE CATEGORY PAGE FUNCTIONALITY =====
    if (document.body.classList.contains('category-page')) {
        // Add animation to post cards on category pages
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };
        
        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animated');
                }
            });
        }, observerOptions);
        
        // Observe all post cards
        document.querySelectorAll('.post-card').forEach(card => {
            observer.observe(card);
        });
    }

    // ===== ENHANCE SINGLE POST READING EXPERIENCE =====
    if (document.body.classList.contains('single-post')) {
        // Add reading progress bar
        const readingProgress = document.createElement('div');
        readingProgress.className = 'reading-progress';
        readingProgress.innerHTML = '<div class="reading-progress-bar"></div>';
        document.body.appendChild(readingProgress);
        
        const readingProgressBar = readingProgress.querySelector('.reading-progress-bar');
        
        function updateReadingProgress() {
            const postContent = document.querySelector('.post-content');
            if (!postContent) return;
            
            const postHeight = postContent.offsetHeight;
            const windowHeight = window.innerHeight;
            const scrollTop = window.pageYOffset;
            const postTop = postContent.offsetTop;
            const postBottom = postTop + postHeight;
            
            // Calculate progress
            let progress = 0;
            if (scrollTop >= postTop) {
                const visibleHeight = Math.min(scrollTop + windowHeight, postBottom) - postTop;
                progress = (visibleHeight / postHeight) * 100;
            }
            
            // Update progress bar
            if (readingProgressBar) {
                readingProgressBar.style.width = Math.min(100, progress) + '%';
            }
        }
        
        // Update progress on scroll
        window.addEventListener('scroll', updateReadingProgress);
        window.addEventListener('resize', updateReadingProgress);
        
        // Initial update
        updateReadingProgress();
    }

    // ===== ENHANCE FORM INTERACTIONS =====
    const commentForm = document.getElementById('commentform');
    if (commentForm) {
        // Add focus effects to form fields
        const formFields = commentForm.querySelectorAll('input, textarea, select');
        formFields.forEach(field => {
            field.addEventListener('focus', function() {
                this.parentElement.classList.add('focused');
            });
            
            field.addEventListener('blur', function() {
                if (!this.value) {
                    this.parentElement.classList.remove('focused');
                }
            });
        });
    }

    // ===== PERFORMANCE MONITORING (DEV ONLY) =====
    if (window.ezoix_ajax && window.performance) {
        // Log performance metrics in development
        const perfEntries = performance.getEntriesByType('navigation');
        if (perfEntries.length > 0) {
            const navTiming = perfEntries[0];
            console.log('Page Load Time:', navTiming.loadEventEnd - navTiming.startTime, 'ms');
            console.log('DOM Content Loaded:', navTiming.domContentLoadedEventEnd - navTiming.startTime, 'ms');
        }
    }

    // ===== POLYFILLS FOR OLDER BROWSERS =====
    // NodeList.forEach polyfill
    if (window.NodeList && !NodeList.prototype.forEach) {
        NodeList.prototype.forEach = function(callback, thisArg) {
            thisArg = thisArg || window;
            for (var i = 0; i < this.length; i++) {
                callback.call(thisArg, this[i], i, this);
            }
        };
    }

    // Object.assign polyfill
    if (typeof Object.assign != 'function') {
        Object.assign = function(target) {
            if (target == null) {
                throw new TypeError('Cannot convert undefined or null to object');
            }

            target = Object(target);
            for (var index = 1; index < arguments.length; index++) {
                var source = arguments[index];
                if (source != null) {
                    for (var key in source) {
                        if (Object.prototype.hasOwnProperty.call(source, key)) {
                            target[key] = source[key];
                        }
                    }
                }
            }
            return target;
        };
    }
});

// ===== LOAD EVENT HANDLERS =====
window.addEventListener('load', function() {
    // Ensure all lazy images are loaded
    const lazyImages = document.querySelectorAll('img.lazy');
    lazyImages.forEach(img => {
        if (!img.classList.contains('loaded')) {
            if (img.dataset.src) {
                img.src = img.dataset.src;
            }
            img.classList.add('loaded');
        }
    });
    
    // Add loaded class to body for CSS transitions
    document.body.classList.add('loaded');
    
    // Initialize any third-party scripts if needed
    if (typeof initializeThirdPartyScripts === 'function') {
        initializeThirdPartyScripts();
    }
});

// ===== RESIZE EVENT HANDLER (DEBOUNCED) =====
let resizeTimeout;
window.addEventListener('resize', function() {
    clearTimeout(resizeTimeout);
    resizeTimeout = setTimeout(function() {
        // Handle responsive adjustments here
        const viewportWidth = window.innerWidth;
        
        // You can add responsive JS logic here if needed
        if (viewportWidth < 768) {
            // Mobile-specific adjustments
        } else if (viewportWidth < 992) {
            // Tablet-specific adjustments
        } else {
            // Desktop adjustments
        }
    }, 250);
});
// Mobile Device Template Interactivity
document.addEventListener('DOMContentLoaded', function() {
    // Gallery image switching
    const galleryThumbs = document.querySelectorAll('.gallery-thumb');
    const mainGalleryImage = document.getElementById('main-gallery-image');
    
    if (galleryThumbs.length && mainGalleryImage) {
        galleryThumbs.forEach(thumb => {
            thumb.addEventListener('click', function() {
                const fullImage = this.getAttribute('data-full');
                mainGalleryImage.src = fullImage;
                mainGalleryImage.alt = this.alt;
                
                // Update active state
                galleryThumbs.forEach(t => t.classList.remove('active'));
                this.classList.add('active');
            });
        });
    }
    
    // Accordion functionality
    const accordionToggles = document.querySelectorAll('.accordion-toggle');
    const expandAllBtn = document.querySelector('.expand-all');
    
    accordionToggles.forEach(toggle => {
        toggle.addEventListener('click', function() {
            const content = this.closest('.spec-category').querySelector('.category-content');
            const isExpanded = content.style.display === 'block';
            
            // Toggle content
            content.style.display = isExpanded ? 'none' : 'block';
            
            // Update toggle icon
            const icon = this.querySelector('.toggle-icon');
            icon.textContent = isExpanded ? '+' : '−';
            
            // Update aria attribute
            this.setAttribute('aria-expanded', !isExpanded);
        });
    });
    
    // Expand/Collapse All button
    if (expandAllBtn) {
        expandAllBtn.addEventListener('click', function() {
            const allContents = document.querySelectorAll('.category-content');
            const isAllExpanded = Array.from(allContents).every(content => 
                content.style.display === 'block' || content.style.display === ''
            );
            
            allContents.forEach(content => {
                content.style.display = isAllExpanded ? 'none' : 'block';
            });
            
            accordionToggles.forEach(toggle => {
                const icon = toggle.querySelector('.toggle-icon');
                icon.textContent = isAllExpanded ? '+' : '−';
                toggle.setAttribute('aria-expanded', !isAllExpanded);
            });
            
            this.textContent = isAllExpanded ? 'Expand All' : 'Collapse All';
        });
    }
    
    // Smooth scroll for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            const targetId = this.getAttribute('href');
            if (targetId !== '#') {
                const targetElement = document.querySelector(targetId);
                if (targetElement) {
                    e.preventDefault();
                    window.scrollTo({
                        top: targetElement.offsetTop - 100,
                        behavior: 'smooth'
                    });
                }
            }
        });
    });
    
    // Share button enhancements
    document.querySelectorAll('.share-button').forEach(button => {
        button.addEventListener('click', function(e) {
            if (this.classList.contains('email')) {
                // Email opens in default client, no need for window.open
                return;
            }
            
            e.preventDefault();
            const url = this.href;
            const width = 600;
            const height = 400;
            const left = (window.innerWidth - width) / 2;
            const top = (window.innerHeight - height) / 2;
            
            window.open(
                url,
                'share',
                `width=${width},height=${height},left=${left},top=${top},toolbar=0,status=0`
            );
        });
    });
});