<?php if (!have_posts()) : ?>
    <div class="alert alert-warning">
        <?php _e('Sorry, no results were found.', 'sage'); ?>
    </div>
    <?php get_search_form(); ?>
<?php endif; ?>

<div class="post-list">
    <?php while (have_posts()) : the_post(); ?>
        <div class="post-list-item line-below">
            <div class="post-list-item-head">
                <div class="post-list-item-title">
                    <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                </div>
                <div class="post-list-item-author">
                    <?php the_field('author'); ?>
                </div>
            </div>
            <div class="post-list-item-body">
                <?php the_excerpt(); ?>
            </div>
        </div>
    <?php endwhile; ?>
</div>

<?php the_posts_navigation(); ?>
