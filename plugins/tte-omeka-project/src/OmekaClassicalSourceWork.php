<?php

namespace TTE;

class OmekaClassicalSourceWork extends OmekaEntity
{


    public function getClassicalSourceWorkSection()
    {
        $res = [];
        $relatedIds = [];

        $realated = $this->getProperty('field_schema_haspart');
        foreach ($realated as $related) {
            if ($related['type'] == 'resource:item') {
                $relatedIds[] = $related['value_resource_id'];
            }
        }

        if (empty($relatedIds)) {
            return null;
        }


        $classicalSourceWorkSections = \TTE\OmekaClassicalSourceWorkSection::search([
            'resource_template_id' => \TTE\OmekaMap::get('resourceTemplates', 'classicalSourceWorkSection')['id'],
            'id' => implode(',', $relatedIds),
            'per_page' => count($relatedIds),
            'is_public' => 1,
            'sort_order' => 'asc',
        ], false)['entities'];

        foreach ($classicalSourceWorkSections as $classicalSourceWorkSection) {
            $classicalSourceWorkSection->addTranslationSectionToProperties();
            $res[] = $classicalSourceWorkSection;
        }

        usort($res, function ($a, $b) {
            return $a->getChapterNumber() - $b->getChapterNumber();
        });

        return count($res) > 0 ? $res : null;
    }

    /**
     * Get the related translation. Order by year
     *
     * @return OmekaEntity[]|null
     */
    public function getTranslation()
    {

        $query =  [
            'resource_template_id' => \TTE\OmekaMap::get('resourceTemplates', 'translation')['id'],
            'is_public' => 1,
            'sort_by'    => 'title',
            'per_page' => 10000,
            'property' => [
                [
                    'joiner' => 'and',
                    'property' => \TTE\OmekaMap::get('propertyIDs', 'field_te_classicalsource')['id'],
                    'type' => \TTE\OmekaMap::get('propertyIDs', 'field_te_classicalsource')['queryType'],
                    'text' => $this->getId()
                ]
            ]
        ];

        $translations = \TTE\OmekaEntity::search($query, false)['entities'];
        return $translations;
    }
}
