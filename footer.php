<!-- Footer -->
    <footer class="site-footer">
        <div class="container">
            <div class="footer-widgets">
                <?php if (is_active_sidebar('footer-widgets')) : ?>
                    <?php dynamic_sidebar('footer-widgets'); ?>
                <?php else : ?>
                    <!-- Default footer widgets -->
                    <div class="footer-widget">
                        <h3 class="footer-widget-title"><?php bloginfo('name'); ?></h3>
                        <p>Your trusted source for technology insights, reviews, and tutorials.</p>
                    </div>
                    
                    <div class="footer-widget">
                        <h3 class="footer-widget-title">Quick Links</h3>
                        <?php
                        wp_nav_menu(array(
                            'theme_location' => 'footer',
                            'menu_class' => 'footer-menu',
                            'container' => false,
                            'fallback_cb' => false,
                        ));
                        ?>
                    </div>
                    
                    <div class="footer-widget">
                        <h3 class="footer-widget-title">Categories</h3>
                        <ul class="footer-menu">
                            <?php
                            $categories = get_categories(array(
                                'orderby' => 'name',
                                'number' => 5
                            ));
                            foreach ($categories as $category) {
                                echo '<li><a href="' . get_category_link($category->term_id) . '">' . $category->name . '</a></li>';
                            }
                            ?>
                        </ul>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> <?php bloginfo('name'); ?>. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <?php 
    // Performance monitoring
    ezoix_performance_monitor();
    ?>

    <?php wp_footer(); ?>
</body>
</html>