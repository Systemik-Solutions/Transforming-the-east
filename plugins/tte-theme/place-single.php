<?php
$entityID = get_query_var('placeID');
$entity = \TTE\OmekaEntity::load($entityID);

if (!isset($entity)) {
    global $wp_query;
    $wp_query->set_404();
    status_header(404);
    get_template_part(404);
    exit();
}

$properties = $entity->getProperties();

$excludeMetaNames = ['field_o_module_mapping_feature'];

$relatedEntities = $entity->getRelatedEntities();

$coordinates = null;


if (!empty($entity->getProperty('field_o_module_mapping_feature'))) {
    $mappingFeature = $entity->getProperty('field_o_module_mapping_feature');
    foreach ($mappingFeature as $feature) {
        if ($feature['o-module-mapping:geography-type'] === 'Point') {
            $coordinates = $feature['o-module-mapping:geography-coordinates'];
            break;
        }
    }
}


$sigmaEntities = array_merge([$entity], $relatedEntities);
$sigmaGraph = tte_omeka_get_sigma_node_graph($sigmaEntities);

?>
<?php
get_template_part('templates/breadcrumbs', '', [
    'isPlacePage' => true,
    'placeTitle' => $entity->getTitle(),
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

                        <?php if (isset($coordinates)) : ?>
                            <li class="nav-item" role="presentation">
                                <a class="nav-link" id="mapTab" data-toggle="tab" href="#map" type="button" role="tab" aria-controls="map" aria-selected="false">Map</a>
                            </li>
                        <?php endif; ?>

                    </ul>

                    <div class="tab-content textus-tab-content" id="textPageTabContent">

                        <!-- Details tab -->
                        <div class="tab-pane textus-tab-pane fade show active" id="details" role="tabpanel" aria-labelledby="detailsTab">
                            <?php
                            echo tte_omeka_get_table_display($properties, [], $excludeMetaNames);
                            ?>

                            <h3 class="mt-6 mb-0">Linked resources</h3>
                            <div class="row mb-3 ">
                                <div class="col">
                                    <div class="type-filter" style="float: right;font-size:23px;padding-right:15px">

                                        <label class="radio">
                                            <input type="radio" name="typeFilter" class="typeFilter-list" checked>
                                            <span class="label-body">List</span>
                                        </label>

                                        <label class="radio pl-2">
                                            <input type="radio" name="typeFilter" class="typeFilter-network">
                                            <span class="label-body">Network</span>
                                        </label>

                                    </div>
                                </div>
                            </div>

                            <div class="block-view">
                                <?php
                                get_template_part('templates/list', 'translation', [
                                    'translation' => tte_omeka_create_translation_tile_data($relatedEntities),
                                ]);
                                ?>
                            </div>

                            <div class="network-view" style="display: none;">
                                <div id="network-container">
                                    <div id="network-controls">
                                        <button id="network-fullscreen" class="network-button" style=" background-image: url('<?php echo get_stylesheet_directory_uri(); ?>/image/sigma.js/full-screen.svg');"></button>
                                        <button id="network-zoomIn" class="network-button" style=" background-image: url('<?php echo get_stylesheet_directory_uri(); ?>/image/sigma.js/zoom-in.svg');"></button>
                                        <button id="network-zoomOut" class="network-button" style=" background-image: url('<?php echo get_stylesheet_directory_uri(); ?>/image/sigma.js/zoom-out.svg');"></button>
                                        <button id="network-fitScreen" class="network-button" style=" background-image: url('<?php echo get_stylesheet_directory_uri(); ?>/image/sigma.js/show-graph.svg'); background-size: 40% 40%; background-position: center center; background-repeat: no-repeat;"></button>
                                    </div>
                                    <div id="legendDiv"></div>
                                    <div id="network-tooltip"></div>
                                </div>
                            </div>

                        </div>

                        <?php if (isset($coordinates)) : ?>
                            <!-- Map tab -->
                            <div class="tab-pane textus-tab-pane fade" id="map" role="tabpanel" aria-labelledby="mapTab">
                                <div id="placeMap" style="height: 500px;"></div>
                                <script>
                                    jQuery('#mapTab').on('shown.bs.tab', function() {
                                        if (document.getElementById('placeMap').dataset.initialized) return;
                                        document.getElementById('placeMap').dataset.initialized = '1';

                                        var map = L.map('placeMap').setView([<?php echo implode(',', array_reverse($coordinates)); ?>], 10);

                                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                                            attribution: '&copy; OpenStreetMap contributors'
                                        }).addTo(map);

                                        var marker = L.marker([<?php echo implode(',', array_reverse($coordinates)); ?>]).addTo(map);
                                        marker.bindPopup("<?php echo esc_js($entity->getTitle()); ?>").openPopup();
                                    });
                                </script>
                            </div>
                        <?php endif; ?>


                    </div>
                </div>
            </div>
        </main><!-- /.main -->
    </div><!-- /.content-wrap -->
</div>


<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/sigma.js/1.2.1/sigma.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/sigma.js/1.2.1/plugins/sigma.layout.forceAtlas2.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/sigma.js/1.2.1/plugins/sigma.plugins.dragNodes.min.js"></script>
<script>
    /**
     *
     * IMPORTANT: This node renderer just works with the canvas renderer.
     */
    sigma.utils.pkg("sigma.canvas.nodes");
    sigma.canvas.nodes.image = (function() {
        var _cache = {},
            _loading = {},
            _callbacks = {};

        // Return the renderer itself:
        var renderer = function(node, context, settings) {
            var args = arguments,
                prefix = settings("prefix") || "",
                size = node[prefix + "size"],
                color = node.color || settings("defaultNodeColor"),
                url = node.url;

            // Draw the colored circle
            context.fillStyle = color;
            context.beginPath();
            context.arc(
                node[prefix + "x"],
                node[prefix + "y"],
                size,
                0,
                Math.PI * 2,
                true
            );
            context.closePath();
            context.fill();

            if (_cache[url]) {
                // Draw the image
                context.drawImage(
                    _cache[url],
                    node[prefix + "x"] - size,
                    node[prefix + "y"] - size,
                    2 * size,
                    2 * size
                );
            } else {
                sigma.canvas.nodes.image.cache(url);
                sigma.canvas.nodes.def.apply(sigma.canvas.nodes, args);
            }
        };

        // Add public method to cache images
        renderer.cache = function(url, callback) {
            if (callback) _callbacks[url] = callback;

            if (_loading[url]) return;

            var img = new Image();

            img.onload = function() {
                _loading[url] = false;
                _cache[url] = img;

                if (_callbacks[url]) {
                    _callbacks[url].call(this, img);
                    delete _callbacks[url];
                }
            };

            _loading[url] = true;
            img.src = url;
        };

        return renderer;
    })();

    urls = ['<?php echo get_stylesheet_directory_uri(); ?>/image/sigma.js/person.svg', '<?php echo get_stylesheet_directory_uri(); ?>/image/sigma.js/place.svg', '<?php echo get_stylesheet_directory_uri(); ?>/image/sigma.js/book.svg', '<?php echo get_stylesheet_directory_uri(); ?>/image/sigma.js/newspaper.svg', '<?php echo get_stylesheet_directory_uri(); ?>/image/sigma.js/translation.svg'];
    loaded = 0;

    // Convert PHP array to JavaScript
    var nodes = <?php echo json_encode($sigmaGraph['nodes']); ?>;
    var edges = <?php echo json_encode($sigmaGraph['edges']); ?>;
    var legendMap = <?php echo json_encode($sigmaGraph['legendMap']); ?>;

    // Wait for all images to be loaded before instanciating sigma:
    urls.forEach(function(url) {
        sigma.canvas.nodes.image.cache(url, function() {
            if (++loaded === urls.length) {

                var s = new sigma({
                    graph: {
                        nodes: nodes,
                        edges: edges
                    },
                    renderer: {
                        container: 'network-container',
                        type: "canvas",
                    },
                    settings: {
                        defaultNodeType: 'custom',
                        minNodeSize: 3,
                        maxNodeSize: 20
                    }
                });

                // Initialize ForceAtlas2 layout
                var fa2 = s.configForceAtlas2({
                    worker: true,
                    edgeWeightInfluence: 1,
                    autoStop: true,
                    scalingRatio: 10,
                    strongGravityMode: true,
                    gravity: 1
                });
                s.startForceAtlas2();
                setTimeout(function() {
                    s.stopForceAtlas2();
                }, 500);

                function node_click_function(e) {
                    var node = e.data.node;
                    if (node.link) {
                        window.open(node.link, '_self');
                    }
                }

                //Drag node event, avoid click event
                var dragListener = sigma.plugins.dragNodes(s, s.renderers[0]);
                dragListener.bind('drag', function() {
                    setTimeout(function() {
                        s.unbind('clickNode');
                    });
                });
                dragListener.bind('dragend', function() {
                    setTimeout(function() {
                        s.bind('clickNode', node_click_function);
                    }, 250)
                });

                //Click node event
                s.bind('clickNode', function(e) {
                    node_click_function(e);
                });

                //Hover on node event
                s.bind('overNode', function(e) {
                    // Get the hovered node's ID
                    var nodeId = e.data.node.id;

                    if (e.data.node.link) {
                        document.getElementById('network-container').style.cursor = 'pointer';
                    }

                    e.data.node.title = '';

                    // First change all nodes to grey
                    s.graph.nodes().forEach(function(node) {
                        if (node.id != nodeId) {
                            node.color = '#ccc';
                        }
                        node.label = ''; // Hide label
                    });

                    // Then restore the original color for hovered node and its neighbors
                    s.graph.edges().forEach(function(edge) {
                        if (edge.source === nodeId || edge.target === nodeId) {
                            s.graph.nodes(edge.source).color = s.graph.nodes(edge.source).keyperiodColor;
                            s.graph.nodes(edge.source).label = s.graph.nodes(edge.source).title;
                            s.graph.nodes(edge.target).color = s.graph.nodes(edge.target).keyperiodColor;
                            s.graph.nodes(edge.target).label = s.graph.nodes(edge.target).title;
                            edge.color = e.data.node.keyperiodColor;
                        } else {
                            edge.hidden = true;
                        }
                    });

                    s.refresh();
                    showTooltip(e.data.node);
                });

                //Hover out node event
                s.bind('outNode', function(e) {

                    //Restore label
                    e.data.node.title = e.data.node.name;

                    document.getElementById('network-container').style.cursor = 'default';

                    // Restore original node colors and labels
                    s.graph.nodes().forEach(function(node) {
                        node.color = node.keyperiodColor;
                        node.label = node.title;
                    });

                    // Restore original edge colors
                    s.graph.edges().forEach(function(edge) {
                        edge.color = "#ccc";
                        edge.hidden = false;
                    });

                    s.refresh();
                    hideTooltip();
                });

                // Fullscreen toggle
                document.getElementById('network-fullscreen').addEventListener('click', function() {
                    var elem = document.getElementById('network-container');
                    if (!document.fullscreenElement) {
                        elem.requestFullscreen();
                        document.getElementById('network-fullscreen').style.backgroundImage = "url('<?php echo get_stylesheet_directory_uri(); ?>/image/sigma.js/exit-full-screen.svg')";
                    } else {
                        document.exitFullscreen();
                        document.getElementById('network-fullscreen').style.backgroundImage = "url('<?php echo get_stylesheet_directory_uri(); ?>/image/sigma.js/full-screen.svg')";
                    }
                });

                // Zoom in toggle
                document.getElementById('network-zoomIn').addEventListener('click', function() {
                    sigma.misc.animation.camera(
                        s.camera, {
                            ratio: s.camera.ratio / 1.5
                        }, {
                            duration: 300
                        }
                    );
                });

                // Zoom out toggle
                document.getElementById('network-zoomOut').addEventListener('click', function() {
                    sigma.misc.animation.camera(
                        s.camera, {
                            ratio: s.camera.ratio * 1.5
                        }, {
                            duration: 300
                        }
                    );
                });

                //Fit screen toggle
                document.getElementById('network-fitScreen').addEventListener('click', function() {
                    s.camera.goTo({
                        x: 0,
                        y: 0,
                        angle: 0,
                        ratio: 1
                    });
                });

                //Lengend Map
                buildLegend(legendMap);

                let isFirstClick = true;
                document.getElementsByClassName('typeFilter-network')[0].addEventListener('click', function() {
                    document.getElementsByClassName('network-view')[0].style.display = 'block';
                    if (isFirstClick) {
                        s.startForceAtlas2();
                        setTimeout(function() {
                            s.stopForceAtlas2();
                        }, 100);
                        isFirstClick = false;
                    }
                });
            }
        });
    });

    // Function to build lengend div
    function buildLegend(legendData) {
        var legendHTML = '';

        for (var key in legendData) {
            if (legendData.hasOwnProperty(key)) {
                var color = legendData[key].color;
                var url = legendData[key].url;

                legendHTML += `
                <div style="display: flex; align-items: center; margin-bottom: 5px;">
                    <div style="width: 30px; height: 30px; border-radius: 50%; background-color: ${color}; background-image: url('${url}'); background-size: cover; background-repeat: no-repeat; background-position: center;"></div>
                    <span style="margin-left: 10px;">${key}</span>
                </div>
            `;
            }
        }

        document.getElementById('legendDiv').innerHTML = legendHTML;
    }

    // Function to show tooltip
    function showTooltip(node) {
        var tooltip = document.getElementById('network-tooltip');
        tooltip.innerHTML = ''
        if (node.name != null) {
            tooltip.innerHTML += '<p>' + node.fullName + '</p>'; // Update tooltip content
        }
        if (node.year != null) {
            tooltip.innerHTML += '<p>' + node.year + '</p>'; // Update tooltip content
        }
        if (node.language != null) {
            tooltip.innerHTML += '<p>' + node.language + '</p>'; // Update tooltip content
        }
        if (node.bookType != null) {
            tooltip.innerHTML += '<p>' + node.bookType + '</p>'; // Update tooltip content
        }
        if (node.translator != null) {
            tooltip.innerHTML += '<p>' + node.translator + '</p>'; // Update tooltip content
        }

        tooltip.style.display = 'block'; // Show tooltip
    }

    // Function to hide tooltip
    function hideTooltip() {
        var tooltip = document.getElementById('network-tooltip');
        tooltip.style.display = 'none'; // Hide tooltip
    }

    // Function to update tooltip position
    function updateTooltipPosition(event) {
        var tooltip = document.getElementById('network-tooltip');
        var container = document.getElementById('network-container');
        var x = event.clientX;
        var y = event.clientY;

        if (document.fullscreenElement) {
            tooltip.style.left = (x + 10) + 'px';
            tooltip.style.top = (y - 10) + 'px';
        } else {
            var containerRect = container.getBoundingClientRect();
            tooltip.style.left = (x - containerRect.left + 10) + 'px';
            tooltip.style.top = (y - containerRect.top - 10) + 'px';
        }
    }

    // Update tooltip position when mouse moves
    document.addEventListener('mousemove', updateTooltipPosition);
</script>

<script>
    function bindViewClickEvent() {
        document.querySelectorAll('input[name="typeFilter"]').forEach(function(input) {
            input.addEventListener('change', function() {
                if (document.querySelector('.typeFilter-list').checked) {
                    document.querySelector('.network-view').style.display = 'none';
                    document.querySelector('.block-view').style.display = '';
                } else if (document.querySelector('.typeFilter-network').checked) {
                    document.querySelector('.block-view').style.display = 'none';
                    document.querySelector('.network-view').style.display = 'block';
                }
            });
        });
    }

    bindViewClickEvent();
</script>