<?php
$entityID = get_query_var('classicsID');
$entity = \TTE\OmekaClassicalSourceWork::load($entityID);

if (!isset($entity)) {
    global $wp_query;
    $wp_query->set_404();
    status_header(404);
    echo '<p>' . esc_html__('Classical source work not found.', 'tte-theme') . '</p>';
    return;
}

$properties = $entity->getProperties();
$classicalSourceWorkSections = $entity->getClassicalSourceWorkSection();


?>
            <div class="single-header">
                <div class="container-fluid">
                    <div class="d-flex">
                        <h1 class="single-title mb-0 pr-5 mt-0"><?php echo esc_html($entity->getTitle()) ?></h2>
                        <!-- If user login with admin or curator, show the checkbox -->
                        <?php if (current_user_can('edit_posts')) : ?>
                            <div class="vertical-center">
                                <input type="checkbox"
                                    id="markAsFeatured"
                                    data-entity-id="<?php echo esc_attr($entityID); ?>"
                                    style="margin-right:5px;"
                                    <?php checked(tte_omeka_check_whether_entity_is_featured($entityID)); ?> />
                                Feature record on homepage
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="single-content pt-3">
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
                            $excludeMetaNames = ['field_schema_haspart', 'field_schema_name'];
                            echo tte_omeka_get_table_display($properties, [], $excludeMetaNames);

                            $translations = $entity->getTranslation();
                            ?>

                            <?php if (!empty($translations)) : ?>
                                <h3 class="mt-4">Translations</h3>

                                <?php
                                tte_omeka_plugin_get_template_part('list', 'entities', [
                                    'entities' => tte_omeka_create_entity_tile_data($translations),
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
