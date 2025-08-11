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

    const filteredAnnotationIDs = new Set();

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
            // Hide all rows first
            allRows.forEach((row) => {
                row.style.display = "none";
            });
        },
        onCanvasAnnotationsLoaded: (data) => {
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
            }, 600);
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
                row.classList.add("match-row");
            }
        });

        highLightMatchedAnnotation(Array.from(filteredAnnotationIDs));
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
            $viewerBtn.find("p").html("");

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
            $tableBtn.find("p").html("");

            // Show view button
            $viewerBtn.show();

            $toggleControls.css({ "justify-content": "center" });
        }
    });
});
