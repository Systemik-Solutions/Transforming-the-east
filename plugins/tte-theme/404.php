<?php get_template_part('templates/page', 'header'); ?>

<div class="page-content">
    <div class="container-fluid">
		<div class="alert alert-warning">
		  <?php _e('Sorry, but the page you were trying to view does not exist.', 'sage'); ?>
		</div>

		<?php get_search_form(); ?>
    </div>
</div>

