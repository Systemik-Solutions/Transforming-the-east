jQuery(document).ready(function ($) {
    function setDefaultView() {
        var viewParam = getQueryParam("view");
        if (viewParam == "list") {
            $(".typeFilter-list").prop("checked", true);
        } else if (viewParam == "listmap") {
            $(".typeFilter-listmap").prop("checked", true);
        } else if (viewParam == "timemap") {
            $(".typeFilter-timemap").prop("checked", true);
        } else if (viewParam == "network") {
            $(".typeFilter-network").prop("checked", true);
        } else {
            $(".typeFilter-list").prop("checked", true);
            updateParameter("view", "list");
        }
    }

    function setViewBehaviour() {
        if ($(".typeFilter-list").is(":checked")) {
            $(".listmap-view").hide();
            $(".timemap-view").hide();
            $(".network-view").hide();
            $(".block-view").show();
        } else if ($(".typeFilter-listmap").is(":checked")) {
            $(".block-view").hide();
            $(".timemap-view").hide();
            $(".network-view").hide();
            $(".listmap-view").show();
        } else if ($(".typeFilter-timemap").is(":checked")) {
            $(".block-view").hide();
            $(".listmap-view").hide();
            $(".network-view").hide();
            $(".timemap-view").show();
        } else if ($(".typeFilter-network").is(":checked")) {
            $(".block-view").hide();
            $(".listmap-view").hide();
            $(".timemap-view").hide();
            $(".network-view").show();
        }
    }

    function bindViewClickEvent() {
        $('input[name="typeFilter"]').on("change", function () {
            if ($(".typeFilter-list").is(":checked")) {
                updateParameter("view", "list");
            } else if ($(".typeFilter-listmap").is(":checked")) {
                updateParameter("view", "listmap");
            } else if ($(".typeFilter-timemap").is(":checked")) {
                updateParameter("view", "timemap");
            } else if ($(".typeFilter-network").is(":checked")) {
                updateParameter("view", "network");
            }
            setViewBehaviour();
        });
    }

    // Initialize pagination
    var totalItems = parseInt($("#totalItems").val());
    var itemsPerPage = parseInt($("#itemsPerPage").val());
    var currentPage = parseInt($("#currentPage").val());
    let firstLoad = true;
    $("#pagination").pagination({
        dataSource: Array.from({ length: totalItems }, (_, i) => i + 1),
        pageSize: itemsPerPage,
        pageNumber: currentPage,
        showPrevious: true,
        showNext: true,
        showPageNumbers: true,
        pageRange: 4,
        showGoInput: true,
        showGoButton: true,
        callback: function (data, pagination) {
            if (firstLoad) {
                firstLoad = false;
                return;
            }

            updateParameter("cp", pagination.pageNumber);
            location.reload();
        },
    });

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

    // Function to update the specific parameter in the URL
    function updateParameter(param, value) {
        var url = new URL(window.location);
        url.searchParams.delete(param); // Clear existing entries
        url.searchParams.delete(param + "[]");

        if (Array.isArray(value)) {
            value.forEach((item) => {
                url.searchParams.append(param + "[]", item); // Append each item as `param[]`
            });
        } else if (value !== null) {
            url.searchParams.set(param, value);
        }

        window.history.pushState({}, "", url);
    }

    function getPresetUrlParams() {
        const params = new URLSearchParams(window.location.search);

        const parsed = {
            val: params.get("val") || "",
            title: {},
            dates: {},
            props: {}, // multiselects (choices-select)
            groups: {}, // checkbox-group
            recTypes: [], // resource template checkboxes
        };

        for (const [key, value] of params.entries()) {
            // Title fields
            const titleMatch = key.match(/^t(\d+)$/);
            if (titleMatch) parsed.title[titleMatch[1]] = value;

            // Date fields
            const dateMatch = key.match(/^d(\d+)$/);
            if (dateMatch) parsed.dates[dateMatch[1]] = value;

            // Multiselect fields
            const propMatch = key.match(/^p(\d+)\[\]$/);
            if (propMatch) {
                const pid = propMatch[1];
                if (!parsed.props[pid]) parsed.props[pid] = [];
                parsed.props[pid].push(value);
            }

            // Checkbox group fields
            const groupMatch = key.match(/^g(\d+)\[\]$/);
            if (groupMatch) {
                const pid = groupMatch[1];
                if (!parsed.groups[pid]) parsed.groups[pid] = [];
                parsed.groups[pid].push(value);
            }

            // Record types: rt[]=18&rt[]=19
            if (key === "rt[]") {
                parsed.recTypes.push(value);
            }
        }

        return parsed;
    }

    function initSearchControls() {
        const urlParams = getPresetUrlParams();

        // General search
        if (urlParams.val) $("#searchInput").val(urlParams.val);

        $(`*[data-control-type]`).each(function () {
            const controlType = $(this).data("controlType");
            const propertyId = $(this).data("fieldPropertyId")?.toString();

            // -----------------------------
            // MULTISELECT (Choices.js)
            // -----------------------------
            if (controlType === "multiselect") {
                const choices = new Choices(this, {
                    removeItemButton: true,
                    itemSelectText: "",
                });

                if (urlParams.props[propertyId]) {
                    choices.setChoiceByValue(urlParams.props[propertyId]);
                }
            }

            // -----------------------------
            // DATE RANGE
            // -----------------------------
            else if (controlType === "date-range") {
                const slider = $(this);
                const min = parseInt(slider.data("min"));
                const max = parseInt(slider.data("max"));
                let [setMin, setMax] = [min, max];

                if (urlParams.dates[propertyId]) {
                    const [from, to] = urlParams.dates[propertyId]
                        .split("-")
                        .map(Number);
                    setMin = from;
                    setMax = to;
                }

                new rSlider({
                    target: this,
                    values: { min, max },
                    range: true,
                    set: [setMin, setMax],
                    step: 1,
                    tooltip: true,
                });
            }

            // CHECKBOX-GROUP
            else if (controlType === "checkbox-group") {
                const checkboxes = $(this).find("[data-term-oid]");

                checkboxes.each(function () {
                    const termId = $(this).data("termOid").toString();
                    if (urlParams.groups[propertyId]?.includes(termId)) {
                        $(this).prop("checked", true);
                    }
                });
            }
        });

        // -----------------------------------
        // RECORD TYPE CHECKBOXES (rt[])
        // -----------------------------------
        const recTypesFromUrl = urlParams.recTypes; // array or []
        const $typeBoxes = $(
            ".rec-type-options input.custom-control-input"
        ).not("#recTypeAll");

        if (recTypesFromUrl && recTypesFromUrl.length > 0) {
            $typeBoxes.prop("checked", false);
            $typeBoxes.each(function () {
                const rt = $(this).data("recResourceTemplateId")?.toString();
                if (recTypesFromUrl.includes(rt)) {
                    $(this).prop("checked", true);
                }
            });
        } else {
            $typeBoxes.prop("checked", true);
        }
    }

    function searchAction() {
        const omekaParams = new URLSearchParams();
        const friendly = new URLSearchParams();
        let propertyIndex = 0;

        // -----------------------------------
        // General text search
        // -----------------------------------
        const searchVal = $("#searchInput").val()?.trim() || "";
        if (searchVal) {
            omekaParams.append(`property[${propertyIndex}][joiner]`, "and");
            omekaParams.append(`property[${propertyIndex}][property]`, "");
            omekaParams.append(`property[${propertyIndex}][type]`, "in");
            omekaParams.append(`property[${propertyIndex}][text]`, searchVal);
            friendly.append("val", searchVal);
            propertyIndex++;
        }

        // -----------------------------------
        // MULTISELECT (choices-select)
        // -----------------------------------
        $(".choices-select").each(function () {
            const pid = $(this).data("fieldPropertyId");
            const queryType = $(this).data("fieldPropertyType") || "res";
            const values = $(this).val();

            if (!values?.length) return;

            values.forEach((v, i) => {
                friendly.append(`p${pid}[]`, v);

                omekaParams.append(
                    `property[${propertyIndex}][joiner]`,
                    i === 0 ? "and" : "or"
                );
                omekaParams.append(`property[${propertyIndex}][property]`, pid);
                omekaParams.append(
                    `property[${propertyIndex}][type]`,
                    queryType
                );
                omekaParams.append(`property[${propertyIndex}][text]`, v);
                propertyIndex++;
            });
        });

        // -----------------------------------
        // CHECKBOX GROUP
        // -----------------------------------
        $("[data-control-type='checkbox-group']").each(function () {
            const pid = $(this).data("fieldPropertyId");
            const queryType = $(this).data("fieldPropertyType") || "eq";

            const values = $(this)
                .find("input[type=checkbox]:checked")
                .map(function () {
                    return $(this).data("termOid").toString();
                })
                .get();

            values.forEach((v, i) => {
                friendly.append(`g${pid}[]`, v);

                omekaParams.append(
                    `property[${propertyIndex}][joiner]`,
                    i === 0 ? "and" : "or"
                );
                omekaParams.append(`property[${propertyIndex}][property]`, pid);
                omekaParams.append(
                    `property[${propertyIndex}][type]`,
                    queryType
                );
                omekaParams.append(`property[${propertyIndex}][text]`, v);
                propertyIndex++;
            });
        });

        // -----------------------------------
        // RECORD TYPE CHECKBOXES
        // -----------------------------------
        const recTypes = [];
        $("[data-rec-resource-template-id]:checked").each(function () {
            recTypes.push($(this).data("recResourceTemplateId").toString());
        });
        recTypes.forEach((rt) => {
            omekaParams.append("resource_template_id[]", rt);
            friendly.append("rt[]", rt);
        });

        // -----------------------------------
        // DATE RANGE
        // -----------------------------------
        $(".date-select").each(function () {
            const pid = $(this).data("fieldPropertyId");
            const [from, to] = $(this).val().split(",").map(Number);
            friendly.append(`d${pid}`, `${from}-${to}`);
        });

        // -----------------------------------
        // Build final redirect URL
        // -----------------------------------
        const queryString = encodeURIComponent(omekaParams.toString());
        const extra = friendly.toString();
        let redirectUrl = `?hq=${queryString}${extra ? "&" + extra : ""}`;

        // Append 'view' parameter if present
        let url = new URL(window.location);
        let params = new URLSearchParams(url.search);
        if (params.has("view")) {
            if (redirectUrl !== "?") {
                redirectUrl += "&";
            }
            redirectUrl += `view=${encodeURIComponent(params.get("view"))}`;
        }

        // Append 'nr' parameter if present
        if (params.has("nr")) {
            if (redirectUrl !== "?") {
                redirectUrl += "&";
            }
            redirectUrl += `nr=${encodeURIComponent(params.get("nr"))}`;
        }

        window.location.href = redirectUrl;
    }

    setDefaultView();
    setViewBehaviour();
    bindViewClickEvent();
    initSearchControls();

    $("#searchInput").keydown(function (e) {
        if (e.keyCode === 13) {
            e.preventDefault();
            searchAction();
        }
    });

    // Bind the filter button click event.
    $("#searchFilterBtn").on("click", function (e) {
        e.preventDefault();
        searchAction();
    });

    // Bind the filter button click event.
    $("#searchResetBtn").on("click", function (e) {
        e.preventDefault();
        window.location.href = "?";
    });
});
