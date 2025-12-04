<?php
/**
 * Template Name: Mobile Categories Archive
 * 
 * @package Ezoix_Tech_Blog
 */

get_header(); ?>

<style>
.archive-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 3rem 2rem;
    border-radius: 16px;
    margin-bottom: 2.5rem;
    text-align: center;
    position: relative;
    overflow: hidden;
}

.archive-header::before {
    content: '';
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: radial-gradient(circle, rgba(255,255,255,0.1) 1px, transparent 1px);
    background-size: 50px 50px;
    animation: float 20s linear infinite;
    opacity: 0.3;
}

@keyframes float {
    0% { transform: translate(0, 0) rotate(0deg); }
    100% { transform: translate(-50px, -50px) rotate(360deg); }
}

.archive-title {
    font-size: 2.75rem;
    font-weight: 800;
    margin-bottom: 1rem;
    position: relative;
    display: inline-block;
    color: white;

}

.archive-title::after {
    content: '';
    position: absolute;
    bottom: -10px;
    left: 50%;
    transform: translateX(-50%);
    width: 100px;
    height: 4px;
    background: linear-gradient(90deg, #48bb78, #38a169);
    border-radius: 2px;
}

.archive-description {
    font-size: 1.125rem;
    opacity: 0.9;
    max-width: 800px;
    margin: 0 auto 1.5rem;
    line-height: 1.6;
}

.archive-meta {
    background: rgba(255,255,255,0.15);
    backdrop-filter: blur(10px);
    padding: 0.75rem 1.5rem;
    border-radius: 50px;
    display: inline-block;
    border: 1px solid rgba(255,255,255,0.2);
}

.device-count {
    font-weight: 600;
    font-size: 1.1rem;
    letter-spacing: 0.5px;
}

/* ============================================
   DEVICES LIST STYLING (Index Style)
   ============================================ */
.mobile-devices-grid {
    background: #ffffff;
    border-radius: 20px;
    padding: 2.5rem;
    box-shadow: 0 5px 15px rgba(0,0,0,0.05);
    border: 1px solid #f0f0f0;
}

/* List Container */
.devices-list-container {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

/* Device List Item (Index Style) */
.device-list-item {
    display: flex;
    align-items: center;
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    border: 1px solid #e2e8f0;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.device-list-item::before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 4px;
    background: linear-gradient(to bottom, #667eea, #764ba2);
    opacity: 0;
    transition: opacity 0.3s ease;
}

.device-list-item:hover {
    transform: translateX(5px);
    box-shadow: 0 10px 25px rgba(102, 126, 234, 0.1);
    border-color: #c3dafe;
}

.device-list-item:hover::before {
    opacity: 1;
}

/* Device Image */
.device-list-image {
    flex: 0 0 120px;
    height: 120px;
    border-radius: 10px;
    overflow: hidden;
    background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 1.5rem;
    position: relative;
}

.device-list-image img {
    width: 100%;
    height: 100%;
    object-fit: contain;
    padding: 1rem;
    transition: transform 0.5s ease;
}

.device-list-item:hover .device-list-image img {
    transform: scale(1.05);
}

.no-image-list {
    color: #a0aec0;
    font-size: 2rem;
}

/* Status Badge */
.list-status-badge {
    position: absolute;
    top: 10px;
    right: 10px;
    padding: 0.25rem 0.75rem;
    border-radius: 15px;
    font-size: 0.7rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    background: white;
    box-shadow: 0 3px 8px rgba(0,0,0,0.1);
}

.list-status-new { background: #48bb78; color: white; }
.list-status-upcoming { background: #ed8936; color: white; }
.list-status-discontinued { background: #a0aec0; color: white; }

/* Device Content */
.device-list-content {
    flex: 1;
}

.device-list-title {
    font-size: 1.4rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
}

.device-list-title a {
    color: #2d3748;
    text-decoration: none;
    transition: color 0.2s;
}

.device-list-title a:hover {
    color: #667eea;
}

.device-list-meta {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 0.75rem;
    flex-wrap: wrap;
}

.device-list-brand {
    background: #edf2f7;
    color: #4a5568;
    padding: 0.3rem 0.9rem;
    border-radius: 15px;
    font-size: 0.85rem;
    font-weight: 600;
}

.device-list-price {
    font-size: 1.3rem;
    font-weight: 800;
    color: #2d3748;
    position: relative;
    padding-left: 1.5rem;
}

.device-list-price::before {
    content: '$';
    position: absolute;
    left: 0;
    top: 50%;
    transform: translateY(-50%);
    font-size: 1rem;
    color: #48bb78;
    font-weight: 600;
}

.device-list-description {
    color: #718096;
    font-size: 0.95rem;
    line-height: 1.5;
    margin-bottom: 1rem;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* View Details Button */
.device-list-actions {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-top: 1rem;
    padding-top: 1rem;
    border-top: 1px solid #edf2f7;
}

.device-list-specs {
    display: flex;
    gap: 1rem;
    font-size: 0.85rem;
    color: #718096;
}

.spec-item {
    display: flex;
    align-items: center;
    gap: 0.3rem;
}

.spec-item .dashicons {
    color: #667eea;
}

.list-view-details {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.6rem 1.2rem;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    text-decoration: none;
    border-radius: 8px;
    font-weight: 600;
    font-size: 0.9rem;
    transition: all 0.3s ease;
}

.list-view-details:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
    color: white;
}

/* ============================================
   NO DEVICES STATE
   ============================================ */
.no-devices {
    text-align: center;
    padding: 4rem 2rem;
    background: linear-gradient(135deg, #f8fafc 0%, #edf2f7 100%);
    border-radius: 16px;
    border: 2px dashed #cbd5e0;
}

.no-devices-icon {
    font-size: 3rem;
    color: #a0aec0;
    margin-bottom: 1.5rem;
}

.no-devices h3 {
    font-size: 1.5rem;
    color: #4a5568;
    margin-bottom: 1rem;
}

.no-devices p {
    color: #718096;
    margin-bottom: 2rem;
    max-width: 500px;
    margin-left: auto;
    margin-right: auto;
}

.cta-button {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 1rem 2rem;
    background: white;
    color: #667eea;
    text-decoration: none;
    border-radius: 10px;
    font-weight: 600;
    border: 2px solid #667eea;
    transition: all 0.3s ease;
    box-shadow: 0 4px 6px rgba(102, 126, 234, 0.1);
}

.cta-button:hover {
    background: #667eea;
    color: white;
    transform: translateY(-3px);
    box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
}

/* ============================================
   SIDEBAR STYLING
   ============================================ */
.sidebar {
    background: #ffffff;
    border-radius: 16px;
    padding: 2rem;
    margin-top: 2.5rem;
    box-shadow: 0 5px 15px rgba(0,0,0,0.05);
    border: 1px solid #f0f0f0;
}

.sidebar-widget {
    margin-bottom: 2.5rem;
}

.sidebar-widget:last-child {
    margin-bottom: 0;
}

.widget-title {
    font-size: 1.25rem;
    font-weight: 700;
    color: #2d3748;
    margin-bottom: 1.25rem;
    padding-bottom: 0.75rem;
    border-bottom: 2px solid #edf2f7;
    position: relative;
}

.widget-title::after {
    content: '';
    position: absolute;
    bottom: -2px;
    left: 0;
    width: 50px;
    height: 2px;
    background: linear-gradient(90deg, #667eea, #764ba2);
}

/* Categories & Brands List */
.categories-list, .brands-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.categories-list li, .brands-list li {
    margin-bottom: 0.5rem;
}

.categories-list a, .brands-list a {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem 1rem;
    background: #f8fafc;
    color: #4a5568;
    text-decoration: none;
    border-radius: 8px;
    transition: all 0.2s ease;
    border-left: 3px solid transparent;
}

.categories-list a:hover, .brands-list a:hover {
    background: #667eea;
    color: white;
    padding-left: 1.25rem;
    border-left-color: #764ba2;
}

.categories-list li.active a {
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    border-left-color: #48bb78;
}

.count {
    font-size: 0.8rem;
    font-weight: 600;
    background: rgba(255,255,255,0.2);
    padding: 0.15rem 0.5rem;
    border-radius: 10px;
    min-width: 35px;
    text-align: center;
}

/* ============================================
   PAGINATION STYLING
   ============================================ */
.pagination {
    padding: 2.5rem 0 1rem;
    border-top: 1px solid #edf2f7;
    margin-top: 2rem;
}

.nav-links {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.page-numbers {
    display: flex;
    align-items: center;
    justify-content: center;
    min-width: 40px;
    height: 40px;
    padding: 0 0.75rem;
    border-radius: 10px;
    background: white;
    color: #4a5568;
    text-decoration: none;
    font-weight: 600;
    border: 1px solid #e2e8f0;
    transition: all 0.2s ease;
}

.page-numbers:hover:not(.current) {
    border-color: #667eea;
    color: #667eea;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(102, 126, 234, 0.1);
}

.page-numbers.current {
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    border-color: transparent;
    box-shadow: 0 4px 10px rgba(102, 126, 234, 0.3);
}

.page-numbers.dots {
    background: transparent;
    border: none;
    color: #a0aec0;
}

.page-numbers.prev,
.page-numbers.next {
    padding: 0 1.25rem;
    background: #f8fafc;
}

/* ============================================
   RESPONSIVE STYLING
   ============================================ */
@media (max-width: 992px) {
    .device-list-item {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .device-list-image {
        flex: 0 0 100%;
        height: 200px;
        margin-right: 0;
        margin-bottom: 1.5rem;
    }
    
    .device-list-actions {
        flex-direction: column;
        gap: 1rem;
        align-items: flex-start;
    }
}

@media (max-width: 768px) {
    .archive-header {
        padding: 2rem 1.5rem;
    }
    
    .archive-title {
        font-size: 2rem;
    }
    
    .mobile-devices-grid {
        padding: 1.5rem;
    }
    
    .device-list-meta {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.5rem;
    }
    
    .sidebar {
        padding: 1.5rem;
    }
}

@media (max-width: 480px) {
    .archive-title {
        font-size: 1.75rem;
    }
    
    .device-list-title {
        font-size: 1.25rem;
    }
    
    .device-list-specs {
        flex-wrap: wrap;
    }
    
    .widget-title {
        font-size: 1.1rem;
    }
    
    .page-numbers {
        min-width: 35px;
        height: 35px;
        font-size: 0.9rem;
    }
}

/* Container & Layout */
.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
}

.content-area {
    display: grid;
    grid-template-columns: 1fr 300px;
    gap: 3rem;
    margin-top: 2rem;
}

@media (max-width: 992px) {
    .content-area {
        grid-template-columns: 1fr;
    }
}
</style>

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
                <h1 class="archive-title"> Mobile Devices</h1>
            </header>
            
            <!-- Devices List -->
            <div class="mobile-devices-grid">
                <?php if (have_posts()) : ?>
                    
                    <div class="devices-list-container">
                        <?php while (have_posts()) : the_post(); 
                            $device_price = get_field('device_price');
                            $device_status = get_field('device_status');
                            $device_rating = get_field('device_rating');
                            $device_ram = get_field('device_ram');
                            $device_storage = get_field('device_storage');
                            $device_camera = get_field('device_camera');
                            $device_excerpt = get_the_excerpt();
                        ?>
                        <article class="device-list-item">
                            <div class="device-list-image">
                                <?php if (has_post_thumbnail()) : ?>
                                    <a href="<?php the_permalink(); ?>">
                                        <?php the_post_thumbnail('medium', array('loading' => 'lazy')); ?>
                                    </a>
                                <?php else : ?>
                                    <div class="no-image-list">
                                        <span class="dashicons dashicons-smartphone"></span>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if ($device_status) : ?>
                                    <span class="list-status-badge list-status-<?php echo esc_attr($device_status); ?>">
                                        <?php echo ucfirst($device_status); ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="device-list-content">
                                <h2 class="device-list-title">
                                    <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                </h2>
                                
                                <div class="device-list-meta">
                                    <?php
                                    $brands = get_the_terms(get_the_ID(), 'mobile_brand');
                                    if ($brands && !is_wp_error($brands)) :
                                        echo '<span class="device-list-brand">';
                                        foreach ($brands as $brand) {
                                            echo esc_html($brand->name);
                                            break;
                                        }
                                        echo '</span>';
                                    endif;
                                    ?>
                                    
                                    <?php if ($device_price) : ?>
                                        <div class="device-list-price">
                                            <?php echo esc_html($device_price); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <?php if ($device_excerpt) : ?>
                                    <div class="device-list-description">
                                        <?php echo esc_html(wp_trim_words($device_excerpt, 20)); ?>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="device-list-actions">
                                    <div class="device-list-specs">
                                        <?php if ($device_ram) : ?>
                                            <span class="spec-item">
                                                <span class="dashicons dashicons-performance"></span>
                                                <?php echo esc_html($device_ram); ?> RAM
                                            </span>
                                        <?php endif; ?>
                                        
                                        <?php if ($device_storage) : ?>
                                            <span class="spec-item">
                                                <span class="dashicons dashicons-database"></span>
                                                <?php echo esc_html($device_storage); ?>
                                            </span>
                                        <?php endif; ?>
                                        
                                        <?php if ($device_camera) : ?>
                                            <span class="spec-item">
                                                <span class="dashicons dashicons-camera"></span>
                                                <?php echo esc_html($device_camera); ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <a href="<?php the_permalink(); ?>" class="list-view-details">
                                        View Details <span class="dashicons dashicons-arrow-right-alt2"></span>
                                    </a>
                                </div>
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
                        <div class="no-devices-icon">
                            <span class="dashicons dashicons-smartphone"></span>
                        </div>
                        <h3>No Devices Found</h3>
                        <p>There are currently no mobile devices in this category. Check back soon for new additions!</p>
                        <a href="<?php echo get_post_type_archive_link('mobile_device'); ?>" class="cta-button">
                            <span class="dashicons dashicons-arrow-left-alt2"></span>
                            Browse All Devices
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