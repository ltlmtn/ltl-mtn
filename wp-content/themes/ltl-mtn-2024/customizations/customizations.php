<?php
function asw_customize_register( $wp_customize ) {

    // Create our sections

    $wp_customize->add_section( 'video' , array(
        'title'             => 'Homepage Video',
        'priority'          => 1
    ) );

    $wp_customize->add_section( 'seo' , array(
        'title'             => 'SEO',
        'priority'          => 3
    ) );

    // Video Settings

    $wp_customize->add_setting( 'video_file');
    $wp_customize->add_control( new WP_Customize_Media_Control( $wp_customize, 'video_file', array(
        'label' => __( 'Video File' ),
        'section' => 'video',
        'description' => 'Upload the video file for the homepage.'
    ) ) );

    $wp_customize->add_setting( 'video_poster');
    $wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'video_poster', array(
        'label' => __( 'Video Poster' ),
        'section' => 'video',
        'description' => 'Upload the video poster for the homepage.'
    ) ) );

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