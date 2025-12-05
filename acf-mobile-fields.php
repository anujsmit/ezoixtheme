<?php

if (function_exists('acf_add_local_field_group')):

    acf_add_local_field_group(array(
        'key' => 'group_mobile_device_specs',
        'title' => 'Mobile Device Specifications',
        'fields' => array(
            array(
                'key' => 'field_device_name',
                'label' => 'Device Name',
                'name' => 'device_name',
                'type' => 'text',
                'required' => 1,
            ),
            array(
                'key' => 'field_device_model',
                'label' => 'Model',
                'name' => 'device_model',
                'type' => 'text',
            ),
            array(
                'key' => 'field_release_date',
                'label' => 'Release Date',
                'name' => 'release_date',
                'type' => 'date_picker',
            ),
            array(
                'key' => 'field_device_price',
                'label' => 'Price',
                'name' => 'device_price',
                'type' => 'text',
                'append' => 'USD',
            ),
            array(
                'key' => 'field_seo_meta_description',
                'label' => 'SEO Meta Description',
                'name' => 'seo_meta_description', 
                'type' => 'textarea',
                'rows' => 2,
                'new_lines' => '',
                'instructions' => 'Automatically generated from JSON or specs.',
            ),
            array(
                'key' => 'field_device_status',
                'label' => 'Status',
                'name' => 'device_status',
                'type' => 'select',
                'choices' => array(
                    'available' => 'Available',
                    'upcoming' => 'Upcoming',
                    'discontinued' => 'Discontinued',
                    'rumored' => 'Rumored',
                ),
            ),
            array(
                'key' => 'field_specifications',
                'label' => 'Specifications',
                'name' => 'specifications',
                'type' => 'repeater',
                'layout' => 'block',
                'sub_fields' => array(
                    array(
                        'key' => 'field_spec_category',
                        'label' => 'Category',
                        'name' => 'category',
                        'type' => 'text',
                        'placeholder' => 'e.g., Display, Camera, Battery',
                    ),
                    array(
                        'key' => 'field_spec_items',
                        'label' => 'Specifications',
                        'name' => 'items',
                        'type' => 'repeater',
                        'layout' => 'table',
                        'sub_fields' => array(
                            array(
                                'key' => 'field_spec_key',
                                'label' => 'Feature',
                                'name' => 'key',
                                'type' => 'text',
                                'placeholder' => 'e.g., Size, Resolution',
                            ),
                            array(
                                'key' => 'field_spec_value',
                                'label' => 'Value',
                                'name' => 'value',
                                'type' => 'text',
                                'placeholder' => 'e.g., 6.7 inches, 1080x2400 pixels',
                            ),
                        ),
                    ),
                ),
            ),
            array(
                'key' => 'field_affiliate_links',
                'label' => 'Buy Links',
                'name' => 'affiliate_links',
                'type' => 'repeater',
                'layout' => 'table',
                'sub_fields' => array(
                    array(
                        'key' => 'field_link_platform',
                        'label' => 'Platform',
                        'name' => 'platform',
                        'type' => 'select',
                        'choices' => array(
                            'amazon' => 'Amazon',
                            'flipkart' => 'Flipkart',
                            'ebay' => 'eBay',
                            'aliexpress' => 'AliExpress',
                            'official_store' => 'Official Store',
                            'other' => 'Other',
                        ),
                    ),
                    array(
                        'key' => 'field_link_url',
                        'label' => 'URL',
                        'name' => 'url',
                        'type' => 'url',
                        'required' => 1,
                    ),
                    array(
                        'key' => 'field_link_price',
                        'label' => 'Price',
                        'name' => 'price',
                        'type' => 'text',
                    ),
                ),
            ),
            array(
                'key' => 'field_device_images',
                'label' => 'Device Images',
                'name' => 'device_images',
                'type' => 'gallery',
            ),
            array(
                'key' => 'field_device_rating',
                'label' => 'Rating',
                'name' => 'device_rating',
                'type' => 'range',
                'min' => 0,
                'max' => 10,
                'step' => 0.5,
                'default_value' => 5,
            ),
            array(
                'key' => 'field_pros_cons',
                'label' => 'Pros & Cons',
                'name' => 'pros_cons',
                'type' => 'group',
                'sub_fields' => array(
                    array(
                        'key' => 'field_pros',
                        'label' => 'Pros',
                        'name' => 'pros',
                        'type' => 'repeater',
                        'layout' => 'table',
                        'sub_fields' => array(
                            array(
                                'key' => 'field_pro_item',
                                'label' => 'Pro',
                                'name' => 'item',
                                'type' => 'text',
                            ),
                        ),
                    ),
                    array(
                        'key' => 'field_cons',
                        'label' => 'Cons',
                        'name' => 'cons',
                        'type' => 'repeater',
                        'layout' => 'table',
                        'sub_fields' => array(
                            array(
                                'key' => 'field_con_item',
                                'label' => 'Con',
                                'name' => 'item',
                                'type' => 'text',
                            ),
                        ),
                    ),
                ),
            ),
        ),
        'location' => array(
            array(
                array(
                    'param' => 'post_type',
                    'operator' => '==',
                    'value' => 'mobile_device',
                ),
            ),
        ),
    ));

endif;
