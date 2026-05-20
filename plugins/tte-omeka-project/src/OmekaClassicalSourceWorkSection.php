<?php

namespace TTE;

class OmekaClassicalSourceWorkSection extends OmekaEntity
{


    // Get parent classical source work id
    public function getClassicalSourceWorkID()
    {

        $relatedEntities = \TTE\OmekaEntity::search(
            [
                'resource_template_id' => \TTE\OmekaMap::get('resourceTemplates', 'classicalSourceWork')['id'],
                'per_page' => 1,
                'is_public' => 1,
                'sort_order' => 'asc',
                'property' => [
                    [
                        'joiner' => 'and',
                        'property' => \TTE\OmekaMap::get('propertyIDs', 'field_schema_haspart')['id'],
                        'type' => \TTE\OmekaMap::get('propertyIDs', 'field_schema_haspart')['queryType'],
                        'text' => $this->getId()
                    ]
                ]
            ],
            false
        )['entities'];

        if (count($relatedEntities) > 0) {
            return $relatedEntities[0]->getId();
        }

        return null;
    }


    public function getChapterNumber()
    {
        if (isset($this->properties['field_te_chapternumber'])) {
            return $this->properties['field_te_chapternumber'][0]['@value'];
        }
        return 0;
    }


    /**
     * Get the related translation section.
     *
     * @return HeuristTranslationSection[]|null
     */
    public function addTranslationSectionToProperties()
    {
        $translationSection = \TTE\OmekaTranslationSection::search([
            'resource_template_id' => \TTE\OmekaMap::get('resourceTemplates', 'translationSection')['id'],
            'is_public' => 1,
            'sort_order' => 'asc',
            'per_page' => 10000,
            'property' => [
                [
                    'joiner' => 'and',
                    'property' => \TTE\OmekaMap::get('propertyIDs', 'field_schema_translationofwork')['id'],
                    'type' => \TTE\OmekaMap::get('propertyIDs', 'field_schema_translationofwork')['queryType'],
                    'text' => $this->getId()
                ]
            ]
        ], false)['entities'];


        $translationSectionObject = [];
        foreach ($translationSection as $section) {
            $translationSectionObject[] = [
                'type' => 'resource:item',
                'value_resource_id' => $section->getParentTranslationID(),
                'value_resource_name' => 'items',
                'property_label' => "Translation Section",
                'display_title' => $section->getTitle(),
            ];
        }

        if (count($translationSectionObject) > 0) {
            $this->properties['field_translation_section'] = $translationSectionObject;
        }
    }
}
