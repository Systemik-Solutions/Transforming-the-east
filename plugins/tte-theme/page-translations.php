<?php
$currentURL = $_SERVER['HTTP_HOST'];

$recResourceTemplateIDs = [
    \TTE\OmekaMap::get('resourceTemplates', 'translation')['id']
];
$itemsPerPage = isset($_GET['nr']) ? $_GET['nr'] : '40';
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


$allOmekaEntities  = \TTE\OmekaEntity::search([
    'resource_template_id' => $recResourceTemplateIDs,
    'per_page' => 10000,
], true)['entities'];


// Build sigma js data
$allSearchEntities = tte_omeka_get_all_searched_entities($_GET['hq'], [
    'resource_template_id' => $recResourceTemplateIDs,
    'per_page' => 10000,
]);
$allOtherEntities = tte_omeka_get_all_searched_entities(null, [
    'resource_template_id' => [
        \TTE\OmekaMap::get('resourceTemplates', 'classicalSourceWork')['id'],
        \TTE\OmekaMap::get('resourceTemplates', 'locationHistoryRecord')['id'],
        \TTE\OmekaMap::get('resourceTemplates', 'place')['id'],
        \TTE\OmekaMap::get('resourceTemplates', 'person')['id'],
    ],
    'per_page' => 10000,
]);
$sigmaEntities = array_merge($allSearchEntities, $allOtherEntities);
$sigmaGraph = tte_omeka_get_sigma_node_graph($sigmaEntities);
?>

<?php get_template_part('templates/page', 'header'); ?>

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

                <div class="row mb-4 text-center">
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

                <div class="row mb-4 text-center">

                    <div class="col-lg-6">
                        <div class="row" style="padding-left: 0; padding-right:0">
                            <div class="col-lg-4" style="text-align: left;">
                                Language
                            </div>
                            <div class="col-lg-8">
                                <?php
                                $languageFieldInfoID = \TTE\OmekaMap::get('propertyIDs', 'field_schema_language')['id'];
                                $languageTerms = \TTE\OmekaVocabulary::getFieldTerms('field_schema_language', $allOmekaEntities);
                                $languageQueryType = \TTE\OmekaMap::get('propertyIDs', 'field_schema_language')['queryType'];
                                ?>
                                <select class="location-select choices-select" name="types" data-field-property-id="<?php echo $languageFieldInfoID; ?>" data-field-property-type="<?php echo $languageQueryType; ?>" data-control-type="multiselect" multiple>
                                    <?php foreach ($languageTerms as $languageTerm) : ?>
                                        <option value="<?php echo $languageTerm['id']; ?>">
                                            <?php echo htmlspecialchars($languageTerm['label']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="row">
                            <div class="col-lg-4">
                                Book Type
                            </div>
                            <div class="col-lg-8">
                                <?php
                                $bookTypeFieldInfoID = \TTE\OmekaMap::get('propertyIDs', 'field_te_booktype')['id'];
                                $bookTypeTerms = \TTE\OmekaVocabulary::getFieldTerms('field_te_booktype', $allOmekaEntities);
                                $bookTypeQueryType = \TTE\OmekaMap::get('propertyIDs', 'field_te_booktype')['queryType'];
                                ?>
                                <select class="location-select choices-select" name="types" data-field-property-id="<?php echo $bookTypeFieldInfoID; ?>" data-field-property-type="<?php echo $bookTypeQueryType; ?>" data-control-type="multiselect" multiple>
                                    <?php foreach ($bookTypeTerms as $bookTypeTerm) : ?>
                                        <option value="<?php echo $bookTypeTerm['id']; ?>">
                                            <?php echo htmlspecialchars($bookTypeTerm['label']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                </div>

            </div>
        </div>

        <?php echo "Total items: " . $totalItems . "<br>"; ?>

        <div class="row mb-3 ">
            <div class="col">
                <div class="type-filter" style="float: right;font-size:23px;padding-right:15px">

                    <label class="radio">
                        <input type="radio" name="typeFilter" class="typeFilter-list">
                        <span class="label-body">List</span>
                    </label>

                    <label class="radio pl-2">
                        <input type="radio" name="typeFilter" class="typeFilter-listmap">
                        <span class="label-body">Map</span>
                    </label>
                    <label class="radio pl-2">
                        <input type="radio" name="typeFilter" class="typeFilter-timemap">
                        <span class="label-body">Time Map</span>
                    </label>

                    <label class="radio pl-2">
                        <input type="radio" name="typeFilter" class="typeFilter-network">
                        <span class="label-body">Network</span>
                    </label>

                </div>
            </div>
        </div>

        <div class="block-view" style="display: none;">
            <?php
            get_template_part('templates/list', 'translation', [
                'translation' => tte_omeka_create_translation_tile_data($omekaEntities),
            ]);
            ?>
            <div id="pagination" class="horizontal-center"></div>
        </div>

        <?php
        $apiBase = "https://$currentURL/wp-json/tte-omeka-api/v1/omeka-geojson";

        $hqPart = '';
        if (isset($_GET['hq']) && $_GET['hq'] !== '') {
            $hqValue = rawurldecode((string) $_GET['hq']);
            $hqPart = 'hq=' . rawurlencode($hqValue) . '&';
        }
        ?>

        <div class="listmap-view" style="display: none;">
            <?php $clusterLoadUrl = $apiBase . '?' . $hqPart . 'type=cluster'; ?>
            <iframe
                title="listmap"
                src="https://views.tlcmap.org/v2/cluster.html?load=<?php echo rawurlencode($clusterLoadUrl); ?>"
                width="100%"
                height="700"
                frameborder="0"
                scrolling="no">
            </iframe>
        </div>

        <div class="timemap-view" style="display: none;">
            <?php $timelineLoadUrl = $apiBase . '?' . $hqPart . 'type=timeLine'; ?>
            <iframe
                title="timemap"
                src="https://views.tlcmap.org/v2/timeline.html?load=<?php echo rawurlencode($timelineLoadUrl); ?>"
                width="100%"
                height="700"
                frameborder="0"
                scrolling="no">
            </iframe>
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
                    document.getElementsByClassName('block-view')[0].style.display = 'none';
                    document.getElementsByClassName('listmap-view')[0].style.display = 'none';
                    document.getElementsByClassName('timemap-view')[0].style.display = 'none';
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