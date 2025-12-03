
<?php
/**
 * Template Name: Mobile Categories Archive
 * 
 * @package Ezoix_Tech_Blog
 */

get_header(); ?>

<div class="container">
    <div class="content-area">
        <main class="main-content">
            
            <!-- Archive Header -->
            <header class="archive-header">
                <?php 
                $term = get_queried_object();
                $term_name = single_term_title('', false);
                $term_description = term_description();
                ?>
                <h1 class="archive-title"><?php echo esc_html($term_name); ?> Mobile Devices</h1>
                
                <?php if ($term_description) : ?>
                    <div class="archive-description">
                        <?php echo wp_kses_post($term_description); ?>
                    </div>
                <?php endif; ?>
                
                <div class="archive-meta">
                    <span class="device-count">
                        <?php 
                        global $wp_query;
                        echo number_format($wp_query->found_posts) . ' ' . _n('device', 'devices', $wp_query->found_posts, 'ezoix'); 
                        ?>
                    </span>
                </div>
            </header>
            
            <!-- Devices Grid -->
            <div class="mobile-devices-grid">
                <?php if (have_posts()) : ?>
                    
                    <div class="grid-container">
                        <?php while (have_posts()) : the_post(); 
                            $device_price = get_field('device_price');
                            $device_status = get_field('device_status');
                            $device_rating = get_field('device_rating');
                        ?>
                        <article class="mobile-device-card">
                            <div class="device-image">
                                <?php if (has_post_thumbnail()) : ?>
                                    <a href="<?php the_permalink(); ?>">
                                        <?php the_post_thumbnail('medium', array('loading' => 'lazy')); ?>
                                    </a>
                                <?php else : ?>
                                    <div class="no-image">
                                        <span class="dashicons dashicons-smartphone"></span>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if ($device_status) : ?>
                                    <span class="device-status status-<?php echo esc_attr($device_status); ?>">
                                        <?php echo ucfirst($device_status); ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="device-content">
                                <h2 class="device-title">
                                    <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                </h2>
                                
                                <div class="device-meta">
                                    <?php
                                    $brands = get_the_terms(get_the_ID(), 'mobile_brand');
                                    if ($brands && !is_wp_error($brands)) :
                                        echo '<span class="device-brand">';
                                        foreach ($brands as $brand) {
                                            echo esc_html($brand->name);
                                            break;
                                        }
                                        echo '</span>';
                                    endif;
                                    ?>
                                </div>
                                
                                <?php if ($device_price) : ?>
                                <div class="device-price">
                                    <span class="price-value"><?php echo esc_html($device_price); ?></span>
                                </div>
                                <?php endif; ?>
                                
                                <a href="<?php the_permalink(); ?>" class="view-details">View Details →</a>
                            </div>
                        </article>
                        <?php endwhile; ?>
                    </div>
                    
                    <!-- Pagination -->
                    <div class="pagination">
                        <?php
                        the_posts_pagination(array(
                            'mid_size' => 2,
                            'prev_text' => __('← Previous', 'ezoix'),
                            'next_text' => __('Next →', 'ezoix'),
                        ));
                        ?>
                    </div>
                    
                <?php else : ?>
                    <div class="no-devices">
                        <p>No mobile devices found in this category.</p>
                        <a href="<?php echo get_post_type_archive_link('mobile_device'); ?>" class="cta-button">
                            ← Browse All Devices
                        </a>
                    </div>
                <?php endif; ?>
            </div>
            
        </main>
        
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-widget">
                <h3 class="widget-title">All Categories</h3>
                <ul class="categories-list">
                    <?php
                    $all_categories = get_terms(array(
                        'taxonomy' => 'mobile_category',
                        'orderby' => 'name',
                        'order' => 'ASC',
                        'hide_empty' => true,
                    ));
                    
                    if ($all_categories && !is_wp_error($all_categories)) :
                        foreach ($all_categories as $category) :
                            $active_class = ($category->term_id == $term->term_id) ? ' class="active"' : '';
                    ?>
                        <li<?php echo $active_class; ?>>
                            <a href="<?php echo get_term_link($category); ?>">
                                <?php echo esc_html($category->name); ?>
                                <span class="count">(<?php echo esc_html($category->count); ?>)</span>
                            </a>
                        </li>
                    <?php
                        endforeach;
                    endif;
                    ?>
                </ul>
            </div>
            
            <div class="sidebar-widget">
                <h3 class="widget-title">Popular Brands</h3>
                <ul class="brands-list">
                    <?php
                    $popular_brands = get_terms(array(
                        'taxonomy' => 'mobile_brand',
                        'orderby' => 'count',
                        'order' => 'DESC',
                        'number' => 8,
                        'hide_empty' => true,
                    ));
                    
                    if ($popular_brands && !is_wp_error($popular_brands)) :
                        foreach ($popular_brands as $brand) :
                    ?>
                        <li>
                            <a href="<?php echo get_term_link($brand); ?>">
                                <?php echo esc_html($brand->name); ?>
                                <span class="count">(<?php echo esc_html($brand->count); ?>)</span>
                            </a>
                        </li>
                    <?php
                        endforeach;
                    endif;
                    ?>
                </ul>
            </div>
        </aside>
    </div>
</div>

<?php get_footer(); ?>
