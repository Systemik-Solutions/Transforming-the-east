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
if (!empty($args['translation'])) {
    $tiles = $args['translation'];
}

if (!empty($args['width'])) {
    $width = $args['width'];
}
?>

<?php if (count($tiles) > 0) : ?>
    <div class="archive-list">
        <?php foreach ($tiles as $tile) : ?>
            <div class="translation-tile" <?php echo (isset($width) ? 'style="width:' . $width . '; background-color:' . $tile['backgroundColor'] . ';"' : 'style="background-color:' . $tile['backgroundColor'] . ';"'); ?>>
                <div class="tile-title">
                    <a href=<?php echo $tile['url'] ?> target="_self">
                        <?php echo empty($tile['title']) ? '&nbsp;' : esc_html($tile['title']); ?>
                    </a>
                </div>
                <div class="tile-info" style="padding-top: 1rem!important;"><?php echo empty($tile['year']) ? '&nbsp;' : esc_html($tile['year']); ?></div>
                <div class="tile-info"><?php echo empty($tile['language']) ? '&nbsp;' : esc_html($tile['language']); ?></div>
                <div class="tile-info"><?php echo empty($tile['bookType']) ? '&nbsp;' : esc_html($tile['bookType']); ?></div>
                <div class="tile-info"><?php echo empty($tile['translator']) ? '&nbsp;' : esc_html($tile['translator']); ?></div>
            </div>

        <?php endforeach; ?>
    </div>
<?php else : ?>
    <p>No result found.</p>
<?php endif; ?>