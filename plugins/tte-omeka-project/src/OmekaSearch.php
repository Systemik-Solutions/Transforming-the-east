<?php

namespace TTE;

use OMAKEAPI\OmekaClient;

class OmekaSearch
{

    /**
     * Do a search in Heurist.
     *
     * @param array|string $query
     *   The search query.
     * 
     * @param bool $enableCache
     *  Whether to enable caching for the request.
     * 
     * @param bool $depth
     * Whether to include all depth in query
     * 
     * @param array $additional_params
     * Additional query parameters to include in the request. 
     *
     * @return array|null
     *   The search results.
     */
    public static function search(array $query = [], $enableCache = false)
    {
        if(!isset($query['is_public'])) {
            $query['is_public'] = '1';
        }

        $omekaClient = new OmekaClient(
            OMEKA_API_ROUTE
        );

        if (!empty($query)) {
            $result = $omekaClient->get('items', $query, $enableCache);
            return $result;
        }
        return null;
    }


    /**
     * Search on entities.
     *
     * @param array|string $query
     *   The search query.
     *
     * @return OmekaEntity[]|null
     */
    public static function searchEntities(array $query = [], $enableCache = false)
    {

        $result = self::search($query, $enableCache);

        if (!$result || !isset($result['items'])) return ['items' => [], 'total' => 0];

        return [
            'entities' => array_map(function ($item) {
                return new OmekaEntity($item);
            }, $result['items'] ?? []),
            'total' => $result['total'] ?? 0
        ];
    }
}
