// mobile-menu.js
document.addEventListener('DOMContentLoaded', function() {
    // Mobile menu toggle
    const mobileMenuToggle = document.querySelector('.mobile-menu-toggle');
    const mainNavigation = document.querySelector('.main-navigation');
    
    if (mobileMenuToggle && mainNavigation) {
        mobileMenuToggle.addEventListener('click', function() {
            mainNavigation.classList.toggle('active');
            this.setAttribute('aria-expanded', 
                this.getAttribute('aria-expanded') === 'true' ? 'false' : 'true'
            );
        });
        
        // Close menu when clicking outside
        document.addEventListener('click', function(event) {
            if (!mainNavigation.contains(event.target) && !mobileMenuToggle.contains(event.target)) {
                mainNavigation.classList.remove('active');
                mobileMenuToggle.setAttribute('aria-expanded', 'false');
            }
        });
    }
    
    // Scroll to top button
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
    
    // Share buttons functionality
    document.querySelectorAll('.share-button').forEach(button => {
        button.addEventListener('click', function() {
            const url = this.getAttribute('data-url');
            const title = this.getAttribute('data-title') || document.title;
            
            if (navigator.share) {
                navigator.share({
                    title: title,
                    url: url
                });
            } else {
                // Fallback to copying URL
                navigator.clipboard.writeText(url).then(() => {
                    alert('Link copied to clipboard!');
                });
            }
        });
    });
    
    // Load more functionality
    const loadMoreBtn = document.getElementById('load-more-feed');
    const feedContainer = document.getElementById('feed-container');
    
    if (loadMoreBtn && feedContainer) {
        loadMoreBtn.addEventListener('click', function() {
            const page = parseInt(this.getAttribute('data-page')) + 1;
            this.setAttribute('data-page', page);
            
            // Implement your AJAX load more functionality here
            // This is a placeholder
            this.disabled = true;
            this.innerHTML = '<span class="loading-spinner"></span> Loading...';
            
            setTimeout(() => {
                this.disabled = false;
                this.innerHTML = '<span class="btn-icon">⬇️</span> Load More Content';
            }, 1000);
        });
    }
});