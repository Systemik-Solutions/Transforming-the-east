<?php
$textID = get_query_var('textID');
$text = \TTE\OmekaEntity::load($textID);
if ($text == null) {
    global $wp_query;
    $wp_query->set_404();
    status_header(404);
    get_template_part(404);
    exit();
}
$properties = $text->getProperties();


$translationID = null;
if (!empty($text->getProperty('field_te_dbprinttext'))) {
    $translationID = $text->getProperty('field_te_dbprinttext')[0]['value_resource_id'];
}


$collectionID = !empty($text->getProperty('field_schema_collection')) ? $text->getProperty('field_schema_collection')[0]['@value'] : null;

get_template_part('templates/breadcrumbs', '', [
    'isHeuristTranslationPage' => false,
    'heuristTranslationPageTitle' => null,
    'isClassicalSourceWorkPage' => false,
    'heuristclassicalSourceWorkTitle' => null,
    'isTextPage' => true,
    'heuristTextTitle' => $text->getTitle(),
]);

?>

<div class="page-content">
    <div class="container-fluid">

        <div class="row mb-4">
            <div class="col" style="display:flex; justify-content:end;">
                <button onclick='location.href="/translation/<?= esc_attr($translationID) ?>"'>Bibliographic Data</button>
            </div>
        </div>
        <div class="archive-list">

            <?php if (!empty($text->getProperty('field_schema_dataset'))) : ?>
                <?php foreach ($text->getProperty('field_schema_dataset') as $imageSet) : ?>

                    <?php
                    $imageSetId = $imageSet['@value'];


                    $api_key = get_option('tte_glycerine_api_key');
                    $base_url = get_option('tte_glycerine_iaw_base_url');
                    $iawClient = new TTEIAWClient($base_url, $api_key);
                    $imageSetData = $iawClient->getImageSet($collectionID, $imageSetId);

                    ?>

                    <div class="classics-tile" style="background-color:#301934;">
                        <div class="tile-title">
                            <a href="/text/<?= esc_attr($textID) ?>/treatise/<?= esc_attr($imageSetId) ?>"
                                target="_self"
                                style="font-family:inherit;">
                                <?= esc_html($imageSetData['name']) ?>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>


        </div>
    </div>
</div>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css">
<script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>

<style>
    button {
        cursor: pointer;
    }
</style>