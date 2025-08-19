jQuery(document).ready(function ($) {
    const allRows = Array.from(
        document.querySelectorAll("#annotation-table-body tr")
    );

    const table = document.getElementById("annotation-table");
    const searchInput = document.getElementById("annotation-search");

    const columnHeaders = Array.from(
        table.querySelector("thead").rows[0].cells
    ).map((cell) => cell.textContent.trim());

    const filterDropdowns = document.querySelectorAll(".column-filter");
    filterDropdowns.forEach((select) => {
        new Choices(select, {
            removeItemButton: true,
            allowHTML: false,
            itemSelectText: "",
        });
    });

    let isSearchOn = false;
    let filteredCanvasIDs = [];
    const filteredAnnotationIDs = new Set();
    let currentActiveCanvasID = null;

    // Initialize glycerine viewer
    const ele = document.getElementById("glycerine-text-annotation-viewer");
    const viewer = new GlycerineViewer(ele, {
        width: "100%",
        height: "700px",
        manifest: document.getElementById("tte_glycerine_manifest_url").value,
        showFullScreenButton: ele.dataset.showFullScreenButton === "true",
        showIndexButton: ele.dataset.showIndexButton === "true",
        showAnnotationViewButton:
            ele.dataset.showAnnotationViewButton === "true",
        showAboutPaneButton: ele.dataset.showAboutPaneButton === "true",
        showSettingPaneButton: ele.dataset.showSettingPaneButton === "true",
        defaultInfoPanel: ele.dataset.defaultInfoPanel === "true",
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
            console.log("OSD Initialized");
            // Hide all rows first
            allRows.forEach((row) => {
                row.style.display = "none";
            });
        },
        onCanvasAnnotationsLoaded: (data) => {
            console.log("Canvas Annotations Loaded");
            clearHighlightedAnnotations();

            data.forEach((annotation) => {
                const row = document
                    .querySelector(
                        `#annotation-table-body tr input.annotation-id[value="${annotation.id}"]`
                    )
                    ?.closest("tr");

                if (row) {
                    row.style.display = "";
                }
            });

            setTimeout(() => {
                highLightMatchedAnnotation(data.map((anno) => anno.id));
            }, 750);
        },
        onCanvasLoaded: (data) => {
            console.log("Canvas Loaded");
            currentActiveCanvasID = data;
            updateSearchNavigations();
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
            currentActiveCanvasID
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
                    `.rdwb-ano-shape[data-id="${annotationId}"] .a9s-inner`
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
            ".rdwb-ano-shape .a9s-inner"
        );
        highlightedShapes.forEach((shape) => {
            shape.style.stroke = "";
            shape.style.fill = "";
            shape.style.fillOpacity = "";
        });
    }

    function updateTable() {
        const searchQuery = searchInput.value.toLowerCase().trim();
        let activeFilters = {};

        filteredAnnotationIDs.clear();
        filteredCanvasIDs = [];
        isSearchOn = searchQuery !== "";

        clearHighlightedAnnotations();
        // Collect active filters
        filterDropdowns.forEach((select) => {
            const column = select.getAttribute("data-column");
            const selectedValues = Array.from(select.selectedOptions).map(
                (option) => option.value
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

            // Reset row and cell highlights
            row.classList.remove("match-row");
            row.querySelectorAll("td").forEach((cell) => {
                cell.innerHTML = cell.innerHTML.replace(
                    /<span class="highlight">([\s\S]*?)<\/span>/gi,
                    "$1"
                );
            });

            const cells = row.querySelectorAll("td");

            cells.forEach((cell, index) => {
                if (index >= columnHeaders.length) return;

                const columnName = columnHeaders[index];
                const rawText = cell.textContent.trim();

                // Search match and bold highlight
                if (
                    searchQuery &&
                    rawText.toLowerCase().includes(searchQuery)
                ) {
                    textMatch = true;
                    const regex = new RegExp(`(${searchQuery})`, "ig");

                    const walker = document.createTreeWalker(
                        cell,
                        NodeFilter.SHOW_TEXT
                    );
                    const textNodes = [];

                    while (walker.nextNode()) {
                        textNodes.push(walker.currentNode);
                    }

                    textNodes.forEach((node) => {
                        const parent = node.parentNode;
                        const replaced = node.nodeValue.replace(
                            regex,
                            '<span class="highlight">$1</span>'
                        );
                        if (replaced !== node.nodeValue) {
                            const span = document.createElement("span");
                            span.innerHTML = replaced;
                            parent.replaceChild(span, node);
                        }
                    });
                }

                // Filter match
                if (activeFilters[columnName]) {
                    const selectedValues = activeFilters[columnName];

                    if (columnName === "Tags") {
                        const tagElements =
                            cell.querySelectorAll(".annotation-tag");
                        const tagValues = Array.from(tagElements).map((tag) =>
                            tag.textContent.trim()
                        );

                        if (
                            !selectedValues.some((tag) =>
                                tagValues.includes(tag)
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
                filteredCanvasIDs.push(canvasId);
                row.classList.add("match-row");
            }
        });

        if (searchQuery == "" && Object.keys(activeFilters).length == 0) {
            isSearchOn = false;
        }

        // Go to the first search result
        if (filteredCanvasIDs.length > 0) {
            viewer.activateCanvas(filteredCanvasIDs[0]);
            allRows.forEach((row) => {
                row.style.display = "none";
            });

            const row = document
                .querySelector(
                    `#annotation-table-body tr input.annotation-id[value="${filteredAnnotationIDs.values().next().value}"]`
                )
                ?.closest("tr");
            if (row) {
                row.style.display = "block";
            }
        }

        highLightMatchedAnnotation(Array.from(filteredAnnotationIDs));
        updateSearchNavigations();
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
});

jQuery(document).ready(function ($) {
    const $viewer = $("#viewer-container");
    const $table = $("#table-container");
    const $viewerBtn = $("#toggle-viewer");
    const $tableBtn = $("#toggle-table");

    const $toggleControls = $("#glycerine-toggle-controls");

    // For the viewer button
    $viewerBtn.on("click", function () {
        if ($viewer.is(":visible")) {
            // Hide viewer, expand table
            $viewer.hide();
            $table.removeClass("col-6").addClass("col-12");

            // Update button icon/text
            $viewerBtn
                .find("span")
                .removeClass("icon-arrow-left-dark")
                .addClass("icon-arrow-right-dark");
            $viewerBtn.find("p").html("Show Viewer");

            // Hide table button
            $tableBtn.hide();
            $toggleControls.css({ "justify-content": "flex-start" });
        } else {
            // Show viewer, shrink table, expand viewer
            $viewer.show();
            $table.removeClass("col-12").addClass("col-6");

            // Update button icon/text
            $viewerBtn
                .find("span")
                .removeClass("icon-arrow-right-dark")
                .addClass("icon-arrow-left-dark");
            $viewerBtn.find("p").html("Hide Viewer");

            // Show table button
            $tableBtn.show();
            $toggleControls.css({ "justify-content": "center" });
        }
    });

    // For the table button
    $tableBtn.on("click", function () {
        if ($table.is(":visible")) {
            // Hide table, expand viewer
            $table.hide();
            $viewer.removeClass("col-6").addClass("col-12");

            // Update button icon/text
            $tableBtn
                .find("span")
                .removeClass("icon-arrow-right-dark")
                .addClass("icon-arrow-left-dark");
            $tableBtn.find("p").html("Show Table");

            // Hide view button
            $viewerBtn.hide();

            $toggleControls.css({ "justify-content": "flex-end" });
        } else {
            // Show table, shrink viewer, expand table
            $table.show();
            $viewer.removeClass("col-12").addClass("col-6");

            // Update button icon/text
            $tableBtn
                .find("span")
                .removeClass("icon-arrow-left-dark")
                .addClass("icon-arrow-right-dark");
            $tableBtn.find("p").html("Hide Table");

            // Show view button
            $viewerBtn.show();

            $toggleControls.css({ "justify-content": "center" });
        }
    });
});
