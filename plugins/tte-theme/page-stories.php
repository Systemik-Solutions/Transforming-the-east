<?php

$recResourceTemplateIDs = [
    \TTE\OmekaMap::get('resourceTemplates', 'story')['id']
];
$itemsPerPage = isset($_GET['nr']) ? $_GET['nr'] : '20';
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


<?php get_template_part('templates/page', 'header'); ?>

<div class="page-content">
    <div class="container-fluid">

        <input type="hidden" id="totalItems" value="<?php echo $totalItems; ?>">
        <input type="hidden" id="itemsPerPage" value="<?php echo $itemsPerPage; ?>">
        <input type="hidden" id="currentPage" value="<?php echo $pageNum; ?>">

        <?php the_content(); ?>

        <div class="block-view mt-2">
            <?php
            get_template_part('templates/list', 'story', [
                'classicalSourceWork' => $omekaEntities,
            ]);
            ?>
            <div id="pagination" class="horizontal-center"></div>
        </div>
    </div>
</div>