<?php

$recResourceTemplateIDs = [
    \TTE\OmekaMap::get('resourceTemplates', 'person')['id']
];
$itemsPerPage = isset($_GET['nr']) ? max(1, min(100, absint($_GET['nr']))) : 10;
$pageNum = empty($_GET['cp']) ? 1 : intval($_GET['cp']);


// Perform search
$baseParams = [
    'resource_template_id' => $recResourceTemplateIDs,
    'sort_by'    => 'title',
    'page'       => $pageNum,
    'per_page'   => $itemsPerPage,
];
$searchQuery = [];
if (isset($_GET['hq'])) {
    $hqRaw = $_GET['hq'];
    $hqDecoded = rawurldecode($hqRaw);
    parse_str($hqDecoded, $searchQuery);
}
$searchQuery = array_merge($searchQuery, $baseParams);
$searchResult = \TTE\OmekaEntity::search($searchQuery, false);
$omekaEntities = $searchResult['entities'];
$totalItems = $searchResult['total'];
?>


<div class="page-content">
    <div class="container-fluid">

        <input type="hidden" id="totalItems" value="<?php echo $totalItems; ?>">
        <input type="hidden" id="itemsPerPage" value="<?php echo $itemsPerPage; ?>">
        <input type="hidden" id="currentPage" value="<?php echo $pageNum; ?>">

        <?php the_content(); ?>

        <div class="facet-container line-below">
            <div class="controller-container">

                <div class="row mb-4 text-center">
                    <div class="col">
                        <a id="searchFilterBtn" class="btn btn-primary mr-4" href="#" role="button">
                            <i class="fa fa-filter" aria-hidden="true"></i>Search
                        </a>
                        <a id="searchResetBtn" class="btn btn-outline-primary" href="#" role="button">
                            <i class="fa fa-refresh" aria-hidden="true"></i>Reset
                        </a>

                    </div>
                </div>

                <div class="row text-center">
                    <div class="col">
                        <div class="form-group row" style="padding-left: 0; padding-right:0">
                            <label for="searchInput" class="col-lg-2 col-form-label" style="text-align: left;">
                                Search
                            </label>
                            <div class="col-lg-10">
                                <input type="text" class="form-control" id="searchInput">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php echo "Total items: " . $totalItems . "<br>"; ?>

        <div class="block-view mt-2">
            <?php
            tte_omeka_plugin_get_template_part('list', 'entities', [
                'entities' => tte_omeka_create_entity_tile_data($omekaEntities),
            ]);
            ?>
            <div id="pagination" class="horizontal-center"></div>
        </div>
    </div>
</div>
