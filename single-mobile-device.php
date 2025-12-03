<?php
/**
 * Template for displaying single mobile devices
 */

get_header(); ?>

<div class="mobile-device-container">
    <div class="device-header">
        <div class="container">
            <div class="breadcrumbs">
                <a href="<?php echo home_url('/'); ?>">Home</a> &raquo;
                <span><?php the_title(); ?></span>
            </div>
            
            <div class="device-title-section">
                <h1 class="device-title"><?php the_title(); ?></h1>
                <div class="device-meta">
                    <?php
                    $brands = get_the_terms(get_the_ID(), 'mobile_brand');
                    if ($brands && !is_wp_error($brands)) :
                        echo '<span class="device-brand">';
                        echo '<span class="meta-label">Brand:</span> ';
                        foreach ($brands as $brand) {
                            echo '<a href="' . get_term_link($brand) . '">' . esc_html($brand->name) . '</a>';
                            break;
                        }
                        echo '</span>';
                    endif;
                    
                    $model = get_field('device_model');
                    if ($model) {
                        echo '<span class="device-model"><span class="meta-label">Model:</span> ' . esc_html($model) . '</span>';
                    }
                    
                    $status = get_field('device_status');
                    if ($status) {
                        $status_labels = array(
                            'available' => 'Available',
                            'upcoming' => 'Upcoming',
                            'discontinued' => 'Discontinued',
                            'rumored' => 'Rumored'
                        );
                        echo '<span class="device-status status-' . esc_attr($status) . '">' . esc_html($status_labels[$status]) . '</span>';
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="device-content-wrapper">
            <main class="device-main-content">
                <?php if (function_exists('get_field') && get_field('device_images')) : ?>
                <section class="device-gallery">
                    <div class="main-image">
                        <?php
                        $images = get_field('device_images');
                        if ($images) : ?>
                            <img src="<?php echo esc_url($images[0]['url']); ?>" 
                                 alt="<?php echo esc_attr($images[0]['alt']); ?>"
                                 class="featured-image"
                                 id="main-gallery-image">
                        <?php endif; ?>
                    </div>
                    <?php if (count($images) > 1) : ?>
                    <div class="gallery-thumbs">
                        <?php foreach ($images as $index => $image) : ?>
                            <img src="<?php echo esc_url($image['sizes']['thumbnail']); ?>" 
                                 alt="<?php echo esc_attr($image['alt']); ?>"
                                 data-full="<?php echo esc_url($image['url']); ?>"
                                 class="gallery-thumb <?php echo $index === 0 ? 'active' : ''; ?>">
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </section>
                <?php endif; ?>

                <section class="quick-specs">
                    <h2 class="section-title">Quick Specifications</h2>
                    <div class="specs-grid">
                        <?php
                        if (function_exists('get_field')) {
                            $specs = get_field('specifications');
                            if ($specs) :
                                $display_categories = array('Display', 'Camera', 'Battery', 'Performance', 'Memory', 'Storage');
                                
                                // Collect quick specs
                                $quick_specs_data = [];
                                $found_categories = [];
                                
                                foreach ($specs as $category) {
                                    $cat_name = trim($category['category']);
                                    if (in_array($cat_name, $display_categories) && !empty($category['items'])) {
                                        $first_item = $category['items'][0];
                                        $quick_specs_data[] = [
                                            'label' => esc_html($cat_name),
                                            'value' => esc_html($first_item['value'])
                                        ];
                                        $found_categories[] = $cat_name;
                                    }
                                }
                                
                                // If we don't have enough specs, add some from other categories
                                if (count($quick_specs_data) < 4) {
                                    foreach ($specs as $category) {
                                        $cat_name = trim($category['category']);
                                        if (!in_array($cat_name, $found_categories) && !empty($category['items'])) {
                                            $first_item = $category['items'][0];
                                            $quick_specs_data[] = [
                                                'label' => esc_html($cat_name),
                                                'value' => esc_html($first_item['value'])
                                            ];
                                            if (count($quick_specs_data) >= 6) break;
                                        }
                                    }
                                }
                                
                                // Display the collected quick specs
                                foreach ($quick_specs_data as $spec_item) :
                                    ?>
                                    <div class="spec-item">
                                        <span class="spec-label"><?php echo $spec_item['label']; ?></span>
                                        <span class="spec-value"><?php echo $spec_item['value']; ?></span>
                                    </div>
                                    <?php
                                endforeach;
                                
                                if (empty($quick_specs_data)) :
                                    echo '<p class="no-quick-specs">No quick specifications available.</p>';
                                endif;
                                
                            endif;
                        }
                        ?>
                    </div>
                </section>

                <section class="full-specifications">
                    <div class="section-header">
                        <h2 class="section-title">Full Specifications</h2>
                        <button class="expand-all">Expand All</button>
                    </div>
                    <div class="specs-accordion">
                        <?php
                        if (function_exists('get_field')) {
                            $specifications = get_field('specifications');
                            if ($specifications) :
                                foreach ($specifications as $index => $category) :
                                    if (!empty($category['items'])) :
                            ?>
                            <div class="spec-category">
                                <h3 class="category-title">
                                    <button class="accordion-toggle" aria-expanded="<?php echo $index === 0 ? 'true' : 'false'; ?>">
                                        <span class="category-name"><?php echo esc_html($category['category']); ?></span>
                                        <span class="toggle-icon"><?php echo $index === 0 ? '−' : '+'; ?></span>
                                    </button>
                                </h3>
                                <div class="category-content" <?php echo $index === 0 ? 'style="display:block;"' : 'style="display:none;"'; ?>>
                                    <table class="specs-table">
                                        <tbody>
                                            <?php foreach ($category['items'] as $item) : ?>
                                            <tr>
                                                <td class="spec-key"><?php echo esc_html($item['key']); ?></td>
                                                <td class="spec-value"><?php echo esc_html($item['value']); ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <?php
                                    endif;
                                endforeach;
                            endif;
                        }
                        ?>
                    </div>
                </section>

                <?php if (function_exists('get_field') && get_field('pros_cons')) : 
                    $pros_cons = get_field('pros_cons');
                ?>
                <section class="pros-cons">
                    <h2 class="section-title">Pros & Cons</h2>
                    <div class="pros-cons-grid">
                        <div class="pros-section">
                            <div class="section-header">
                                <h3><span class="icon-thumbs-up">👍</span> Pros</h3>
                                <span class="count">(<?php echo count($pros_cons['pros']); ?>)</span>
                            </div>
                            <ul class="pros-list">
                                <?php if ($pros_cons['pros']) : ?>
                                    <?php foreach ($pros_cons['pros'] as $pro) : ?>
                                    <li>
                                        <span class="check-icon">✓</span>
                                        <?php echo esc_html($pro['item']); ?>
                                    </li>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </ul>
                        </div>
                        <div class="cons-section">
                            <div class="section-header">
                                <h3><span class="icon-thumbs-down">👎</span> Cons</h3>
                                <span class="count">(<?php echo count($pros_cons['cons']); ?>)</span>
                            </div>
                            <ul class="cons-list">
                                <?php if ($pros_cons['cons']) : ?>
                                    <?php foreach ($pros_cons['cons'] as $con) : ?>
                                    <li>
                                        <span class="cross-icon">✗</span>
                                        <?php echo esc_html($con['item']); ?>
                                    </li>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </div>
                </section>
                <?php endif; ?>

                <?php if (function_exists('get_field') && get_field('affiliate_links')) : ?>
                <section class="buy-links">
                    <h2 class="section-title">Where to Buy</h2>
                    <div class="buy-links-grid">
                        <?php
                        $affiliate_links = get_field('affiliate_links');
                        foreach ($affiliate_links as $link) :
                            $platform_class = strtolower($link['platform']);
                            $platform_icons = array(
                                'amazon' => '🛒',
                                'flipkart' => '📱',
                                'ebay' => '💰',
                                'aliexpress' => '🚚',
                                'official_store' => '🏢',
                                'other' => '🛍️'
                            );
                            $icon = isset($platform_icons[$platform_class]) ? $platform_icons[$platform_class] : '🛒';
                        ?>
                        <a href="<?php echo esc_url($link['url']); ?>" 
                           class="buy-link <?php echo esc_attr($platform_class); ?>" 
                           target="_blank" 
                           rel="nofollow noopener">
                            <div class="platform-icon">
                                <span class="icon"><?php echo $icon; ?></span>
                            </div>
                            <div class="platform-info">
                                <span class="platform-name">Buy on <?php echo esc_html(ucfirst(str_replace('_', ' ', $link['platform']))); ?></span>
                                <?php if ($link['price']) : ?>
                                <span class="platform-price"><?php echo esc_html($link['price']); ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="external-icon">↗</div>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </section>
                <?php endif; ?>

                <?php
                $related_args = array(
                    'post_type' => 'mobile_device',
                    'posts_per_page' => 4,
                    'post__not_in' => array(get_the_ID()),
                    'orderby' => 'rand',
                    'tax_query' => array(
                        array(
                            'taxonomy' => 'mobile_brand',
                            'field' => 'term_id',
                            'terms' => wp_get_post_terms(get_the_ID(), 'mobile_brand', array('fields' => 'ids')),
                        ),
                    ),
                );
                $related_devices = new WP_Query($related_args);
                
                if ($related_devices->have_posts()) :
                ?>
                <section class="related-devices">
                    <div class="section-header">
                        <h2 class="section-title">Related Devices</h2>
                        <a href="<?php echo get_post_type_archive_link('mobile_device') ?: home_url('/mobile-devices/'); ?>" class="view-all">View All</a>
                    </div>
                    <div class="related-grid">
                        <?php while ($related_devices->have_posts()) : $related_devices->the_post(); ?>
                        <article class="related-device-card">
                            <a href="<?php the_permalink(); ?>" class="device-link">
                                <div class="device-image">
                                    <?php if (has_post_thumbnail()) : ?>
                                        <?php the_post_thumbnail('medium'); ?>
                                    <?php else : ?>
                                        <div class="no-image">
                                            <span class="placeholder-icon">📱</span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="device-info">
                                    <h3><?php the_title(); ?></h3>
                                    <?php
                                    $specs = get_field('specifications');
                                    $quick_spec = '';
                                    if ($specs) :
                                        foreach ($specs as $category) :
                                            if ($category['category'] === 'Display' && !empty($category['items'][0])) :
                                                $quick_spec = esc_html($category['items'][0]['value']);
                                                break;
                                            endif;
                                        endforeach;
                                    endif;
                                    
                                    if (empty($quick_spec) && $specs && !empty($specs[0]['items'][0])) {
                                        $quick_spec = esc_html($specs[0]['items'][0]['value']);
                                    }
                                    
                                    if ($quick_spec) :
                                        echo '<p class="device-spec">' . $quick_spec . '</p>';
                                    endif;
                                    ?>
                                </div>
                            </a>
                        </article>
                        <?php endwhile; wp_reset_postdata(); ?>
                    </div>
                </section>
                <?php endif; ?>
            </main>

            <aside class="device-sidebar">
                <?php if (function_exists('get_field') && get_field('device_rating')) : ?>
                <div class="rating-widget sidebar-widget">
                    <h3 class="widget-title">Our Rating</h3>
                    <div class="rating-score">
                        <span class="score"><?php echo number_format(get_field('device_rating'), 1); ?>/5</span>
                        <div class="stars">
                            <?php
                            $rating = get_field('device_rating');
                            $full_stars = floor($rating / 2);
                            $half_star = ($rating / 2) - $full_stars >= 0.5;
                            for ($i = 1; $i <= 5; $i++) :
                                if ($i <= $full_stars) {
                                    echo '<span class="star full">★</span>';
                                } elseif ($i == $full_stars + 1 && $half_star) {
                                    echo '<span class="star half">★</span>';
                                } else {
                                    echo '<span class="star empty">★</span>';
                                }
                            endfor;
                            ?>
                        </div>
                        <div class="rating-bar">
                            <div class="bar-fill" style="width: <?php echo ($rating / 10) * 100; ?>%"></div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <?php if (function_exists('get_field') && get_field('device_price')) : ?>
                <div class="price-widget sidebar-widget">
                    <h3 class="widget-title">Price</h3>
                    <div class="price-value"><?php echo esc_html(get_field('device_price')); ?></div>
                    <p class="price-note">Official price at launch</p>
                </div>
                <?php endif; ?>

                <?php if (function_exists('get_field') && get_field('release_date')) : ?>
                <div class="release-widget sidebar-widget">
                    <h3 class="widget-title">Release Date</h3>
                    <div class="release-date"><?php echo esc_html(get_field('release_date')); ?></div>
                </div>
                <?php endif; ?>

                <?php if (function_exists('get_field') && get_field('device_status')) : ?>
                <div class="status-widget sidebar-widget">
                    <h3 class="widget-title">Status</h3>
                    <div class="status-badge status-<?php echo esc_attr(get_field('device_status')); ?>">
                        <?php 
                        $status = get_field('device_status');
                        $status_labels = array(
                            'available' => 'Available',
                            'upcoming' => 'Upcoming',
                            'discontinued' => 'Discontinued',
                            'rumored' => 'Rumored'
                        );
                        echo isset($status_labels[$status]) ? $status_labels[$status] : ucfirst($status);
                        ?>
                    </div>
                </div>
                <?php endif; ?>

                <div class="share-widget sidebar-widget">
                    <h3 class="widget-title">Share This Device</h3>
                    <div class="share-buttons">
                        <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode(get_permalink()); ?>" 
                           target="_blank" class="share-button facebook" title="Share on Facebook">
                            <span class="icon">f</span>
                            <span class="label">Facebook</span>
                        </a>
                        <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode(get_permalink()); ?>&text=<?php echo urlencode(get_the_title()); ?>" 
                           target="_blank" class="share-button twitter" title="Share on Twitter">
                            <span class="icon">𝕏</span>
                            <span class="label">Twitter</span>
                        </a>
                        <a href="https://api.whatsapp.com/send?text=<?php echo urlencode(get_the_title() . ' ' . get_permalink()); ?>" 
                           target="_blank" class="share-button whatsapp" title="Share on WhatsApp">
                            <span class="icon">WhatsApp</span>
                            <span class="label">WhatsApp</span>
                        </a>
                        <a href="mailto:?subject=<?php echo rawurlencode(get_the_title()); ?>&body=<?php echo rawurlencode(get_permalink()); ?>" 
                           class="share-button email" title="Share via Email">
                            <span class="icon">✉</span>
                            <span class="label">Email</span>
                        </a>
                    </div>
                </div>

                <?php if (function_exists('get_field') && get_field('affiliate_links')) : ?>
                <div class="cta-widget sidebar-widget">
                    <h3 class="widget-title">Ready to Buy?</h3>
                    <p>Check the best prices from our trusted partners:</p>
                    <?php
                    $affiliate_links = get_field('affiliate_links');
                    $first_link = reset($affiliate_links);
                    if ($first_link) :
                    ?>
                    <a href="<?php echo esc_url($first_link['url']); ?>" 
                       class="cta-button" 
                       target="_blank" 
                       rel="nofollow noopener">
                        <span class="cta-icon">🛒</span>
                        <span>Buy Now</span>
                    </a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </aside>
        </div>
    </div>
</div>

<?php get_footer(); ?>