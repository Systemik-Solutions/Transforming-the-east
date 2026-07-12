<?php

namespace TTE;

/**
 * Class to store name mapping for Omeka.
 */
class OmekaMap
{
    protected static $map = [
        // Heurist record types.
        'resourceTemplates' => [
            'classicalSourceWork' => [
                'id' => '15',
                'color' => '#393A3B',
                'slug' => 'classical-source-work/',
                'name' => 'Classics',
            ],
            'classicalSourceWorkSection' => [
                'id' => '16',
                'color' => '#301934',
                'slug' => null,
                'name' => 'Classical Source Work Section',
            ],
            'translation' => [
                'id' => '18',
                'color' => '#393A3B',
                'slug' => 'translation/',
                'name' => 'Translation',
            ],
            'translationSection' => [
                'id' => '19',
                'color' => '#301934',
                'slug' => null,
                'name' => 'Translation Section',
            ],
            'translationCopy' => [
                'id' => '20',
                'color' => '#301934',
                'slug' => null,
                'name' => 'Translation Copy',
            ],
            'locationHistoryRecord' => [
                'id' => '21',
                'color' => '#393A3B',
                'slug' => null,
                'name' => 'Location History Record',
            ],
            'text' => [
                'id' => '22',
                'color' => '#301934',
                'slug' => 'text/',
                'name' => 'Text',
            ],
            'place' => [
                'id' => '14',
                'color' => '#512D2D',
                'slug' => 'place/',
                'name' => 'Place',
            ],
            'person' => [
                'id' => '13',
                'color' => '#301934',
                'slug' => 'person/',
                'name' => 'Person',
            ],
            'story' => [
                'id' => '25',
                'color' => '#87B38D',
                'slug' => 'story/',
                'name' => 'StoryMap',
            ],
        ],
        'propertyIDs' => [
            'field_schema_haspart' => [
                'id' => '1686',
                'queryType' => 'res',
            ],
            'field_schema_translationofwork' => [
                'id' => '1653',
                'queryType' => 'res',
            ],
            'field_te_classicalsource' => [
                'id' => '1709',
                'queryType' => 'res',
            ],
            'field_te_section' => [
                'id' => '1733',
                'queryType' => 'res',
            ],
            'field_schema_language' => [
                'id' => '879',
                'queryType' => 'eq',
            ],
            'field_te_booktype' => [
                'id' => '1706',
                'queryType' => 'eq',
            ],
            'field_te_copylocation' => [
                'id' => '1711',
                'queryType' => 'res',
            ],
            'field_schema_collection' => [
                'id' => '421',
                'queryType' => 'eq',
            ],
            'field_te_placetype' => [
                'id' => '1728',
                'queryType' => 'eq',
            ],
        ]
    ];

    /**
     * Get a mapping value.
     *
     * @param string $type
     *   The type name.
     * @param string $key
     *   The key to search.
     *
     * @return string[]|null
     */
    public static function get($type, $key)
    {
        if (isset(self::$map[$type][$key])) {
            return self::$map[$type][$key];
        }
        return null;
    }

    public static function getResourceTemplateMap()
    {
        return self::$map['resourceTemplates'];
    }
}
