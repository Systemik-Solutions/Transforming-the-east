<?php
$textID = get_query_var('textID');
$text = \TTE\OmekaEntity::load($textID);
if ($text == null) {
    global $wp_query;
    $wp_query->set_404();
    status_header(404);
    echo '<p>' . esc_html__('Text not found.', 'tte-theme') . '</p>';
    return;
}
$properties = $text->getProperties();


$translationID = null;
if (!empty($text->getProperty('field_te_dbprinttext'))) {
    $translationID = $text->getProperty('field_te_dbprinttext')[0]['value_resource_id'];
}


$collectionID = !empty($text->getProperty('field_schema_collection')) ? $text->getProperty('field_schema_collection')[0]['@value'] : null;

?>

<div class="page-content">
    <div class="container-fluid">

        <div class="row mb-4">
            <div class="col" style="display:flex; justify-content:end;">
                <button type="button" onclick="location.href='<?php echo esc_url(home_url('/translation/' . $translationID . '/')); ?>'">Bibliographic Data</button>
            </div>
        </div>
        <div class="archive-list" id="text-tiles">

            <?php if (!empty($text->getProperty('field_schema_dataset'))) : ?>
                <?php foreach ($text->getProperty('field_schema_dataset') as $imageSet) : ?>

                    <?php
                    $imageSetId = $imageSet['@value'];
                    $api_key = get_option('tte_glycerine_api_key');
                    $base_url = get_option('tte_glycerine_iaw_base_url');
                    $iawClient = new TTEIAWClient($base_url, $api_key);
                    $imageSetData = $iawClient->getImageSet($collectionID, $imageSetId);
                    ?>

                    <div class="classics-tile text-tile" style="background-color:#301934;">
                        <div class="tile-title">
                            <a href="<?php echo esc_url(home_url('/text/' . $textID . '/treatise/' . $imageSetId . '/')); ?>"
                                target="_self"
                                style="font-family:inherit;">
                                <?= esc_html($imageSetData['name']) ?>
                            </a>
                        </div>

                        <?php if (!empty($imageSetData['metadata'])): ?>
                            <?php foreach ($imageSetData['metadata'] as $metadata): ?>
                                <div class="tile-info"><?php echo esc_html($metadata['name'] . ': ' . $metadata['value']); ?></div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
