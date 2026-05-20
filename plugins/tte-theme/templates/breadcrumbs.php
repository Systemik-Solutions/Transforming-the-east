<div class="container-fluid">
    <div class="row">
        <div class="col bg-shadow_left">
            <div class="breadcrumbs-wrap my-3">
                <?php
                tte_omeka_breadcrumb(
                    $args['isHeuristTranslationPage'] ?? null,
                    $args['heuristTranslationPageTitle'] ?? null,
                    $args['isClassicalSourceWorkPage'] ?? null,
                    $args['heuristclassicalSourceWorkTitle'] ?? null,
                    $args['isTextPage'] ?? null,
                    $args['heuristTextTitle'] ?? null,
                    $args['isPersonPage'] ?? null,
                    $args['heuristPersonTitle'] ?? null,
                    $args['isEventPage'] ?? null,
                    $args['eventTitle'] ?? null,
                    $args['isTreatisePage'] ?? null,
                    $args['treatiseTitle'] ?? null,
                    $args['treatiseTextID'] ?? null,
                    $args['isPlacePage'] ?? null,
                    $args['placeTitle'] ?? null,
                    $args['isStoryPage'] ?? null,
                    $args['storyTitle'] ?? null,
                );
                ?>
            </div>
        </div>
    </div>
</div>