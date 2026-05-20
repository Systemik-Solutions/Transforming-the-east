<?php
$entityID = get_query_var('storyID');
$entity = \TTE\OmekaEntity::load($entityID);

if (!isset($entity)) {
    global $wp_query;
    $wp_query->set_404();
    status_header(404);
    get_template_part(404);
    exit();
}

$properties = $entity->getProperties();


?>
<?php
get_template_part('templates/breadcrumbs', '', [
    'isHeuristTranslationPage' => false,
    'heuristTranslationPageTitle' => null,
    'isStoryPage' => true,
    'storyTitle' => $entity->getTitle(),
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
                    </ul>

                    <div class="tab-content textus-tab-content" id="textPageTabContent">

                        <!-- Details tab -->
                        <div class="tab-pane textus-tab-pane fade show active" id="details" role="tabpanel" aria-labelledby="detailsTab">
                            <?php
                            $excludeMetaNames = ['field_schema_haspart' , 'field_schema_name'];
                            echo tte_omeka_get_table_display($properties, [], $excludeMetaNames);
                            ?>
                        </div>
                    </div>

                </div>
            </div>
        </main><!-- /.main -->
    </div><!-- /.content-wrap -->
</div>