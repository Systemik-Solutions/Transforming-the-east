<?php

namespace TTE;

class OmekaTranslation extends OmekaEntity
{

    // Get the image media linked to this translation via intermediate source
    // Add them to properties as 'field_linked_media'
    public function addLinkedMediaToProperties()
    {

        $intermediaSources = $this->getProperty('field_te_intermediatesource');

        if (empty($intermediaSources)) {
            return;
        }

        $intermediaSourceIds = [];
        foreach ($intermediaSources as $source) {
            if ($source['type'] == 'resource:item') {
                $intermediaSourceIds[] = $source['value_resource_id'];
            }
        }

        $relatedTranslationsWithMedia = \TTE\OmekaTranslation::search(
            [
                'resource_template_id' => \TTE\OmekaMap::get('resourceTemplates', 'translation')['id'],
                'per_page' => count($intermediaSourceIds),
                'is_public' => 1,
                'sort_order' => 'desc',
                'id' => implode(',', $intermediaSourceIds),
                'has_media' => 1
            ],
            false
        )['entities'];


        if (empty($relatedTranslationsWithMedia)) {
            return;
        }

        $linkedMediaObject = [];
        foreach ($relatedTranslationsWithMedia as $translation) {
            if ($translation->getThumbnailImageUrl() != null) {
                $linkedMediaObject[] = [
                    'type' => 'resource:media',
                    'value_resource_id' => $translation->getId(),
                    'value_resource_name' => 'media',
                    'property_label' => 'Linked Media',
                    'display_title' => $translation->getTitle(),
                    'thumbnail_url' => $translation->getThumbnailImageUrl(),
                    'thumbnail_title' => $translation->getTitle(),
                    'thumbnail_type' => 'image/jpeg'
                ];
            }
        }

        if (count($linkedMediaObject) > 0) {
            $this->properties = array_merge(
                ['field_linked_media' => $linkedMediaObject],
                $this->properties
            );
        }
    }

    // Het the text id that is linked to this translation
    public function getLinkedTexts()
    {
        $linkedTexts = \TTE\OmekaEntity::search([
            'resource_template_id' => \TTE\OmekaMap::get('resourceTemplates', 'text')['id'],
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
        ], false)['entities'];

        if (empty($linkedTexts)) {
            return null;
        }

        return $linkedTexts[0]->getId();
    }

    // Get related translation sections
    // In the 'Translation of >' field , change "classicalsourceworksection" id to "classical source work" id
    public function getTranslationSections()
    {

        $res = [];
        $relatedIds = [];
        if (!empty($this->getProperty('field_te_section'))) {
            foreach ($this->getProperty('field_te_section') as $translationSection) {
                $relatedIds[] = $translationSection['value_resource_id'];
            }
        }

        if(empty($relatedIds)) {
            return null;
        }

        $translationSections = [];
        foreach ($relatedIds as $id) {
            $translationSection = \TTE\OmekaTranslationSection::load($id);
            if (isset($translationSection)) {
                $translationSections[] = $translationSection;
            }
        }

        foreach ($translationSections as $section) {
            $classsicalSourceWorkID = $section->getClassicalSourceWorkID();

            //Link to the correspond classical source work. . change classical source work section id to classical source work id
            if (isset($classsicalSourceWorkID)) {
                foreach ($section->properties['field_schema_translationofwork'] as $key => $value) {
                    $section->properties['field_schema_translationofwork'][$key]['value_resource_id'] = $classsicalSourceWorkID;
                }
            }
            $res[] = $section;
        }

        return count($res) > 0 ? $res : null;
    }


    // Get related translation copies as Omeka Entity array
    public function getTranslationCopies()
    {
        $translationCopyIds = [];
        if (!empty($this->getProperty('field_te_copy'))) {
            foreach ($this->getProperty('field_te_copy') as $translationCopy) {
                $translationCopyIds[] = $translationCopy['value_resource_id'];
            }
        }

        if (empty($translationCopyIds)) {
            return null;
        }

        $translationCopies = \TTE\OmekaEntity::search(
            [
                'resource_template_id' => \TTE\OmekaMap::get('resourceTemplates', 'translationCopy')['id'],
                'id' => implode(',', $translationCopyIds),
                'per_page' => count($translationCopyIds),
                'is_public' => 1,
                'sort_order' => 'desc',

            ],
            false
        )['entities'];

        return count($translationCopies) > 0 ? $translationCopies : null;
    }
}
