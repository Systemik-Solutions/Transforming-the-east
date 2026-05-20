<?php
$textID = get_query_var('textID');
$treatiseID = get_query_var('treatiseID');
$text = \TTE\OmekaEntity::load($textID);
if ($text == null) {
    global $wp_query;
    $wp_query->set_404();
    status_header(404);
    get_template_part(404);
    exit();
}
$properties = $text->getProperties();


$collectionID = !empty($text->getProperty('field_schema_collection')) ? $text->getProperty('field_schema_collection')[0]['@value'] : null;
$api_key = get_option('tte_glycerine_api_key');
$base_url = get_option('tte_glycerine_iaw_base_url');
$iawClient = new TTEIAWClient($base_url, $api_key);
$imageSetData = $iawClient->getImageSet($collectionID, $treatiseID);

$treatiseName = $imageSetData['name'];



$publicationID = $imageSetData['publication'] ? $imageSetData['publication']['id'] : null;
$viewUrl = $publicationID ? 'https://iaw.ardc-hdcl-sia-iaw.cloud.edu.au/publications/image-sets/' . $publicationID : null;



$translationID = null;
if (!empty($text->getProperty('field_te_dbprinttext'))) {
    $translationID = $text->getProperty('field_te_dbprinttext')[0]['value_resource_id'];
}


get_template_part('templates/breadcrumbs', '', [
    'isHeuristTranslationPage' => false,
    'heuristTranslationPageTitle' => null,
    'isClassicalSourceWorkPage' => false,
    'heuristclassicalSourceWorkTitle' => null,
    'isTextPage' => false,
    'heuristTextTitle' => $text->getTitle(),
    'isPersonPage' => false,
    'heuristPersonName' => null,
    'isEventPage' => false,
    'heuristEventTitle' => null,
    'isTreatisePage' => true,
    'treatiseTitle' => $treatiseName,
    'treatiseTextID' => $textID
]);


?>

<div class="page-content">
    <div class="container-fluid">

        <div class="tte-toolbar" style="display: flex; justify-content: space-between; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
            <div class="tte-toolbar-left" style="display: flex; gap: 0.5rem; align-items: center;">
                <div class="tte-button-wrap" style="position: relative;">
                    <button type="button" id="tte-pages-toggle">Pages</button>
                    <div id="tte-pages-dropdown" class="tte-pages-dropdown" style="display: none;">
                        <input type="text" id="tte-pages-search" placeholder="search" autocomplete="off">
                        <ul id="tte-pages-list"></ul>
                    </div>
                </div>
                <div class="tte-button-wrap" style="position: relative;">
                    <button type="button" id="tte-toc-toggle">Table of Contents</button>
                    <div id="tte-toc-dropdown" class="tte-toc-dropdown" style="display: none;">
                        <input type="text" id="tte-toc-search" placeholder="search" autocomplete="off">
                        <ul id="tte-toc-list"></ul>
                    </div>
                </div>
                <button type="button" id="tte-search-toggle">
                    Search <span class="tte-search-arrow">&#x25BC;</span>
                </button>
                <?php if ($viewUrl) : ?>
                    <button type="button" onclick="window.open('<?php echo esc_url($viewUrl); ?>', '_blank')">Immersive View</button>
                <?php endif; ?>
            </div>
            <div class="tte-toolbar-right">
                <button type="button" onclick="location.href='<?php echo get_site_url(null, 'translation/' . $translationID); ?>'">Bibliographic Data</button>
            </div>
        </div>

        <div id="printedTextSection" class="mt-4">
            <?php
            $attributes = [
                'selectedCollectionID' => (int) $collectionID,
                'selectedImageSetID' => (int) $treatiseID,
                'selectedColumns' => ["Transcription"],
                'imageQuality' => 600,
                'collectionMap' => [],
                'viewerSettings' => ['indexbutton'],
            ];

            echo tte_annotation_render_text_annotation_block($attributes);
            ?>
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