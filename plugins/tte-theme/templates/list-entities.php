<?php
$tiles = [];
if (!empty($args['entities'])) {
    $tiles = $args['entities'];
}


$showResourceTemplateName = false;
if (!empty($args['showResourceTemplateName'])) {
    $showResourceTemplateName = $args['showResourceTemplateName'];
}

?>

<?php if (count($tiles) > 0) : ?>
    <div class="archive-list">
        <?php foreach ($tiles as $tile) : ?>
            <div class="entity-tile" style="background-color:<?php echo esc_attr($tile['backgroundColor']); ?>;">

                <div class="tile-title">
                    <a href="<?php echo esc_url($tile['url']); ?>" target="_self">
                        <?php echo empty($tile['title']) ? '&nbsp;' : esc_html($tile['title']); ?>
                    </a>
                </div>

                <?php if ($showResourceTemplateName): ?>
                    <div class="tile-info"><?php echo esc_html(tte_omeka_get_template_attr($tile['resourceTemplateID'], 'name')); ?></div>
                <?php endif; ?>

                <?php if (!empty($tile['resourceTemplateID'] ==  \TTE\OmekaMap::get('resourceTemplates', 'translation')['id'])): ?>
                    <?php if (!empty($tile['year'])): ?>
                        <div class="tile-info"><?php echo empty($tile['year']) ? '&nbsp;' : esc_html($tile['year']); ?></div>
                    <?php endif; ?>
                    <?php if (!empty($tile['language'])): ?>
                        <div class="tile-info"><?php echo empty($tile['language']) ? '&nbsp;' : esc_html($tile['language']); ?></div>
                    <?php endif; ?>
                    <?php if (!empty($tile['bookType'])): ?>
                        <div class="tile-info"><?php echo empty($tile['bookType']) ? '&nbsp;' : esc_html($tile['bookType']); ?></div>
                    <?php endif; ?>
                    <?php if (!empty($tile['translator'])): ?>
                        <div class="tile-info"><?php echo empty($tile['translator']) ? '&nbsp;' : esc_html($tile['translator']); ?></div>
                    <?php endif; ?>
                <?php endif; ?>

                <?php if (!empty($tile['resourceTemplateID'] ==  \TTE\OmekaMap::get('resourceTemplates', 'story')['id'])): ?>
                    <?php if (!empty($tile['subtitle'])): ?>
                        <div class="tile-info"> <?php echo esc_html(implode(' , ', $tile['subtitle'])); ?></div>
                    <?php endif; ?>

                    <?php if (!empty($tile['author'])): ?>
                        <div class="tile-info"> <?php echo esc_html(implode(' , ', $tile['author'])); ?></div>
                    <?php endif; ?>
                <?php endif; ?>

            </div>
        <?php endforeach; ?>
    </div>
<?php else : ?>
    <p>No result found.</p>
<?php endif; ?>
