<?php
get_template_part('templates/page', 'header');
?>


<div class="page-content">
    <div class="container-fluid">

        <p> 
            If you would like to be informed of upcoming events, please <a href="/contact/">contact us</a>.
        </p>

        <?php if (have_posts()) : ?>
            <ul class="events-grid">
                <?php while (have_posts()) : the_post(); ?>
                    <li class="event-card">
                        <h2 class="event-title">
                            <a href="<?php echo esc_url(home_url('/event/' . get_post_field('post_name', get_the_ID()) . '/')); ?>">
                                <?php the_title(); ?>
                            </a>
                        </h2>

                        <div class="event-meta">
                            <time datetime="<?php echo esc_attr(get_the_date('c')); ?>">
                                <?php echo esc_html(get_the_date('d/m/Y')); ?>
                            </time>

                            <span class="meta-sep">—</span>

                            <span class="event-cats">
                                <?php the_category(', '); ?>
                            </span>
                        </div>

                        <?php if (has_excerpt()) : ?>
                            <div class="event-excerpt">
                                <?php the_excerpt(); ?>
                            </div>
                        <?php endif; ?>
                    </li>
                <?php endwhile; ?>
            </ul>

            <div class="events-pagination">
                <?php the_posts_pagination(); ?>
            </div>

        <?php else : ?>
            <p>No events found.</p>
        <?php endif; ?>

    </div>
</div>


<script>
    document.getElementsByClassName('breadcrumbs__current')[0].innerText = 'Events';
</script>

<style>
    .events-grid {
        list-style: none;
        padding: 0;
        margin: 0;
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 24px;
    }

    .event-card {
        background: #f1f1f1;
        padding: 20px 24px;
        border-radius: 6px;
        transition: opacity .15s ease;
    }

    .event-card:hover {
        opacity: .85;
    }

    .event-title {
        margin: 0 0 10px 0;
        font-size: 18px;
        line-height: 1.3;
    }

    .event-title a {
        text-decoration: none;
    }

    .event-title a:hover {
        text-decoration: underline;
    }

    .event-meta {
        display: flex;
        gap: 10px;
        align-items: center;
        font-size: 14px;
        opacity: .8;
        margin-bottom: 12px;
    }

    .meta-sep {
        opacity: .7;
    }

    .event-excerpt {
        font-size: 14px;
        opacity: .85;
    }

    .events-pagination {
        margin-top: 28px;
    }

    /* Responsive */
    @media (max-width: 1024px) {
        .events-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 640px) {
        .events-grid {
            grid-template-columns: 1fr;
        }
    }
</style>