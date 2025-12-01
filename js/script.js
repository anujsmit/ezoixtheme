document.addEventListener('DOMContentLoaded', function() {
    // Mobile menu toggle
    const mobileToggle = document.querySelector('.mobile-menu-toggle');
    const mainNav = document.querySelector('.main-navigation');
    
    if (mobileToggle && mainNav) {
        mobileToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            const isExpanded = this.getAttribute('aria-expanded') === 'true';
            this.setAttribute('aria-expanded', !isExpanded);
            mainNav.classList.toggle('active');
        });
        
        // Close menu when clicking outside
        document.addEventListener('click', function(e) {
            if (!mainNav.contains(e.target) && !mobileToggle.contains(e.target)) {
                mobileToggle.setAttribute('aria-expanded', 'false');
                mainNav.classList.remove('active');
            }
        });
        
        // Close menu on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                mobileToggle.setAttribute('aria-expanded', 'false');
                mainNav.classList.remove('active');
            }
        });
    }

    // Infinite Scroll Variables
    let currentPage = 1;
    let isLoading = false;
    let hasMorePosts = true;
    const postsContainer = document.getElementById('posts-container');
    const loadingIndicator = document.getElementById('infinite-scroll-loading');
    const endMessage = document.getElementById('infinite-scroll-end');
    const loadMoreButton = document.getElementById('load-more-posts');
    const scrollToTopBtn = document.getElementById('scrollToTop');

    // Infinite Scroll Functionality
    function initInfiniteScroll() {
        if (!postsContainer) return;

        // Throttled scroll handler
        let scrollTimeout;
        window.addEventListener('scroll', function() {
            if (scrollTimeout) {
                clearTimeout(scrollTimeout);
            }
            
            scrollTimeout = setTimeout(function() {
                checkScrollPosition();
            }, 100);
        });

        // Load more button fallback
        if (loadMoreButton) {
            loadMoreButton.addEventListener('click', loadMorePosts);
        }
    }

    // Check if user has scrolled near bottom
    function checkScrollPosition() {
        if (isLoading || !hasMorePosts) return;

        const scrollPosition = window.innerHeight + window.scrollY;
        const pageHeight = document.documentElement.scrollHeight;
        const threshold = 500; // Load when 500px from bottom

        if (scrollPosition >= pageHeight - threshold) {
            loadMorePosts();
        }
    }

    // Load more posts via AJAX
    function loadMorePosts() {
        if (isLoading || !hasMorePosts) return;

        isLoading = true;
        currentPage++;

        // Show loading indicator
        if (loadingIndicator) {
            loadingIndicator.style.display = 'block';
        }

        if (loadMoreButton) {
            loadMoreButton.classList.add('loading');
            loadMoreButton.disabled = true;
        }

        // AJAX request
        fetch(ezoix_ajax.ajaxurl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                action: 'infinite_scroll_posts',
                page: currentPage,
                nonce: ezoix_ajax.nonce
            })
        })
        .then(response => response.text())
        .then(data => {
            if (data === 'no_more_posts') {
                hasMorePosts = false;
                showEndMessage();
            } else if (data) {
                // Add new posts with fade-in animation
                const tempDiv = document.createElement('div');
                tempDiv.innerHTML = data;
                const newPosts = tempDiv.querySelectorAll('.post-card-compact');
                
                newPosts.forEach((post, index) => {
                    post.style.opacity = '0';
                    post.style.transform = 'translateY(20px)';
                    postsContainer.appendChild(post);
                    
                    // Staggered animation
                    setTimeout(() => {
                        post.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
                        post.style.opacity = '1';
                        post.style.transform = 'translateY(0)';
                    }, index * 100);
                });

                // Update load more button
                if (loadMoreButton) {
                    loadMoreButton.setAttribute('data-page', currentPage);
                }
            }
        })
        .catch(error => {
            console.error('Error loading more posts:', error);
            // Fallback: show load more button if infinite scroll fails
            if (loadMoreButton) {
                loadMoreButton.style.display = 'block';
            }
        })
        .finally(() => {
            isLoading = false;
            
            if (loadingIndicator) {
                loadingIndicator.style.display = 'none';
            }
            
            if (loadMoreButton && hasMorePosts) {
                loadMoreButton.classList.remove('loading');
                loadMoreButton.disabled = false;
            }
        });
    }

    // Show end of posts message
    function showEndMessage() {
        if (loadingIndicator) {
            loadingIndicator.style.display = 'none';
        }
        
        if (endMessage) {
            endMessage.style.display = 'block';
        }
        
        if (loadMoreButton) {
            loadMoreButton.style.display = 'none';
        }
    }

    // Scroll to top functionality
    function initScrollToTop() {
        if (!scrollToTopBtn) return;

        window.addEventListener('scroll', function() {
            if (window.pageYOffset > 300) {
                scrollToTopBtn.classList.add('visible');
            } else {
                scrollToTopBtn.classList.remove('visible');
            }
        });

        scrollToTopBtn.addEventListener('click', function() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    }

    // Lazy load images with Intersection Observer
    function initLazyLoading() {
        if ('IntersectionObserver' in window) {
            const imageObserver = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        if (img.dataset.src) {
                            img.src = img.dataset.src;
                        }
                        if (img.dataset.srcset) {
                            img.srcset = img.dataset.srcset;
                        }
                        img.classList.remove('lazy');
                        imageObserver.unobserve(img);
                    }
                });
            }, {
                rootMargin: '50px 0px',
                threshold: 0.1
            });

            document.querySelectorAll('img[data-src]').forEach(img => {
                imageObserver.observe(img);
            });
        }
    }

    // Smooth scroll for anchor links
    function initSmoothScroll() {
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                const href = this.getAttribute('href');
                if (href === '#') return;

                const target = document.querySelector(href);
                if (target) {
                    e.preventDefault();
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    }

    // Initialize all functionality
    initInfiniteScroll();
    initScrollToTop();
    initLazyLoading();
    initSmoothScroll();

    // Performance monitoring
    if (window.performance) {
        window.addEventListener('load', function() {
            const perfData = window.performance.timing;
            const loadTime = perfData.loadEventEnd - perfData.navigationStart;
            console.log('Page load time:', loadTime + 'ms');
        });
    }
});