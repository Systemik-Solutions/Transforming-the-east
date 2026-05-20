<?php
/**
 * Template Name: Page - Events
 */
?>

<?php while (have_posts()) : the_post(); ?>
    <?php get_template_part('templates/page', 'header'); ?>

    <?php
        $latest = new WP_Query( (
            array(
                'post_type' => 'event',
                'posts_per_page' => 7,
                'meta_key' => 'event_details_event_date', // name of custom field
                'orderby' => 'meta_value_num',
                'order' => 'DESC',
                'paged' => ( get_query_var( 'paged' ) ) ? absint( get_query_var( 'paged' ) ) : 1
            )
        ) );
    ?>
    <?php if( $latest->have_posts() ): ?>
        <div class="event-list">
            <div class="container-fluid">

                <?php $counter = 0; ?>
                <?php while( $latest->have_posts() ):  $latest->the_post(); ?>
                    <?php $event_excerpt = get_field('event_excerpt'); ?>
                    <?php $event_details = get_field('event_details'); ?>

                    <?php if($counter%2 == 0): ?>

                        <?php if(has_post_thumbnail()): ?>

                            <div class="event-item">
                                <div class="row no-gutters d-flex flex-column flex-lg-row">
                                    <div class="col-lg-4">

                                        <?php if($event_details['event_date'] || $event_details['event_time']): ?>
                                        <div class="event-item__schedule bg-dark text-white d-flex align-items-center justify-content-center p-4">
                                            <div>
                                                <div class="event-date mb-3"><?php echo $event_details['event_date']; ?></div>
                                                <div class="event-time"><?php echo $event_details['event_time']; ?></div>
                                            </div>
                                        </div>
                                        <?php endif; ?>

                                        <?php $featured_img_url = get_the_post_thumbnail_url(get_the_ID(),'large'); ?>

                                        <figure class="event-item__thumbnail bg-image" style="background-image: url(<?php echo $featured_img_url; ?>"></figure>
                                    </div>
                                    <div class="event-item__copy col-lg p-4">
                                        <h2 class="event-title"><?php the_title(); ?></h2>
                                        <div class="event-excerpt">
                                            <?php if($event_excerpt): ?>
                                                <?php echo $event_excerpt; ?>
                                            <?php else: ?>
                                                <?php the_excerpt(); ?>
                                            <?php endif; ?>
                                        </div>
                                        <a href="<?php echo get_the_permalink(); ?>" class="event-cta link-with-arrow">FIND OUT MORE <span data-icon="&#xe068;" class="icon text-primary ml-3"></span></a>
                                    </div>

                                    <?php if($event_details['event_location_address'] || $event_details['event_cost']): ?>
                                    <div class="event-item__location bg-primary text-white d-flex flex-column justify-content-between p-4">
                                        <div class="location-address"><?php echo $event_details['event_location_address']; ?></div>
                                        <div class="event-cost">COST <?php echo $event_details['event_cost']; ?></div>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                        <?php else: ?>

                            <div class="event-item">
                                <div class="row no-gutters d-flex flex-column flex-lg-row">
                                    <div class="col-lg-4 d-lg-flex">

                                        <?php if($event_details['event_date'] || $event_details['event_time']): ?>
                                        <div class="event-item__schedule bg-dark text-white d-flex align-items-center justify-content-center p-4">
                                            <div>
                                                <div class="event-date mb-3"><?php echo $event_details['event_date']; ?></div>
                                                <div class="event-time"><?php echo $event_details['event_time']; ?></div>
                                            </div>
                                        </div>
                                        <?php endif; ?>

                                        <?php if($event_details['event_location_address'] || $event_details['event_cost']): ?>
                                        <div class="event-item__location bg-primary text-white d-flex flex-column justify-content-between p-4">
                                            <div class="location-address"><?php echo $event_details['event_location_address']; ?></div>
                                            <div class="event-cost">COST <?php echo $event_details['event_cost']; ?></div>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="event-item__copy col-lg p-4">
                                        <h2 class="event-title"><?php the_title(); ?></h2>
                                        <div class="event-excerpt">
                                            <?php if($event_excerpt): ?>
                                                <?php echo $event_excerpt; ?>
                                            <?php else: ?>
                                                <?php the_excerpt(); ?>
                                            <?php endif; ?>
                                        </div>
                                        <a href="<?php echo get_the_permalink(); ?>" class="event-cta link-with-arrow">FIND OUT MORE <span data-icon="&#xe068;" class="icon text-primary ml-3"></span></a>
                                    </div>
                                </div>
                            </div>

                        <?php endif; ?>

                    <?php else: ?>

                        <?php if(has_post_thumbnail()): ?>

                            <div class="event-item">
                                <div class="row no-gutters d-flex flex-column flex-lg-row">
                                    <div class="col-lg-4">

                                        <?php if($event_details['event_date'] || $event_details['event_time']): ?>
                                        <div class="event-item__schedule bg-primary text-white d-flex align-items-center justify-content-center p-4">
                                            <div>
                                                <div class="event-date mb-3"><?php echo $event_details['event_date']; ?></div>
                                                <div class="event-time"><?php echo $event_details['event_time']; ?></div>
                                            </div>
                                        </div>
                                        <?php endif; ?>

                                        <?php $featured_img_url = get_the_post_thumbnail_url(get_the_ID(),'large'); ?>

                                        <figure class="event-item__thumbnail bg-image" style="background-image: url(<?php echo $featured_img_url; ?>"></figure>
                                    </div>
                                    <div class="event-item__copy col-lg p-4">
                                        <h2 class="event-title"><?php the_title(); ?></h2>
                                        <div class="event-excerpt">
                                            <?php if($event_excerpt): ?>
                                                <?php echo $event_excerpt; ?>
                                            <?php else: ?>
                                                <?php the_excerpt(); ?>
                                            <?php endif; ?>
                                        </div>
                                        <a href="<?php echo get_the_permalink(); ?>" class="event-cta link-with-arrow">FIND OUT MORE <span data-icon="&#xe068;" class="icon text-primary ml-3"></span></a>
                                    </div>

                                    <?php if($event_details['event_location_address'] || $event_details['event_cost']): ?>
                                    <div class="event-item__location bg-dark text-white d-flex flex-column justify-content-between p-4">
                                        <div class="location-address"><?php echo $event_details['event_location_address']; ?></div>
                                        <div class="event-cost">COST <?php echo $event_details['event_cost']; ?></div>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                        <?php else: ?>

                            <div class="event-item">
                                <div class="row no-gutters d-flex flex-column flex-lg-row">
                                    <div class="col-lg-4 d-lg-flex">

                                        <?php if($event_details['event_date'] || $event_details['event_time']): ?>
                                        <div class="event-item__schedule bg-primary text-white d-flex align-items-center justify-content-center p-4">
                                            <div>
                                                <div class="event-date mb-3"><?php echo $event_details['event_date']; ?></div>
                                                <div class="event-time"><?php echo $event_details['event_time']; ?></div>
                                            </div>
                                        </div>
                                        <?php endif; ?>

                                        <?php if($event_details['event_location'] || $event_details['event_cost']): ?>
                                        <div class="event-item__location bg-dark text-white d-flex flex-column justify-content-between p-4">
                                            <div class="location-address"><?php echo $event_details['event_location_address']; ?></div>
                                            <div class="event-cost">COST <?php echo $event_details['event_cost']; ?></div>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="event-item__copy col-lg p-4">
                                        <h2 class="event-title"><?php the_title(); ?></h2>
                                        <div class="event-excerpt">
                                            <?php if($event_excerpt): ?>
                                                <?php echo $event_excerpt; ?>
                                            <?php else: ?>
                                                <?php the_excerpt(); ?>
                                            <?php endif; ?>
                                        </div>
                                        <a href="<?php echo get_the_permalink(); ?>" class="event-cta link-with-arrow">FIND OUT MORE <span data-icon="&#xe068;" class="icon text-primary ml-3"></span></a>
                                    </div>
                                </div>
                            </div>

                        <?php endif; ?>

                    <?php endif;  ?>

                <?php $counter++; endwhile; ?>

            </div>
        </div>

        <nav aria-label="events navigation">
            <div class="container-fluid mb-5">
                <div class="events-pagination">
                    <?php
                    $big = 999999999; // need an unlikely integer

                    echo paginate_links( array(
                        'base' => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
                        'format' => '?paged=%#%',
                        'current' => max( 1, get_query_var('paged') ),
                        'total' => $latest->max_num_pages
                    ) );
                    ?>
                </div>
            </div>
        </nav>
        <?php wp_reset_postdata();  ?>
    <?php endif;  ?>

<?php endwhile; ?>
