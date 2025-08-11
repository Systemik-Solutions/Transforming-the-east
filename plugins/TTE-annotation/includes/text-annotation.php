<?php
require_once plugin_dir_path(__FILE__) . '../TTEIAWClient.php';


// Ensure this file is accessed only from within the block render function
if (!isset($selectedCollectionID) || !isset($selectedImageSetID)) {
    return;
}
$collection_id = $selectedCollectionID;
$imageSet_id = $selectedImageSetID;

$api_key = get_option('tte_glycerine_api_key');
$base_url = get_option('tte_glycerine_iaw_base_url');
$iawClient = new TTEIAWClient($base_url, $api_key);

$imageSet = $iawClient->getImageSet($collection_id, $imageSet_id);
$imageSet_manifest_url = $imageSet['publication']['persistent_url'] . '/manifest';

$all_annotations = $iawClient->searchWithCollection($collection_id, 'annotation', [], true, $block_cache_id);

// Filter annotations by image set ID
$all_annotations = array_filter($all_annotations, function ($annotation) use ($imageSet_id) {
    return isset($annotation['annotation_set']['image_set_id']) && $annotation['annotation_set']['image_set_id'] === $imageSet_id;
});

if (empty($all_annotations)) {
    echo '<p>No annotations found for this image set.</p>';
    return;
}

?>

<input type="hidden" id="tte_glycerine_manifest_url" value="<?php echo $imageSet_manifest_url; ?>">
<input type="hidden" id="annotation-image-width" value="<?php echo $imageQuality; ?>">

<div class="row">
    <!-- Filters Row -->
    <div id="table-filters" class="row">
        <input type="text" id="annotation-search" placeholder="Search All Columns..." style="padding: 5px; flex-grow: 1;">

        <div class="annotation-table-column-filters">
            <div class="column-filter-container">
                <div style="align-self: flex-start; font-weight:600 ; font-size: 1.6rem;">
                    <?php echo "Tags"; ?>
                </div>
                <select class="column-filter" multiple data-column="Tags" style="padding: 5px;">
                    <?php
                    $unique_values = [];

                    // Collect unique values for this column
                    foreach ($all_annotations as $annotation) {

                        if (!empty($annotation['fields']['tag']['en']['values']) && is_array($annotation['fields']['tag']['en']['values'])) {
                            foreach ($annotation['fields']['tag']['en']['values'] as $tag) {
                                $value = $tag['term_label'] ?? '';
                                if (!empty($value)) {
                                    $unique_values[$value] = true;
                                }
                            }
                        }

                        if (!empty($value)) {
                            $unique_values[$value] = true;
                        }
                    }

                    // Output options
                    foreach (array_keys($unique_values) as $value) {
                        echo '<option value="' . esc_attr($value) . '">' . esc_html($value) . '</option>';
                    }
                    ?>
                </select>
            </div>
        </div>
    </div>
</div>


<div id="glycerine-toggle-controls" style="display: flex; justify-content: center; align-items: center; margin-bottom: 10px;">
    <div id="toggle-viewer" style="display: flex; cursor: pointer; align-items: center;">
        <p class="ml-2" style="margin: 0; font-weight:bold"></p>
        <span class="icon icon-arrow-left-dark"></span>
    </div>

    <div id="toggle-table" class="ml-4" style="display: flex; cursor: pointer; align-items: center;">
        <span class="icon icon-arrow-right-dark"></span>
        <p class="ml-2" style="margin: 0; font-weight:bold"></p>
    </div>
</div>

<div class="row glycerine-text-annotation" style="display: flex; flex-wrap: wrap;">

    <div class="col-6" id="viewer-container">
        <div id="glycerine-text-annotation-viewer"
            data-show-full-screen-button="<?= in_array('fullscreenbutton', $viewerSettings) ? 'true' : 'false' ?>"
            data-show-index-button="<?= in_array('indexbutton', $viewerSettings) ? 'true' : 'false' ?>"
            data-show-annotation-view-button="<?= in_array('annotationviewbutton', $viewerSettings) ? 'true' : 'false' ?>"
            data-show-about-pane-button="<?= in_array('aboutpanebutton', $viewerSettings) ? 'true' : 'false' ?>"
            data-show-setting-pane-button="<?= in_array('settingpanebutton', $viewerSettings) ? 'true' : 'false' ?>"
            data-default-info-panel="<?= in_array('infopanel', $viewerSettings) ? 'true' : 'false' ?>">
        </div>
    </div>

    <div class="col-6" id="table-container">
        <div class="glycerine-text-annotation-table">

            <table id="annotation-table" class="glycerine-table">
                <thead id="annotation-table-header">
                    <tr>
                        <?php foreach ($selected_columns as $selected_column) : ?>
                            <th>
                                <?php if (isset($columnLabelMap[$selected_column])) : ?>
                                    <?php echo esc_html($columnLabelMap[$selected_column]); ?>
                                <?php else : ?>
                                    <?php echo esc_html($selected_column); ?>
                                <?php endif; ?>
                            </th>
                        <?php endforeach; ?>
                    </tr>
                </thead>

                <tbody id="annotation-table-body">
                    <?php foreach ($all_annotations as $index => $annotation) : ?>
                        <tr style="display: none;">
                            <?php foreach ($selected_columns as $selected_column) : ?>
                                <td>
                                    <?php
                                    switch ($selected_column) {
                                        case 'Collection':
                                            echo $collection_map[$annotation['annotation_set']['image_set']['collection_id']] ?? '';
                                            break;
                                        case 'Image Set':
                                            echo $annotation['annotation_set']['image_set']['name'] ?? '';
                                            break;
                                        case 'Annotation Set':
                                            echo $annotation['annotation_set']['name'] ?? '';
                                            break;
                                        case 'Title':
                                            echo $annotation['fields']['title']['en']['values'][0] ?? '';
                                            break;
                                        case 'Description (EN)':
                                            echo '<div style="white-space: pre-wrap;">' . ($annotation['fields']['description']['en']['values'][0] ?? '') . '</div>';
                                            break;
                                        case 'Description (zh_CN)':
                                            echo '<div style="white-space: pre-wrap;">' . ($annotation['fields']['description']['zh']['values'][0] ?? '') . '</div>';
                                            break;
                                        case 'Description (en-Latn)':
                                            echo '<div style="white-space: pre-wrap;">' . ($annotation['fields']['description']['en-Latn']['values'][0] ?? '') . '</div>';
                                            break;
                                        case 'Tags':
                                            if (!empty($annotation['fields']['tag']['en']['values']) && is_array($annotation['fields']['tag']['en']['values'])) {
                                                foreach ($annotation['fields']['tag']['en']['values'] as $tag) {
                                                    $tag_label = $tag['term_label'] ?? '';
                                                    if (!empty($tag_label)) {
                                                        echo '<div class="annotation-tag">' . $tag_label . '</div>';
                                                    }
                                                }
                                            }
                                            break;
                                    }
                                    ?>
                                </td>
                            <?php endforeach; ?>


                            <!-- annotation data-->
                            <td style="display:none;"><input type="hidden" class="canvas-id" value="<?php echo ($imageSet_manifest_url .  '/canvases/' . $annotation['image_id']); ?>"></td>
                            <td style="display:none;"><input type="hidden" class="annotation-id" value="<?php echo ($annotation['annotation_set']['persistent_url'] .  '/annotations/' .  $annotation['id']); ?>"></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>