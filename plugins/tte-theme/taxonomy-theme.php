<?php get_template_part('templates/page', 'header'); ?>

<?php
$currentTerm = get_queried_object();
?>
<div class="page-content">
    <div class="container-fluid">
        <p><?php echo esc_html($currentTerm->description); ?></p>
        <h2>Blogs</h2>
        <?php get_template_part('templates/list', 'post'); ?>
    </div>
</div>
