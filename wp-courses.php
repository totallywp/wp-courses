<?php
/*
Plugin Name: WP Courses
Plugin URI: https://totallywp.com/wp-courses
Description: WP Courses is a WordPress plugin that allows you to create and manage online courses on your WordPress website.
Version: 1.0.0
Author: TotallyWP
Author URI: https://totallywp.com
License: GPL v2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Text Domain: wp-courses
*/
 
require_once plugin_dir_path(__FILE__) . '/includes/woocommerce.php';

// Register Custom Post Type for Courses
 function wp_courses_register_course_post_type() {
     $labels = array(
         'name'               => 'Courses',
         'singular_name'      => 'Course',
         'menu_name'          => 'Courses',
         'name_admin_bar'     => 'Course',
         'add_new'            => 'Add New',
         'add_new_item'       => 'Add New Course',
         'new_item'           => 'New Course',
         'edit_item'          => 'Edit Course',
         'view_item'          => 'View Course',
         'all_items'          => 'All Courses',
         'search_items'       => 'Search Courses',
         'parent_item_colon'  => 'Parent Courses:',
         'not_found'          => 'No courses found.',
         'not_found_in_trash' => 'No courses found in Trash.'
     );
 
     $args = array(
         'labels'              => $labels,
         'public'              => true,
         'publicly_queryable'  => true,
         'show_ui'             => true,
         'show_in_menu'        => true,
         'query_var'           => true,
         'rewrite'             => array( 'slug' => 'course', 'with_front' => false ),
         'capability_type'     => 'post',
         'has_archive'         => 'courses',
         'hierarchical'        => false,
         'menu_position'       => 5,
         'supports'            => array( 'title', 'editor', 'thumbnail' ),
         'show_in_rest'        => true
     );
 
     register_post_type( 'course', $args );
 }
 add_action( 'init', 'wp_courses_register_course_post_type' );
 
 // Modify course permalinks
 function wp_courses_modify_course_permalink( $post_link, $post ) {
     if ( 'course' === $post->post_type ) {
         $post_link = str_replace( '%course%', $post->post_name, $post_link );
     }
     return $post_link;
 }
 add_filter( 'post_type_link', 'wp_courses_modify_course_permalink', 10, 2 );

// Register Custom Post Type for Lessons
function wp_courses_register_lesson_post_type() {
    $labels = array(
        'name'               => 'Lessons',
        'singular_name'      => 'Lesson',
        'menu_name'          => 'Lessons',
        'name_admin_bar'     => 'Lesson',
        'add_new'            => 'Add New',
        'add_new_item'       => 'Add New Lesson',
        'new_item'           => 'New Lesson',
        'edit_item'          => 'Edit Lesson',
        'view_item'          => 'View Lesson',
        'all_items'          => 'All Lessons',
        'search_items'       => 'Search Lessons',
        'parent_item_colon'  => 'Parent Lessons:',
        'not_found'          => 'No lessons found.',
        'not_found_in_trash' => 'No lessons found in Trash.'
    );

    $args = array(
        'labels'              => $labels,
        'public'              => true,
        'publicly_queryable'  => true,
        'show_ui'             => true,
        'show_in_menu'        => 'edit.php?post_type=course',
        'query_var'           => true,
        'rewrite'             => array( 'slug' => 'lesson' ),
        'capability_type'     => 'post',
        'has_archive'         => true,
        'hierarchical'        => false,
        'menu_position'       => 5,
        'supports'            => array( 'title', 'editor', 'thumbnail' ),
        'show_in_rest'        => true
    );

    register_post_type( 'lesson', $args );
}
add_action( 'init', 'wp_courses_register_lesson_post_type' );

// Register Custom Taxonomy for Modules
function wp_courses_register_module_taxonomy() {
    $labels = array(
        'name'                       => 'Modules',
        'singular_name'              => 'Module',
        'menu_name'                  => 'Modules',
        'all_items'                  => 'All Modules',
        'parent_item'                => 'Parent Module',
        'parent_item_colon'          => 'Parent Module:',
        'new_item_name'              => 'New Module Name',
        'add_new_item'               => 'Add New Module',
        'edit_item'                  => 'Edit Module',
        'update_item'                => 'Update Module',
        'separate_items_with_commas' => 'Separate modules with commas',
        'search_items'               => 'Search Modules',
        'add_or_remove_items'        => 'Add or remove modules',
        'choose_from_most_used'      => 'Choose from the most used modules',
        'not_found'                  => 'No modules found.'
    );

    $args = array(
        'labels'            => $labels,
        'hierarchical'      => true,
        'public'            => true,
        'show_ui'           => true,
        'show_admin_column' => true,
        'rewrite'           => array( 'slug' => 'module' ),
        'show_in_rest'      => true
    );

    register_taxonomy( 'module', array( 'lesson', 'course' ), $args );
}
add_action( 'init', 'wp_courses_register_module_taxonomy' );

// Add Meta Box for Lessons to Select Modules
function wp_courses_lesson_module_meta_box() {
    add_meta_box(
        'wp_courses_lesson_module_meta_box',
        'Select Module for Lesson',
        'wp_courses_render_lesson_module_meta_box',
        'lesson',
        'side'
    );
}
add_action( 'add_meta_boxes', 'wp_courses_lesson_module_meta_box' );

// Render the Lesson Module Meta Box
function wp_courses_render_lesson_module_meta_box( $post ) {
    $modules = get_terms( 'module', array( 'hide_empty' => false ) );
    $selected_module = get_post_meta( $post->ID, 'wp_courses_lesson_module', true );

    wp_nonce_field( 'wp_courses_save_lesson_module', 'wp_courses_lesson_module_nonce' );

    echo '<label for="wp_courses_lesson_module">Select Module:</label>';
    echo '<select name="wp_courses_lesson_module" id="wp_courses_lesson_module">';
    echo '<option value="">None</option>';

    foreach ( $modules as $module ) {
        $selected = selected( $selected_module, $module->term_id, false );
        echo '<option value="' . $module->term_id . '" ' . $selected . '>' . $module->name . '</option>';
    }

    echo '</select>';
}

// Save Lesson Module Meta Box Data
function wp_courses_save_lesson_module_meta_box( $post_id ) {
    if ( ! isset( $_POST['wp_courses_lesson_module_nonce'] ) || ! wp_verify_nonce( $_POST['wp_courses_lesson_module_nonce'], 'wp_courses_save_lesson_module' ) ) {
        return;
    }

    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }

    if ( isset( $_POST['wp_courses_lesson_module'] ) ) {
        $module_id = absint( $_POST['wp_courses_lesson_module'] );
        update_post_meta( $post_id, 'wp_courses_lesson_module', $module_id );
    }
}
add_action( 'save_post_lesson', 'wp_courses_save_lesson_module_meta_box' );

// Add Meta Box for Modules to Select Courses
function wp_courses_module_course_meta_box() {
    add_meta_box(
        'wp_courses_module_course_meta_box',
        'Select Course for Module',
        'wp_courses_render_module_course_meta_box',
        'module',
        'side'
    );
}
add_action( 'add_meta_boxes', 'wp_courses_module_course_meta_box' );

// Render the Module Course Meta Box
function wp_courses_render_module_course_meta_box( $term ) {
    $courses = get_posts( array( 'post_type' => 'course', 'posts_per_page' => -1 ) );
    $selected_course = get_term_meta( $term->term_id, 'wp_courses_module_course', true );

    wp_nonce_field( 'wp_courses_save_module_course', 'wp_courses_module_course_nonce' );

    echo '<label for="wp_courses_module_course">Select Course:</label>';
    echo '<select name="wp_courses_module_course" id="wp_courses_module_course">';
    echo '<option value="">None</option>';

    foreach ( $courses as $course ) {
        $selected = selected( $selected_course, $course->ID, false );
        echo '<option value="' . $course->ID . '" ' . $selected . '>' . $course->post_title . '</option>';
    }

    echo '</select>';
}

// Save Module Course Meta Box Data
function wp_courses_save_module_course_meta_box( $term_id ) {
    if ( ! isset( $_POST['wp_courses_module_course_nonce'] ) || ! wp_verify_nonce( $_POST['wp_courses_module_course_nonce'], 'wp_courses_save_module_course' ) ) {
        return;
    }

    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }

    if ( isset( $_POST['wp_courses_module_course'] ) ) {
        $course_id = absint( $_POST['wp_courses_module_course'] );
        update_term_meta( $term_id, 'wp_courses_module_course', $course_id );
    }
}
add_action( 'edited_module', 'wp_courses_save_module_course_meta_box' );

// Register custom templates
function custom_course_templates($template) {
    if (is_singular('course') && !file_exists(get_stylesheet_directory() . '/template-course-page.php')) {
        $template = plugin_dir_path(__FILE__) . '/templates/template-course-page.php';
    }

    if (is_singular('lesson') && !file_exists(get_stylesheet_directory() . '/template-single-lesson.php')) {
        $template = plugin_dir_path(__FILE__) . '/templates/template-single-lesson.php';
    }

    return $template;
}
add_filter('single_template', 'custom_course_templates');

// Add meta boxes for lessons as sidebar panels
function wp_courses_add_lesson_meta_boxes() {
    add_meta_box(
        'lesson_panel',
        'Lesson Content',
        'wp_courses_lesson_panel_callback',
        'lesson',
        'side',
        'default'
    );

    add_meta_box(
        'lesson_video_url',
        'Video URL',
        'wp_courses_lesson_video_url_callback',
        'lesson',
        'side',
        'default'
    );

    add_meta_box(
        'lesson_workbook',
        'Workbook',
        'wp_courses_lesson_workbook_callback',
        'lesson',
        'side',
        'default'
    );

    add_meta_box(
        'lesson_audio_file',
        'Audio File',
        'wp_courses_lesson_audio_file_callback',
        'lesson',
        'side',
        'default'
    );
}
add_action('add_meta_boxes', 'wp_courses_add_lesson_meta_boxes');

// Lesson Content panel callback
function wp_courses_lesson_panel_callback($post) {
    $lesson_content = get_post_meta($post->ID, 'lesson_content', true);
    wp_nonce_field('wp_courses_save_lesson_meta', 'wp_courses_lesson_meta_nonce');
    ?>
    <div class="lesson-content-panel">
        <?php wp_editor($lesson_content, 'lesson_content'); ?>
    </div>
    <?php
}

// Lesson Video URL meta box callback
function wp_courses_lesson_video_url_callback($post) {
    $video_url = get_post_meta($post->ID, 'lesson_video_url', true);
    ?>
    <label for="lesson_video_url">Video URL:</label>
    <input type="text" name="lesson_video_url" id="lesson_video_url" value="<?php echo esc_attr($video_url); ?>" />
    <?php
}

// Lesson Workbook meta box callback
function wp_courses_lesson_workbook_callback($post) {
    $workbook_title = get_post_meta($post->ID, 'lesson_workbook_title', true);
    $workbook_content = get_post_meta($post->ID, 'lesson_workbook_content', true);
    ?>
    <label for="lesson_workbook_title">Workbook Title:</label>
    <input type="text" name="lesson_workbook_title" id="lesson_workbook_title" value="<?php echo esc_attr($workbook_title); ?>" />
    <?php wp_editor($workbook_content, 'lesson_workbook_content'); ?>
    <?php
}

// Lesson Audio File meta box callback
function wp_courses_lesson_audio_file_callback($post) {
    $audio_file = get_post_meta($post->ID, 'lesson_audio_file', true);
    ?>
    <label for="lesson_audio_file">Audio File:</label>
    <input type="text" name="lesson_audio_file" id="lesson_audio_file" value="<?php echo esc_attr($audio_file); ?>" />
    <?php
}

// Save lesson meta box values
function wp_courses_save_lesson_meta_boxes($post_id) {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (isset($_POST['lesson_content'])) {
        update_post_meta($post_id, 'lesson_content', wp_kses_post($_POST['lesson_content']));
    }

    if (isset($_POST['lesson_video_url'])) {
        update_post_meta($post_id, 'lesson_video_url', sanitize_text_field($_POST['lesson_video_url']));
    }

    if (isset($_POST['lesson_workbook_title'])) {
        update_post_meta($post_id, 'lesson_workbook_title', sanitize_text_field($_POST['lesson_workbook_title']));
    }

    if (isset($_POST['lesson_workbook_content'])) {
        update_post_meta($post_id, 'lesson_workbook_content', wp_kses_post($_POST['lesson_workbook_content']));
    }

    if (isset($_POST['lesson_audio_file'])) {
        update_post_meta($post_id, 'lesson_audio_file', sanitize_text_field($_POST['lesson_audio_file']));
    }
}
add_action('save_post_lesson', 'wp_courses_save_lesson_meta_boxes');