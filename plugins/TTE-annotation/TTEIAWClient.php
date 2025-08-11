<?php
class TTEIAWClient
{
    private $baseUrl;
    private $apiToken;

    public function __construct($baseUrl, $apiToken)
    {
        $this->baseUrl = $baseUrl;
        $this->apiToken = $apiToken;
    }


    public function get($url, $enableCache = false, $blockId = '')
    {

        if ($enableCache) {
            // Generate a cache key based on the request URL
            $cacheKey = 'tte_glycerine_' . $blockId . '_' . md5($url);
            // Attempt to retrieve cached data
            $cachedData = get_transient($cacheKey);

            // If we have cached data, return it
            if ($cachedData !== false) {
                return $cachedData;
            }
        }

        $response = wp_remote_get($url, [
            'timeout' => 180,
            'headers' => [
                'Authorization' => 'Bearer ' . $this->apiToken,
                'Accept' => 'application/json'
            ]
        ]);
        $body =  wp_remote_retrieve_body($response);

        if (!empty($body)) {

            $decodedData = json_decode($body, true);

            if ($enableCache) {
                // cache for 7 days
                set_transient($cacheKey, $decodedData, 7 * DAY_IN_SECONDS);
            }
            return $decodedData;
        }

        return null;
    }


    /**
     * Retrieves all collections accessible by the authenticated user.
     *
     * @return 
     */
    public function getAllCollections()
    {
        $url = $this->baseUrl . '/collections';
        $allCollections = $this->get($url, false);

        $res = [];
        foreach ($allCollections as $collection) {
            $res[] = [
                'id' => $collection['id'],
                'name' => $collection['name'],
                'description' => $collection['description'],
                'is_published' => isset($collection['publication']) && $collection['publication']['active'],
                'publication_id' => isset($collection['publication']) ? $collection['publication']['id'] : null
            ];
        }

        return $res;
    }

    /**
     * Retrieves a specific collection by its ID.
     *
     * @param string $id The ID of the collection to retrieve.
     * @return mixed The API response.
     */
    public function getCollection($id)
    {
        $url = $this->baseUrl . '/collections/' . $id;
        return $this->get($url);
    }

    /**
     * Searches within a collection based on given parameters.
     *
     * @param string $collectionId The ID of the collection to search within.
     * @param string $type The type of entity to search for (e.g., 'annotation', 'image').
     * @param array $queryParams An associative array of query parameters for the search.
     * @return mixed The API response, or null if an invalid type or query parameter is provided.
     */
    public function searchWithCollection($collectionId, $type, $queryParams = [], $enableCache = false, $blockId = '')
    {
        // Define the allowed types and query parameter keys
        $allowedTypes = ['annotation', 'image', 'image_set', 'annotation_set', 'tag'];
        $allowedQueryKeys = ['id', 'full_text', 'tag_label'];

        // Validate type
        if (!in_array($type, $allowedTypes)) {
            return null;
        }

        $url = $this->baseUrl . '/collections/' . $collectionId . '/search?type=' . urlencode($type);

        // Add other query parameters to the URL
        foreach ($queryParams as $key => $value) {
            if (!in_array($key, $allowedQueryKeys)) {
                continue;
            }

            if (is_array($value)) {
                foreach ($value as $val) {
                    $url .= '&' . $key . '[]=' . urlencode($val);
                }
            } else {
                $url .= '&' . $key . '=' . urlencode($value);
            }
        }

        return $this->get($url,  $enableCache, $blockId);
    }

    /**
     * Searches within a collection based on given parameters.
     *
     * @param array $queryParams An associative array of query parameters for the search.
     * @return mixed The API response, or null if an invalid type or query parameter is provided.
     */


    /**
     * Retrieves all image sets within a specific collection.
     *
     * @param string $collectionId The ID of the collection containing the image sets.
     * @return mixed The API response.
     */
    public function getAllImageSets($collectionId)
    {
        $url = $this->baseUrl . '/collections/' . $collectionId . '/image-sets';
        return $this->get($url);
    }

    /**
     * Retrieves all annotation sets within a specific image set.
     *
     * @param string $imageSetId The ID of the image set containing the annotation sets.
     * @return mixed The API response.
     */
    public function getAllAnnotationSets($imageSetId)
    {
        $url = $this->baseUrl . '/image-sets/' . $imageSetId . '/annotation-sets';
        return $this->get($url);
    }


    public function getAnnotation($annotation_set_id, $annotation_id, $enableCache = false, $blockId = '')
    {

        $url = $this->baseUrl . '/annotation-sets/' . $annotation_set_id . '/annotations/' . $annotation_id;
        return $this->get($url, $enableCache, $blockId);
    }

    public function getImageSet($collectionId, $imageSetId)
    {
        $url = $this->baseUrl . '/collections/' . $collectionId . '/image-sets/' . $imageSetId;
        return $this->get($url);
    }

    public function publishCollection($collectionId)
    {
        $url = $this->baseUrl . '/collections/' . $collectionId;

        $response = wp_remote_request($url, [
            'method'    => 'PUT',
            'timeout'   => 60,
            'headers'   => [
                'Authorization' => 'Bearer ' . $this->apiToken,
                'Accept'        => 'application/json',
                'Content-Type'  => 'application/json'
            ],
            'body' => json_encode([
                'published' => true
            ])
        ]);

        return wp_remote_retrieve_body($response);
    }

    public function unpublishCollection($collectionId)
    {
        $url = $this->baseUrl . '/collections/' . $collectionId;

        $response = wp_remote_request($url, [
            'method'    => 'PUT',
            'timeout'   => 60,
            'headers'   => [
                'Authorization' => 'Bearer ' . $this->apiToken,
                'Accept'        => 'application/json',
                'Content-Type'  => 'application/json'
            ],
            'body' => json_encode([
                'published' => false
            ])
        ]);

        return wp_remote_retrieve_body($response);
    }
}
