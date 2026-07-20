<?php
$textEntities = tte_omeka_get_all_searched_entities(null, [
    'resource_template_id' => [\TTE\OmekaMap::get('resourceTemplates', 'text')['id']],
    'property' => [
        [
            'joiner' => 'and',
            'property' => \TTE\OmekaMap::get('propertyIDs', 'field_schema_collection')['id'],
            'type' => 'ex',
        ]
    ],
    'per_page' => 10000,
    'is_public' => 1,

]);

$texts = tte_omeka_create_entity_tile_data($textEntities);


$collectionIDs = [];
$collectionMap = [];
$clickUrlMap = [];
foreach ($texts as $text) {
    $collectionID = absint($text['collectionID'] ?? 0);
    if (!$collectionID) {
        continue;
    }

    $collectionIDs[$collectionID] = $collectionID;
    $collectionMap[$collectionID] = $text['title'] ?? sprintf(__('Collection %d', 'tte-theme'), $collectionID);
    $clickUrlMap[$collectionID] = $text['url'];
}
$collectionIDs = array_values($collectionIDs);
?>

<div class="page-content">
    <div class="container-fluid">
        <?php the_content(); ?>

        <div class="glycerine-table-section mt-4">
            <div style="display:flex; justify-content:end; margin-bottom:20px;">
                <a class="openGlycerineTable btn btn-outline-primary" href="#" role="button" style="background-color:white; border-color:black;color: black;">
                    Search corpus
                </a>
            </div>
            <?php if (!empty($collectionIDs)) : ?>
                <?php
                echo glycerine_render_table_block([
                    'selectedCollections'   => [],
                    'selectedColumns'       => ['Image Set', 'Image', 'Page number', 'Transcription', 'Tags', 'Title'],
                    'columnLabelMap'        => ['Image Set' => 'Division', 'Page number' => 'Page'],
                    'paging'                => 25,
                    'imageQuality'          => 600,
                    'filterColumns'         => ['Collection', 'Image Set', 'Tags'],
                    'filterColumnLabelMap'  => ['Collection' => 'Book Title', 'Image Set' => 'Book Division'],
                    'collectionMap'         => $collectionMap,
                    'clickUrlMap'           => $clickUrlMap,
                    'lazyLoad'              => true,
                    'lazyLoadNonce'         => wp_create_nonce('tte_load_annotation_table'),
                ]);
                ?>
            <?php endif; ?>
        </div>


        <div class="block-view pt-2">
            <?php if (!empty($texts)) : ?>

                <div class="archive-list" id="text-tiles">
                    <?php foreach ($texts as $text) :
                        $title   = empty($text['title']) ? '&nbsp;' : esc_html($text['title']);
                        $bgColor = esc_attr($text['backgroundColor'] ?? '#301934');
                    ?>
                        <div
                            class="classics-tile text-tile"
                            style="cursor:pointer; background-color:<?php echo $bgColor; ?>"
                            data-panel="#texts-dropdown-<?php echo $text['id']; ?>">
                            <div class="tile-title">
                                <a href="<?php echo esc_url($text['url']); ?>" target="_self">
                                    <?php echo empty($text['title']) ? '&nbsp;' : esc_html($text['title']); ?>
                                </a>
                            </div>

                            <?php if (!empty($text['author'])): ?>
                                <div class="tile-info"> <?php echo esc_html(implode(' , ', $text['author'])); ?></div>
                            <?php endif; ?>


                            <?php if (!empty($text['date'])): ?>
                                <div class="tile-info"> <?php echo esc_html(implode(' , ', $text['date'])); ?></div>
                            <?php endif; ?>

                        </div>
                    <?php endforeach; ?>
                </div>

            <?php endif; ?>
        </div>

    </div>
</div>


<script>
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('.openGlycerineTable').forEach((button) => {
            const section = button.closest('.glycerine-table-section');
            const initialTable = section?.querySelector('.glycerine-table');
            if (!initialTable) return;

            initialTable.style.display = 'none';
            button.addEventListener('click', (event) => {
                event.preventDefault();
                const table = section.querySelector('.glycerine-table');
                if (!table) return;
                const isHidden = table.style.display === 'none';
                table.style.display = isHidden ? 'block' : 'none';
                button.textContent = isHidden ? 'Hide search' : 'Search corpus';
            });
        });
    });
</script>
