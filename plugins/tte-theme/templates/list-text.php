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
if (!empty($args['text'])) {
    $tiles = textus_create_text_tile_data($args['text']);
}

?>


<?php if (count($tiles) > 0) : ?>
    <div class="archive-list">
        <?php foreach ($tiles as $tile) : ?>
            <div class="classics-tile" style='background-color: <?php echo $tile['backgroundColor']; ?>;'>
                <div class="tile-title">
                    <a href=<?php echo $tile['url'] ?> target="_self">
                        <?php echo empty($tile['title']) ? '&nbsp;' : esc_html($tile['title']); ?>
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php else : ?>
    <p>No result found.</p>
<?php endif; ?>