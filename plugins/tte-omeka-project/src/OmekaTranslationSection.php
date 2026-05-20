<?php

namespace TTE;

class OmekaTranslationSection extends OmekaEntity
{

    // Get first classical source work srction
    // Then get parent classical source work id
    public function getClassicalSourceWorkID()
    {

        $classicalSourceWorkSectionField = $this->getProperty('field_schema_translationofwork');

        if (empty($classicalSourceWorkSectionField)) {
            return null;
        }


        $classicalSourceWorkSection = \TTE\OmekaClassicalSourceWorkSection::load($classicalSourceWorkSectionField[0]['value_resource_id']);

        if (!isset($classicalSourceWorkSection)) {
            return null;
        }


        return $classicalSourceWorkSection->getClassicalSourceWorkID();
    }

    public function getParentTranslationID()
    {
        $parentTranslation = \TTE\OmekaTranslation::search(
            [
                'resource_template_id' => \TTE\OmekaMap::get('resourceTemplates', 'translation')['id'],
                'is_public' => 1,
                'sort_order' => 'asc',
                'property' => [
                    [
                        'joiner' => 'and',
                        'property' => \TTE\OmekaMap::get('propertyIDs', 'field_te_section')['id'],
                        'type' => \TTE\OmekaMap::get('propertyIDs', 'field_te_section')['queryType'],
                        'text' => $this->getId()
                    ]
                ]
            ],
            true
        )['entities'];


        if (count($parentTranslation) > 0) {
            return $parentTranslation[0]->getId();
        }

        return null;
    }
}
