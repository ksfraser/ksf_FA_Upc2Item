<?php
declare(strict_types=1);

namespace ksfraser\FrontAccounting\Upc2Item\Services;

use ksfraser\FrontAccounting\Upc2Item\Contracts\ProductSearchInterface;
use ksfraser\FrontAccounting\Upc2Item\Models\ScanResult;

/**
 * Product search service across multiple marketplaces.
 * 
 * Searches Amazon, eBay, and Facebook Marketplace by UPC.
 * Uses cURL with proper headers and timeout configuration.
 * 
 * @UML Note: Service class
 * @BABOK Related: FR-UPCS-003, FR-UPCS-004, FR-UPCS-005
 */
class ProductSearchService implements ProductSearchInterface
{
    /** @var array<string, array{url: string, enabled: bool}> */
    private $sources;

    /** @var int cURL timeout in seconds */
    private $timeout;

    /**
     * ProductSearchService constructor.
     * 
     * @param array<string, array{url?: string, enabled?: bool}> $sources
     * @param int $timeout
     */
    public function __construct(array $sources = [], int $timeout = 15)
    {
        $this->sources = $sources;
        $this->timeout = $timeout;
    }

    /**
     * Search for a product by UPC across enabled sources.
     * 
     * @param string $upc
     * @return ScanResult|null
     */
    public function searchByUpc(string $upc): ?ScanResult
    {
        $result = new ScanResult($upc);
        $found = false;

        // Amazon search via Product Advertising API or lightweight keepa-style lookup
        if (!empty($this->sources['Amazon']['enabled'])) {
            $amazonData = $this->searchAmazon($upc);
            if ($amazonData !== null) {
                $result->setTitle($amazonData['title'] ?? null)
                       ->setDescription($amazonData['description'] ?? null)
                       ->setImageUrl($amazonData['image_url'] ?? null)
                       ->setCategory($amazonData['category'] ?? null)
                       ->setBrand($amazonData['brand'] ?? null)
                       ->setModel($amazonData['model'] ?? null)
                       ->setAmazonPrice($amazonData['price'] ?? null)
                       ->setAmazonRetail($amazonData['retail'] ?? null);
                $found = true;
            }
        }

        // eBay search via Finding API
        if (!empty($this->sources['Ebay']['enabled'])) {
            $ebayData = $this->searchEbay($upc);
            if ($ebayData !== null) {
                if (!$found) {
                    $result->setTitle($ebayData['title'] ?? null)
                           ->setDescription($ebayData['description'] ?? null)
                           ->setImageUrl($ebayData['image_url'] ?? null)
                           ->setCategory($ebayData['category'] ?? null)
                           ->setBrand($ebayData['brand'] ?? null)
                           ->setModel($ebayData['model'] ?? null);
                    $found = true;
                }
                $result->setEbayPrice($ebayData['price'] ?? null)
                       ->setEbayRetail($ebayData['retail'] ?? null);
            }
        }

        // Facebook Marketplace search (public graph search limited)
        if (!empty($this->sources['Facebook']['enabled'])) {
            $fbData = $this->searchFacebook($upc);
            if ($fbData !== null) {
                if (!$found) {
                    $result->setTitle($fbData['title'] ?? null)
                           ->setDescription($fbData['description'] ?? null)
                           ->setImageUrl($fbData['image_url'] ?? null)
                           ->setCategory($fbData['category'] ?? null)
                           ->setBrand($fbData['brand'] ?? null)
                           ->setModel($fbData['model'] ?? null);
                    $found = true;
                }
                $result->setFacebookPrice($fbData['price'] ?? null)
                       ->setFacebookRetail($fbData['retail'] ?? null);
            }
        }

        return $found ? $result : null;
    }

    /**
     * Search Amazon by UPC.
     * 
     * @param string $upc
     * @return array<string, mixed>|null
     */
    private function searchAmazon(string $upc): ?array
    {
        $url = $this->sources['Amazon']['url'] ?? 'https://www.amazon.com/s?k=' . urlencode($upc);
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (compatible; UPC2Item/1.0; +https://example.com/bot)',
            CURLOPT_HTTPHEADER => [
                'Accept: text/html,application/xhtml+xml',
                'Accept-Language: en-US,en;q=0.9',
            ],
        ]);
        $html = curl_exec($ch);
        curl_close($ch);

        if ($html === false || empty($html)) {
            return null;
        }

        return $this->parseAmazon($html, $upc);
    }

    /**
     * Parse Amazon search result HTML.
     * 
     * @param string $html
     * @param string $upc
     * @return array<string, mixed>|null
     */
    private function parseAmazon(string $html, string $upc): ?array
    {
        $data = [
            'title' => null, 'description' => null, 'image_url' => null,
            'category' => null, 'brand' => null, 'model' => null,
            'price' => null, 'retail' => null,
        ];

        // Extract title
        if (preg_match('/<span[^>]+class="[^"]*a-size-medium[^"]*"[^>]*>(.*?)<\/span>/si', $html, $m)) {
            $data['title'] = strip_tags($m[1]);
        } elseif (preg_match('/<h2[^>]*>(.*?)<\/h2>/si', $html, $m)) {
            $data['title'] = strip_tags($m[1]);
        }

        // Extract image
        if (preg_match('/<img[^>]+class="[^"]*s-image[^"]*"[^>]+src="([^"]+)"/si', $html, $m)) {
            $data['image_url'] = $m[1];
        }

        // Extract price (whole + fraction)
        if (preg_match('/<span[^>]+class="[^"]*a-price[^"]*"[^>]*>\s*<span[^>]*>\s*\$([0-9,]+)<\/span>\s*<span[^>]*>\s*([0-9]+)/s', $html, $m)) {
            $data['price'] = (float)str_replace(',', '', $m[1]) + (float)$m[2] / 100;
            $data['retail'] = $data['price'];
        }

        if (empty($data['title']) && empty($data['price'])) {
            return null;
        }

        return $data;
    }

    /**
     * Search eBay by UPC using Finding API or search page.
     * 
     * @param string $upc
     * @return array<string, mixed>|null
     */
    private function searchEbay(string $upc): ?array
    {
        $url = $this->sources['Ebay']['url'] ?? 'https://www.ebay.com/sch/i.html?_nkw=' . urlencode($upc) . '&_sacat=0';
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (compatible; UPC2Item/1.0)',
        ]);
        $html = curl_exec($ch);
        curl_close($ch);

        if ($html === false || empty($html)) {
            return null;
        }

        return $this->parseEbay($html, $upc);
    }

    /**
     * Parse eBay search result HTML.
     * 
     * @param string $html
     * @param string $upc
     * @return array<string, mixed>|null
     */
    private function parseEbay(string $html, string $upc): ?array
    {
        $data = [
            'title' => null, 'description' => null, 'image_url' => null,
            'category' => null, 'brand' => null, 'model' => null,
            'price' => null, 'retail' => null,
        ];

        if (preg_match('/<h3[^>]+class="[^"]*s-item__title[^"]*"[^>]*>(.*?)<\/h3>/si', $html, $m)) {
            $data['title'] = strip_tags($m[1]);
        }

        if (preg_match('/<span[^>]+class="[^"]*s-item__price[^"]*"[^>]*>\s*\$([0-9.,]+)/s', $html, $m)) {
            $data['price'] = (float)str_replace(',', '', $m[1]);
            $data['retail'] = $data['price'];
        }

        if (empty($data['title']) && empty($data['price'])) {
            return null;
        }

        return $data;
    }

    /**
     * Search Facebook Marketplace by UPC.
     * 
     * @param string $upc
     * @return array<string, mixed>|null
     */
    private function searchFacebook(string $upc): ?array
    {
        $url = $this->sources['Facebook']['url'] ?? 'https://www.facebook.com/marketplace/search/?query=' . urlencode($upc);
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (compatible; UPC2Item/1.0)',
            CURLOPT_HTTPHEADER => [
                'Accept: text/html',
            ],
        ]);
        $html = curl_exec($ch);
        curl_close($ch);

        if ($html === false || empty($html)) {
            return null;
        }

        return $this->parseFacebook($html, $upc);
    }

    /**
     * Parse Facebook Marketplace HTML.
     * 
     * @param string $html
     * @param string $upc
     * @return array<string, mixed>|null
     */
    private function parseFacebook(string $html, string $upc): ?array
    {
        $data = [
            'title' => null, 'description' => null, 'image_url' => null,
            'category' => null, 'brand' => null, 'model' => null,
            'price' => null, 'retail' => null,
        ];

        if (preg_match('/<span[^>]+class="[^"]*x1lliihq[^"]*"[^>]*>\s*\$([0-9.,]+)/s', $html, $m)) {
            $data['price'] = (float)str_replace(',', '', $m[1]);
            $data['retail'] = $data['price'];
        }

        if (preg_match('/<span[^>]+class="[^"]*x1lliihq[^"]*"[^>]*>([^<]+)<\/span>/s', $html, $m)) {
            $data['title'] = trim($m[1]);
        }

        if (empty($data['title']) && empty($data['price'])) {
            return null;
        }

        return $data;
    }
}
