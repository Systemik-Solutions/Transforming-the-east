<?php
function tte_blocks_render_home_block_feature()
{
    ob_start();

    $entities = \TTE\OmekaVocabulary::getAllEntities(true);

    $total = count($entities);

    // FEATURED
    $featuredEntitiesRaw = tte_omeka_get_all_featured_entities();
    $featuredEntities    = tte_omeka_create_entity_tile_data($featuredEntitiesRaw);
 
    $feat_first  = array_slice($featuredEntities, 0, 3);
    $feat_rest   = array_slice($featuredEntities, 3);
    $feat_chunks = array_chunk($feat_rest, 4);

    $featSlidesCount = 1 + count($feat_chunks);
?>
        <div id="carousel-featured" class="carousel">
            <div class="slideshow-container">

                <!-- Slide 1: info tile + up to 3 -->
                <div class="mySlidesFeatured">
                    <div class="archive-list">

                        <!-- INFO TILE -->
                        <div class="entity-tile" style="background-color:#D45135">
                            <div style="font-size:60px; display:flex; justify-content:center; padding:9% 0">
                                <?php echo (int) $total; ?>
                            </div>
                            <div style="font-size:25px; padding-top:10%;">records in collection</div>
                            <div class="d-flex">
                                <div id="arrow">&#x27F6;</div>
                            </div>
                            <div style="font-size:25px; position:relative; display:inline-block; padding-top:10px;">Featured records</div>
                        </div>

                        <?php foreach ($feat_first as $tile): ?>
                            <div class="entity-tile" <?php echo (isset($width) ? 'style="width:' . $width . '; background-color:' . $tile['backgroundColor'] . ';"' : 'style="background-color:' . $tile['backgroundColor'] . ';"'); ?>>
                                <div class="tile-title">
                                    <a href=<?php echo $tile['url'] ?> target="_self">
                                        <?php echo empty($tile['title']) ? '&nbsp;' : esc_html($tile['title']); ?>
                                    </a>
                                </div>

                                <div class="tile-info"><?php echo tte_omeka_get_template_attr($tile['resourceTemplateID'], 'name'); ?></div>

                                <?php if (!empty($tile['resourceTemplateID'] ==  \TTE\OmekaMap::get('resourceTemplates', 'translation')['id'])): ?>
                                    <?php if (!empty($tile['year'])): ?>
                                        <div class="tile-info"><?php echo empty($tile['year']) ? '&nbsp;' : esc_html($tile['year']); ?></div>
                                    <?php endif; ?>
                                    <?php if (!empty($tile['language'])): ?>
                                        <div class="tile-info"><?php echo empty($tile['language']) ? '&nbsp;' : esc_html($tile['language']); ?></div>
                                    <?php endif; ?>
                                    <?php if (!empty($tile['bookType'])): ?>
                                        <div class="tile-info"><?php echo empty($tile['bookType']) ? '&nbsp;' : esc_html($tile['bookType']); ?></div>
                                    <?php endif; ?>
                                    <?php if (!empty($tile['translator'])): ?>
                                        <div class="tile-info"><?php echo empty($tile['translator']) ? '&nbsp;' : esc_html($tile['translator']); ?></div>
                                    <?php endif; ?>
                                <?php endif; ?>

                                <?php if (!empty($tile['resourceTemplateID'] ==  \TTE\OmekaMap::get('resourceTemplates', 'story')['id'])): ?>
                                    <?php if (!empty($tile['subtitle'])): ?>
                                        <div class="tile-info"> <?php echo implode(" , ", $tile['subtitle']) ?></div>
                                    <?php endif; ?>

                                    <?php if (!empty($tile['author'])): ?>
                                        <div class="tile-info"> <?php echo implode(" , ", $tile['author']) ?></div>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>

                    </div>
                </div>

                <!-- Remaining FEATURED slides (4 per slide) -->
                <?php foreach ($feat_chunks as $chunk): ?>
                    <div class="mySlidesFeatured">
                        <?php
                        tte_omeka_plugin_get_template_part('list', 'entities', [
                            'entities'       => $chunk,
                            'showResourceTemplateName' => true,
                        ]);
                        ?>
                    </div>
                <?php endforeach; ?>

            </div>

            <!-- Arrows for FEATURED -->
            <a class="prev featureBtn" id="prevBtnFeatured">❮</a>
            <a class="next featureBtn" id="nextBtnFeatured">❯</a>

            <!-- Dots for FEATURED (same style) -->
            <div style="text-align:center; margin-top:0;">
                <?php for ($i = 1; $i <= max(1, $featSlidesCount); $i++): ?>
                    <span class="dot" onclick="currentSlideFeatured(<?php echo $i; ?>)"></span>
                <?php endfor; ?>
            </div>
        </div>

        <script>
            (function() {
                let idx = 1;
                const container = document.getElementById('carousel-featured');
                const slides = container.querySelectorAll('.mySlidesFeatured');
                const prev = document.getElementById('prevBtnFeatured');
                const next = document.getElementById('nextBtnFeatured');

                function show(n) {
                    if (n < 1) n = 1;
                    if (n > slides.length) n = slides.length;
                    idx = n;

                    slides.forEach(s => s.style.display = 'none');
                    slides[idx - 1].style.display = 'block';

                    prev.style.display = (idx === 1) ? 'none' : 'block';
                    next.style.display = (idx === slides.length) ? 'none' : 'block';

                    const dots = container.querySelectorAll('.dot');
                    dots.forEach(d => d.className = d.className.replace(' active', ''));
                    if (dots[idx - 1]) dots[idx - 1].className += ' active';
                }

                window.plusSlidesFeatured = (n) => show(idx + n);
                window.currentSlideFeatured = (n) => show(n);

                prev.addEventListener('click', () => show(idx - 1));
                next.addEventListener('click', () => show(idx + 1));

                // init
                slides.forEach(s => s.style.display = 'none');
                show(1);
            })();
        </script>

<?php
    return ob_get_clean();
}
