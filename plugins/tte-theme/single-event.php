<?php

if (have_posts()) :
    while (have_posts()) : the_post();

        $eventTitle = get_the_title();
        get_template_part('templates/breadcrumbs', '', [
            'isHeuristTranslationPage' => false,
            'heuristTranslationPageTitle' => null,
            'isClassicalSourceWorkPage' => false,
            'heuristclassicalSourceWorkTitle' => null,
            'isTextPage' => false,
            'heuristTextTitle' => null,
            'isPersonPage' => null,
            'heuristPersonTitle' => null,
            'isEventPage' => true,
            'eventTitle' =>  $eventTitle,
        ]);

?>
        <div id="content" class="site-content" role="document">
            <div class="content-wrap">
                <main class="main">
                    <div class="single-header">
                        <div class="container-fluid">
                            <h2 class="single-title"><?php echo esc_html($eventTitle) ?></h2>
                        </div>
                    </div>
                    <div class="single-content">
                        <div class="container-fluid">
                            <div class="event-meta">
                                <time datetime="<?php echo esc_attr(get_the_date('c')); ?>">
                                    <?php echo esc_html(get_the_date('d/m/Y')); ?>
                                </time>

                                <?php
                                $terms = get_the_terms(get_the_ID(), 'event-category');
                                if ($terms && !is_wp_error($terms)) :
                                ?>
                                    <span class="meta-sep">—</span>
                                    <span class="event-categories">
                                        <?php
                                        $out = [];
                                        foreach ($terms as $term) {
                                            $out[] = esc_html($term->name);
                                        }
                                        echo implode(', ', $out);
                                        ?>
                                    </span>
                                <?php endif; ?>
                            </div>


                            <div class="event-content">
                                <?php the_content(); ?>
                            </div>
                        </div>
                    </div>
                </main><!-- /.main -->
            </div><!-- /.content-wrap -->
        </div>
<?php
    endwhile;
endif;


?>
<script>
document.addEventListener('DOMContentLoaded', function () {
  const crumbs = document.querySelectorAll('div.breadcrumbs-wrap.my-3');
  if (crumbs.length > 0) {
    crumbs[0].style.display = 'none';   // hide only the first
  }
});
</script>