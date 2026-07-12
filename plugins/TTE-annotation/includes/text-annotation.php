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

$all_annotations = $iawClient->searchWithCollection($collection_id, 'annotation', [], false, $block_cache_id);
// Filter annotations by image set ID
$all_annotations = array_filter($all_annotations, function ($annotation) use ($imageSet_id) {
    return isset($annotation['annotation_set']['image_set_id']) && $annotation['annotation_set']['image_set_id'] === $imageSet_id;
});

// Sort annotation by image order
usort($all_annotations, function ($a, $b) {
    $wa = isset($a['image']['weight']) ? (int) $a['image']['weight'] : PHP_INT_MAX;
    $wb = isset($b['image']['weight']) ? (int) $b['image']['weight'] : PHP_INT_MAX;
    return $wa <=> $wb;
});

$has_annotations = !empty($all_annotations);


error_log(json_encode($all_annotations));

$image_name = 0;
?>


<div class="search-dropdown" style="display: none;">

    <div class="row" <?php echo $has_annotations ? '' : ' style="display: none;"'; ?>>
        <!-- Filters Row -->
        <div id="table-filters" class="col">

            <div class="row">
                <div class="col-8">
                    <input type="text" id="annotation-search" placeholder="Search..." style="padding: 5px; flex-grow: 1; width:100%">
                </div>
                <div class="col-4">
                    <div class="vertical-center">

                        <select class="column-filter" multiple data-column="Tags" data-placeholder="Tags" style="padding: 5px;">
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

            <div class="row mt-2 text-center">
                <div class="col">
                    <a id="searchFilterBtn" class="btn btn-primary mr-4" href="#" role="button">
                        <i class="fa fa-filter" aria-hidden="true"></i>Search
                    </a>
                    <a id="searchResetBtn" class="btn btn-outline-primary" href="#" role="button">
                        <i class="fa fa-refresh" aria-hidden="true"></i>Reset
                    </a>

                </div>
            </div>
        </div>
    </div>
</div>

<div class="row pt-4 pb-4" id="search-navigation" style="display: none;<?php echo $has_annotations ? '' : ' visibility: hidden;'; ?>">

    <div class="col d-flex">
        <div style="display: flex; align-items:center">
            <p class="mr-2" style="margin: 0" id="search-navigation-label">Search result</p>
            <span class="icon icon-arrow-left-dark" id="search-navigation-prev" style="cursor: pointer;"></span>
            <span class="icon icon-arrow-right-dark ml-2" id="search-navigation-next" style="cursor: pointer;"></span>
        </div>

        <div style="margin-left: 10px;">
            <button id="annotation-show-search-result">Show all results</button>
        </div>
    </div>

</div>


<div id="annotation-search-table-container" style="display: none;">
</div>
</div>




<div style="padding: 3rem; border:2px solid black">
    <input type="hidden" id="tte_glycerine_manifest_url" value="<?php echo $imageSet_manifest_url; ?>">
    <input type="hidden" id="annotation-image-width" value="<?php echo $imageQuality; ?>">






    <div id="glycerine-toggle-controls" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
        <div id="toggle-viewer" style="display: flex; cursor: pointer; align-items: center;">
            <p class="mr-2" style="margin: 0; font-weight:bold">Hide Facsimile</p>
        </div>

        <div style="display: flex; justify-content:flex; align-items: center;">
            <div class="glycerine-viewer-prev mr-2" style="display: flex; justify-content:flex; align-items: center;cursor: pointer;">
                <span class="icon icon-arrow-left-dark"></span>
                Previous
            </div>
            <p class="mb-0 mt-0" id="page-number" style="font-weight: bold;"></p>
            <div class="glycerine-viewer-next ml-2" style="display: flex; justify-content:center; align-items: center;cursor: pointer;">
                Next
                <span class="icon icon-arrow-right-dark"></span>
            </div>
        </div>

        <div id="toggle-table" class="ml-4" style="display: flex; cursor: pointer; align-items: center;">
            <p class="ml-2" style="margin: 0; font-weight:bold"><?php echo $has_annotations ? 'Hide Transcription' : 'Show Transcription'; ?></p>
        </div>
    </div>

    <div class="row glycerine-text-annotation" style="display: flex; flex-wrap: wrap;">

        <div class="<?php echo $has_annotations ? 'col-6' : 'col-12'; ?>" id="viewer-container" style="max-height: 750px; min-height:750px">
            <div id="glycerine-text-annotation-viewer"
                data-show-full-screen-button="<?= in_array('fullscreenbutton', $viewerSettings) ? 'true' : 'false' ?>"
                data-show-index-button="<?= in_array('indexbutton', $viewerSettings) ? 'true' : 'false' ?>"
                data-show-annotation-view-button="<?= in_array('annotationviewbutton', $viewerSettings) ? 'true' : 'false' ?>"
                data-show-about-pane-button="<?= in_array('aboutpanebutton', $viewerSettings) ? 'true' : 'false' ?>"
                data-show-setting-pane-button="<?= in_array('settingpanebutton', $viewerSettings) ? 'true' : 'false' ?>"
                data-default-info-panel="<?= in_array('infopanel', $viewerSettings) ? 'true' : 'false' ?>">
            </div>
        </div>

        <div class="col-6" id="table-container" style="max-height: 750px; min-height:750px; overflow: auto;<?php echo $has_annotations ? '' : ' display: none;'; ?>">
            <div class="glycerine-text-annotation-table">

                <table id="annotation-table" class="glycerine-table">
                    <thead id="annotation-table-header">
                        <tr>
                            <?php foreach ($selected_columns as $selected_column) : ?>
                                <th style="display: flex; justify-content: end; width: 100%;">
                                    <?php if (isset($columnLabelMap[$selected_column])) : ?>
                                        <?php
                                        // echo esc_html($columnLabelMap[$selected_column]);
                                        ?>
                                    <?php else : ?>
                                        <?php
                                        // echo esc_html($selected_column);
                                        ?>
                                    <?php endif; ?>

                                    <div style="display: flex; justify-content:center; align-items: center; cursor: pointer;">
                                        <div class="cssCircle minusSign" id="annotation-table-decrease-font-size">
                                            &#8211;
                                        </div>
                                        <div class="cssCircle plusSign" id="annotation-table-increase-font-size" style="margin-left: 5px;">
                                            &#43;
                                        </div>
                                    </div>
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
                                                foreach ($annotation['fields'] as $field) {
                                                    if ($field['field']['name'] == 'Title' && isset($field['values']['en'])) {
                                                        echo '<div style="white-space: pre-wrap;">' . ($field['values']['en'][0] ?? '') . '</div>';
                                                        break;
                                                    }
                                                }
                                                break;
                                            case 'Description (EN)':
                                                foreach ($annotation['fields'] as $field) {
                                                    if ($field['field']['name'] == 'Description' && isset($field['values']['en'])) {
                                                        echo '<div style="white-space: pre-wrap;">' . ($field['values']['en'][0] ?? '') . '</div>';
                                                        break;
                                                    }
                                                }
                                                break;
                                            case 'Description (zh_CN)':
                                                foreach ($annotation['fields'] as $field) {
                                                    if ($field['field']['name'] == 'Description' && isset($field['values']['zh_CN'])) {
                                                        echo '<div style="white-space: pre-wrap;">' . ($field['values']['zh_CN'][0] ?? '') . '</div>';
                                                        break;
                                                    }
                                                }
                                                break;
                                            case 'Description (en-Latn)':
                                                foreach ($annotation['fields'] as $field) {
                                                    if ($field['field']['name'] == 'Description' && isset($field['values']['en-Latn'])) {
                                                        echo '<div style="white-space: pre-wrap;">' . ($field['values']['en-Latn'][0] ?? '') . '</div>';
                                                        break;
                                                    }
                                                }
                                                break;
                                            case 'Transcription':
                                                foreach ($annotation['fields'] as $field) {
                                                    if ($field['field']['name'] == 'Transcription' && isset($field['values']['en-Latn'])) {
                                                        echo '<div style="white-space: pre-wrap;">' . ($field['values']['en-Latn'][0] ?? '') . '</div>';
                                                        break;
                                                    } else if ($field['field']['name'] == 'Transcription' && isset($field['values']['en'])) {
                                                        echo '<div class="text-annotation-transcription"  style="white-space: pre-wrap;">' . ($field['values']['en'][0] ?? '') . '</div>';
                                                        break;
                                                    }
                                                }
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

                                <td style="display:none;"><input type="hidden" class="annotation-description" value="<?php echo $annotation['annotation_set']['description'] ?>"></td>
                                <td style="display:none;"><input type="hidden" class="canvas-id" value="<?php echo ($imageSet_manifest_url .  '/canvases/' . $annotation['image_id']); ?>"></td>
                                <td style="display:none;"><input type="hidden" class="image-name" value="<?php echo ++$image_name ?>"></td>
                                <td style="display:none;"><input type="hidden" class="annotation-id" value="<?php echo ($annotation['annotation_set']['persistent_url'] .  '/annotations/' .  $annotation['id']); ?>"></td>
                                <td style="display:none;"><input type="hidden" class="page-number" value="<?php
                                                                                                            $page_number = '';
                                                                                                            foreach ($annotation['fields'] as $field) {
                                                                                                                if (isset($field['field']['name']) && $field['field']['name'] === 'Page number') {
                                                                                                                    $page_number = $field['values']['en-Latn'][0] ?? $field['values']['en'][0] ?? '';
                                                                                                                    break;
                                                                                                                }
                                                                                                            }
                                                                                                            echo esc_attr($page_number);
                                                                                                            ?>"></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="row glycerine-annotation-description horizontal-center mt-2">

    </div>
</div>