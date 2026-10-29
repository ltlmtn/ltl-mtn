<?php
function asw_customize_register( $wp_customize ) {

    // Create our sections

    $wp_customize->add_section( 'theme_settings' , array(
        'title'             => 'Theme Settings',
        'priority'          => 1
    ) );

    $wp_customize->add_section( 'seo' , array(
        'title'             => 'SEO',
        'priority'          => 3
    ) );

    // Theme Settings
    
    $wp_customize->add_setting( 'slogan');
    $wp_customize->add_control( 'slogan', array(
        'label' => __( 'Slogan' ),
        'type' => 'text',
        'section' => 'theme_settings',
        'description' => 'Enter the site slogan.'
    ) );
    

    // SEO Settings

    $wp_customize->add_setting( 'seo_title');
    $wp_customize->add_control( 'seo_title', array(
        'label' => __( 'SEO Title' ),
        'type' => 'text',
        'section' => 'seo',
        'description' => 'Enter the SEO title for the page.'
    ) );

    $wp_customize->add_setting( 'seo_description');
    $wp_customize->add_control( 'seo_description', array(
        'label' => __( 'SEO Description' ),
        'type' => 'text',
        'section' => 'seo',
        'description' => 'Enter the SEO description for the page.'
    ) );

    $wp_customize->add_setting( 'default_image');
    $wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'default_image', array(
        'label' => __( 'Default Image' ),
        'section' => 'seo',
        'description' => 'Enter the default image for social sharing.'
    ) ) );

}

add_action( 'customize_register', 'asw_customize_register' );