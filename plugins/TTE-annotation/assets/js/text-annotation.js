jQuery(document).ready(function ($) {
    const allRows = Array.from(
        document.querySelectorAll("#annotation-table-body tr"),
    );

    const table = document.getElementById("annotation-table");
    const searchInput = document.getElementById("annotation-search");

    const columnHeaders = Array.from(
        table.querySelector("thead").rows[0].cells,
    ).map((cell) => cell.textContent.trim());

    const filterDropdowns = document.querySelectorAll(".column-filter");
    const choicesInstances = [];
    filterDropdowns.forEach((select) => {
        const placeholder = select.dataset.placeholder || "";
        choicesInstances.push(
            new Choices(select, {
                removeItemButton: true,
                allowHTML: false,
                itemSelectText: "",
                placeholder: !!placeholder,
                placeholderValue: placeholder,
                searchPlaceholderValue: placeholder,
            })
        );
    });

    let isSearchOn = false;
    let filteredCanvasIDs = [];
    const filteredAnnotationIDs = new Set();
    let currentSearchTableResult = [];
    let currentActiveCanvasID = null;

    // Function to get the specific parameter from the URL
    function getQueryParam(param) {
        const queryString = window.location.search;
        const urlParams = new URLSearchParams(queryString);

        // First try to get the parameter as an array
        const values = urlParams.getAll(param + "[]");

        // If no values were found, try as a non-array parameter
        if (values.length === 0) {
            const singleValue = urlParams.get(param);
            return singleValue ? singleValue : null;
        }

        // If only one value is found in the array, return just that one unless it's actually an array param
        return values.length === 1 ? values[0] : values;
    }


    const startCanvasID = getQueryParam("startCanvasID");
    const startAnnotationID = getQueryParam("startAnnotationID");

    let isFirstLoad = true;

    // Initialize glycerine viewer
    const ele = document.getElementById("glycerine-text-annotation-viewer");
    const viewer = new GlycerineViewer(ele, {
        width: "100%",
        height: "700px",
        manifest: document.getElementById("tte_glycerine_manifest_url").value,
        showFullScreenButton: ele.dataset.showFullScreenButton === "true",
        showIndexButton: false,
        showPlayControls: false,
        showAnnotationViewButton:
            ele.dataset.showAnnotationViewButton === "true",
        showAboutPaneButton: ele.dataset.showAboutPaneButton === "true",
        showSettingPaneButton: ele.dataset.showSettingPaneButton === "true",
        defaultInfoPanel: ele.dataset.defaultInfoPanel === "true",
        displayAnnotations: "false",
        onMouseEnterAnnotation: (data) => {
            allRows.forEach((row) => {
                const input = row.querySelector("input.annotation-id");
                if (input && input.value === data) {
                    row.classList.add("highlighted-annotation-row");
                    return;
                }
            });
        },
        onMouseLeaveAnnotation: () => {
            allRows.forEach((row) => {
                row.classList.remove("highlighted-annotation-row");
            });
        },
        onOsdInitialized: () => {
            // Hide all rows first
            allRows.forEach((row) => {
                row.style.display = "none";
            });

            $(".anno-gallery-nav").each(function () {
                this.style.setProperty("display", "none", "important");
            });
        },
        onCanvasAnnotationsLoaded: (data) => {
            clearHighlightedAnnotations();
            let pageNumberText = "";
            data.forEach((annotation) => {
                if (Array.isArray(annotation.body)) {
                    annotation.body.forEach((body) => {
                        if (
                            body.type === "TextualBody" &&
                            typeof body.value === "string" &&
                            body.value.includes("Page number")
                        ) {
                            const parser = new DOMParser();
                            const doc = parser.parseFromString(
                                body.value,
                                "text/html",
                            );

                            const valueEl = doc.querySelector(".field-value");
                            if (valueEl) {
                                pageNumberText = valueEl.textContent.trim();
                            }
                        }
                    });
                }

                const row = document
                    .querySelector(
                        `#annotation-table-body tr input.annotation-id[value="${annotation.id}"]`,
                    )
                    ?.closest("tr");

                if (row) {
                    row.style.display = "";

                    const desc = row.querySelector("input.annotation-description");
                    if (desc && desc.value) {
                        document.querySelector(".glycerine-annotation-description").textContent = desc.value;
                    }
                }
            });
            if (pageNumberText) {
                document.getElementById("page-number").textContent =
                    pageNumberText;
            } else {
                document.getElementById("page-number").textContent = "";
            }

            setTimeout(() => {
                highLightMatchedAnnotation(data.map((anno) => anno.id));
            }, 750);
        },
        onCanvasLoaded: (data) => {
            currentActiveCanvasID = data;
            updateSearchNavigations();


            if(startAnnotationID && startCanvasID && startAnnotationID != '' && startCanvasID != '' && isFirstLoad){
                viewer.activateCanvas(startCanvasID);
                tableGoToRow(startAnnotationID);
                currentActiveCanvasID = startCanvasID;
            }

            isFirstLoad = false;
        },
    });

    // Initialize the viewer.
    viewer.init();

    // Hover rows to trigger annotation highlight in viewer
    allRows.forEach((row) => {
        const annotationId = row.querySelector("input.annotation-id")?.value;
        row.addEventListener("mouseenter", () => {
            if (annotationId) {
                viewer.highlightAnnotation(annotationId);
            }
        });
        row.addEventListener("mouseleave", () => {
            viewer.clearHighlight();
        });
    });

    function updateSearchNavigations() {
        const navContainer = document.getElementById("search-navigation");
        const navText = document.getElementById("search-navigation-label");
        const prevBtn = document.getElementById("search-navigation-prev");
        const nextBtn = document.getElementById("search-navigation-next");

        if (!isSearchOn) {
            // Destroy events by replacing elements
            prevBtn.replaceWith(prevBtn.cloneNode(true));
            nextBtn.replaceWith(nextBtn.cloneNode(true));

            navText.textContent = "";
            navContainer.style.display = "none";
            return;
        }

        navContainer.style.display = "flex";

        // Clear old events
        prevBtn.replaceWith(prevBtn.cloneNode(true));
        nextBtn.replaceWith(nextBtn.cloneNode(true));

        const newPrevBtn = document.getElementById("search-navigation-prev");
        const newNextBtn = document.getElementById("search-navigation-next");

        // No results
        if (filteredCanvasIDs.length === 0) {
            navText.textContent = "No results";
            return;
        }

        // Find current index
        let currentCanvasIndex = filteredCanvasIDs.indexOf(
            currentActiveCanvasID,
        );
        if (currentCanvasIndex === -1) {
            currentCanvasIndex = 0;
        }
        navText.textContent = `(${currentCanvasIndex + 1} / ${
            filteredCanvasIDs.length
        } results)`;

        const preFilteredCanvasID =
            currentCanvasIndex > 0
                ? filteredCanvasIDs[currentCanvasIndex - 1]
                : null;

        const nextFilteredCanvasID =
            currentCanvasIndex < filteredCanvasIDs.length - 1
                ? filteredCanvasIDs[currentCanvasIndex + 1]
                : null;

        // Prev click
        newPrevBtn.addEventListener("click", () => {
            if (preFilteredCanvasID) {
                viewer.activateCanvas(preFilteredCanvasID);
            }
        });

        // Next click
        newNextBtn.addEventListener("click", () => {
            if (nextFilteredCanvasID) {
                viewer.activateCanvas(nextFilteredCanvasID);
            }
        });
    }

    function highLightMatchedAnnotation(annotationIds) {
        annotationIds.forEach((annotationId) => {
            if (filteredAnnotationIDs.has(annotationId)) {
                // Find annotation shape by data-id
                const shapeEl = document.querySelector(
                    `.rdwb-ano-shape[data-id="${annotationId}"] .a9s-inner`,
                );
                if (shapeEl) {
                    // Apply green highlight via element.style
                    shapeEl.style.stroke = "green";
                    shapeEl.style.fill = "green";
                    shapeEl.style.fillOpacity = "0.3"; // adjust opacity if needed
                }
            }
        });
    }

    function clearHighlightedAnnotations() {
        // Clear all highlighted annotations
        const highlightedShapes = document.querySelectorAll(
            ".rdwb-ano-shape .a9s-inner",
        );
        highlightedShapes.forEach((shape) => {
            shape.style.stroke = "";
            shape.style.fill = "";
            shape.style.fillOpacity = "";
        });
    }

    // Annotation table , show only the row with the matched annotation id
    function tableGoToRow(annotationId) {
        allRows.forEach((row) => {
            row.style.display = "none";
        });

        const row = document
            .querySelector(
                `#annotation-table-body tr input.annotation-id[value="${annotationId}"]`,
            )
            ?.closest("tr");
        if (row) {
            row.style.display = "block";
        }
    }

    function updateTable() {
        const searchQuery = searchInput.value.toLowerCase().trim();
        let activeFilters = {};

        filteredAnnotationIDs.clear();
        currentSearchTableResult = [];
        filteredCanvasIDs = [];
        isSearchOn = searchQuery !== "";

        clearHighlightedAnnotations();
        // Collect active filters
        filterDropdowns.forEach((select) => {
            const column = select.getAttribute("data-column");
            const selectedValues = Array.from(select.selectedOptions).map(
                (option) => option.value,
            );
            if (selectedValues.length > 0 && selectedValues[0] !== "") {
                activeFilters[column] = selectedValues;
            }
        });

        allRows.forEach((row) => {
            let textMatch = false;
            let filterMatch = true;

            let annotationId = row.querySelector("input.annotation-id")?.value;
            let canvasId = row.querySelector("input.canvas-id")?.value;
            let imageName = row.querySelector("input.image-name")?.value;
            let pageNumber = row.querySelector("input.page-number")?.value || imageName;

            // Reset row and cell highlights
            row.classList.remove("match-row");
            row.querySelectorAll("td").forEach((cell) => {
                cell.innerHTML = cell.innerHTML.replace(
                    /<span class="highlight">([\s\S]*?)<\/span>/gi,
                    "$1",
                );
            });

            let snippetHTMLForRow = null;

            const cells = row.querySelectorAll("td");

            cells.forEach((cell, index) => {
                if (index >= columnHeaders.length) return;

                const columnName = columnHeaders[index];
                const rawText = cell.textContent.trim();
                const rawTextLower = rawText.toLowerCase();

                // Search match and bold highlight
                if (searchQuery && rawTextLower.includes(searchQuery)) {
                    textMatch = true;
                    const regex = new RegExp(`(${searchQuery})`, "ig");

                    const walker = document.createTreeWalker(
                        cell,
                        NodeFilter.SHOW_TEXT,
                    );
                    const textNodes = [];

                    while (walker.nextNode()) {
                        textNodes.push(walker.currentNode);
                    }

                    textNodes.forEach((node) => {
                        const parent = node.parentNode;
                        const replaced = node.nodeValue.replace(
                            regex,
                            '<span class="highlight">$1</span>',
                        );
                        if (replaced !== node.nodeValue) {
                            const span = document.createElement("span");
                            span.innerHTML = replaced;
                            parent.replaceChild(span, node);
                        }
                    });

                    // create snippet once per row
                    if (!snippetHTMLForRow) {
                        const idx = rawTextLower.indexOf(searchQuery);
                        const before = Math.max(0, idx - 45);
                        const after = Math.min(
                            rawText.length,
                            idx + searchQuery.length + 45,
                        );
                        let snippet = rawText.slice(before, after);
                        if (before > 0) snippet = "… " + snippet;
                        if (after < rawText.length) snippet = snippet + " …";
                        snippetHTMLForRow = snippet.replace(
                            regex,
                            '<span class="highlight">$1</span>',
                        );
                    }
                }

                // Filter match
                if (activeFilters[columnName]) {
                    const selectedValues = activeFilters[columnName];

                    if (columnName === "Tags") {
                        const tagElements =
                            cell.querySelectorAll(".annotation-tag");
                        const tagValues = Array.from(tagElements).map((tag) =>
                            tag.textContent.trim(),
                        );

                        if (
                            !selectedValues.some((tag) =>
                                tagValues.includes(tag),
                            )
                        ) {
                            filterMatch = false;
                        }
                    } else {
                        if (!selectedValues.includes(rawText)) {
                            filterMatch = false;
                        }
                    }
                }
            });

            // Final visibility / highlight decision
            const shouldHighlight = textMatch && filterMatch;
            if (shouldHighlight) {
                filteredAnnotationIDs.add(annotationId);
                if (!filteredCanvasIDs.includes(canvasId)) {
                    filteredCanvasIDs.push(canvasId);
                }
                row.classList.add("match-row");

                if (!currentSearchTableResult.some((r) => r.pageNumber === pageNumber)) {
                    currentSearchTableResult.push({
                        textSnippet: snippetHTMLForRow || "", // safe default
                        imageName,
                        canvasId,
                        annotationId,
                        pageNumber,
                    });
                }
            }
        });

        if (searchQuery == "" && Object.keys(activeFilters).length == 0) {
            isSearchOn = false;
            $("#annotation-show-search-result").text("Show all results");
            $("#annotation-search-table-container").empty().hide();
        }

        // Go to the first search result. Viewer and table
        if (filteredCanvasIDs.length > 0) {
            viewer.activateCanvas(filteredCanvasIDs[0]);
            tableGoToRow(filteredAnnotationIDs.values().next().value);
        }

        highLightMatchedAnnotation(Array.from(filteredAnnotationIDs));
        updateSearchNavigations();

        if (isSearchOn && currentSearchTableResult.length > 0) {
            renderSearchResultTable();
        }
    }

    // Search event
    searchInput.addEventListener("keypress", function (event) {
        if (event.key === "Enter") {
            event.preventDefault();
            updateTable();
        }
    });
    filterDropdowns.forEach((select) => {
        select.addEventListener("change", updateTable);
    });

    $("#searchFilterBtn").on("click", function (e) {
        e.preventDefault();
        updateTable();
    });

    $("#searchResetBtn").on("click", function (e) {
        e.preventDefault();
        searchInput.value = "";
        choicesInstances.forEach((c) => c.removeActiveItems());
        updateTable();
    });

    function renderSearchResultTable() {
        const $container = $("#annotation-search-table-container");
        $container.empty();

        let html = `
                <table border="1" cellpadding="5" cellspacing="0" style="width:100%;">
                    <tbody>
            `;

        currentSearchTableResult.forEach((r, i) => {
            html += `
                    <tr>
                        <td>${r.textSnippet}</td>
                        <td>
                            <a href="#" class="js-goto" 
                            data-canvas="${r.canvasId}" 
                            data-annotation="${r.annotationId}">
                            ${r.pageNumber}
                            </a>
                        </td>
                        <td>
                            <button class="js-goto" 
                                    data-canvas="${r.canvasId}" 
                                    data-annotation="${r.annotationId}">
                                Jump to
                            </button>
                        </td>
                    </tr>
                `;
        });

        html += `
                    </tbody>
                </table>
            `;

        $container.html(html);

        // Bind jump events
        $container.find(".js-goto").on("click", function (e) {
            e.preventDefault();
            const canvasId = $(this).data("canvas");
            const annotationId = $(this).data("annotation");
            if (canvasId) viewer.activateCanvas(canvasId);
            if (annotationId) tableGoToRow(annotationId);
        });
    }

    $("#annotation-show-search-result").on("click", function () {
        const $btn = $(this);
        const $container = $("#annotation-search-table-container");

        if (!isSearchOn || currentSearchTableResult.length === 0) {
            return;
        }

        if ($container.is(":visible")) {
            $container.hide();
            $btn.text("Show all results");
        } else {
            $container.show();
            $btn.text("Hide all results");
        }
    });

    // Toolbar: Search toggle (slide + arrow flip)
    const $searchToggle = $("#tte-search-toggle");
    const $searchDropdown = $(".search-dropdown");
    const $searchArrow = $searchToggle.find(".tte-search-arrow");

    $searchToggle.on("click", function () {
        $searchDropdown.slideToggle(200, function () {
            $searchArrow.html(
                $searchDropdown.is(":visible") ? "&#x25BC;" : "&#x25B2;"
            );
        });
    });

    // Toolbar: Pages dropdown
    const $pagesToggle = $("#tte-pages-toggle");
    const $pagesDropdown = $("#tte-pages-dropdown");
    const $pagesList = $("#tte-pages-list");
    const $pagesSearch = $("#tte-pages-search");

    // Toolbar: Table of Contents dropdown
    const $tocToggle = $("#tte-toc-toggle");
    const $tocDropdown = $("#tte-toc-dropdown");
    const $tocList = $("#tte-toc-list");
    const $tocSearch = $("#tte-toc-search");

    function populatePagesList() {
        $pagesList.empty();
        const seen = new Set();
        allRows.forEach((row) => {
            const canvasId = row.querySelector("input.canvas-id")?.value;
            const annotationId = row.querySelector("input.annotation-id")?.value;
            const pageNumber =
                row.querySelector("input.page-number")?.value ||
                row.querySelector("input.image-name")?.value ||
                "";
            if (!canvasId || seen.has(canvasId)) return;
            seen.add(canvasId);
            const $li = $("<li>", {
                class: "tte-page-item",
                text: pageNumber,
            })
                .attr("data-canvas", canvasId)
                .attr("data-annotation", annotationId || "");
            $pagesList.append($li);
        });
    }

    function populateTocList() {
        $tocList.empty();
        const items = [];
        let minLevel = 6;

        allRows.forEach((row) => {
            const canvasId = row.querySelector("input.canvas-id")?.value;
            const annotationId = row.querySelector("input.annotation-id")?.value;
            if (!canvasId) return;

            const headings = row.querySelectorAll(
                "td h1, td h2, td h3, td h4, td h5, td h6"
            );
            headings.forEach((h) => {
                const level = parseInt(h.tagName.substring(1), 10);
                const text = h.textContent.trim();
                if (!text) return;
                if (level < minLevel) minLevel = level;
                items.push({ level, text, canvasId, annotationId });
            });
        });

        if (items.length === 0) {
            $tocList.append(
                $("<li>", {
                    class: "tte-toc-empty",
                    text: "No table of contents",
                })
            );
            return;
        }

        items.forEach(({ level, text, canvasId, annotationId }) => {
            const depth = Math.max(0, level - minLevel);
            const $li = $("<li>", {
                class: "tte-toc-item",
                text,
            })
                .attr("data-canvas", canvasId)
                .attr("data-annotation", annotationId || "")
                .attr("data-level", depth)
                .css("padding-left", 0.75 + depth * 1.25 + "rem");
            $tocList.append($li);
        });
    }

    $pagesToggle.on("click", function (e) {
        e.stopPropagation();
        $tocDropdown.hide();
        if ($pagesDropdown.is(":visible")) {
            $pagesDropdown.hide();
            return;
        }
        if ($pagesList.children().length === 0) {
            populatePagesList();
        }
        $pagesDropdown.show();
    });

    $pagesList.on("click", ".tte-page-item", function () {
        const canvasId = $(this).attr("data-canvas");
        const annotationId = $(this).attr("data-annotation");
        if (canvasId) viewer.activateCanvas(canvasId);
        if (annotationId) tableGoToRow(annotationId);
        $pagesDropdown.hide();
    });

    $pagesSearch.on("input", function () {
        const q = $(this).val().toLowerCase();
        $pagesList.find(".tte-page-item").each(function () {
            $(this).toggle($(this).text().toLowerCase().includes(q));
        });
    });

    $tocToggle.on("click", function (e) {
        e.stopPropagation();
        $pagesDropdown.hide();
        if ($tocDropdown.is(":visible")) {
            $tocDropdown.hide();
            return;
        }
        if ($tocList.children().length === 0) {
            populateTocList();
        }
        $tocDropdown.show();
    });

    $tocList.on("click", ".tte-toc-item", function () {
        const canvasId = $(this).attr("data-canvas");
        const annotationId = $(this).attr("data-annotation");
        if (canvasId) viewer.activateCanvas(canvasId);
        if (annotationId) tableGoToRow(annotationId);
        $tocDropdown.hide();
    });

    $tocSearch.on("input", function () {
        const q = $(this).val().toLowerCase();
        $tocList.find(".tte-toc-item").each(function () {
            $(this).toggle($(this).text().toLowerCase().includes(q));
        });
    });

    $(document).on("click", function (e) {
        const $t = $(e.target);
        if (!$t.closest("#tte-pages-dropdown, #tte-pages-toggle").length) {
            $pagesDropdown.hide();
        }
        if (!$t.closest("#tte-toc-dropdown, #tte-toc-toggle").length) {
            $tocDropdown.hide();
        }
    });
});

jQuery(document).ready(function ($) {
    const $viewer = $("#viewer-container");
    const $table = $("#table-container");
    const $viewerBtn = $("#toggle-viewer");
    const $tableBtn = $("#toggle-table");

    // For the viewer button
    $viewerBtn.on("click", function () {
        if ($viewer.is(":visible")) {
            // Hide viewer, expand table
            $viewer.hide();
            $table.removeClass("col-6").addClass("col-12");

            $viewerBtn.find("p").html("Show Facsimile");

            $tableBtn.find("p").css("visibility", "hidden");
        } else {
            // Show Facsimile, shrink table, expand viewer
            $viewer.show();
            $table.removeClass("col-12").addClass("col-6");

            $viewerBtn.find("p").html("Hide Facsimile");

            $tableBtn.find("p").css("visibility", "visible");
        }
    });

    // For the table button
    $tableBtn.on("click", function () {
        if ($table.is(":visible")) {
            // Hide table, expand viewer
            $table.hide();
            $viewer.removeClass("col-6").addClass("col-12");

            $tableBtn.find("p").html("Show Transcription");

            $viewerBtn.find("p").css("visibility", "hidden");
        } else {
            // Show Transcription, shrink viewer, expand table
            $table.show();
            $viewer.removeClass("col-12").addClass("col-6");

            $tableBtn.find("p").html("Hide Transcription");

            $viewerBtn.find("p").css("visibility", "visible");
        }
    });

    $("#annotation-table-decrease-font-size").on("click", function () {
        const table = $("#annotation-table");
        const current =
            parseFloat(
                getComputedStyle(table[0]).getPropertyValue("--base-font"),
            ) || 16;
        table.css("--base-font", current - 1 + "px");
    });

    $("#annotation-table-increase-font-size").on("click", function () {
        const table = $("#annotation-table");
        const current =
            parseFloat(
                getComputedStyle(table[0]).getPropertyValue("--base-font"),
            ) || 16;
        table.css("--base-font", current + 1 + "px");
    });

    // Viewer navigation buttons
    $(".glycerine-viewer-prev").on("click", function () {
        const $internalLeft = $viewer
            .find("button span.pi.pi-chevron-left")
            .first();
        if ($internalLeft.length) {
            $internalLeft.closest("button").trigger("click");
        }
    });

    $(".glycerine-viewer-next").on("click", function () {
        const $internalRight = $viewer
            .find("button span.pi.pi-chevron-right")
            .first();
        if ($internalRight.length) {
            $internalRight.closest("button").trigger("click");
        }
    });
});
