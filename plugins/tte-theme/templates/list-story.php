<?php

/**
 * Template part for tiled text list.
 *
 * Arguments:
 * - texts: An array of HeuristTranslation instances.
 */
?>

<?php
$tiles = [];
if (!empty($args['classicalSourceWork'])) {
    $tiles = tte_omeka_create_story_tile_data($args['classicalSourceWork']);
}

?>

<?php if (count($tiles) > 0) : ?>
    <div class="archive-list">
        <?php foreach ($tiles as $tile) : ?>
            <div class="story-tile" style='background-color: <?php echo $tile['backgroundColor']; ?>;'>
                <div class="tile-title">
                    <?php $title_html = empty($tile['title']) ? '&nbsp;' : esc_html($tile['title']); ?>
                    <?php if (!empty($tile['url'])) : ?>
                        <a href="<?php echo esc_url($tile['url'][0]); ?>" target="_blank"><?php echo $title_html; ?></a>
                    <?php else : ?>
                        <?php echo $title_html; ?>
                    <?php endif; ?>
                </div>

                <?php if (!empty($tile['subtitle'])): ?>
                    <div class="tile-info"> <?php echo implode(" , ", $tile['subtitle']) ?></div>
                <?php endif; ?>


                <?php if (!empty($tile['author'])): ?>
                    <div class="tile-info"> <?php echo implode(" , ", $tile['author']) ?></div>
                <?php endif; ?>

            </div>
        <?php endforeach; ?>
    </div>
<?php else : ?>
    <p>No result found.</p>
<?php endif; ?>