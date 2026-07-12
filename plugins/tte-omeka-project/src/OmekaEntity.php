<?php

namespace TTE;

use OMAKEAPI\OmekaClient;

class OmekaEntity
{
    protected $properties = [];

    /**
     * Constructor: parses and stores properties from raw Omeka item data.
     */
    public function __construct(array $rawData)
    {
        if (!array_key_exists('o:id', $rawData)) {
            throw new \InvalidArgumentException('Omeka item data must contain an o:id value.');
        }

        $this->properties['rec_ID'] = $rawData['o:id'];
        $this->properties['rec_Title'] = $rawData['o:title'] ?? '';

        $resourceTemplateId = $rawData['o:resource_template']['o:id'] ?? null;
        $altLabelMap = $this->getAlternativeLabelMap($resourceTemplateId);

        foreach ($rawData as $propName => $propValue) {
            // Keep only certain system fields
            if (in_array($propName, ['o:id', 'o:is_public', 'o:title', 'thumbnail_display_urls', 'o:resource_template', 'o:media'])) {
                $this->properties[$propName] = $propValue;
            }
            // Skip other o: fields
            elseif (strpos($propName, 'o:') === 0) {
                continue;
            }
            // All other vocab fields , Replace property label with alternative label if available
            elseif (strpos($propName, ':') !== false) {
                foreach ($propValue as $index => $value) {
                    // Use alternative label if available
                    if (isset($value['property_id']) && isset($altLabelMap[$value['property_id']])) {
                        $altFieldName = $altLabelMap[$value['property_id']];
                        $propValue[$index]['property_label'] = $altFieldName;
                    }
                }
                $fieldName = self::createFieldName($propName);
                $this->properties[$fieldName] = $propValue;
            }
        }
    }


    /**
     * Create a field-safe property key based on the Omeka field label.
     */
    public static function createFieldName($fieldLabel)
    {
        $name = preg_replace('~[^\pL\d]+~u', '_', $fieldLabel);
        $name = preg_replace('~[^-\w]+~', '', $name);
        return 'field_' . strtolower(trim($name));
    }

    /**
     * Get all parsed properties.
     */
    public function getProperties()
    {
        return $this->properties;
    }


    public function getThumbnailImageUrl()
    {
        if (!empty($this->getProperty('o:media')) && !empty($this->getProperty('thumbnail_display_urls'))) {
            return $this->getProperty('thumbnail_display_urls')['large'];
        }
        return null;
    }

    /**
     * Get a property by name.
     */
    public function getProperty(string $name)
    {
        return $this->properties[$name] ?? null;
    }

    public function getResourceTemplateId()
    {
        return isset($this->properties['o:resource_template']['o:id']) ? (int)$this->properties['o:resource_template']['o:id'] : null;
    }

    public function getId()
    {
        return isset($this->properties['o:id']) ? (int)$this->properties['o:id'] : null;
    }
    public function getTitle()
    {
        return isset($this->properties['o:title']) ? $this->properties['o:title'] : "";
    }

    public static function load(int $id): ?self
    {
        $omekaClient = new OmekaClient(
            OMEKA_API_ROUTE
        );

        $result = $omekaClient->getById('items', $id);

        // Omeka returns a JSON error object for missing/private items. Do not
        // pass that response to the entity constructor as though it were an item.
        if (!is_array($result) || !array_key_exists('o:id', $result)) {
            return null;
        }

        return new static($result);
    }

    public function getRelatedEntities()
    {
        $relatedEntities = [];
        $query =  [
            'is_public' => 1,
            'sort_order' => 'asc',
            'per_page' => 10000,
            'property' => [
                [
                    'joiner' => 'and',
                    'property' => '',
                    'type' => 'res',
                    'text' => $this->getId()
                ]
            ]
        ];

        $relatedEntities = \TTE\OmekaEntity::search($query, false)['entities'];
        return $relatedEntities;
    }

    public function addLinkedRecordsToProperties()
    {
        $query =  [
            'is_public' => 1,
            'sort_order' => 'asc',
            'per_page' => 10000,
            'property' => [
                [
                    'joiner' => 'and',
                    'property' => '',
                    'type' => 'res',
                    'text' => $this->getId()
                ]
            ]
        ];

        $relatedEntities = \TTE\OmekaEntity::search($query, false)['entities'];
        foreach ($relatedEntities as $entity) {

            $fieldName = 'field_linked_from';

            if (!isset($this->properties[$fieldName])) {
                $this->properties[$fieldName] = [];
            }
            $this->properties[$fieldName][] = [
                'type' => 'resource:item',
                'value_resource_id' => $entity->getId(),
                'value_resource_name' => 'items',
                'property_label' => 'Linked From',
                'display_title' => $entity->getTitle()
            ];
        }
    }

    public static function search($query, $enableCache = false)
    {
        $result = OmekaSearch::search($query, $enableCache);

        if (!$result || !isset($result['items'])) return ['items' => [], 'total' => 0];

        $items = array_filter($result['items'] ?? [], function ($item) {
            return is_array($item) && array_key_exists('o:id', $item);
        });

        return [
            'entities' => array_map(function ($item) {
                return new static($item);
            }, array_values($items)),
            'total' => $result['total'] ?? 0
        ];
    }


    private function getAlternativeLabelMap($resourceTemplateId)
    {
        if (!isset($resourceTemplateId)) {
            return [];
        }

        $cacheKey = 'tte_omeka_cached_resource_template_label_' . (int) $resourceTemplateId;
        $cached = get_transient($cacheKey);
        if ($cached !== false) {
            return $cached;
        }

        $res = [];

        $requestURL = OMEKA_PUBLIC_ROUTE . '/resource_templates/' . $resourceTemplateId;
        $response = wp_remote_get($requestURL, [
            'timeout' => 180,
            'headers' => [
                'Accept' => 'application/json',
            ],
        ]);
        $body = wp_remote_retrieve_body($response);
        if (isset($body)) {
            $decodedData = json_decode($body, true);
            $templateProperties = $decodedData['o:resource_template_property'] ?? [];
            foreach ($templateProperties as $property) {
                if (isset($property['o:alternate_label']) && !empty($property['o:alternate_label'])) {
                    $res[$property['o:property']['o:id']] =  trim($property['o:alternate_label']);
                }
            }
        }

        set_transient($cacheKey, $res, 4 * DAY_IN_SECONDS);
        return $res;
    }
}
