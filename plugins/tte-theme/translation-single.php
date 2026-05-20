<?php
$translationID = get_query_var('translationID');
$translation = \TTE\OmekaTranslation::load($translationID);

if (!isset($translation)) {
    global $wp_query;
    $wp_query->set_404();
    status_header(404);
    get_template_part(404);
    exit();
}

//Add linked media from related translations
// $translation->addLinkedMediaToProperties();
$properties = $translation->getProperties();


$translationTitle = $translation->getTitle();

$translationSections = $translation->getTranslationSections();
$translationCopies = $translation->getTranslationCopies();
$linkedTextID = $translation->getLinkedTexts();


if (!empty($translation->getProperty('field_schema_url')) || !empty($translation->getThumbnailImageUrl())) {
    $hasReaderTab = true;
} else {
    $hasReaderTab = false;
}



$maxLength = 70;
$titleDisplay = $translationTitle;
if (mb_strlen($translationTitle) > $maxLength) {
    $titleDisplay = mb_substr($translationTitle, 0, $maxLength) . '...';
}

get_template_part('templates/breadcrumbs', '', [
    'isHeuristTranslationPage' => true,
    'heuristTranslationPageTitle' => $titleDisplay,
    'isClassicalSourceWorkPage' => false,
    'heuristclassicalSourceWorkTitle' => null,
]);
?>

<div id="content" class="site-content" role="document">
    <div class="content-wrap">
        <main class="main">
            <div class="single-header">
                <div class="container-fluid">
                    <h2 class="single-title"><?php echo esc_html($translationTitle) ?></h2>

                    <?php
                    if (isset($linkedTextID)) {
                        echo '<div style="height: 40px; margin-bottom: 1em; text-align: right;">'
                            . '<button style="height:100%;cursor:pointer" onclick="location.href=\''
                            . get_site_url(null, 'text/' . $linkedTextID)
                            . '\'">View full text</button></div>';
                    }
                    ?>

                </div>
            </div>
            <div class="single-content">
                <div class="container-fluid">
                    <ul class="nav nav-tabs" id="textPageTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <a class="nav-link active" id="detailsTab" data-toggle="tab" href="#details" type="button" role="tab" aria-controls="details" aria-selected="true">Details</a>
                        </li>

                        <?php if ($hasReaderTab) : ?>
                            <li class="nav-item" role="presentation">
                                <a class="nav-link" id="readerTab" data-toggle="tab" href="#reader" type="button" role="tab" aria-controls="reader" aria-selected="false">Resources</a>
                            </li>
                        <?php endif; ?>

                        <?php if ($translationSections != null) : ?>
                            <li class="nav-item" role="presentation">
                                <a class="nav-link" id="sectionTab" data-toggle="tab" href="#sections" type="button" role="tab" aria-controls="sections" aria-selected="false">Sections</a>
                            </li>
                        <?php endif; ?>

                        <?php if ($translationCopies != null) : ?>
                            <li class="nav-item" role="presentation">
                                <a class="nav-link" id="copiesTab" data-toggle="tab" href="#copies" type="button" role="tab" aria-controls="copies" aria-selected="false">Copies</a>
                            </li>
                        <?php endif; ?>
                    </ul>

                    <div class="tab-content textus-tab-content" id="textPageTabContent">

                        <!-- Details tab -->
                        <div class="tab-pane textus-tab-pane fade show active" id="details" role="tabpanel" aria-labelledby="detailsTab">
                            <h3>Bibliographic Data</h3>
                            <?php
                            $excludeMetaNames = [
                                'field_te_classicalsource', // Sources
                                'field_te_intermediatesource',
                                'field_dcterms_source',

                                'field_te_section', // Sections
                                'field_te_copy',   // Copies

                                'field_te_copylocation',  // Copies

                                'field_schema_citation',  // Bibliography
                                'field_schema_catalog',  // Bibliography

                                'field_schema_url',  // External Resources
                                'field_te_transcriptionfile',

                            ];
                            echo tte_omeka_get_table_display($properties, [], $excludeMetaNames, '20%', true, false)
                            ?>


                            <?php if (
                                !empty($translation->getProperty('field_te_classicalsource')) || !empty($translation->getProperty('field_te_intermediatesource')) || !empty($translation->getProperty('field_dcterms_source'))
                            ) : ?>
                                <h3 class="mt-5">Sources</h3>
                                <?php
                                $includeMetaNames = ['field_te_classicalsource', 'field_te_intermediatesource', 'field_dcterms_source'];
                                echo tte_omeka_get_table_display($properties, $includeMetaNames)
                                ?>
                            <?php endif; ?>

                            <?php if (
                                !empty($translation->getProperty('field_te_copylocation'))
                            ) : ?>
                                <h3 class="mt-5">Copies</h3>
                                <?php
                                $includeMetaNames = ['field_te_copylocation'];
                                echo tte_omeka_get_table_display($properties, $includeMetaNames)
                                ?>
                            <?php endif; ?>

                            <?php if (
                                !empty($translation->getProperty('field_schema_citation')) || !empty($translation->getProperty('field_schema_catalog'))
                            ) : ?>
                                <h3 class="mt-5">Bibliography</h3>
                                <?php
                                $includeMetaNames = ['field_schema_citation', 'field_schema_catalog'];
                                echo tte_omeka_get_table_display($properties, $includeMetaNames)
                                ?>
                            <?php endif; ?>



                        </div>

                        <!-- Reader Tab -->
                        <?php if ($hasReaderTab) : ?>
                            <div class="tab-pane textus-tab-pane fade" id="reader" role="tabpanel" aria-labelledby="readerTab">
                               
                                <?php if (
                                    !empty($translation->getProperty('field_schema_url')) || !empty($translation->getThumbnailImageUrl())
                                ) : ?>

                                    <h3>External Resources</h3>
                                    <?php

                                    $includeMetaNames = ['field_schema_url', 'field_te_transcriptionfile'];
                                    echo tte_omeka_get_table_display($properties, $includeMetaNames, [], '20%', false, true)
                                    ?>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <!-- Sections Tab -->
                        <?php if ($translationSections != null) : ?>
                            <div class="tab-pane textus-tab-pane fade" id="sections" role="tabpanel" aria-labelledby="sectionTab">

                                <?php
                                $isFrist = true;
                                foreach ($translationSections as $translationSection) : ?>

                                    <?php
                                    $translationSectionproperties  = $translationSection->getProperties();
                                    ?>
                                    <h3 class="<?php echo (!$isFrist) ? 'mt-5' : ''; ?>">
                                        <?php echo wp_kses($translationSection->getTitle(), []) ?>
                                    </h3>
                                    <?php
                                    $includeMetaNames = ['field_schema_name', 'field_schema_pagestart', 'field_schema_pageend', 'field_schema_translationofwork'];
                                    echo tte_omeka_get_table_display($translationSectionproperties);

                                    $isFrist = false;
                                    ?>

                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <!-- Copies Tab -->
                        <?php if ($translationCopies != null) : ?>
                            <div class="tab-pane textus-tab-pane fade" id="copies" role="tabpanel" aria-labelledby="copiesTab">

                                <h4 style="font-weight: 900;"> <?php echo count($translationCopies) ?> copies</h4>
                                <?php
                                foreach ($translationCopies as $translationCopy) : ?>
                                    <?php
                                    $translationCopyproperties  = $translationCopy->getProperties();
                                    ?>
                                    <h3 class='mt-5'>
                                        <?php echo wp_kses($translationCopy->getTitle(), []) ?>
                                    </h3>
                                    <?php
                                    $includeMetaNames = [
                                        'field_te_callnumber',
                                        'field_schema_locationcreated',
                                        'field_te_copytype',
                                        'field_schema_url',
                                        'field_schema_name'
                                    ];
                                    echo tte_omeka_get_table_display($translationCopyproperties);
                                    ?>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
        </main><!-- /.main -->
    </div><!-- /.content-wrap -->
</div>