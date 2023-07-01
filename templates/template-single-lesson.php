<?php
/*
Template Name: Single Lesson
*/

get_header('divi');

// Get the current lesson ID
$lesson_id = get_the_ID();

// Get the lesson title, content, and featured image
$lesson_title = get_the_title($lesson_id);
//$lesson_content = get_the_content($lesson_id);
$lesson_image = get_the_post_thumbnail($lesson_id, 'full');

// Get the course ID for the current lesson
$course_id = 0;
$modules = wp_get_post_terms($lesson_id, 'module');

if (!empty($modules)) {
    $module = $modules[0];
    $course_id = get_term_meta($module->term_id, 'wp_courses_module_course', true);
}

// Get the URL of the course page
$course_url = get_permalink($course_id);

// Get the URL of the next lesson in the same module
$next_lesson_url = '';
$next_lesson = get_adjacent_post(false, '', true, 'module');

if ($next_lesson) {
    $next_lesson_url = get_permalink($next_lesson);
}
?>

<div id="main-content">
    <div class="container">
        <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
            <h1 class="entry-title"><?php echo $lesson_title; ?></h1>

            <div class="entry-content">
                <?php echo the_content(); //$lesson_content; ?>
            </div>

            <div class="entry-thumbnail">
                <?php echo $lesson_image; ?>
            </div>

            <div class="entry-navigation">
                <?php if (!empty($next_lesson_url)) : ?>
                    <a href="<?php echo $next_lesson_url; ?>" class="next-lesson-link">Next Lesson</a>
                <?php endif; ?>

                <?php if (!empty($course_url)) : ?>
                    <a href="<?php echo $course_url; ?>" class="course-link">Back to Course</a>
                <?php endif; ?>
            </div>
        </article>
    </div>
</div>

<?php
get_footer('divi');