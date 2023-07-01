<?php
/*
Template Name: Course Page
*/

get_header('divi');

// Get the current course ID
$course_id = get_the_ID();

// Get the course title, description, and featured image
$course_title = get_the_title($course_id);
$course_description = get_the_content($course_id);
$course_image = get_the_post_thumbnail($course_id, 'full');

// Get all the modules for the current course
$modules = get_the_terms($course_id, 'module');
?>

<div id="main-content">
    <div class="container">
        <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
            <h1 class="entry-title"><?php echo $course_title; ?></h1>
            
            <div class="entry-thumbnail">
                <?php echo $course_image; ?>
            </div>
            
            <div class="entry-content">
                <?php echo $course_description; ?>
            </div>

            <div class="course-modules">
                <?php foreach ($modules as $module) : ?>
                    <div class="module-panel">
                        <h2 class="module-title"><?php echo $module->name; ?></h2>

                        <div class="module-lessons">
                            <?php
                            // Get the lessons for the current module
                            $lessons = new WP_Query(array(
                                'post_type' => 'lesson',
                                'tax_query' => array(
                                    array(
                                        'taxonomy' => 'module',
                                        'field' => 'term_id',
                                        'terms' => $module->term_id,
                                    ),
                                ),
                                'posts_per_page' => -1,
                                'order' => 'ASC',
                            ));

                            while ($lessons->have_posts()) :
                                $lessons->the_post();
                                ?>

                                <div class="lesson">
                                    <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                </div>

                            <?php endwhile; ?>
                            <?php wp_reset_postdata(); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </article>
    </div>
</div>

<?php
get_footer('divi');