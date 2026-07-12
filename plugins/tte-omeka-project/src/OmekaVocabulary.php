<?php

namespace TTE;

class OmekaVocabulary
{

    /**
     * Get the term list from a text field.
     *
     * @param string $fieldName
     *   The internal field name.
     *
     * @return array
     *   A list of terms, each element contains the 'id', 'label', and 'code' of the term.
     */
    public static function getFieldTerms($fieldName, $entities)
    {
        $termsById = [];     // Prevent duplicate resource items
        $labelsSeen = [];    // Prevent duplicate labels (for custom vocab)
        $results = [];

        foreach ($entities as $entity) {
            $fieldValues = $entity->getProperty($fieldName);

            if (empty($fieldValues)) {
                continue;
            }

            foreach ($fieldValues as $item) {

                // ------------------------------
                // Case 1: Resource item
                // ------------------------------
                if ($item['type'] === 'resource:item' && $item['value_resource_name'] === 'items') {

                    $termID = $item['value_resource_id'];

                    if (!isset($termsById[$termID])) {
                        $label = $item['display_title'] ?? '';

                        // Avoid duplicate label too
                        if (!isset($labelsSeen[$label])) {
                            $termsById[$termID] = [
                                'id' => $termID,
                                'label' => $label,
                            ];
                            $labelsSeen[$label] = true;
                        }
                    }
                }
                // ------------------------------
                // Case 2: Custom vocabulary
                // ------------------------------
                else if (strpos($item['type'], 'customvocab') === 0) {

                    $label = trim($item['@value']);

                    if ($label !== '' && !isset($labelsSeen[$label])) {
                        $results[] = [
                            'id' => $label,
                            'label' => $label
                        ];
                        $labelsSeen[$label] = true;
                    }
                }
            }
        }

        // Merge resource + custom vocab terms together
        $all = array_merge(array_values($termsById), $results);

        // Sort by label
        usort($all, function ($a, $b) {
            return strcmp($a['label'], $b['label']);
        });

        return $all;
    }

    public static function getAllEntities($enableCache = false)
    {
        // Omeka record type IDs.
        $recResourceTemplateIDs = [
            \TTE\OmekaMap::get('resourceTemplates', 'translation')['id'],
            \TTE\OmekaMap::get('resourceTemplates', 'person')['id'],
            \TTE\OmekaMap::get('resourceTemplates', 'place')['id'],
            \TTE\OmekaMap::get('resourceTemplates', 'classicalSourceWork')['id']
        ];

        $entities = OmekaSearch::searchEntities([
            'resource_template_id' =>  $recResourceTemplateIDs,
            'per_page' => 10000,
        ], $enableCache);

        return $entities['entities'];
    }
}
