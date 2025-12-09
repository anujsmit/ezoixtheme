/**
 * Ezoix Tech Blog Theme JavaScript
 * Version: 1.0.0
 */

document.addEventListener('DOMContentLoaded', function() {
    // ===== MOBILE MENU TOGGLE =====
    const mobileMenuToggle = document.querySelector('.mobile-menu-toggle');
    const mainNavigation = document.getElementById('main-navigation');
    
    if (mobileMenuToggle && mainNavigation) {
        mobileMenuToggle.addEventListener('click', function(e) {
            e.preventDefault();
            mainNavigation.classList.toggle('active');
            this.textContent = mainNavigation.classList.contains('active') ? '✕' : '☰';
            this.setAttribute('aria-expanded', mainNavigation.classList.contains('active'));
        });
        
        // Close menu when clicking outside
        document.addEventListener('click', function(e) {
            if (!mainNavigation.contains(e.target) && !mobileMenuToggle.contains(e.target)) {
                mainNavigation.classList.remove('active');
                mobileMenuToggle.textContent = '☰';
                mobileMenuToggle.setAttribute('aria-expanded', 'false');
            }
        });
    }
    
    // ===== SCROLL TO TOP BUTTON =====
    const scrollToTopBtn = document.getElementById('scrollToTop');
    
    if (scrollToTopBtn) {
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
    
    // ===== THEME TOGGLE =====
    const themeToggle = document.querySelector('.theme-toggle');
    
    if (themeToggle) {
        // Check for saved theme preference or default to light mode
        const currentTheme = localStorage.getItem('theme') || 'light';
        
        if (currentTheme === 'dark') {
            document.body.classList.add('dark-mode');
            themeToggle.textContent = '🌙';
        } else {
            themeToggle.textContent = '☀️';
        }
        
        themeToggle.addEventListener('click', function() {
            document.body.classList.toggle('dark-mode');
            
            if (document.body.classList.contains('dark-mode')) {
                localStorage.setItem('theme', 'dark');
                themeToggle.textContent = '🌙';
            } else {
                localStorage.setItem('theme', 'light');
                themeToggle.textContent = '☀️';
            }
        });
    }
    
    // ===== INFINITE SCROLL =====
    const feedContainer = document.getElementById('feed-container');
    const loadingElement = document.getElementById('feed-loading');
    const endMessage = document.getElementById('feed-end-message');
    
    if (feedContainer && loadingElement) {
        let isLoading = false;
        let page = 2;
        let hasMorePosts = true;
        
        function loadMorePosts() {
            if (isLoading || !hasMorePosts) return;
            
            isLoading = true;
            loadingElement.style.display = 'flex';
            
            // Get the current URL to determine post type
            const url = new URL(window.location.href);
            const isHomePage = url.pathname === '/' || url.pathname === '';
            
            // Prepare data for AJAX request
            const data = new FormData();
            data.append('action', 'ezoix_load_more_posts');
            data.append('page', page);
            data.append('is_home', isHomePage ? '1' : '0');
            
            // Add current query parameters for archive pages
            if (!isHomePage) {
                const params = new URLSearchParams(window.location.search);
                params.forEach((value, key) => {
                    data.append(key, value);
                });
            }
            
            fetch(ezoix_ajax.ajax_url, {
                method: 'POST',
                credentials: 'same-origin',
                body: data
            })
            .then(response => response.text())
            .then(html => {
                if (html.trim() === '') {
                    hasMorePosts = false;
                    endMessage.style.display = 'block';
                } else {
                    // Create a temporary container to parse HTML
                    const tempDiv = document.createElement('div');
                    tempDiv.innerHTML = html;
                    
                    // Append new posts
                    const newPosts = tempDiv.querySelectorAll('.category-post-card, .mobile-device-card');
                    newPosts.forEach(post => {
                        feedContainer.appendChild(post);
                    });
                    
                    page++;
                }
            })
            .catch(error => {
                console.error('Error loading more posts:', error);
            })
            .finally(() => {
                isLoading = false;
                loadingElement.style.display = 'none';
            });
        }
        
        // Intersection Observer for infinite scroll
        const observer = new IntersectionObserver((entries) => {
            if (entries[0].isIntersecting && hasMorePosts) {
                loadMorePosts();
            }
        }, {
            rootMargin: '100px'
        });
        
        // Observe the loading element
        observer.observe(loadingElement);
    }
    
    // ===== LAZY LOADING IMAGES =====
    if ('IntersectionObserver' in window) {
        const lazyImages = document.querySelectorAll('img[loading="lazy"]');
        
        const imageObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src || img.src;
                    img.classList.add('loaded');
                    imageObserver.unobserve(img);
                }
            });
        });
        
        lazyImages.forEach(img => imageObserver.observe(img));
    }
    
    // ===== SMOOTH SCROLL FOR ANCHOR LINKS =====
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            
            if (href === '#') return;
            
            const targetElement = document.querySelector(href);
            if (targetElement) {
                e.preventDefault();
                targetElement.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
    
    // ===== RESPONSIVE TABLE FOR COMPARISON =====
    const comparisonTables = document.querySelectorAll('.best-buy-table');
    
    comparisonTables.forEach(table => {
        if (window.innerWidth <= 600) {
            const headers = [];
            const thElements = table.querySelectorAll('th');
            
            thElements.forEach(th => {
                headers.push(th.textContent);
            });
            
            const rows = table.querySelectorAll('tbody tr');
            rows.forEach(row => {
                const cells = row.querySelectorAll('td');
                cells.forEach((cell, index) => {
                    if (headers[index]) {
                        cell.setAttribute('data-label', headers[index]);
                    }
                });
            });
        }
    });
});