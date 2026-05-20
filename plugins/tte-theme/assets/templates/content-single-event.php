<?php while (have_posts()) : the_post(); ?>
<article <?php post_class('single'); ?>>
    <header class="single-header">
        <div class="container-fluid">
            <h1 class="single-title"><?php the_title(); ?></h1>
        </div>
    </header>

    <div class="single-content">
        <div class="container-fluid">
            <?php the_content(); ?>
        </div>
    </div>

    <?php if($event_details = get_field('event_details')): ?>
    <div class="container-fluid my-5">
        <div class="event-details bg-gray p-4">
            <h2 class="text-primary h4">Event Details</h2>
            <div class="row">
                <div class="col-lg-6">
                    <?php if($event_details['event_date'] || $event_details['event_time']): ?>
                    <div class="mb-4">
                        <h2 class="text-primary h5 mb-2">When: </h2>
                        <p><?php echo $event_details['event_date']; ?><br><?php echo $event_details['event_time']; ?></p>
                    </div>
                    <?php endif; ?>

                    <?php if($event_details['event_cost']): ?>
                    <div class="mb-4">
                        <h2 class="text-primary h5 mb-2">Cost: </h2>
                        <p><?php echo $event_details['event_cost']; ?></p>
                    </div>
                    <?php endif; ?>

                    <?php if($event_details['event_rsvp']): ?>
                    <div class="mb-4">
                        <h2 class="text-primary h5 mb-2">RSVP: </h2>
                        <p><?php echo $event_details['event_rsvp']; ?></p>
                    </div>
                    <?php endif; ?>

                    <?php if($event_details['event_contact_email']): ?>
                    <div class="mb-4">
                        <h2 class="text-primary h5 mb-2">Contact: </h2>
                        <p><?php echo $event_details['event_contact_email']; ?></p>
                    </div>
                    <?php endif; ?>

                    <?php if($event_details['event_registration_cta']): ?>
                        <a href="<?php echo $event_details['event_registration_cta']['url']; ?>" class="btn btn-lg btn-primary"><?php echo $event_details['event_registration_cta']['title']; ?></a>
                    <?php endif; ?>
                </div>
                <div class="col-lg-6">
                    <?php if($event_details['event_location_address']): ?>
                    <div class="mb-4">
                        <h2 class="text-primary h5 mb-2">Where: </h2>
                        <p><?php echo $event_details['event_location_address']; ?></p>
                    </div>
                    <?php endif; ?>

                    <?php if($event_details['event_location_map_iframe']): ?>
                    <div class="mb-4">
                        <div class="embed-responsive embed-responsive-16by9">
                            <?php echo $event_details['event_location_map_iframe']; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</article>
<?php endwhile; ?>
