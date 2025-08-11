const { registerBlockType } = wp.blocks;
const { PanelBody, TextControl, CheckboxControl, Button, Spinner } =
    wp.components;
const { InspectorControls } = wp.blockEditor;
const { useState, useEffect } = wp.element;

registerBlockType("glycerine/text-annotation", {
    title: "Glycerine Text Annotation",
    icon: "text",
    category: "common",

    attributes: {
        selectedCollectionID: {
            type: "string",
            default: null,
        },
        selectedImageSetID: {
            type: "string",
            default: null,
        },
        isRendered: {
            type: "boolean",
            default: false,
        },
        selectedColumns: {
            type: "array",
            default: [
                "Collection",
                "Image Set",
                "Annotation Set",
                "Title",
                "Description (EN)",
                "Description (zh_CN)",
                "Description (en-Latn)",
                "Tags",
            ],
        },
        viewerSettings: {
            type: "array",
            default: [
                "fullscreenbutton",
                "indexbutton",
                "annotationviewbutton",
                "aboutpanebutton",
                "settingpanebutton",
                "infopanel",
            ],
        },
        imageQuality: {
            type: "number",
            default: 600,
        },
        collectionMap: { type: "object", default: {} },
        columnLabelMap: {
            type: "object",
            default: {},
        },
    },

    edit: (props) => {
        const { attributes, setAttributes } = props;
        const [collections, setCollections] = useState([]);
        const [imageSets, setImageSets] = useState([]);
        const [loadingImageSets, setLoadingImageSets] = useState(false);
        const [collectionMap, setCollectionMap] = useState({});
        const [setIsLoading] = useState(true);
        const [selectedCollectionID, setSelectedCollection] = useState(null);

        const allColumns = [
            "Collection",
            "Image Set",
            "Annotation Set",
            "Title",
            "Description (EN)",
            "Description (zh_CN)",
            "Description (en-Latn)",
            "Tags",
        ];

        useEffect(() => {
            wp.apiRequest({
                path: "/tte_glycerine/v1/collections",
                method: "GET",
            }).then((response) => {
                setCollections(response);
                setIsLoading(false);

                // Convert response to ID-to-Name map
                const idToNameMap = response.reduce((acc, collection) => {
                    acc[collection.id] = collection.name;
                    return acc;
                }, {});

                setCollectionMap(idToNameMap);
                setAttributes({ collectionMap: idToNameMap });
            });
        }, []);

        const handleCheckboxChange = (key) => {
            let newSettings = [...attributes.viewerSettings];
            if (newSettings.includes(key)) {
                newSettings = newSettings.filter((item) => item !== key);
            } else {
                newSettings.push(key);
            }
            setAttributes({ viewerSettings: newSettings });
        };

        const fetchImageSets = (collectionId) => {
            setLoadingImageSets(true);
            setImageSets([]);
            wp.apiRequest({
                path: `/tte_glycerine/v1/collection/${collectionId}/image_sets`,
                method: "GET",
            }).then((response) => {
                setLoadingImageSets(false);
                if (response.length === 0) {
                    alert("No public image sets found for this collection");
                    return;
                }
                setImageSets(response);
                setAttributes({ selectedCollectionID: collectionId });
                setSelectedCollection(collectionId);
            });
        };

        // Handle column selection
        const handleColumnChange = (column) => {
            let newSelectedColumns = attributes.selectedColumns.includes(column)
                ? attributes.selectedColumns.filter((col) => col !== column)
                : [...attributes.selectedColumns, column];

            // Prevent selectedColumns from being empty
            if (newSelectedColumns.length === 0) {
                newSelectedColumns = allColumns;
            }

            setAttributes({ selectedColumns: newSelectedColumns });
        };

        const handleColumnOrderChange = (column, value) => {
            let newOrder = [...attributes.selectedColumns];
            const index = newOrder.indexOf(column);

            if (index !== -1) {
                newOrder.splice(index, 1); // Remove column
                newOrder.splice(value - 1, 0, column); // Insert at new position
            }

            setAttributes({ selectedColumns: newOrder });
        };

        return (
            <div>
                <InspectorControls>
                    <PanelBody title="Choose an Image Set">
                        <Spinner
                            style={{
                                display: loadingImageSets ? "block" : "none",
                                marginBottom: "10px",
                            }}
                        />

                        {collections.map((collection) => (
                            <div key={collection.id}>
                                <div
                                    onClick={() =>
                                        attributes.selectedCollectionID ===
                                        collection.id
                                            ? setSelectedCollection(null)
                                            : fetchImageSets(collection.id)
                                    }
                                    style={{
                                        display: "flex",
                                        justifyContent: "space-between",
                                        padding: "10px",
                                        border: "1px solid #ddd",
                                        cursor: "pointer",
                                        marginBottom: "5px",
                                        backgroundColor:
                                            selectedCollectionID ===
                                            collection.id
                                                ? "#f0f0f0"
                                                : "#fff",
                                    }}
                                >
                                    <span
                                        style={{
                                            display: "flex",
                                            alignItems: "center",
                                        }}
                                    >
                                        <span style={{ marginRight: "10px" }}>
                                            📋
                                        </span>
                                        {collection.name}
                                    </span>
                                    <span
                                        style={{
                                            color: "#000",
                                            fontWeight: "bold",
                                        }}
                                    >
                                        {attributes.selectedCollectionID ===
                                        collection.id
                                            ? "▼"
                                            : "→"}
                                    </span>
                                </div>

                                {attributes.selectedCollectionID ===
                                    collection.id &&
                                    !loadingImageSets && (
                                        <div
                                            style={{
                                                overflow: "auto",
                                                maxHeight: "500px",
                                                transition:
                                                    "max-height 1s ease-in-out",
                                                border: "1px solid #ddd",
                                            }}
                                        >
                                            {imageSets.map((imageSet) => (
                                                <div
                                                    key={imageSet.id}
                                                    onClick={() => {
                                                        setAttributes({
                                                            selectedCollectionID:
                                                                collection.id,
                                                            selectedImageSetID:
                                                                imageSet.id,
                                                            isRendered: true,
                                                        });
                                                    }}
                                                    style={{
                                                        display: "flex",
                                                        alignItems: "center",
                                                        padding: "10px",
                                                        cursor: "pointer",
                                                    }}
                                                >
                                                    <img
                                                        src={imageSet.thumbnail}
                                                        alt={imageSet.name}
                                                        style={{
                                                            minWidth: "50px",
                                                            minHeight: "50px",
                                                            maxWidth: "50px",
                                                            maxHeight: "50px",
                                                            marginRight: "10px",
                                                        }}
                                                    />
                                                    <p
                                                        style={{
                                                            margin: 0,
                                                            fontWeight: "bold",
                                                        }}
                                                    >
                                                        {imageSet.name}
                                                    </p>
                                                </div>
                                            ))}
                                        </div>
                                    )}
                            </div>
                        ))}
                    </PanelBody>

                    <div
                        style={{
                            backgroundColor: "#d3f8d3",
                            padding: "10px",
                            borderRadius: "5px",
                        }}
                    >
                        <PanelBody title="Table Settings">
                            <TextControl
                                label="Image quality (300-1200)"
                                type="number"
                                value={attributes.imageQuality}
                                onChange={(value) =>
                                    setAttributes({
                                        imageQuality: value
                                            ? parseInt(value, 10)
                                            : 600,
                                    })
                                }
                                min={1}
                            />
                        </PanelBody>

                        <PanelBody title="Columns to Display">
                            {allColumns.map((column) => {
                                const isChecked =
                                    attributes.selectedColumns.includes(column);
                                const index =
                                    attributes.selectedColumns.indexOf(column);
                                const overrideLabel =
                                    attributes.columnLabelMap[column] || "";

                                return (
                                    <div
                                        key={column}
                                        style={{
                                            marginBottom: "10px",
                                            borderBottom: "2px solid black",
                                            paddingBottom: "5px",
                                        }}
                                    >
                                        <div
                                            style={{
                                                display: "flex",
                                                justifyContent: "space-between",
                                            }}
                                        >
                                            <CheckboxControl
                                                label={column}
                                                checked={isChecked}
                                                onChange={() =>
                                                    handleColumnChange(column)
                                                }
                                            />
                                            {isChecked && (
                                                <input
                                                    type="number"
                                                    value={index + 1}
                                                    min="1"
                                                    max={
                                                        attributes
                                                            .selectedColumns
                                                            .length
                                                    }
                                                    style={{
                                                        width: "40px",
                                                        height: "20px",
                                                    }}
                                                    onChange={(e) =>
                                                        handleColumnOrderChange(
                                                            column,
                                                            parseInt(
                                                                e.target.value,
                                                                10
                                                            )
                                                        )
                                                    }
                                                />
                                            )}
                                        </div>
                                        {isChecked && (
                                            <TextControl
                                                label="Override Label"
                                                value={overrideLabel}
                                                onChange={(newLabel) => {
                                                    setAttributes({
                                                        columnLabelMap: {
                                                            ...attributes.columnLabelMap,
                                                            [column]: newLabel,
                                                        },
                                                    });
                                                }}
                                            />
                                        )}
                                    </div>
                                );
                            })}
                        </PanelBody>

                        <PanelBody title="Display">
                            {[
                                {
                                    key: "fullscreenbutton",
                                    label: "Full Screen",
                                },
                                { key: "indexbutton", label: "Index Page" },
                                {
                                    key: "annotationviewbutton",
                                    label: "Annotation Views",
                                },
                                { key: "aboutpanebutton", label: "About Pane" },
                                {
                                    key: "settingpanebutton",
                                    label: "Settings Pane",
                                },
                                {
                                    key: "infopanel",
                                    label: "Information Panel",
                                },
                            ].map(({ key, label }) => (
                                <CheckboxControl
                                    key={key}
                                    label={label}
                                    checked={attributes.viewerSettings.includes(
                                        key
                                    )}
                                    onChange={() => handleCheckboxChange(key)}
                                />
                            ))}
                        </PanelBody>
                    </div>
                </InspectorControls>

                <div className="glycerine-text-annotation">
                    <h4 style={{ textAlign: "center", marginBottom: "10px" }}>
                        Glycerine Text Annotation
                    </h4>

                    {attributes.isRendered && attributes.selectedImageSetID && (
                        <div className="rendered-content">
                            <h5
                                style={{
                                    textAlign: "center",
                                    marginBottom: "0",
                                    marginTop: "0",
                                }}
                            >
                                Selected Collection:{" "}
                                {
                                    attributes.collectionMap[
                                        attributes.selectedCollectionID
                                    ]
                                }
                                <br />
                                Selected Image Set ID:{" "}
                                {attributes.selectedImageSetID}
                            </h5>
                            <table
                                style={{
                                    width: "100%",
                                    borderCollapse: "collapse",
                                    backgroundColor: "#f8f9fa",
                                    height: "150px",
                                }}
                            >
                                <thead>
                                    <tr>
                                        {attributes.selectedColumns.map(
                                            (column) => (
                                                <th
                                                    key={column}
                                                    style={{
                                                        padding: "10px",
                                                        border: "1px solid #ddd",
                                                        backgroundColor:
                                                            "#d3f8d3",
                                                    }}
                                                >
                                                    {attributes.columnLabelMap[
                                                        column
                                                    ] || column}
                                                </th>
                                            )
                                        )}
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        {attributes.selectedColumns.map(
                                            (column) => (
                                                <td
                                                    key={column}
                                                    style={{
                                                        padding: "10px",
                                                        border: "1px solid #ddd",
                                                        height: "100px",
                                                        textAlign: "center",
                                                    }}
                                                >
                                                    (Data will appear here)
                                                </td>
                                            )
                                        )}
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    )}
                </div>
            </div>
        );
    },

    save: () => null,
});
