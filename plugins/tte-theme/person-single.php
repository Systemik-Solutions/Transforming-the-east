<?php
$entityID = get_query_var('personID');
$entity = \TTE\OmekaEntity::load($entityID);

if (!isset($entity)) {
    global $wp_query;
    $wp_query->set_404();
    status_header(404);
    echo '<p>' . esc_html__('Person not found.', 'tte-theme') . '</p>';
    return;
}

$entity->addLinkedRecordsToProperties();
$properties = $entity->getProperties();

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
                    </ul>

                    <div class="tab-content textus-tab-content" id="textPageTabContent">

                        <!-- Details tab -->
                        <div class="tab-pane textus-tab-pane fade show active" id="details" role="tabpanel" aria-labelledby="detailsTab">
                            <?php
                            echo tte_omeka_get_table_display($properties);
                            ?>
                        </div>

                    </div>

                </div>
            </div>

<script>
    var ajaxurl = tteTheme.ajaxUrl;
    // Handle "Mark as Featured" checkbox change
    $('#markAsFeatured').on('change', function(e) {
        const $cb = $(this);
        const entityID = $cb.data('entity-id');
        const shouldAdd = $cb.is(':checked');
        const actionType = shouldAdd ? 'add' : 'remove';

        if (!entityID) {
            alert('Missing entity id.');
            $cb.prop('checked', !shouldAdd); // revert
            return;
        }
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'tte_update_featured_record',
                nonce: tteTheme.featuredNonce,
                action_type: actionType,
                entity_id: entityID
            },
            success: function(response) {},
            error: function(jqXHR) {
                try {
                    const err = JSON.parse(jqXHR.responseText);
                    alert(err.message || err.data?.message || 'Request failed.');
                } catch (e) {
                    console.error('Unexpected error', jqXHR.responseText);
                    alert('Unexpected error occurred. Please refresh the page and try again.');
                }
            }
        });
    });
</script>
