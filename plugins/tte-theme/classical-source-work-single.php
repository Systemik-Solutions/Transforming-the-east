<?php
$entityID = get_query_var('classicsID');
$entity = \TTE\OmekaClassicalSourceWork::load($entityID);

if (!isset($entity)) {
    global $wp_query;
    $wp_query->set_404();
    status_header(404);
    get_template_part(404);
    exit();
}

$properties = $entity->getProperties();
$classicalSourceWorkSections = $entity->getClassicalSourceWorkSection();


?>
<?php
get_template_part('templates/breadcrumbs', '', [
    'isHeuristTranslationPage' => false,
    'heuristTranslationPageTitle' => null,
    'isClassicalSourceWorkPage' => true,
    'heuristclassicalSourceWorkTitle' => $entity->getTitle(),
]);
?>


<div id="content" class="site-content" role="document">
    <div class="content-wrap">
        <main class="main">
            <div class="single-header">
                <div class="container-fluid">
                    <h2 class="single-title"><?php echo esc_html($entity->getTitle()) ?></h2>
                </div>
            </div>
            <div class="single-content">
                <div class="container-fluid">
                    <ul class="nav nav-tabs" id="textPageTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <a class="nav-link active" id="detailsTab" data-toggle="tab" href="#details" type="button" role="tab" aria-controls="details" aria-selected="true">Details</a>
                        </li>
                        <?php if (!empty($classicalSourceWorkSections)) : ?>
                            <li class="nav-item" role="presentation">
                                <a class="nav-link" id="sectionTab" data-toggle="tab" href="#sections" type="button" role="tab" aria-controls="sections" aria-selected="false">Sections</a>
                            </li>
                        <?php endif; ?>
                    </ul>

                    <div class="tab-content textus-tab-content" id="textPageTabContent">

                        <!-- Details tab -->
                        <div class="tab-pane textus-tab-pane fade show active" id="details" role="tabpanel" aria-labelledby="detailsTab">
                            <?php
                            $excludeMetaNames = ['field_schema_haspart' , 'field_schema_name'];
                            echo tte_omeka_get_table_display($properties, [], $excludeMetaNames);

                            $translations = $entity->getTranslation();
                            ?>

                            <?php if (!empty($translations)) : ?>
                                <h3 class="mt-4">Translations</h3>

                                <?php
                                get_template_part('templates/list', 'translation', [
                                    'translation' => tte_omeka_create_translation_tile_data($translations),
                                    'width' => '250px'
                                ]);
                                ?>

                            <?php endif; ?>

                        </div>

                        <!-- Sections Tab -->
                        <?php if (!empty($classicalSourceWorkSections)) : ?>
                            <div class="tab-pane textus-tab-pane fade" id="sections" role="tabpanel" aria-labelledby="sectionTab">
                                <?php
                                $isFrist = true;
                                foreach ($classicalSourceWorkSections as $classicalSourceWorkSection) : ?>
                                    <h3 class="<?php echo (!$isFrist) ? 'mt-5' : ''; ?>">
                                        <?php echo wp_kses($classicalSourceWorkSection->getTitle(), []) ?>
                                    </h3>
                                    <?php
                                    echo tte_omeka_get_table_display($classicalSourceWorkSection->getProperties(), [], ['field_schema_name']);
                                    $isFrist = false;
                                    ?>

                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                    </div>

                </div>
            </div>
        </main><!-- /.main -->
    </div><!-- /.content-wrap -->
</div>